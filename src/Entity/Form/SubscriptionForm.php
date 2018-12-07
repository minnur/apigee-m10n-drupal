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

namespace Drupal\apigee_m10n\Entity\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Cache\Cache;
use Drupal\apigee_m10n\Monetization;

/**
 * Subscription entity form.
 */
class SubscriptionForm extends MonetizationEntityForm {

  /**
   * Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Monetization factory.
   *
   * @var \Drupal\apigee_m10n\MonetizationInterface
   */
  protected $monetization;

  /**
   * Constructs a SubscriptionEditForm object.
   *
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository service.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   Messenger service.
   */
  public function __construct(EntityRepositoryInterface $entity_repository, EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL, TimeInterface $time = NULL, MessengerInterface $messenger = NULL, Monetization $monetization = NULL) {
    parent::__construct($entity_repository, $entity_type_bundle_info, $time);
    $this->messenger = $messenger;
    $this->monetization = $monetization;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.repository'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time'),
      $container->get('messenger'),
      $container->get('apigee_m10n.monetization')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    // We won't ask a user to accept terms and conditions again if
    // it has been already accepted.
    if (!$this->monetization->isLatestTermsAndConditionAccepted($this->getEntity()->getDeveloper()->getEmail())) {
      $form['tnc'] = [
        '#type'  => 'checkbox',
        '#title' => $this->t('Acceptance text goes here'),
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    // We only apply checking when terms and conditions checkbox is present in the form.
    if (!empty($form['tnc']) && empty($form_state->getValue('tnc'))) {
      $form_state->setErrorByName('tnc', $this->t('Terms and conditions acceptance required.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    // Set the save label if one has been passed into storage.
    if (!empty($actions['submit']) && ($save_label = $form_state->get('save_label'))) {
      $actions['submit']['#value'] = $save_label;
    }
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    try {
      $this->monetization->acceptLatestTermsAndConditions($this->getEntity()->getDeveloper()->getEmail());
      $display_name = $this->entity->getRatePlan()->getDisplayName();
      if ($this->entity->save()) {
        $this->messenger->addStatus($this->t('You have purchased <em>%label</em> plan', [
          '%label' => $display_name,
        ]));
      }
      else {
        $this->messenger->addWarning($this->t('Unable purchase <em>%label</em> plan', [
          '%label' => $display_name,
        ]));
      }
      Cache::invalidateTags(['apigee_my_subscriptions']);
      $form_state->setRedirect('apigee_monetization.my_subscriptions');
    }
    catch (\Exception $e) {
      $this->messenger->addError($e->getMessage());
    }
  }

}
