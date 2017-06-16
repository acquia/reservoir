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
    if ($entity_mode === 'content_entities') {
      // Exclude content entities that are not related to the exposed data
      // models.
      $options['exclude'] = [
        'oauth2_client',
        'oauth2_token',
      ];
    }
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
