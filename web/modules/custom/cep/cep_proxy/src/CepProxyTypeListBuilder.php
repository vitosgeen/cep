<?php

namespace Drupal\cep_proxy;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Provides a listing of CEP proxy type entities.
 */
class CepProxyTypeListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('CEP proxy type');
    $header['id'] = $this->t('Machine name');
    $header['url'] = $this->t('External URL');
    $header['aHrefList'] = $this->t('List');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label();
    $row['id'] = $entity->id();
    $row['url'] = $entity->url();
    $url = Url::fromUri('internal:/admin/cep/cep_proxy?id=' . $entity->id());
    $link = Link::fromTextAndUrl($this->t('List'), $url)->toString();
    $row['aHrefList'] = $link;
    // You probably want a few more properties here...
    return $row + parent::buildRow($entity);
  }

}
