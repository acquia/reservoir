<?php

namespace Drupal\reservoir_ui\Routing;

use Drupal\Core\Routing\RouteBuildEvent;
use Drupal\Core\Routing\RoutingEvents;
use Drupal\node\NodeTypeInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscriber for Reservoir UI routes.
 */
class ReservoirUiRouteSubscriber implements EventSubscriberInterface  {

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
   * @param \Drupal\Core\Routing\RouteBuildEvent $event
   *   The route build event.
   */
  public function alterRoutes(RouteBuildEvent $event) {
    $collection = $event->getRouteCollection();
    foreach (static::$disabledRouteNames as $route_name) {
      $collection->get($route_name)->setRequirement('_access', 'FALSE');
    }

    foreach ($collection->all() as $name => $route) {
      switch ($name) {
        case 'entity.node_type.collection':
          $route->setPath('/admin/models');
          $route->setDefault('_title', 'Content models');
          break;
        case 'entity.node_type.edit_form':
          $route->setPath('/admin/models/{node_type}');
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

  public function alterRoutesLate(RouteBuildEvent $event) {
    $collection = $event->getRouteCollection();
    foreach ($collection->all() as $name => $route) {
      switch ($name) {
        case 'entity.node.field_ui_fields':
          $route->setDefault('_title_callback', '\Drupal\reservoir_ui\Routing\ReservoirUiRouteSubscriber::manageFieldsTitle');
          break;
        case 'node.type_add':
          $route->setPath('/admin/models/add');
          $route->setDefault('_title', 'Add content model');
          break;
        case 'entity.entity_form_display.node.default':
          $route->setRequirement('_access', 'FALSE');
          break;
        case 'entity.entity_view_display.node.default':
          $route->setRequirement('_access', 'FALSE');
          break;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[RoutingEvents::ALTER] = [
      ['alterRoutes'],
      ['alterRoutesLate', -10000],
    ];
    return $events;
  }

  // @see \Drupal\node\Controller\NodeController::addPageTitle
  // @todo fix in core
  public static function manageFieldsTitle(NodeTypeInterface $node_type) {
    return t('Manage %type fields', ['%type' => $node_type->label()]);
  }

}
