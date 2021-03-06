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

namespace Drupal\apigee_m10n\Entity;

use Apigee\Edge\Api\Monetization\Entity\ApiPackageInterface;
use Drupal\apigee_edge\Entity\FieldableEdgeEntityInterface;

/**
 * Defines the interface for the `package` entity objects.
 */
interface PackageInterface extends ApiPackageInterface, FieldableEdgeEntityInterface {

  /**
   * Gets a list of package entities for a developer.
   *
   * @param string $developer_id
   *   The SDK developer ID.
   *
   * @return array
   *   A list of package entities that would have be available to a developer.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public static function getAvailableApiPackagesByDeveloper($developer_id);

}
