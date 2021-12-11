<?php

namespace Drupal\cep_query\Services;

use Drupal\cep_query\Entity\CepQuery;
use Drupal\cep_proxy\Entity\CepProxy;

/**
 * Class CepProxyService.
 */
class CepQueryServiceJob {

  const CACHE_URI = 'public://cep_query_cache/';
  const CACHE_COOK_FILE = 'public://cep_query_cookie/cookies.txt';
  const CACHE_COOK_DIR = 'public://cep_query_cookie/';

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
  public function getServiceName() {
    return "cep_query.cep_query_service_job";
  }

  /**
   * {@inheritdoc}
   */
  public function startServiceJob($proxy = FALSE) {
    // Get url for job.
    $queryEntity = \Drupal::service('cep_query.cep_query_service')->getQuery();
    if (empty($queryEntity)) {
      \Drupal::logger('cep_query')->info("Service has not free query. All queries completed!");
      return FALSE;
    }

    \Drupal::service('cep_query.cep_query_service')->setJobStatusQuery($queryEntity->id->value, CepQuery::BUSY);

    $html = $this->cepQueryGetCacheHtmlByUrl($queryEntity->url->value);
    if ($html) {
      \Drupal::logger('cep_query')->info("{$queryEntity->id->value} COMPLETED from cache " . strlen($html));
      \Drupal::service('cep_query.cep_query_service')->setJobStatusQuery($queryEntity->id->value, CepQuery::COMPLETED);
      $queryEntity->job_status->value = CepQuery::COMPLETED;
      $queryEntity->file_data_name->value = $this->cepQueryGetHashByUrl($queryEntity->url->value);
      $queryEntity->save();
      return $queryEntity;
    }
    $ip = "";
    if ($proxy) {
      // Get free proxy ip.
      $ip = \Drupal::service('cep_proxy.cep_proxy_service')->getFreeProxy($queryEntity->proxy_type->value);
      if (empty($ip)) {
        \Drupal::logger('cep_query')->info("Service has not free ip");
        \Drupal::service('cep_query.cep_query_service')->setJobStatusQuery($queryEntity->id->value, CepQuery::FREE);
        return FALSE;
      }
    }
    // Proxy ip is busy.
    if ($proxy) {
      \Drupal::service('cep_proxy.cep_proxy_service')->setStatusIpProxy($ip, CepProxy::BUSY);
    }
    \Drupal::service('cep_query.cep_query_service')->setJobStatusQuery($queryEntity->id->value, CepQuery::PERFORMING);
    try {
      $html = $this->retrieveDataForServiceJob($queryEntity->url->value, $ip);
      // $html = \Drupal::service('cep_query.cep_query_service_job_curl')->retrieveData($queryEntity->url->value, $ip);
    }
    catch (\Throwable $th) {
      \Drupal::logger('cep_query')->alert($th->getMessage() . "\n " . $th->getFile() . " " . $th->getLine() . "\n " . $th->getTraceAsString());
    }
    if (strlen($html) > 100) {
      \Drupal::logger('cep_query')->info("{$queryEntity->id->value} COMPLETED " . strlen($html));

      if ($proxy) {
        \Drupal::service('cep_proxy.cep_proxy_service')->setStatusIpProxy($ip, CepProxy::FREE);
      }
      \Drupal::service('cep_query.cep_query_service')->setJobStatusQuery($queryEntity->id->value, CepQuery::COMPLETED);
      $queryEntity->job_status->value = CepQuery::COMPLETED;
      $queryEntity->file_data_name->value = $this->cepQueryGetHashByUrl($queryEntity->url->value);
      $queryEntity->save();
    }
    elseif (strlen($html) < 100) {
      if ($proxy) {
        \Drupal::logger('cep_query')->info("$ip failed " . strlen($html));
        \Drupal::service('cep_proxy.cep_proxy_service')->setStatusIpProxy($ip, CepProxy::FAIL);
      }
      \Drupal::service('cep_query.cep_query_service')->setJobStatusQuery($queryEntity->id->value, CepQuery::FREE);
    }
    elseif (strlen($html) == 0) {
      if ($proxy) {
        \Drupal::logger('cep_query')->info("$ip failed length zero");
        \Drupal::service('cep_proxy.cep_proxy_service')->setStatusIpProxy($ip, CepProxy::FAIL);
      }
      \Drupal::service('cep_query.cep_query_service')->setJobStatusQuery($queryEntity->id->value, CepQuery::FREE);
    }

    return $queryEntity;
  }

  /**
   * {@inheritdoc}
   */
  public function retrieveDataForServiceJob($url, $ip) {
    $hash_filename_uri = md5($url);
    $html = $this->cepQueryGetCacheHtml($hash_filename_uri);
    if (!$html) {
      $html = \Drupal::service('cep_query.cep_query_service_job_curl')->retrieveData($url, $ip);
      $this->cepQuerySetCacheHtml($hash_filename_uri, $html);
    }
    return $html;
  }

  public function cepQuerySetCacheHtml($hash_filename_uri, $htmlStr) {
    $filePath = self::CACHE_URI . $hash_filename_uri;
    if (!is_file($filePath)) {
      try {
        file_put_contents($filePath, $htmlStr);
      }
      catch (\Throwable $th) {
        return FALSE;
      }
      return TRUE;
    }
    return FALSE;
  }

  public function cepQueryGetCacheHtml($hash_filename_uri) {
    $filePath = self::CACHE_URI . $hash_filename_uri;
    if (is_file($filePath)) {
      return file_get_contents($filePath);
    }
    return FALSE;
  }

  public function cepQueryGetCacheHtmlByUrl($url) {
    $hash_filename_uri = md5($url);
    $filePath = self::CACHE_URI . $hash_filename_uri;
    if (is_file($filePath)) {
      return file_get_contents(self::CACHE_URI . $hash_filename_uri);
    }
    return FALSE;
  }

  public function cepQueryGetHashByUrl($url){
    $hash_filename_uri = md5($url);
    return $hash_filename_uri;
  }

}
