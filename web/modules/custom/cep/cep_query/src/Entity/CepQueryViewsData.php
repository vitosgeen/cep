<?php

namespace Drupal\cep_query\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Cep query entities.
 */
class CepQueryViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.
    return $data;
  }

}
