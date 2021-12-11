<?php

namespace Drupal\cep_parser_item\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\cep_parser_item\Entity\CepParserItem;
use Drupal\cep_query\Entity\CepQuery;

/**
 * Controller routines for page example routes.
 */
class ParserItemController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public static function getModuleName() {
    return 'cep_parser_item';
  }

  /**
   * Constructs a parser item create page.
   */
  public function createAction() {
    $service = \Drupal::service('cep_parser.cep_parser_service');
    $serviceParser = \Drupal::service('cep_parser.cep_parser_service');
    $serviceParserItem = \Drupal::service('cep_parser_item.cep_parser_item_service');
    $serviceQueryUrl = \Drupal::service('cep_query.cep_query_service');

    // Parser to work.
    $service->setReadyParserForWork();
    $serviceParser->setReadyParserForWorking();
    $parser = $serviceParser->getWorkingParser();
    if (!$parser) {
      return FALSE;
    }
    $serviceParserItem->createEntityParserItem($parser);
    $readyParserItems = $serviceParserItem->getReadyParserItemsByParserId($parser->id());
    if ($readyParserItems && is_array($readyParserItems)) {
      foreach ($readyParserItems as $readyParserItem) {
        $serviceParserItem->setBusyParserItem($readyParserItem->id());
        $serviceQueryUrl->createQueryByUrl($readyParserItem->getTitle());
      }
    }

    return [
      '#markup' => "<p> 👌 " . $this->t('Page create of parser items') . '</p>',
    ];
  }

  /**
   * Retrieving content is start service query job.
   */
  public function retrievingContentAction() {
    $serviceQueryUrl = \Drupal::service('cep_query.cep_query_service');
    $queryFree = $serviceQueryUrl->getQuery();
    $url = "";
    if ($queryFree) {
      $serviceQueryJob = \Drupal::service('cep_query.cep_query_service_job');
      $queryEntity = $serviceQueryJob->startServiceJob();
      if ($queryEntity) {
        $url = $queryEntity->url->value;
      }
    }
    return [
      '#markup' => "<p> 👌 " . $this->t('Retrieving content of cep_query is finished.') . "(" . $url . ")" . '</p>',
    ];
  }

  /**
   * Constructs a process parser.
   */
  public function processParser() {
    $serviceParser = \Drupal::service('cep_parser.cep_parser_service');
    $serviceParserItem = \Drupal::service('cep_parser_item.cep_parser_item_service');
    $serviceQueryUrl = \Drupal::service('cep_query.cep_query_service');
    $completedQuery = $serviceQueryUrl->getQueryByStatus(CepQuery::COMPLETED);
    $parserItem = $serviceParserItem->geParserItemByUrl($completedQuery->url->value);
    if ($parserItem) {
      $parserSelectors = $serviceParser->getSelectorsDataParserById($parserItem->field_parser->target_id);
      $data = $serviceParser->extractOfDataFromContentBySelectors($parserItem, $parserSelectors, $completedQuery);
      $serviceParserItem->setDataParserItem($parserItem->id(), $data);
      $serviceParserItem->setCompletedParserItem($parserItem->id());
      $serviceQueryUrl->setJobStatusQuery($completedQuery->id(), CepQuery::FINISHED);
      $statuses = [CepParserItem::STATUS_READY, CepParserItem::STATUS_BUSY];
      $pItem = $serviceParserItem->geParsersItemByParserIdStatus($parserItem->id(), $statuses);
      if (!$pItem) {
        $serviceParser->setCompletedParser($parserItem->field_parser->target_id);
      }
    }
    return [
      '#markup' => '<p> 👌 processParser </p>',
    ];
  }

  /**
   * Constructs a parser item create page.
   */
  public function createParser() {
    $service = \Drupal::service('cep_parser.cep_parser_service');
    // $reqData = file_get_contents('php://input');
    // Begin create parser parser work.
    $data = [
      "started_url" => "https://www.cars.com/research/",
      "selector_entities_list" => "#by-make-tab .sds-list li",
      "selector_entity_link" => "a[href]",
      "selector_entity_title" => "a[data-slugs]",
      "title" => "cars com makes",
    ];
    $service->createParser($data);
    // Parser to work.
    $service->setReadyParserForWork();
    // Get busy parser for work.
    $busyParser = $service->getBusyParser();

    return [
      '#markup' => "<p> 👌 " . $this->t('Page create of parser items') . '</p>',
    ];
  }

}
