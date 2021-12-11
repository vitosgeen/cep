<?php

/**
 * @file
 * Contains cep_query.page.inc.
 *
 * Page callback for Cep query entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Cep query templates.
 *
 * Default template: cep_query.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_cep_query(array &$variables) {
  // Fetch CepQuery Entity Object.
  $cep_query = $variables['elements']['#cep_query'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
