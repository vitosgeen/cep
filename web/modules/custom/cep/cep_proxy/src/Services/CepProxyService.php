<?php

namespace Drupal\cep_proxy\Services;

use Drupal\cep_proxy\Entity\CepProxy;

/**
 * Class CepProxyService. Service cep_proxy.cep_proxy_service.
 */
class CepProxyService {

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

}
