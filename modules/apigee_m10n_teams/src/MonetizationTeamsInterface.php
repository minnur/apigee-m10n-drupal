<?php

/*
 * @file
 * Copyright 2018 Google Inc.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * version 2 as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 */

namespace Drupal\apigee_m10n_teams;

use Apigee\Edge\Api\Monetization\Entity\TermsAndConditionsInterface;
use Apigee\Edge\Api\Monetization\Structure\LegalEntityTermsAndConditionsHistoryItem;
use Drupal\apigee_edge_teams\Entity\TeamInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Interface for the `apigee_m10n.teams` service.
 */
interface MonetizationTeamsInterface {

  /**
   * Handles `hook_entity_type_alter` for the `apigee_m10n_teams` module.
   *
   * @param array $entity_types
   *   An array of entity types.
   */
  public function entityTypeAlter(array &$entity_types);

  /**
   * Handles `hook_field_formatter_info_alter` for `apigee_m10n_teams`.
   *
   * @param array $info
   *   An array of field formatter plugin definitions.
   */
  public function fieldFormatterInfoAlter(array &$info);

  /**
   * Handles `hook_field_widget_info_alter` for `apigee_m10n_teams`.
   *
   * @param array $info
   *   An array of field widget plugin definitions.
   */
  public function fieldWidgetInfoAlter(array &$info);

  /**
   * Handles `hook_ENTITY_TYPE_access` for the `apigee_m10n_teams` module.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The `subscription` entity.
   * @param string $operation
   *   The operation.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account requesting access.
   *
   * @return \Drupal\Core\Access\AccessResultInterface|null
   *   The access result or null for non-team routes.
   */
  public function entityAccess(EntityInterface $entity, $operation, AccountInterface $account);

  /**
   * Gets the current team from the route object.
   *
   * @return \Drupal\apigee_edge_teams\Entity\TeamInterface|null
   *   The current team from the route.
   */
  public function currentTeam(): ?TeamInterface;

  /**
   * Handles `hook_subscription_access` for the `apigee_m10n_teams` module.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The `subscription` entity.
   * @param string $operation
   *   The operation.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account requesting access.
   *
   * @return \Drupal\Core\Access\AccessResultInterface|null
   *   The access result or null for non-team routes.
   */
  public function subscriptionAccess(EntityInterface $entity, $operation, AccountInterface $account);

  /**
   * Handles `hook_subscription_create_access` for `apigee_m10n_teams`.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account requesting access.
   * @param array $context
   *   An array of relevant context.
   * @param string $entity_bundle
   *   This is always `subscription`.
   *
   * @return \Drupal\Core\Access\AccessResultInterface|null
   *   The access result or null for non-team routes.
   */
  public function subscriptionCreateAccess(AccountInterface $account, array $context, $entity_bundle);

  /**
   * Handles `hook_rate_plan_access` for the `apigee_m10n_teams` module.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The `rate_plan` entity.
   * @param string $operation
   *   The operation.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account requesting access.
   *
   * @return \Drupal\Core\Access\AccessResultInterface|null
   *   The access result or null for non-team routes.
   */
  public function ratePlanAccess(EntityInterface $entity, $operation, AccountInterface $account);

  /**
   * Check if company accepted latest terms and conditions.
   *
   * @param string $company_id
   *   Company ID.
   *
   * @return bool|null
   *   User terms and conditions acceptance flag.
   */
  public function isLatestTermsAndConditionAccepted(string $company_id): ?bool;

  /**
   * Accepts a terms and conditions by its id.
   *
   * @param string $company_id
   *   Company ID.
   *
   * @return \Apigee\Edge\Api\Monetization\Structure\LegalEntityTermsAndConditionsHistoryItem|null
   *   Terms and conditions history item.
   */
  public function acceptLatestTermsAndConditions(string $company_id): ?LegalEntityTermsAndConditionsHistoryItem;

}
