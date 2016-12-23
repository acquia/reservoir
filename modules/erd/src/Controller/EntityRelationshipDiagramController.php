<?php

/**
 * @file
 * Contains \Drupal\erd\Controller\EntityRelationshipDiagramController.
 */

namespace Drupal\erd\Controller;

use Drupal\Core\Config\Entity\ConfigEntityTypeInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Contains the primary entity relationship diagram for this module.
 */
class EntityRelationshipDiagramController extends ControllerBase {

  /**
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * Constructs a new EntityRelationshipDiagram.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFieldManager = $entity_field_manager;
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager'),
      $container->get('entity_type.bundle.info')
    );
  }

  public function getMainDiagram() {
    $entity_definitions = $this->entityTypeManager->getDefinitions();
    $entities = [];
    $links = [];

    foreach ($entity_definitions as $definition) {
      $entity = [
        'id' => $definition->id(),
        'type' => 'type',
        'type_label' => t('Entity Type'),
        'label' => $definition->getLabel(),
        'provider' => $definition->getProvider(),
        'group' => $definition->getGroup(),
        'bundles' => [],
      ];

      if ($definition instanceof ConfigEntityTypeInterface) {
        $entity['config_properties'] = $definition->getPropertiesToExport();
      }

      $bundles = $this->entityTypeBundleInfo->getBundleInfo($definition->id());
      foreach ($bundles as $bundle_id => $bundle_label) {
        $bundle = [
          'id' => $bundle_id,
          'type' => 'bundle',
          'type_label' => t('Entity Bundle'),
          'label' => $bundle_label['label'],
          'entity_type' => $definition->id(),
        ];

        if ($definition->isSubclassOf(FieldableEntityInterface::class)) {
          $bundle['fields'] = [];
          $fields = $this->entityFieldManager->getFieldDefinitions($definition->id(), $bundle_id);
          foreach ($fields as $field) {
            $field_storage_definition = $field->getFieldStorageDefinition();
            $field_settings = $field->getItemDefinition()->getSettings();

            $field_name = $field_storage_definition->getName();
            $bundle['fields'][$field_name] = [
              'id' => $field_name,
              'label' => $field->getLabel(),
              'type' => $field_storage_definition->getType(),
              'description' => $field_storage_definition->getDescription(),
              'cardinality' => $field_storage_definition->getCardinality(),
              'is_multiple' => $field_storage_definition->isMultiple(),
            ];
            $types[$field_storage_definition->getType()] = $field_storage_definition->getType();
            if ($bundle['fields'][$field_name]['type'] == 'entity_reference') {
              $link = [
                'label' => t('Entity Reference from field "@field_name"', [
                  '@field_name' => $field_name
                ]),
                'from' => 'bundle:' . $bundle_id,
                'from_selector' => '.attribute-background-' . $field_name,
                'targets' => ['type:' . $field_settings['target_type']],
              ];

              if (isset($field_settings['handler_settings']['target_bundles'])) {
                foreach ($field_settings['handler_settings']['target_bundles'] as $target_bundle) {
                  $link['targets'][] = 'bundle:' . $target_bundle;
                }
              }

              $links[] = $link;
            }
            else if ($bundle['fields'][$field_name]['type'] == 'image') {
              $links[] = [
                'label' => t('Image Reference from field "@field_name"', [
                  '@field_name' => $field_name
                ]),
                'from' => 'bundle:' . $bundle_id,
                'from_selector' => '.attribute-background-' . $field_name,
                'targets' => ['type:' . $field_settings['target_type']],
              ];
            }
          }
        }

        $entity['bundles'][$bundle_id] = $bundle;
      }

      $entities[$definition->id()] = $entity;
    }

    return [
      '#markup' =>
        '<div class="erd-actions">' .
          '<i title="Add Entity Type or Bundle" class="erd-search">' .
          '  <input type="text"/>' .
          '</i>' .
          '<i title="Add editable label" class="erd-label"></i>' .
        '<i title="Change link styles" class="erd-line-style"></i>' .
        '<i title="Toggle machine names" class="erd-machine-name"></i>' .
        '<i title="Save to image" class="erd-save"></i>' .
        '<i title="Zoom in" class="erd-zoom"></i>' .
        '<i title="Zoom out" class="erd-unzoom"></i>' .
        '</div>' .
        '<div class="erd-container"></div>',
      '#allowed_tags' => ['input', 'div', 'i'],
      '#attached' => [
        'library' => ['erd/main'],
        'drupalSettings' => [
          'erd' => [
            'entities' => $entities,
            'links' => $links,
          ],
        ],
      ],
    ];
  }

}
