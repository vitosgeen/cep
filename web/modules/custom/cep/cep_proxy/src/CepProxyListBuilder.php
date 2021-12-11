<?php

namespace Drupal\cep_proxy;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Defines a class to build a listing of CEP proxy entities.
 *
 * @ingroup cep_proxy
 */
class CepProxyListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function load() {
    $entity_query = \Drupal::entityTypeManager()->getStorage('cep_proxy')->getQuery();
    $entity_query->pager(50);
    $header = $this->buildHeader();
    $entity_query->tableSort($header);
    $entity_ids = $entity_query->execute();
    return $this->storage->loadMultiple($entity_ids);
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = [
      'data' => $this->t('CEP proxy ID'),
      'field' => 'id',
      'specifier' => 'id',
    ];
    $header['type'] = [
      'data' => $this->t('Type'),
      'field' => 'type',
      'specifier' => 'type',
    ];
    $header['ip'] = [
      'data' => $this->t('ip'),
      'field' => 'ip',
      'specifier' => 'ip',
    ];
    $header['status'] = [
      'data' => $this->t('status'),
      'field' => 'ip_status',
      'specifier' => 'ip_status',
    ];
    $header['last_call'] = [
      'data' => $this->t('last call'),
      'field' => 'call_date',
      'specifier' => 'call_date',
    ];
    $header['call_counter'] = [
      'data' => $this->t('Total counter'),
      'field' => 'call_counter',
      'specifier' => 'call_counter',
    ];
    $header['last_call_positive'] = [
      'data' => $this->t('ok date'),
      'field' => 'call_date_ok',
      'specifier' => 'call_date_ok',
    ];
    $header['positive'] = [
      'data' => $this->t('+'),
      'field' => 'call_counter_ok',
      'specifier' => 'call_counter_ok',
    ];
    $header['last_call_negative'] = [
      'data' => $this->t('failed date'),
      'field' => 'call_date_failed',
      'specifier' => 'call_date_failed',
    ];
    $header['negative'] = [
      'data' => $this->t('-'),
      'field' => 'call_counter_failed',
      'specifier' => 'call_counter_failed',
    ];
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\cep_proxy\Entity\CepProxy $entity */
    $lastCallDate = \Drupal::service('date.formatter')->formatTimeDiffSince($entity->call_date->value);
    $lastCallDateOk = \Drupal::service('date.formatter')->formatTimeDiffSince($entity->call_date_ok->value);
    $lastCallDateFail = \Drupal::service('date.formatter')->formatTimeDiffSince($entity->call_date_failed->value);

    $row['id'] = $entity->id();
    $row['type'] = $entity->bundle();
    $row['ip'] = $entity->ip->value;
    $row['status'] = $entity->getIpStatus($entity->ip_status->value);
    $row['last_call'] = $lastCallDate;
    $row['call_counter'] = $entity->call_counter->value;
    $row['last_call_positive'] = $lastCallDateOk;
    $row['positive'] = $entity->call_counter_ok->value;
    $row['last_call_negative'] = $lastCallDateFail;
    $row['negative'] = $entity->call_counter_failed->value;

    return $row + parent::buildRow($entity);
  }

}
