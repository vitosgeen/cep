<?php

namespace Drupal\cep_parser_item\Services;

use Drupal\cep_query\Entity\CepQuery;
use Drupal\cep_parser\Entity\CepParser;
use Drupal\cep_parser_item\Entity\CepParserItem;
use stdClass;

/**
 * @file
 * Service Cep Parser - control parsing and retrieving data.
 */

/**
 * Class CepProxyService.
 */
class CepParserItemService {
  /**
   * The lock backend.
   *
   * @var \Drupal\Core\Lock\LockBackendInterface
   */
  protected $lock;

  /**
   * {@inheritdoc}
   */
  public function __construct() {

  }

  public function createEntityParserItem(CepParser $parser, $isSave = 1) {
    $selectorsData = (array) json_decode($parser->data_parser->value, TRUE);
    $url = $selectorsData['started_url'];
    $entity = CepParserItem::create();
    if (empty($url) && !empty($parser->field_parser->target_id)) {
      $parserItems = $entity->getParserItemsByParserId($parser->field_parser->target_id);
      $dataParserItems = [];
      foreach ($parserItems as $item) {
        $piData = (array) json_decode($item->item_data->value, TRUE);
        if (!is_array($piData)) {
          break;
        }
        foreach ($piData as $piItem) {
          if (empty($piItem["href"])) {
            continue;
          }
          $dataEntity = [
            'title' => $piItem["href"],
            'field_url_parser' => $piItem["href"],
            'field_parser' => $parser->id(),
          ];
          if ($isSave == 1) {
            $this->saveEntityParserItem($dataEntity);
          }
        }
      }
    }
    else {
      if (!empty($selectorsData['next_url'])) {
        $url = $selectorsData['next_url'];
      }
      if (!empty($selectorsData['curent_url'])) {
        $url = $selectorsData['curent_url'];
      }
      $dataEntity = [
        'title' => $url,
        'field_url_parser' => $url,
        'field_parser' => $parser->id(),
      ];
  
      if ($isSave == 1) {
        $this->saveEntityParserItem($dataEntity);
      }
      $entity = CepParserItem::create();
      $hash = md5($dataEntity['field_url_parser']);
      $entity = $entity->getEntityByHash($hash);
      return $entity;
    }
  }

  public function buildEntityParserItem(CepParser $parser, $isSave = 1) {
    $selectorsData = (array) json_decode($parser->data_parser->value);
    $url = $selectorsData['started_url'];
    if (!empty($selectorsData['next_url'])) {
      $url = $selectorsData['next_url'];
    }
    if (!empty($selectorsData['curent_url'])) {
      $url = $selectorsData['curent_url'];
    }
    $dataEntity = [
      'title' => $url,
      'field_url_parser' => $url,
      'field_parser' => $parser->id(),
    ];

    if ($isSave == 1) {
      $this->saveEntityParserItem($dataEntity);
    }
    $entity = CepParserItem::create();
    $hash = md5($dataEntity['field_url_parser']);
    $entity = $entity->getEntityByHash($hash);

    return $entity;
  }

  public function saveEntityParserItem($dataEntity) {
    $entity = CepParserItem::create();
    if (empty($dataEntity['field_url_parser'])) {
      return FALSE;
    }
    if (empty($dataEntity['field_parser'])) {
      return FALSE;
    }
    $hash = md5($dataEntity['field_url_parser']);
    $existEntity = $entity->getEntityByHash($hash);
    if (!$existEntity) {
      $entity->title->value = $dataEntity['field_url_parser'];
      $entity->field_hash->value = $hash;
      $entity->field_parser->target_id = $dataEntity['field_parser'];
      $entity->field_url_parser = [
        "uri" => $dataEntity['field_url_parser'],
        "title" => $dataEntity['field_url_parser'],
        "options" => [],
      ];
      $entity->save();
    }
    else {
      $existEntity->title->value = $dataEntity['field_url_parser'];
      $existEntity->field_hash->value = $hash;
      $existEntity->field_parser->target_id = $dataEntity['field_parser'];
      $existEntity->field_url_parser = [
        "uri" => $dataEntity['field_url_parser'],
        "title" => $dataEntity['field_url_parser'],
        "options" => [],
      ];
      $existEntity->save();
    }

  }

  /**
   * {@inheritdoc}
   */
  public function getBusyParserItem() {
    $entity = \Drupal::entityTypeManager()->getStorage(CepParserItem::TYPE_ENTITY);
    $busyParserItem = $entity->getParserItemByParserStatus(CepParserItem::STATUS_BUSY);
    return $busyParserItem;
  }

  /**
   * {@inheritdoc}
   */
  public function getReadyParserItem() {
    $readyParserItem = CepParserItem::getParserItemByParserStatus(CepParserItem::STATUS_READY);
    return $readyParserItem;
  }

  /**
   * {@inheritdoc}
   */
  public function getReadyParserItemsByParserId(int $parserId) {
    $readyParserItem = CepParserItem::getParserItemsByParserId($parserId, CepParserItem::STATUS_READY);
    return $readyParserItem;
  }

  /**
   * {@inheritdoc}
   */
  public function setBusyParserItem($id) {
    $entity = \Drupal::entityTypeManager()->getStorage(CepParserItem::TYPE_ENTITY);
    $parser = $entity->load($id);
    $parser->field_parser_item_status->value = CepParserItem::STATUS_BUSY;
    $parser->save();
  }

  /**
   * {@inheritdoc}
   */
  public function setReadyParserItem($id) {
    $entity = \Drupal::entityTypeManager()->getStorage(CepParserItem::TYPE_ENTITY);
    $parser = $entity->load($id);
    $parser->field_parser_item_status->value = CepParserItem::STATUS_READY;
    $parser->save();
  }

  /**
   * {@inheritdoc}
   */
  public function geParserItemByUrl($url) {
    $serviceParserItemJob = \Drupal::service('cep_parser_item.cep_parser_item_service');
    // $entity = \Drupal::entityTypeManager()->getStorage(CepParserItem::TYPE_ENTITY);
    $entity = CepParserItem::create();
    $hash = md5($url);
    $parserItem = $entity->getEntityByHash($hash);
    return $parserItem;
  }

  /**
   * {@inheritdoc}
   */
  public function geParsersItemByParserIdStatus($parserId, $status) {
    $serviceParserItemJob = \Drupal::service('cep_parser_item.cep_parser_item_service');
    // $entity = \Drupal::entityTypeManager()->getStorage(CepParserItem::TYPE_ENTITY);
    $entity = CepParserItem::create();
    $parserItem = $entity->getParserItemsByParserIdStatus($parserId, $status);
    return $parserItem;
  }

  /**
   * {@inheritdoc}
   */
  public function setDataParserItem($id, $data) {
    $entity = \Drupal::entityTypeManager()->getStorage(CepParserItem::TYPE_ENTITY);
    $parser = $entity->load($id);
    $parser->item_data->value = json_encode($data);
    $parser->save();
  }

  /**
   * {@inheritdoc}
   */
  public function setCompletedParserItem($id) {
    $entity = \Drupal::entityTypeManager()->getStorage(CepParserItem::TYPE_ENTITY);
    $parser = $entity->load($id);
    $parser->field_parser_item_status->value = CepParserItem::STATUS_COMPLETED;
    $parser->save();
  }

  /**
   * {@inheritdoc}
   */
  public function setStatusErrorParserItem($id, $error = 0) {
    $entity = \Drupal::entityTypeManager()->getStorage(CepParserItem::TYPE_ENTITY);
    $parser = $entity->load($id);
    $parser->field_error_status->value = $error;
    $parser->save();
  }

}
