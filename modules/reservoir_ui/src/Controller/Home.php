<?php

namespace Drupal\reservoir_ui\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Cache\CacheableRedirectResponse;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class Home implements ContainerInjectionInterface {

  /**
   * The current user service.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  public function __construct(AccountInterface $current_user) {
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user')
    );
  }

  public function home() {
    $content_access = AccessResult::allowedIfHasPermission($this->currentUser, 'access content overview');
    if ($content_access->isAllowed()) {
      $redirect_url = Url::fromRoute('system.admin_content')->toString(TRUE);
      return (new CacheableRedirectResponse($redirect_url->getGeneratedUrl()))
        ->addCacheableDependency($redirect_url)
        ->addCacheableDependency($content_access);
    }

    $api_docs_access = AccessResult::allowedIfHasPermission($this->currentUser, 'access openapi api docs');
    if ($api_docs_access->isAllowed()) {
      $redirect_url = Url::fromRoute('reservoir_ui.api')->toString(TRUE);
      return (new CacheableRedirectResponse($redirect_url->getGeneratedUrl()))
        ->addCacheableDependency($redirect_url)
        ->addCacheableDependency($content_access)
        ->addCacheableDependency($api_docs_access);
    }

    // Uncacheable redirect to the current user profile page.
    return new RedirectResponse(User::load($this->currentUser->id())
      ->toUrl('canonical')
      ->toString(TRUE)
      ->getGeneratedUrl()
    );
  }

}
