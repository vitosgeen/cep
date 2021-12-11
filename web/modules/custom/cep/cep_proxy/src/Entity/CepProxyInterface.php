<?php

namespace Drupal\cep_proxy\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;

/**
 * Provides an interface for defining CEP proxy entities.
 *
 * @ingroup cep_proxy
 */
interface CepProxyInterface extends ContentEntityInterface, EntityChangedInterface, EntityPublishedInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the CEP proxy name.
   *
   * @return string
   *   Name of the CEP proxy.
   */
  public function getName();

  /**
   * Sets the CEP proxy name.
   *
   * @param string $name
   *   The CEP proxy name.
   *
   * @return \Drupal\cep_proxy\Entity\CepProxyInterface
   *   The called CEP proxy entity.
   */
  public function setName($name);

  /**
   * Gets the CEP proxy creation timestamp.
   *
   * @return int
   *   Creation timestamp of the CEP proxy.
   */
  public function getCreatedTime();

  /**
   * Sets the CEP proxy creation timestamp.
   *
   * @param int $timestamp
   *   The CEP proxy creation timestamp.
   *
   * @return \Drupal\cep_proxy\Entity\CepProxyInterface
   *   The called CEP proxy entity.
   */
  public function setCreatedTime($timestamp);

}
