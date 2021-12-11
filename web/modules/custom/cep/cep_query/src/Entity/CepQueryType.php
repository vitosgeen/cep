<?php

namespace Drupal\cep_query\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Cep query type entity.
 *
 * @ConfigEntityType(
 *   id = "cep_query_type",
 *   label = @Translation("Cep query type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\cep_query\CepQueryTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\cep_query\Form\CepQueryTypeForm",
 *       "edit" = "Drupal\cep_query\Form\CepQueryTypeForm",
 *       "delete" = "Drupal\cep_query\Form\CepQueryTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\cep_query\CepQueryTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "cep_query_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "cep_query",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "tquery" = "tquery",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/cep_query_type/{cep_query_type}",
 *     "add-form" = "/admin/structure/cep_query_type/add",
 *     "edit-form" = "/admin/structure/cep_query_type/{cep_query_type}/edit",
 *     "delete-form" = "/admin/structure/cep_query_type/{cep_query_type}/delete",
 *     "collection" = "/admin/structure/cep_query_type"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "tquery",
 *     "uuid"
 *   }
 * )
 */
class CepQueryType extends ConfigEntityBundleBase implements CepQueryTypeInterface {

  /**
   * The Cep query type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Cep query type label.
   *
   * @var string
   */
  protected $label;

  /**
   * The Cep query type retrive data.
   *
   * @var string
   */
  protected $tquery;

  public function tquery() {
    return $this->tquery;
  }

}
