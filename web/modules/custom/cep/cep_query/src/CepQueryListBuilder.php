<?php

namespace Drupal\cep_query;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Cep query entities.
 *
 * @ingroup cep_query
 */
class CepQueryListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Cep query ID');
    $header['name'] = $this->t('Name');
    $header['proxy_type'] = [
      'data' => $this->t('Proxy type'),
      'field' => 'proxy_type',
      'specifier' => 'proxy_type',
    ];
    $header['job_status'] = [
      'data' => $this->t('Job status'),
      'field' => 'job_status',
      'specifier' => 'job_status',
    ];
    $header['created'] = [
      'data' => $this->t('Created'),
      'field' => 'created',
      'specifier' => 'created',
    ];
    $header['changed'] = [
      'data' => $this->t('Changed'),
      'field' => 'changed',
      'specifier' => 'changed',
    ];
    $header['completed'] = [
      'data' => $this->t('completed'),
      'field' => 'completed',
      'specifier' => 'completed',
    ];
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\cep_proxy\Entity\CepProxy $entity */
    $created = \Drupal::service('date.formatter')->formatTimeDiffSince($entity->created->value);
    $changed = \Drupal::service('date.formatter')->formatTimeDiffSince($entity->changed->value);
    $completed = \Drupal::service('date.formatter')->formatTimeDiffSince($entity->completed->value);

    /* @var \Drupal\cep_query\Entity\CepQuery $entity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.cep_query.edit_form',
      ['cep_query' => $entity->id()]
    );
    $row['proxy_type'] = $entity->proxy_type->value;
    $row['job_status'] = $entity->getQueryStatus($entity->job_status->value);
    $row['created'] = $created;
    $row['changed'] = $completed;
    $row['completed'] = $completed;
    $row['completed'] = $completed;
    return $row + parent::buildRow($entity);
  }

}
