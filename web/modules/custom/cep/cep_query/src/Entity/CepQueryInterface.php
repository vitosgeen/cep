<?php

namespace Drupal\cep_query\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Cep query entities.
 *
 * @ingroup cep_query
 */
interface CepQueryInterface extends ContentEntityInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Cep query name.
   *
   * @return string
   *   Name of the Cep query.
   */
  public function getName();

  /**
   * Sets the Cep query name.
   *
   * @param string $name
   *   The Cep query name.
   *
   * @return \Drupal\cep_query\Entity\CepQueryInterface
   *   The called Cep query entity.
   */
  public function setName($name);

  /**
   * Gets the Cep query creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Cep query.
   */
  public function getCreatedTime();

  /**
   * Sets the Cep query creation timestamp.
   *
   * @param int $timestamp
   *   The Cep query creation timestamp.
   *
   * @return \Drupal\cep_query\Entity\CepQueryInterface
   *   The called Cep query entity.
   */
  public function setCreatedTime($timestamp);

}
