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

use Drupal\apigee_m10n\Plugin\Field\FieldFormatter\SubscribeLinkFormatter;
use Drupal\apigee_m10n_teams\MonetizationTeams;
use Drupal\apigee_m10n\Monetization;
use Drupal\apigee_m10n\Form\SubscriptionConfigForm;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Link;

/**
 * Class override for the `apigee_subscribe_link` field formatter.
 */
class TeamSubscribeLinkFormatter extends SubscribeLinkFormatter {

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
   * Renderable link element.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   Field item variable.
   *
   * @return array
   *   Renderable link element.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  protected function viewValue(FieldItemInterface $item) {
    /** @var \Drupal\apigee_m10n_teams\Entity\TeamAwareRatePlan $rate_plan */
    $rate_plan = $item->getEntity();
    $canonical_url = $rate_plan->toUrl();
    if ($canonical_url->getRouteName() === 'entity.rate_plan.team') {
      $value = $item->getValue();
      if ($this->team_monetization->isCompanyAlreadySubscribed($value['team']->id(), $rate_plan)) {
        $label = \Drupal::config(SubscriptionConfigForm::CONFIG_NAME)->get('already_purchased_label');
        return [
          '#markup' => '<div class="apigee-plan-already-purchased">' . $this->t($label ?? 'Already purchased %rate_plan', [
              '%rate_plan' => $rate_plan->getDisplayName()
            ]) . '</div>'
        ];
      }
      return Link::createFromRoute($this->getSetting('label'), 'entity.rate_plan.team_subscribe', $canonical_url->getRouteParameters())->toRenderable();
    }
    else {
      return parent::viewValue($item);
    }
  }

}
