<?php

namespace Drupal\cep_parser_item;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface defining a cep_parser_item entity type.
 */
interface CepParserItemInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

  /**
   * Gets the cep_parser_item title.
   *
   * @return string
   *   Title of the cep_parser_item.
   */
  public function getTitle();

  /**
   * Sets the cep_parser_item title.
   *
   * @param string $title
   *   The cep_parser_item title.
   *
   * @return \Drupal\cep_parser_item\CepParserItemInterface
   *   The called cep_parser_item entity.
   */
  public function setTitle($title);

  /**
   * Gets the cep_parser_item creation timestamp.
   *
   * @return int
   *   Creation timestamp of the cep_parser_item.
   */
  public function getCreatedTime();

  /**
   * Sets the cep_parser_item creation timestamp.
   *
   * @param int $timestamp
   *   The cep_parser_item creation timestamp.
   *
   * @return \Drupal\cep_parser_item\CepParserItemInterface
   *   The called cep_parser_item entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the cep_parser_item status.
   *
   * @return bool
   *   TRUE if the cep_parser_item is enabled, FALSE otherwise.
   */
  public function isEnabled();

  /**
   * Sets the cep_parser_item status.
   *
   * @param bool $status
   *   TRUE to enable this cep_parser_item, FALSE to disable.
   *
   * @return \Drupal\cep_parser_item\CepParserItemInterface
   *   The called cep_parser_item entity.
   */
  public function setStatus($status);

}
