<?php

/**
 * @file
 * Contains \Drupal\mygov_registration\Routing\RouteSubscriber.
 */

namespace Drupal\mygov_registration\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // login form
    if ($route = $collection->get('user.login')) {
      $route->setDefault('_form', '\Drupal\mygov_registration\Form\NewUserLoginForm');
    }
    // register form
    if ($route = $collection->get('user.register')) {
      $route->setDefault('_form', '\Drupal\mygov_registration\Form\NewUserRegisterForm');
    }

  }
}
