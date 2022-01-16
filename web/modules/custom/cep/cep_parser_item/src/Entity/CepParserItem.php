<?php

namespace Drupal\cep_parser_item\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\cep_parser_item\CepParserItemInterface;
use Drupal\user\UserInterface;

/**
 * Defines the cep_parser_item entity class.
 *
 * @ContentEntityType(
 *   id = "cep_parser_item",
 *   label = @Translation("cep_parser_item"),
 *   label_collection = @Translation("cep_parser_items"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\cep_parser_item\CepParserItemListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "add" = "Drupal\cep_parser_item\Form\CepParserItemForm",
 *       "edit" = "Drupal\cep_parser_item\Form\CepParserItemForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     }
 *   },
 *   base_table = "cep_parser_item",
 *   admin_permission = "administer cep_parser_item",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "title",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "add-form" = "/admin/content/cep-parser-item/add",
 *     "canonical" = "/cep_parser_item/{cep_parser_item}",
 *     "edit-form" = "/admin/content/cep-parser-item/{cep_parser_item}/edit",
 *     "delete-form" = "/admin/content/cep-parser-item/{cep_parser_item}/delete",
 *     "collection" = "/admin/content/cep-parser-item"
 *   },
 *   field_ui_base_route = "entity.cep_parser_item.settings"
 * )
 */
class CepParserItem extends ContentEntityBase implements CepParserItemInterface {

  use EntityChangedTrait;

  const TYPE_ENTITY = "cep_parser_item";

  const ENABLED = TRUE;
  const DISABLED = FALSE;

  const STATUS_NONE = -1;
  const STATUS_READY = 0;
  const STATUS_BUSY = 1;
  const STATUS_COMPLETED = 2;
  const STATUS_FAILED = 3;

  const ERROR_NONE = 0;
  const ERROR_EMPTY_DATA = 1;
  const ERROR_NOT_VALID_DATA = 2;
  const ERROR_NOT_VALID_PAGE = 3;

  /**
   * {@inheritdoc}
   *
   * When a new cep_parser_item entity is created, set the uid entity reference to
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
      ->setDescription(t('The title of the cep_parser_item entity.'))
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
      ->setDescription(t('A boolean indicating whether the cep_parser_item is enabled.'))
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

      $fields['field_hash'] = BaseFieldDefinition::create('string')
        ->setLabel(t('Hash'))
        ->setDescription(t('The hash of the cep_parser_item entity.'))
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

    $fields['description'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Description'))
      ->setDescription(t('A description of the cep_parser_item.'))
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

    $fields['item_data'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Data'))
      ->setDescription(t('Data parser item with json'))
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => 10,
      ]);

    $fields['field_parser'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Parser parent'))
      ->setDescription(t('The parser ID of the cep_parser_item parent.'))
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


    $fields['field_parser_item_status'] = BaseFieldDefinition::create('list_integer')
      ->setLabel(t('Parser status'))
      ->setDescription(t('Current status usage of parser'))
      ->setDefaultValue(0)
      ->setSettings([
        'allowed_values' => self::getParserItemStatus(),
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
    
    $fields['field_error_status'] = BaseFieldDefinition::create('list_integer')
      ->setLabel(t('Error status'))
      ->setDescription(t('Error status data'))
      ->setDefaultValue(0)
      ->setSettings([
        'allowed_values' => self::getErrorStatus(),
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
      ->setDescription(t('The user ID of the cep_parser_item author.'))
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
      ->setDescription(t('The time that the cep_parser_item was created.'))
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
      ->setDescription(t('The time that the cep_parser_item was last edited.'));

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityByHash(string $hash) {
    $entity = \Drupal::entityTypeManager()->getStorage('cep_parser_item');
    $query = $entity->getQuery();
    $n = $query->condition('field_hash', $hash)->range(0, 1)->execute();
    if ($n) {
      $id = current($n);
      return $entity->load($id);
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public static function getParserItemByParserStatus(int $status) {
    $entity = \Drupal::entityTypeManager()->getStorage(CepParserItem::TYPE_ENTITY);
    $params = [
      "status" => self::ENABLED,
      "field_parser_item_status" => $status,
    ];
    $parsers = CepParserItem::getParserItems($params);
    $result = [];
    foreach ($parsers as $k => $v) {
      $result = $entity->load($v);
      break;
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public static function getParserItemsByParserId(int $parserId, $parserItemStatus = NULL) {
    $entity = \Drupal::entityTypeManager()->getStorage(CepParserItem::TYPE_ENTITY);
    $offset = 0;
    $limit = 100;
    $params = [
      "field_parser" => $parserId,
      "limit" => $limit,
      "offset" => $offset,
    ];
    if ($parserItemStatus === NULL) {
      $params["field_parser_item_status"] = $parserItemStatus;
    }
    $total = 10;
    $result = [];
    for ($i = 0; $i < $total; $i++) {
      $parsers = CepParserItem::getParserItems($params);
      foreach ($parsers as $k => $v) {
        $result[] = $entity->load($v);
      }
      if (is_array($parsers) && count($parsers) < $limit) {
        break;
      }
      elseif (!$parsers) {
        break;
      }
      $total++;
      $params["offset"] += $limit;
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public static function getParserItemsByParserIdStatus(int $parserId, $status) {
    $entity = \Drupal::entityTypeManager()->getStorage(CepParserItem::TYPE_ENTITY);
    $params = [
      "status" => self::ENABLED,
      "field_parser" => $parserId,
      "field_parser_item_status" => $status,
      "limit" => 1,
    ];
    $parsers = CepParserItem::getParserItems($params);
    $result = [];
    foreach ($parsers as $k => $v) {
      $result = $entity->load($v);
      break;
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public static function getParserItems(array $param) {
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
    if (isset($param['field_parser'])) {
      $query->condition('field_parser', $param['field_parser'], 'IN');
    }
    if (isset($param['field_parser_item_status'])) {
      $query->condition('field_parser_item_status', $param['field_parser_item_status'], 'IN');
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
  public static function getParserItemStatus(int $status = NULL) {
    $data_status = [
      self::STATUS_NONE => 'NONE',
      self::STATUS_READY    => 'READY',
      self::STATUS_BUSY    => 'BUSY',
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
  public static function getErrorStatus(int $status = NULL) {
    $data_status = [
      self::ERROR_NONE => 'NONE',
      self::ERROR_EMPTY_DATA    => 'EMTY DATA',
      self::ERROR_NOT_VALID_DATA    => 'NOT VALID DATA',
      self::ERROR_NOT_VALID_PAGE    => 'NOT VALID PAGE',
    ];

    if ($status === NULL) {
      return $data_status;
    }
    if (isset($data_status[$status])) {
      return $data_status[$status];
    }
    return FALSE;
  }
}
