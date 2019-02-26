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

namespace Drupal\apigee_m10n_teams\Plugin\AddCreditEntityType;

use Drupal\apigee_edge_teams\TeamMembershipManagerInterface;
use Drupal\apigee_m10n_add_credit\Annotation\AddCreditEntityType;
use Drupal\apigee_m10n_add_credit\Plugin\AddCreditEntityTypeBase;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a team add credit entity type plugin.
 *
 * @AddCreditEntityType(
 *   id = "team",
 *   label = "Team",
 *   path = "/teams/{team}/monetization/billing/add-credit/{currency}",
 * )
 */
class Team extends AddCreditEntityTypeBase implements ContainerFactoryPluginInterface {

  /**
   * The team membership manager.
   *
   * @var \Drupal\apigee_edge_teams\TeamMembershipManagerInterface
   */
  protected $teamMembershipManager;

  /**
   * Team constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\apigee_edge_teams\TeamMembershipManagerInterface $team_membership_manager
   *   The team membership manager.
   */
  public function __construct(array $configuration, string $plugin_id, $plugin_definition, TeamMembershipManagerInterface $team_membership_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->teamMembershipManager = $team_membership_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('apigee_edge_teams.team_membership_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function access(EntityInterface $entity, AccountInterface $account): AccessResultInterface {
    /** @var \Drupal\apigee_edge_teams\Entity\TeamInterface $entity */

    // If user can add credit to any team, access is allowed.
    if ($account->hasPermission('add credit to any team prepaid balance')) {
      return AccessResult::allowed();
    }

    // If user can add credit to own team only, check if this user belongs to this team.
    if (($account->hasPermission('add credit to own team prepaid balance'))
      && ($team_ids = $this->teamMembershipManager->getTeams($account->getEmail()))) {
      return AccessResult::allowedIf(in_array($entity->id(), $team_ids));
    }

    return AccessResult::forbidden();
  }

}
