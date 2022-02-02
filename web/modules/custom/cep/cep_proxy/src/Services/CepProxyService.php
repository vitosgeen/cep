<?php

namespace Drupal\cep_proxy\Services;

use Drupal\cep_proxy\Entity\CepProxy;
use GuzzleHttp\Client;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class CepProxyService. Service cep_proxy.cep_proxy_service.
 */
class CepProxyService {

  use StringTranslationTrait;

  /**
   * The lock backend.
   *
   * @var \Drupal\Core\Lock\LockBackendInterface
   */
  protected $lock;


  /**
   * {@inheritdoc}
   */
  public $freeProxyEntity;

  /**
   * {@inheritdoc}
   */
  public function __construct() {

  }

  /**
   * {@inheritdoc}
   */
  public function getTypesProxy() {
    $types_proxy = \Drupal::entityTypeManager()->getStorage('cep_proxy_type')->loadByProperties();
    $result = [];
    foreach ($types_proxy as $k => $p) {
      $result[$k] = $p->label();
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function clearTypesProxiesIps() {
    $types_proxy = \Drupal::entityTypeManager()->getStorage('cep_proxy_type')->loadByProperties();
    $result = [];
    foreach ($types_proxy as $k => $p) {
      $result[$p->id()] = $p->label();
      $entity_proxy_type = \Drupal::entityTypeManager()->getStorage('cep_proxy_type')->load($p->id());
      $iterations = 10;
      $params = [
        'type' => $p->id(),
        'limit' => 100,
      ];
      $entityIp = CepProxy::create([
        'type' => $p->id(),
      ]);
      for($i = 0; $i < $iterations; $i++){
        $data = CepProxy::getEntities($params);
        foreach($data as $strIp) {          
          $ipEntity = $entityIp->load($strIp);
          $ipEntity->delete();
        }
        if (count($data) == $params['limit']) {
          $iterations++;
        }
        else {
          break;
        }
      }
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function updateTypesProxiesIps() {
    $types_proxy = \Drupal::entityTypeManager()->getStorage('cep_proxy_type')->loadByProperties();
    $result = [];
    $cc = 0;
    $cc_d = 0;
    foreach ($types_proxy as $k => $p) {
      $result[$p->id()] = $p->label();
      $entity_proxy_type = \Drupal::entityTypeManager()->getStorage('cep_proxy_type')->load($p->id());
      $url = $entity_proxy_type->url();
      $urlIpsListStr = "";
      if (!empty($url)) {
        $response = $this->getHttpRequest($url);
        if($response !== FALSE){
          $urlIpsListStr = $response;
        }
        $proxyList = preg_split('[\n]', $urlIpsListStr);
        $entityProxy = CepProxy::create([
          'type' => $p->id(),
        ]);
        foreach ($proxyList as $proxyIp) {
          if (empty($proxyIp)) {
            continue;
          }
          $proxy = str_replace("\r", "", $proxyIp);
          $name = md5($proxy);
          $pe = $entityProxy->getEntityByName($name);
          if (!$pe) {
            $e = CepProxy::create([
              'type' => $p->id(),
            ]);
            $e->name->value = $name;
            $e->ip->value = $proxy;
            $e->save();
            $cc++;
          }
          else {
            $this->setStatusIpProxy($proxy, CepProxy::FREE);
            $cc_d++;
          }
        }
      }
    }
    $message = $this->t('Items were added %value items of IP', ['%value' => $cc]);
    \Drupal::logger('cep_proxy')->info($message);
    $message = $this->t('Duplicates were proved %value items of IP', ['%value' => $cc_d]);
    \Drupal::logger('cep_proxy')->info($message);

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function getFreeProxy($type) {
    $params = [
      'status' => CepProxy::FREE,
      'limit' => 1,
    ];
    $entity = CepProxy::create([
      'type' => $type,
    ]);

    $freeProxyIp = NULL;
    $proxies = $entity->getEntities($params);
    if ($proxies) {
      $id = current($proxies);
      $x = $entity->load($id);
      $this->freeProxyEntity = $x;
      if ($x) {
        $freeProxyIp = $x->ip->value;
      }
    }
    return $freeProxyIp;
  }

  /**
   * {@inheritdoc}
   */
  public function setStatusIpProxy($ip, $status) {
    $proxyEntity = CepProxy::getEntityByIp($ip);
    if (!$proxyEntity) {
      return FALSE;
    }
    if ($status == CepProxy::BUSY) {
      $proxyEntity->call_counter->value++;
      $proxyEntity->call_date->value = time();
    }
    if ($status == CepProxy::FREE) {
      $proxyEntity->call_counter_ok->value++;
      $proxyEntity->call_date_ok->value = time();
    }
    if ($status == CepProxy::FAIL) {
      $proxyEntity->call_counter_failed->value++;
      $proxyEntity->call_date_failed->value = time();
    }
    $proxyEntity->ip_status->value = $status;
    $proxyEntity->save();
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function setBusyIpProxy($ip) {
    $proxyEntity = CepProxy::getEntityByIp($ip);
    if (!$proxyEntity) {
      return FALSE;
    }

    $proxyEntity->call_counter->value++;
    $proxyEntity->call_date->value = time();
    $proxyEntity->ip_status->value = CepProxy::BUSY;
    $proxyEntity->save();

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function setFreeIpProxy($ip) {
    $proxyEntity = CepProxy::getEntityByIp($ip);
    if (!$proxyEntity) {
      return FALSE;
    }
    $proxyEntity->call_counter_ok->value++;
    $proxyEntity->call_date_ok->value = time();
    $proxyEntity->ip_status->value = CepProxy::FREE;
    $proxyEntity->save();
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function setFailIpProxy($ip) {
    $proxyEntity = CepProxy::getEntityByIp($ip);
    if (!$proxyEntity) {
      return FALSE;
    }
    $proxyEntity->call_counter_failed->value++;
    $proxyEntity->call_date_failed->value = time();
    $proxyEntity->ip_status->value = CepProxy::FAIL;
    $proxyEntity->save();
    return TRUE;
  }

  /**
   * Get http request.
   */
  public static function getHttpRequest($url) {
    try {
      $httpClient = new Client();

      $request = $httpClient->request(
          'GET', $url,
      );
      $response = $request->getBody()->getContents();
      return $response;
    }
    catch (Exception $ex) {
      var_dump($ex);
      return FALSE;
    }
  }

}
