<?php

namespace Drupal\reservoir_ui\Form;

use \Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class ApiForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'reservoir_ui_api';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['intro'] = [
      '#markup' => '<p><em>This is where the interactive API documentation powered by OpenAPI will appear.</em></p>',
    ];

    $form['#theme'] = 'system_config_form';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
