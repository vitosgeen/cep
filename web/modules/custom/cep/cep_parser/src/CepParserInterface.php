<?php

namespace Drupal\cep_parser;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface defining a cep_parser entity type.
 */
interface CepParserInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

  /**
   * Gets the cep_parser title.
   *
   * @return string
   *   Title of the cep_parser.
   */
  public function getTitle();

  /**
   * Sets the cep_parser title.
   *
   * @param string $title
   *   The cep_parser title.
   *
   * @return \Drupal\cep_parser\CepParserInterface
   *   The called cep_parser entity.
   */
  public function setTitle($title);

  /**
   * Gets the cep_parser creation timestamp.
   *
   * @return int
   *   Creation timestamp of the cep_parser.
   */
  public function getCreatedTime();

  /**
   * Sets the cep_parser creation timestamp.
   *
   * @param int $timestamp
   *   The cep_parser creation timestamp.
   *
   * @return \Drupal\cep_parser\CepParserInterface
   *   The called cep_parser entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the cep_parser status.
   *
   * @return bool
   *   TRUE if the cep_parser is enabled, FALSE otherwise.
   */
  public function isEnabled();

  /**
   * Sets the cep_parser status.
   *
   * @param bool $status
   *   TRUE to enable this cep_parser, FALSE to disable.
   *
   * @return \Drupal\cep_parser\CepParserInterface
   *   The called cep_parser entity.
   */
  public function setStatus($status);

}
