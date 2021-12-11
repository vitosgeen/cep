<?php

namespace Drupal\cep_parser\Services;

use voku\helper\HtmlDomParser;
use Drupal\cep_query\Entity\CepQuery;
use Drupal\cep_parser\Entity\CepParser;

/**
 * @file
 * Service Cep Parser - control parsing and retrieving data.
 */

/**
 * Class CepProxyService.
 */
class CepParserService {
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

  /**
   * {@inheritdoc}
   */
  public function extractOfDataFromContentBySelectors($parserItem, $parserSelectors, $completedQuery) {
    $result = [];
    if (!empty($parserSelectors[CepParser::SELECTOR_ENTITIES_LIST])) {
      $serviceQueryJob = \Drupal::service('cep_query.cep_query_service_job');
      $content = $serviceQueryJob->cepQueryGetCacheHtml($completedQuery->file_data_name->value);

      $html = HtmlDomParser::str_get_html($content);
      $parserSelectorsList = $parserSelectors[CepParser::SELECTOR_ENTITIES_LIST];
      $parserSelectorsData = [];
      if (!is_array($parserSelectorsList)) {
        $parserSelectorsData[] = $parserSelectorsList;
      }
      else {
        $parserSelectorsData = $parserSelectorsList;
      }
      $resultItem = [];
      foreach ($parserSelectorsData as $keyParserData => $valueParserData) {
        $list = $html->find($valueParserData);
        foreach ($list as $item) {
          if (empty($parserSelectors["selector_item"][$keyParserData])) {
            continue;
          }
          foreach ($parserSelectors["selector_item"][$keyParserData] as $keyItem => $valItem) {
            if (empty($valItem["extractor_value"])) {
              $ext = $valItem["extractor"];
              $value = $item->findOne($valItem["selector"])->{$ext};
              $value = (!empty($valItem["prefix"] && !stristr($value, $valItem["prefix"]))) ? $valItem["prefix"] . $value : $value;
              $resultItem[$keyItem] = $value;
            }
            else {
              $ext = $valItem["extractor"];
              $value = $item->findOne($valItem["selector"])->{$ext}($valItem["extractor_value"]);
              $value = (!empty($valItem["prefix"] && !stristr($value, $valItem["prefix"]))) ? $valItem["prefix"] . $value : $value;
              $resultItem[$keyItem] = $value;
            }
          }
          $result[] = $resultItem;
        }
      }
    }
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function getSelectorsDataParserById($parserId) {
    $entity = \Drupal::entityTypeManager()->getStorage(CepParser::TYPE_ENTITY);
    $result = $entity->load($parserId);
    $dataParser = [];
    try {
      if (empty($result->data_parser->value)) {
        throw new \Exception('Exception: parser broken data parser "' . $parserId . '"');
      }
      $dataParser = (array) json_decode($result->data_parser->value, TRUE);
      if (json_last_error()) {
        throw new \Exception('Exception: parser broken data parser "' . $parserId . '"' . json_last_error_msg());
      }
    }
    catch (\Throwable $th) {
      \Drupal::logger('cep_parser')->error($th->getMessage() . "\n " . $th->getFile() . " " . $th->getLine() . "\n " . $th->getTraceAsString());
      return FALSE;
    }

    return $dataParser;
  }

  /**
   * {@inheritdoc}
   */
  public function setReadyParserForWork() {
    // $busyParser = $this->getBusyParser();
    // if ($busyParser) {
    //   return FALSE;
    // }
    $readyParser = $this->getReadyParser();
    if ($readyParser) {
      $this->setBusyParser($readyParser->id());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setReadyParserForWorking() {
    $busyParser = $this->getBusyParser();
    if ($busyParser) {
      $this->setWorkingParser($busyParser->id());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getReadyParser() {
    $entity = \Drupal::entityTypeManager()->getStorage(CepParser::TYPE_ENTITY);
    $params = [
      "parser_status" => CepParser::STATUS_READY,
    ];
    $parsers = CepParser::getParsers($params);
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
  public function getBusyParser() {
    $entity = \Drupal::entityTypeManager()->getStorage(CepParser::TYPE_ENTITY);
    $params = [
      "parser_status" => CepParser::STATUS_BUSY,
      "status" => TRUE,
    ];
    $parsers = CepParser::getParsers($params);
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
  public function getWorkingParser() {
    $entity = \Drupal::entityTypeManager()->getStorage(CepParser::TYPE_ENTITY);
    $params = [
      "parser_status" => CepParser::STATUS_WORKING,
      "status" => TRUE,
    ];
    $parsers = CepParser::getParsers($params);
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
  public function setBusyParser($id) {
    $entity = \Drupal::entityTypeManager()->getStorage(CepParser::TYPE_ENTITY);
    $parser = $entity->load($id);
    $parser->parser_status->value = CepParser::STATUS_BUSY;
    $parser->save();
  }

  /**
   * {@inheritdoc}
   */
  public function setWorkingParser($id) {
    $entity = \Drupal::entityTypeManager()->getStorage(CepParser::TYPE_ENTITY);
    $parser = $entity->load($id);
    $parser->parser_status->value = CepParser::STATUS_WORKING;
    $parser->save();
  }

  /**
   * {@inheritdoc}
   */
  public function setCompletedParser($id) {
    $entity = \Drupal::entityTypeManager()->getStorage(CepParser::TYPE_ENTITY);
    $parser = $entity->load($id);
    $parser->parser_status->value = CepParser::STATUS_COMPLETED;
    $parser->save();
  }

  /**
   * {@inheritdoc}
   */
  public function createParser($data = []) {
    $entity = \Drupal::entityTypeManager()->getStorage(CepParser::TYPE_ENTITY);
    $parser = $entity->create();
    try {
      $this->createParserValidateData($data, $parser);
    }
    catch (\Throwable $th) {
      \Drupal::logger('cep_parser')->error($th->getMessage() . "\n " . $th->getFile() . " " . $th->getLine() . "\n " . $th->getTraceAsString());
      return FALSE;
    }
    try {
      $this->createParserFillGeneralData($data, $parser);
      $this->createParserFillDataParser($data, $parser);
      $existingParser = $parser->getEntityByTitle($parser->title->value);
      if ($existingParser) {
        throw new \Exception('Exception: parser exists "' . $parser->title->value . '"');
      }
    }
    catch (\Throwable $th) {
      \Drupal::logger('cep_parser')->error($th->getMessage() . "\n " . $th->getFile() . " " . $th->getLine() . "\n " . $th->getTraceAsString());
      return FALSE;
    }
    $parser->save();
  }

  /**
   * {@inheritdoc}
   */
  private function createParserValidateData($data, $parser) {
    if (empty($data['title'])) {
      throw new \Exception('Exception unknown title');
    }
    if (empty($data['started_url'])) {
      throw new \Exception('Exception unknown started_url');
    }
    if (empty($data['selector_entities_list'])) {
      throw new \Exception('Exception unknown selector_entities_list');
    }
  }

  /**
   * {@inheritdoc}
   */
  private function createParserFillGeneralData($data, &$parser) {
    if (!empty($data['title'])) {
      $parser->title->value = $data['title'];
    }
    if (!empty($data['description'])) {
      $parser->description->value = $data['description'];
    }
  }

  /**
   * {@inheritdoc}
   */
  private function createParserFillDataParser($data, &$parser) {
    $jsonData = (array) json_decode($parser->data_parser->value);
    foreach ($data as $k => $v) {
      if (isset($jsonData[$k])) {
        $jsonData[$k] = $v;
      }
      else {
        $jsonData["data"][$k] = $v;
      }
    }
    $jsonString = json_encode($jsonData);
    $parser->data_parser->value = $jsonString;
  }

}
