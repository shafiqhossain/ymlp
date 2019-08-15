<?php

/**
 * @file
 * Contains ymlp_subscriber.page.inc.
 *
 * Page callback for Ymlp subscriber entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Ymlp subscriber templates.
 *
 * Default template: ymlp_subscriber.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_ymlp_subscriber(array &$variables) {
  // Fetch YmlpSubscriber Entity Object.
  $ymlp_subscriber = $variables['elements']['#ymlp_subscriber'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
