<?php

namespace Drupal\baas_ui;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\toolbar\Ajax\SetSubtreesCommand;

class BaasUiController extends ControllerBase {


  /**
   * Returns an AJAX response to render the toolbar subtrees.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   */
  public function apis() {
    $html = <<<HTML
<dl>
<dt>Classical REST</dt>
<dd>Not activated.</dd>
<dt>JSON API</dt>
<dd>Not activated.</dd>
<dt>GraphQL</dt>
<dd>Not activated.</dd>
</dl>
HTML;
    return [
      '#markup' => $html
    ];
  }

}
