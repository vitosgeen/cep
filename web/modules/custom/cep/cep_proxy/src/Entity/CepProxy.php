<?php

namespace Drupal\cep_proxy\Entity;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Defines the CEP proxy entity.
 *
 * @ingroup cep_proxy
 *
 * @ContentEntityType(
 *   id = "cep_proxy",
 *   label = @Translation("CEP proxy"),
 *   bundle_label = @Translation("CEP proxy type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\cep_proxy\CepProxyListBuilder",
 *     "views_data" = "Drupal\cep_proxy\Entity\CepProxyViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\cep_proxy\Form\CepProxyForm",
 *       "add" = "Drupal\cep_proxy\Form\CepProxyForm",
 *       "edit" = "Drupal\cep_proxy\Form\CepProxyForm",
 *       "delete" = "Drupal\cep_proxy\Form\CepProxyDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\cep_proxy\CepProxyHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\cep_proxy\CepProxyAccessControlHandler",
 *   },
 *   base_table = "cep_proxy",
 *   translatable = FALSE,
 *   admin_permission = "administer cep proxy entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "ip" = "ip",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/cep/cep_proxy/{cep_proxy}",
 *     "add-page" = "/admin/cep/cep_proxy/add",
 *     "add-form" = "/admin/cep/cep_proxy/add/{cep_proxy_type}",
 *     "edit-form" = "/admin/cep/cep_proxy/{cep_proxy}/edit",
 *     "delete-form" = "/admin/cep/cep_proxy/{cep_proxy}/delete",
 *     "collection" = "/admin/cep/cep_proxy",
 *   },
 *   bundle_entity_type = "cep_proxy_type",
 *   field_ui_base_route = "entity.cep_proxy_type.edit_form"
 * )
 */
class CepProxy extends ContentEntityBase implements CepProxyInterface {

  use EntityChangedTrait;
  use EntityPublishedTrait;

  const BLOCKED = -2;
  const FAIL    = -1;
  const FREE    = 0;
  const BUSY    = 1;

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->get('type')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function getEntityByName(string $name) {
    $entity = \Drupal::entityTypeManager()->getStorage('cep_proxy');
    $query = $entity->getQuery();
    $n = $query->condition('name', $name)->range(0, 1)->execute();
    if ($n) {
      $id = current($n);
      return $entity->load($id);
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public static function getEntityByIp(string $ip) {
    $entity = \Drupal::entityTypeManager()->getStorage('cep_proxy');
    $query = $entity->getQuery();
    $n = $query->condition('ip', $ip)->range(0, 1)->execute();
    if ($n) {
      $id = current($n);
      return $entity->load($id);
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public static function getEntities(array $param) {
    $limit = (!empty($param['limit'])) ? $param['limit'] : 10000;
    $offset = (isset($param['offset'])) ? $param['offset'] : 0;

    $entity = \Drupal::entityTypeManager()->getStorage('cep_proxy');
    $query = $entity->getQuery();

    if (!empty($param['type'])) {
      $query->condition('type', $param['type']);
    }
    if (isset($param['status']) && self::getIpStatus($param['status'])) {
      $query->condition('ip_status', $param['status']);
    }
    if (!empty($param['call_date_desc'])) {
      $query->sort('call_date', 'DESC');
    }
    else {
      $query->sort('call_date', 'ASC');
    }

    $query->range($offset, $limit);
    $result = $query->execute();

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public static function getIpStatus(int $status = NULL) {
    $data_status = [
      self::BLOCKED => 'BLOCKED',
      self::FAIL    => 'FAIL',
      self::FREE    => 'FREE',
      self::BUSY    => 'BUSY',
    ];

    if ($status === NULL) {
      return $data_status;
    }
    if (isset($data_status[$status])) {
      return $data_status[$status];
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Add the published field.
    $fields += static::publishedBaseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the CEP proxy entity.'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['ip'] = BaseFieldDefinition::create('string')
      ->setLabel(t('IP'))
      ->setDescription(t('The ip of the CEP proxy entity.'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['ip_status'] = BaseFieldDefinition::create('list_integer')
      ->setLabel(t('IP status'))
      ->setDescription(t('Current status usage proxy ip'))
      ->setDefaultValue(0)
      ->setSettings([
        'allowed_values' => self::getIpStatus(),
      ])
      ->setDisplayOptions('view', [
        'label' => 'visible',
        'type' => 'list_default',
        'weight' => 6,
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_select',
        'weight' => 6,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['call_date'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('Call Date'))
      ->setDescription(t('Call date of proxy last time.'))
      ->setSettings([
        'datetime_type' => 'timestamp',
      ])
      ->setDefaultValue(time());

    $fields['call_date_ok'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('Call Date success'))
      ->setDescription(t('Call date success of proxy last time.'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'datetime_type' => 'timestamp',
      ])
      ->setDefaultValue(time());

    $fields['call_date_failed'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('Call Date failed'))
      ->setDescription(t('Call date fail of proxy last time.'))
      ->setSettings([
        'datetime_type' => 'timestamp',
      ])
      ->setDefaultValue(time());

    $fields['call_counter'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Call counter'))
      ->setDescription(t('Call couter of proxy'))
      ->setReadOnly(TRUE)
      ->setDefaultValue(0)
      ->setSetting('unsigned', TRUE);

    $fields['call_counter_failed'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Call counter failed'))
      ->setDescription(t('Call couter failed of proxy ip'))
      ->setReadOnly(TRUE)
      ->setDefaultValue(0)
      ->setSetting('unsigned', TRUE);

    $fields['call_counter_ok'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Call counter success'))
      ->setDescription(t('Call couter success of proxy ip'))
      ->setReadOnly(TRUE)
      ->setDefaultValue(0)
      ->setSetting('unsigned', TRUE);

    $fields['status']->setDescription(t('A boolean indicating whether the CEP proxy is published.'))
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => -3,
      ]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
