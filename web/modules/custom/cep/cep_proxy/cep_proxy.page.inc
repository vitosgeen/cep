<?php

/**
 * @file
 * Contains cep_proxy.page.inc.
 *
 * Page callback for CEP proxy entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for CEP proxy templates.
 *
 * Default template: cep_proxy.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_cep_proxy(array &$variables) {
  // Fetch CepProxy Entity Object.
  $cep_proxy = $variables['elements']['#cep_proxy'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
