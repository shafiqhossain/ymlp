<?php

namespace Drupal\ymlp;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Routing\AdminHtmlRouteProvider;
use Symfony\Component\Routing\Route;

/**
 * Provides routes for Ymlp subscriber entities.
 *
 * @see \Drupal\Core\Entity\Routing\AdminHtmlRouteProvider
 * @see \Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider
 */
class YmlpSubscriberHtmlRouteProvider extends AdminHtmlRouteProvider {

  /**
   * {@inheritdoc}
   */
  public function getRoutes(EntityTypeInterface $entity_type) {
    $collection = parent::getRoutes($entity_type);

    $entity_type_id = $entity_type->id();

    if ($ui_settings_form_route = $this->getUISettingsFormRoute($entity_type)) {
      $collection->add("$entity_type_id.settings", $ui_settings_form_route);
    }

    if ($ymlp_settings_form_route = $this->getYmlpSettingsFormRoute($entity_type)) {
      $collection->add("ymlp.route.settings", $ymlp_settings_form_route);
    }

    if ($subscribe_route = $this->getSubscribeRoute($entity_type)) {
      $collection->add("ymlp.route.subscribe", $subscribe_route);
    }

    return $collection;
  }

  /**
   * Gets the settings form route.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return \Symfony\Component\Routing\Route|null
   *   The generated route, if available.
   */
  protected function getUISettingsFormRoute(EntityTypeInterface $entity_type) {
    if (!$entity_type->getBundleEntityType()) {
      $route = new Route("/admin/config/services/ymlp/{$entity_type->id()}/settings");
      $route
        ->setDefaults([
          '_form' => 'Drupal\ymlp\Form\YmlpSubscriberSettingsForm',
          '_title' => "Subscriber settings",
        ])
        ->setRequirement('_permission', $entity_type->getAdminPermission())
        ->setOption('_admin_route', TRUE);

      return $route;
    }
  }

  /**
   * Gets the settings form route.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return \Symfony\Component\Routing\Route|null
   *   The generated route, if available.
   */
  protected function getYmlpSettingsFormRoute(EntityTypeInterface $entity_type) {
    if (!$entity_type->getBundleEntityType()) {
      $route = new Route("/admin/config/services/ymlp/general/settings");
      $route
        ->setDefaults([
          '_form' => 'Drupal\ymlp\Form\YmlpSettingsForm',
          '_title' => "General settings",
        ])
        ->setRequirement('_permission', $entity_type->getAdminPermission())
        ->setOption('_admin_route', TRUE);

      return $route;
    }
  }

  /**
   * Gets the subscribe route.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return \Symfony\Component\Routing\Route|null
   *   The generated route, if available.
   */
  protected function getSubscribeRoute(EntityTypeInterface $entity_type) {
    if (!$entity_type->getBundleEntityType()) {
      $route = new Route("/admin/config/services/ymlp/{$entity_type->id()}/subscribe/{id}");
      $route
        ->setDefaults([
          '_controller' => 'Drupal\ymlp\Controller\YmlpController::subscribe',
          '_title' => "Subscribe",
        ])
        ->setRequirement('_permission', $entity_type->getAdminPermission())
        ->setOption('_admin_route', TRUE);

      return $route;
    }
  }

}
