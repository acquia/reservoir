<?php

namespace Drupal\reservoir_ui;

use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\jsonapi\ResourceType\ResourceTypeRepository;

/**
 * Decorates the JSON API resource type repository: blacklist some entity types.
 *
 * @see \Drupal\jsonapi\ResourceType\ResourceTypeRepository
 */
class ReservoirResourceTypeRepository extends ResourceTypeRepository {

  /**
   * Quite a few entity types are only relevant in Reservoir's UI: they do not
   * make sense to expose via APIs.
   *
   * @todo make protected once \Drupal\reservoir_ui\Controller\OpenApiDocs::generateDocs() no longer uses this.
   */
  public static $blacklistedEntityTypeIds = [
    // Needed for Drupal core, and even for Reservoir's UI. We cannot unset it
    // in reservoir_ui_entity_type_alter().
    'date_format',

    // Needed for Drupal core's user_user_role_insert().
    'action',

    // Needed only for Reservoir's UI.
    'tour',

    // Needed only for Reservoir's content editing UI.
    'filter_format',
    'editor',

    // These are configurable, but only through Reservoir's UI. Note that we
    // *do* expose field_config, field_storage_config and node_type, because
    // those are necessary for building client-side UIs that change when the
    // content model changes.
    'entity_form_display',
    'entity_view_display',
    'entity_form_mode',
    'entity_view_mode',
    'base_field_override',
    'image_style',
    // @todo Consider exposing managing of vocabularies in Reservoir UI, if the need arises.
    'taxonomy_vocabulary',

    // Needed for authentication, only configurable through Reservoir's UI
    // (although oauth2_token content entities are of course created when
    // using OAuth2 authentication).
    'user_role',
    'oauth2_client',
    'oauth2_token',
    'oauth2_token_type',

    // @todo remove dependency on block module.
    'block',
  ];

  /**
   * The JSON API resource type repository.
   *
   * @var \Drupal\jsonapi\ResourceType\ResourceTypeRepository
   */
  protected $jsonapiResourceTypeRepository;

  public function __construct(ResourceTypeRepository $jsonapi_resource_type_repository, EntityTypeManagerInterface $entity_type_manager, EntityTypeBundleInfoInterface $bundle_manager) {
    $this->jsonapiResourceTypeRepository = $jsonapi_resource_type_repository;
    parent::__construct($entity_type_manager, $bundle_manager);
  }

  /**
   * {@inheritdoc}
   */
  public function all() {
    $resource_types = $this->jsonapiResourceTypeRepository->all();
    foreach ($resource_types as $key => $resource_type) {
      if (in_array($resource_type->getEntityTypeId(), static::$blacklistedEntityTypeIds, TRUE)) {
        unset($resource_types[$key]);
      }
    }
    return $resource_types;
  }

}
