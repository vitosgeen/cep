<?php

namespace Drupal\cep_query\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Cep query entity.
 *
 * @ingroup cep_query
 *
 * @ContentEntityType(
 *   id = "cep_query",
 *   label = @Translation("Cep query"),
 *   bundle_label = @Translation("Cep query type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\cep_query\CepQueryListBuilder",
 *     "views_data" = "Drupal\cep_query\Entity\CepQueryViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\cep_query\Form\CepQueryForm",
 *       "add" = "Drupal\cep_query\Form\CepQueryForm",
 *       "edit" = "Drupal\cep_query\Form\CepQueryForm",
 *       "delete" = "Drupal\cep_query\Form\CepQueryDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\cep_query\CepQueryHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\cep_query\CepQueryAccessControlHandler",
 *   },
 *   base_table = "cep_query",
 *   translatable = FALSE,
 *   admin_permission = "administer cep query entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "regular" = "regular",
 *     "published" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/cep_query/{cep_query}",
 *     "add-page" = "/admin/structure/cep_query/add",
 *     "add-form" = "/admin/structure/cep_query/add/{cep_query_type}",
 *     "edit-form" = "/admin/structure/cep_query/{cep_query}/edit",
 *     "delete-form" = "/admin/structure/cep_query/{cep_query}/delete",
 *     "collection" = "/admin/structure/cep_query",
 *   },
 *   bundle_entity_type = "cep_query_type",
 *   field_ui_base_route = "entity.cep_query_type.edit_form"
 * )
 */
class CepQuery extends ContentEntityBase implements CepQueryInterface {

  use EntityChangedTrait;
  use EntityPublishedTrait;

  const BLOCKED    = -2;
  const FAIL       = -1;
  const PERFORMING = 1;
  const FREE       = 2;
  const BUSY       = 3;
  const COMPLETED  = 4;
  const FINISHED   = 5;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

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
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function getQueryStatus(int $status = NULL) {
    $data_status = [
      self::BLOCKED     => 'BLOCKED',
      self::FAIL        => 'FAIL',
      self::PERFORMING  => 'PERFORMING',
      self::FREE        => 'FREE',
      self::BUSY        => 'BUSY',
      self::COMPLETED   => 'COMPLETED',
      self::FINISHED    => 'FINISHED',
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

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Cep query entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Cep query entity.'))
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

    $fields['proxy_type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Proxy type'))
      ->setDescription(t('The name of the proxy type. Who processed query'))
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
      ->setDisplayConfigurable('view', TRUE);

    $fields['url'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Url query'))
      ->setDescription(t('The url that the entity will make query.'))
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ]);

    $fields['file_data_name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('File data name'))
      ->setDescription(t('Field has name file of data (last actual data)'))
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ]);

    // $fields['file'] = BaseFieldDefinition::create('entity_reference')
    //   ->setLabel(t('File reference'))))
    //   ->setSetting('target_type', 'file')
    //   ->setSetting('handler', 'default');

    // $fields['worker'] = BaseFieldDefinition::create('string')
    //   ->setLabel(t('Worker name'))
    //   ->setDescription(t('Field has worker name'))
    //   ->setDisplayOptions('form', [
    //     'type' => 'string_textfield',
    //     'weight' => -4,
    //   ]);

    $fields['regular'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Regular'))
      ->setDescription(t('A boolean indicating whether the Cep query is Regular.'))
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => -3,
      ]);

    $fields['regular_interval'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Regular interval'))
      ->setDescription(t('Regular interval by day.'))
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ]);

    $fields['status']->setDescription(t('A boolean indicating whether the Cep query is published.'))
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => -3,
      ]);

    $fields['job_status'] = BaseFieldDefinition::create('list_integer')
      ->setLabel(t('Query job status'))
      ->setDescription(t('Current status usage query'))
      ->setDefaultValue(self::FREE)
      ->setSettings([
        'allowed_values' => self::getQueryStatus(),
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

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['completed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Completed'))
      ->setDescription(t('The time that the entity was completed.'));

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public static function getQueries($limit) {
    $entity = \Drupal::entityTypeManager()->getStorage('cep_query');
    $query = $entity->getQuery();

    $ids = $query->condition('job_status', self::FREE)
      // ->condition('completed', (time() - (86400 * 30)), '<')
      ->sort('changed', 'ASC')
      ->pager($limit)
      ->execute();

    return $ids;
  }

}
