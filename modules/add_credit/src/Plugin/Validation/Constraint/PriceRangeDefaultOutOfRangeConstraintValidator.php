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

namespace Drupal\apigee_m10n_add_credit\Plugin\Validation\Constraint;

use Drupal\apigee_m10n_add_credit\Plugin\Field\FieldType\PriceRangeItem;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validates the PriceRangeDefaultOutOfRange constraint.
 */
class PriceRangeDefaultOutOfRangeConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) {
    if (!($value instanceof PriceRangeItem)) {
      throw new UnexpectedTypeException($value, PriceRangeItem::class);
    }

    $price_range = $value->getValue();

    if (!$price_range['default']) {
      return;
    }

    $default = $price_range['default'];

    if (isset($price_range['minimum']) && isset($price_range['maximum'])
      && ($default < $price_range['minimum'] || $default > $price_range['maximum'])) {
      $this->context->addViolation($constraint->outOfRangeMessage);
    }
    elseif (isset($price_range['minimum']) && !isset($price_range['maximum'])
      && ($default < $price_range['minimum'])) {
      $this->context->addViolation($constraint->minMessage);
    }
    elseif (isset($price_range['maximum'])
      && ($default > $price_range['maximum'])) {
      $this->context->addViolation($constraint->maxMessage);
    }
  }

}
