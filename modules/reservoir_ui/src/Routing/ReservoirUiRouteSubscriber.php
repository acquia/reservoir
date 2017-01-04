<?php

namespace Drupal\reservoir_ui\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Subscriber for Reservoir UI routes.
 */
class ReservoirUiRouteSubscriber extends RouteSubscriberBase {

  protected static $disabledRouteNames = [
    'system.themes_page',
    'system.theme_set_default',
    'system.theme_settings',
    'system.theme_settings_theme',
    'system.theme_uninstall',
    'system.theme_install',
    'system.admin_structure',
  ];

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    foreach (static::$disabledRouteNames as $route_name) {
      $collection->get($route_name)->setRequirement('_access', 'FALSE');
    }

    foreach ($collection->all() as $name => $route) {
      switch ($name) {
        case 'system.admin_content':
          $route->setDefault('_title', 'Data');
          $route->setPath('/admin/data');
          break;
        case 'erd.admin':
          $route->setDefault('_title', 'Data modeling');
          $route->setPath('/admin/modeling');
          break;
        case 'entity.user.collection':
          $route->setDefault('_title', 'Users');
          $route->setPath('/admin/access/users');
          break;
        case 'user.admin_permissions':
          $route->setPath('/admin/access/permissions');
          break;
        case 'entity.user_role.collection':
          $route->setPath('/admin/access/roles');
          break;
      }
    }
  }

}
