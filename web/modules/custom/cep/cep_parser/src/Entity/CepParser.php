<?php

namespace Drupal\cep_parser\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\cep_parser\CepParserInterface;
use Drupal\user\UserInterface;

/**
 * Defines the cep_parser entity class.
 *
 * @ContentEntityType(
 *   id = "cep_parser",
 *   label = @Translation("cep_parser"),
 *   label_collection = @Translation("cep_parsers"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\cep_parser\CepParserListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "add" = "Drupal\cep_parser\Form\CepParserForm",
 *       "edit" = "Drupal\cep_parser\Form\CepParserForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     }
 *   },
 *   base_table = "cep_parser",
 *   admin_permission = "administer cep_parser",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "title",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "add-form" = "/admin/content/cep-parser/add",
 *     "canonical" = "/cep_parser/{cep_parser}",
 *     "edit-form" = "/admin/content/cep-parser/{cep_parser}/edit",
 *     "delete-form" = "/admin/content/cep-parser/{cep_parser}/delete",
 *     "collection" = "/admin/content/cep-parser"
 *   },
 *   field_ui_base_route = "entity.cep_parser.settings"
 * )
 */
class CepParser extends ContentEntityBase implements CepParserInterface {

  use EntityChangedTrait;

  const TYPE_ENTITY = "cep_parser";

  const STATUS_NONE = -1;
  const STATUS_READY = 0;
  const STATUS_BUSY = 1;
  const STATUS_WORKING = 2;
  const STATUS_COMPLETED = 3;
  const STATUS_FAILED = 4;

  const SELECTOR_ENTITIES_LIST = "selector_entities_list";
  const SELECTOR_ENTITY_TITLE = "selector_entity_title";
  const SELECTOR_ENTITY_LINK = "selector_entity_link";

  /**
   * {@inheritdoc}
   *
   * When a new cep_parser entity is created, set the uid entity reference to
   * the current user as the creator of the entity.
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += ['uid' => \Drupal::currentUser()->id()];
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->get('title')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setTitle($title) {
    $this->set('title', $title);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isEnabled() {
    return (bool) $this->get('status')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setStatus($status) {
    $this->set('status', $status);
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
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('uid')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('uid', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t('The title of the cep_parser entity.'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Status'))
      ->setDescription(t('A boolean indicating whether the cep_parser is enabled.'))
      ->setDefaultValue(TRUE)
      ->setSetting('on_label', 'Enabled')
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => FALSE,
        ],
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'boolean',
        'label' => 'above',
        'weight' => 0,
        'settings' => [
          'format' => 'enabled-disabled',
        ],
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['description'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Description'))
      ->setDescription(t('A description of the cep_parser.'))
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'text_default',
        'label' => 'above',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['data_parser'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('data parser'))
      ->setDescription(t('A data parser of the cep_parser.'))
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'text_default',
        'label' => 'above',
        'weight' => 10,
      ])
      ->setDefaultValue($this->getDefaultSelectorsData())
      ->setDisplayConfigurable('view', TRUE);

    $fields['field_parser'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Parser parent'))
      ->setDescription(t('The parser ID of the cep_parser parent.'))
      ->setSetting('target_type', 'cep_parser')
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => 60,
          'placeholder' => '',
        ],
        'weight' => 15,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'author',
        'weight' => 15,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['parser_status'] = BaseFieldDefinition::create('list_integer')
      ->setLabel(t('Parser status'))
      ->setDescription(t('Current status usage of parser'))
      ->setDefaultValue(0)
      ->setSettings([
        'allowed_values' => self::getParserStatus(),
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

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Author'))
      ->setDescription(t('The user ID of the cep_parser author.'))
      ->setSetting('target_type', 'user')
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => 60,
          'placeholder' => '',
        ],
        'weight' => 15,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'author',
        'weight' => 15,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Authored on'))
      ->setDescription(t('The time that the cep_parser was created.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'timestamp',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the cep_parser was last edited.'));

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public static function getParserStatus(int $status = NULL) {
    $data_status = [
      self::STATUS_NONE => 'NONE',
      self::STATUS_READY    => 'READY',
      self::STATUS_BUSY    => 'BUSY',
      self::STATUS_WORKING    => 'WORKING',
      self::STATUS_COMPLETED    => 'COMPLETED',
      self::STATUS_FAILED    => 'FAILED',
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
  public static function getParsers(array $param) {
    $limit = (!empty($param['limit'])) ? $param['limit'] : 10000;
    $offset = (isset($param['offset'])) ? $param['offset'] : 0;
    $result = [];
    $entity = \Drupal::entityTypeManager()->getStorage(self::TYPE_ENTITY);
    $query = $entity->getQuery();

    if (!empty($param['uid'])) {
      $query->condition('uid', $param['uid']);
    }
    if (isset($param['status'])) {
      $query->condition('status', $param['status']);
    }
    if (isset($param['status'])) {
      $query->condition('status', $param['status']);
    }
    if (isset($param['parser_status'])) {
      $query->condition('parser_status', $param['parser_status']);
    }
    if (!empty($param['id_desc'])) {
      $query->sort('id', 'ASC');
    }
    else {
      $query->sort('id', 'DESC');
    }

    $query->range($offset, $limit);
    $result = $query->execute();

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public static function getEntityByTitle(string $title) {
    $entity = \Drupal::entityTypeManager()->getStorage(self::TYPE_ENTITY);
    $query = $entity->getQuery();
    $n = $query->condition('title', $title)->range(0, 1)->execute();
    if ($n) {
      $id = current($n);
      return $entity->load($id);
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  private function getDefaultSelectorsData() {
    $dataStrSelectors = '{"started_url":"","next_url":"","curent_url":"","how_often":0,"selector_entities_list":' .
      '{"0":"selector_entities_list 0","1":"selector_entities_list 1"},"selector_entity_title":"","selector_entity_link":"",' .
      '"selector_pager":"","selector_pager_item":"","selector_pager_item_condition":"","selector_item":{"0":{"title":{"selector":' .
        '"selector_item 0 title ","extractor":"plaintext","extractor_value":"","prefix":""},"mname":{"selector":"selector_item 0 mname ",' .
          '"extractor":"getAttribute","extractor_value":"data-slugs","prefix":""},"href":{"selector":"selector_item 0 href","extractor":"getAttribute",' .
            '"extractor_value":"href","prefix":"https://www.example.com"}},"1":{"title":{"selector":"selector_item 1 title","extractor":"plaintext",' .
              '"extractor_value":"","prefix":""},"mname":{"selector":"selector_item 1 mname","extractor":"getAttribute","extractor_value":"data-slugs","prefix":""}' .
              ',"href":{"selector":"selector_item 1 href","extractor":"getAttribute","extractor_value":"href","prefix":"https://www.example.com"}}},"data":{"title":"example.com bla bla bla"}}';

    return $dataStrSelectors;
  }

}
