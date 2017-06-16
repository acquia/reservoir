<?php

namespace Drupal\reservoir_ui\Controller;

use Drupal\Core\Url;
use Drupal\simple_oauth\Entity\Oauth2Client;
use Drupal\user\Entity\User;

class ApiAuthInfo {

  public function info() {
    $build = [];

    $demo_user = User::load(2);
    $demo_client = Oauth2Client::load(1);

    if ($demo_user && $demo_client) {
      $request = \Drupal::request();
      $host = $request->getSchemeAndHttpHost() . $request->getBaseUrl();
      $client_id = $demo_client->uuid();
      $html = <<<HTML
<p>To get started, this is how you can retrieve an access token for the demo user & client:</p>
<pre><code>
curl -X POST -d "grant_type=password&client_id=$client_id&client_secret=foobar&username=demo-user&password=demo-user" $host/oauth/token
</code></pre>
<br>
<p><small>This uses an OAuth2 password grant to retrieve an access token and a refresh token. Use your favorite library's OAuth2 support, or learn how OAuth2 works.</small></p>
HTML;
      $build['info'] = [
        '#markup' => $html,
      ];
      drupal_set_message(t('You still have the demo user & client. Delete them before going into production! Add <a href=":users">users</a> and <a href=":clients">clients</a>, and take the <a href=":tour">access control tour</a>.', [':users' => Url::fromRoute('entity.user.collection')->toString(), ':clients' => Url::fromRoute('entity.oauth2_client.collection')->toString(), ':tour' => Url::fromRoute('entity.user.collection')->setOption('query', ['tour' => TRUE])->toString()]), 'warning');
    }
    else {
      $build['#attached']['html_head'][] = [
        [
          '#tag' => 'meta',
          '#attributes' => [
            'http-equiv' => 'Refresh',
            'content' => '5; URL=' . Url::fromRoute('entity.oauth2_token.collection')->toString(),
          ],
        ],
        'reservoir_ui_api_authentication_production_redirect',
      ];
      drupal_set_message(t('You know what you are doing, because you have deleted the demo user & client. Great! Redirecting you to the …'));
    }

    if (\Drupal::request()->getScheme() !== 'https') {
      drupal_set_message(t('This Reservoir instance is not using HTTPS, this is insecure. Do not do this in production!'), 'error');
    }

    return $build;
  }

}
