<?php

namespace Drupal\cep_proxy\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the CEP proxy type entity.
 *
 * @ConfigEntityType(
 *   id = "cep_proxy_type",
 *   label = @Translation("CEP proxy type"),
 *   url = @Translation("CEP proxy url"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\cep_proxy\CepProxyTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\cep_proxy\Form\CepProxyTypeForm",
 *       "edit" = "Drupal\cep_proxy\Form\CepProxyTypeForm",
 *       "delete" = "Drupal\cep_proxy\Form\CepProxyTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\cep_proxy\CepProxyTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "cep_proxy_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "cep_proxy",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "url" = "url",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/cep/cep_proxy_type/{cep_proxy_type}",
 *     "add-form" = "/admin/cep/cep_proxy_type/add",
 *     "edit-form" = "/admin/cep/cep_proxy_type/{cep_proxy_type}/edit",
 *     "delete-form" = "/admin/cep/cep_proxy_type/{cep_proxy_type}/delete",
 *     "collection" = "/admin/cep/cep_proxy_type"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "url",
 *     "uuid"
 *   }
 * )
 */
class CepProxyType extends ConfigEntityBundleBase implements CepProxyTypeInterface {

  /**
   * The CEP proxy type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The CEP proxy type label.
   *
   * @var string
   */
  protected $label;

  /**
   * The CEP proxy type url.
   *
   * @var string
   */
  protected $url;

  /**
   * Get id cep proxy type entity.
   */
  public function getIdCepProxyType() {
    return $this->id;
  }

  /**
   * Get url cep proxy type entity.
   */
  public function url() {
    return $this->url;
  }

}
