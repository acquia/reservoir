<?php

namespace Drupal\reservoir_ui\Controller;

use Drupal\Core\Url;

/**
 * Controller for generating documentation pages.
 */
class OpenApiDocs {

  /**
   * Generating documentation for node bundles.
   *
   * @param string $entity_type_id
   *   The entity type.
   * @param string $node_type
   *   The entity bundle.
   *
   * @return array
   *   The generated documentation.
   */
  public function generateBundleDocs($entity_type_id, $node_type) {
    $query = [
      'options[bundle_name]' => $node_type,
      'options[entity_type_id]' => $entity_type_id,
    ];
    return $this->generateDocsFromQuery($query);
  }

  /**
   * Generate the default docs.
   *
   * @return array
   *   The generated documentation.
   */
  public function generateDocs($entity_mode) {
    $options = [
      'entity_mode' => $entity_mode,
    ];
    // Quite a few entity types are only relevant in Reservoir's UI: they do not
    // make sense to expose via APIs.
    $options['exclude'] = [
      // Needed for Drupal core, and even for Reservoir's UI. We cannot unset it
      // in reservoir_ui_entity_type_alter().
      'date_format',

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
    return $this->generateDocsFromQuery(['options' => $options]);
  }

  /**
   * Generates the doc for query options.
   *
   * @param array $query
   *   The query options for the OpenAPI generation.
   *
   * @return array
   *   The generated documentation.
   */
  protected function generateDocsFromQuery(array $query) {
    $route_options = [
      'query' => [
        '_format' => 'json',
      ] + $query,
    ];
    $build = [
      '#theme' => 'redoc',
      '#attributes' => [
        'no-auto-auth' => TRUE,
        'scroll-y-offset' => 150,
      ],
      '#openapi_url' => Url::fromRoute('openapi.jsonapi', [], $route_options)
        ->setAbsolute()
        ->toString(),
    ];
    return $build;
  }

}
