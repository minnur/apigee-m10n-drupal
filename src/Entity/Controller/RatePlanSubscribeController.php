<?php

/*
 * Copyright 2018 Google Inc.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 2 as published by the
 * Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public
 * License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 */

namespace Drupal\apigee_m10n\Entity\Controller;

use Apigee\Edge\Api\Monetization\Entity\Developer;
use Drupal\apigee_m10n\Entity\RatePlanInterface;
use Drupal\apigee_m10n\Entity\Subscription;
use Drupal\apigee_m10n\Form\SubscriptionConfigForm;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for subscribing to rate plans.
 */
class RatePlanSubscribeController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Entity form builder.
   *
   * @var \Drupal\Core\Entity\EntityFormBuilderInterface
   */
  protected $entityFormBuilder;

  /**
   * BillingController constructor.
   *
   * @param \Drupal\Core\Entity\EntityFormBuilderInterface $entityFormBuilder
   *   Entity form builder service.
   */
  public function __construct(EntityFormBuilderInterface $entityFormBuilder) {
    $this->entityFormBuilder = $entityFormBuilder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.form_builder')
    );
  }

  /**
   * Page callback to create a new subscription.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user to subscribe.
   * @param \Drupal\apigee_m10n\Entity\RatePlanInterface $rate_plan
   *   The rate plan.
   *
   * @return array
   *   A subscribe form render array.
   *
   * @throws \Exception
   */
  public function subscribeForm(UserInterface $user, RatePlanInterface $rate_plan) {
    // Create a subscription to pass to the subscription edit form.
    $subscription = Subscription::create([
      'ratePlan' => $rate_plan,
      'developer' => new Developer(['email' => $user->getEmail()]),
      'startDate' => new \DateTimeImmutable(),
    ]);

    // Get the save label from settings.
    $save_label = $this->config(SubscriptionConfigForm::CONFIG_NAME)->get('subscribe_button_label');
    $save_label = $save_label ?? 'Subscribe';

    // Return the subscribe form with the label set.
    return $this->entityFormBuilder->getForm($subscription, 'default', [
      'save_label' => $this->t($save_label, [
        '@rate_plan' => $rate_plan->getDisplayName(),
        '@username' => $user->label(),
      ]),
    ]);
  }

  /**
   * Gets the title for the subscribe page.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\user\UserInterface|null $user
   *   The user.
   * @param \Drupal\apigee_m10n\Entity\RatePlanInterface|null $rate_plan
   *   The rate plan.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The title.
   */
  public function title(RouteMatchInterface $route_match, UserInterface $user = NULL, RatePlanInterface $rate_plan = NULL) {
    $title_template = $this->config(SubscriptionConfigForm::CONFIG_NAME)->get('subscribe_form_title');
    $title_template = $title_template ?? 'Subscribe to @rate_plan';
    return $this->t($title_template, [
      '@rate_plan' => $rate_plan->getDisplayName(),
      '%rate_plan' => $rate_plan->getDisplayName(),
      '@username' => $user->label(),
      '%username' => $user->label(),
    ]);
  }

}
