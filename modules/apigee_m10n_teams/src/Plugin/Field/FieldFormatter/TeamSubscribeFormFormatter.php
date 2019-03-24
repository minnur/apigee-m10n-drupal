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

namespace Drupal\apigee_m10n_teams\Plugin\Field\FieldFormatter;

use Apigee\Edge\Api\Monetization\Entity\Company;
use Drupal\apigee_edge_teams\Entity\TeamInterface;
use Drupal\apigee_m10n\Entity\Subscription;
use Drupal\apigee_m10n\Plugin\Field\FieldFormatter\SubscribeFormFormatter;
use Drupal\apigee_m10n_teams\MonetizationTeams;
use Drupal\apigee_m10n\Monetization;
use Drupal\apigee_m10n\Form\SubscriptionConfigForm;
use Drupal\Core\Field\FieldItemInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;

/**
 * Override class for the `apigee_subscribe_form` field formatter.
 */
class TeamSubscribeFormFormatter extends SubscribeFormFormatter {

  /**
   * The Cache backend.
   *
   * @var \Drupal\apigee_m10n\Monetization
   */
  private $team_monetization;

  /**
   * Creates an instance of the plugin.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Entity form builder service.
   * @param \Drupal\apigee_m10n\Monetization $monetization
   *   Monetization service.
   * @param \Drupal\apigee_m10n_teams\MonetizationTeams $team_monetization
   *   Team Monetization service.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, Monetization $monetization, MonetizationTeams $team_monetization) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings, $monetization);
    $this->team_monetization = $team_monetization;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('apigee_m10n.monetization'),
      $container->get('apigee_m10n.teams')
    );
  }

  /**
   * Renderable entity form that handles teams.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   Field item variable.
   *
   * @return array
   *   Renderable form elements.
   *
   * @throws \Exception
   */
  protected function viewValue(FieldItemInterface $item) {
    if (($value = $item->getValue()) && (isset($value['team'])) && ($value['team'] instanceof TeamInterface)) {
      $rate_plan = $item->getEntity();
      if ($rate_plan->access('subscribe')) {
        if ($this->team_monetization->isCompanyAlreadySubscribed($value['team']->id(), $rate_plan)) {
          $label = \Drupal::config(SubscriptionConfigForm::CONFIG_NAME)->get('already_purchased_label');
          return [
            '#markup' => '<div class="apigee-plan-already-purchased">' . $this->t($label ?? 'Already purchased %rate_plan', [
                '%rate_plan' => $rate_plan->getDisplayName()
              ]) . '</div>'
          ];
        }
        $subscription = Subscription::create([
          'ratePlan' => $rate_plan,
          'company' => new Company(['id' => $value['team']->id()]),
          'startDate' => new \DateTimeImmutable(),
        ]);
        return $this->entityFormBuilder->getForm($subscription, 'default', [
          'save_label' => $this->t('@save_label', ['@save_label' => $this->getSetting('label')]),
        ]);
      }
    }
    else {
      return parent::viewValue($item);
    }
  }

}
