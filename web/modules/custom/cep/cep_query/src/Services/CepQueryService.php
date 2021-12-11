<?php

namespace Drupal\cep_query\Services;

use Drupal\cep_query\Entity\CepQuery;

/**
 * Class CepProxyService.
 * $ip = \Drupal::service('cep_proxy.cep_proxy_service')->getFreeProxy("proxy11_com");
 */
class CepQueryService {

  /**
   * The lock backend.
   *
   * @var \Drupal\Core\Lock\LockBackendInterface
   */
  protected $lock;

  /**
   * {@inheritdoc}
   */
  public function __construct() {

  }

  /**
   * {@inheritdoc}
   */
  public function getQueryTypes() {
    $types = \Drupal::entityTypeManager()->getStorage('cep_query_type')->loadByProperties(['type' => 'article']);
    $result = [];
    foreach ($types as $k => $p) {
      $result[$k] = $p->label();
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function getQuery() {
    $entity = \Drupal::entityTypeManager()->getStorage('cep_query');
    $ids = CepQuery::getQueries(1);
    $q = NULL;
    foreach ($ids as $key => $value) {
      $q = $entity->load($value);
    }
    if ($q) {
      return $q;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function setJobStatusQuery($id, $status) {
    $queryEntity = CepQuery::load($id);
    if (!$queryEntity) {
      return FALSE;
    }
    $currentStatus = CepQuery::getQueryStatus($status);
    if ($currentStatus == CepQuery::COMPLETED) {
      $queryEntity->completed->value = time();
    }
    if ($currentStatus) {
      $queryEntity->job_status->value = $status;
      $queryEntity->save();
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function createQueryByUrl($url, $type = "available") {
    $cepQueryEntity = \Drupal::entityTypeManager()->getStorage('cep_query');
    $cepQuery = $cepQueryEntity->loadByProperties(['url' => $url]);
    if (empty($cepQuery)) {
      $entityCepQuery = CepQuery::create(['type' => $type]);
      $entityCepQuery->name->value = $url;
      $entityCepQuery->url->value = $url;
      $entityCepQuery->proxy_type->value = 0;
      $entityCepQuery->regular->value = 30;
      $entityCepQuery->save();
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getQueryByUrl($url, $type = "available") {
    $cepQueryEntity = \Drupal::entityTypeManager()->getStorage('cep_query');
    $cepQueries = $cepQueryEntity->loadByProperties([
      'url' => $url,
      'job_status' => CepQuery::COMPLETED,
      'type' => $type,
    ]);
    $cepQuery = NULL;
    foreach ($cepQueries as $k => $q) {
      if ($q) {
        $cepQuery = $q;
      }
    }
    if (!empty($cepQuery)) {
      return $cepQuery;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getQueryByStatus($status, $type = "available") {
    $cepQueryEntity = \Drupal::entityTypeManager()->getStorage('cep_query');
    $cepQueries = $cepQueryEntity->loadByProperties([
      'job_status' => $status,
    ]);
    $cepQuery = NULL;
    foreach ($cepQueries as $k => $q) {
      if ($q) {
        $cepQuery = $q;
      }
    }

    if (!empty($cepQuery)) {
      return $cepQuery;
    }
    return FALSE;
  }

}
