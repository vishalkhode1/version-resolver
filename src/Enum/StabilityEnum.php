<?php

declare(strict_types=1);

namespace Drupify\Resolver\Enum;

/**
 * Class to manage drupal core/package stability.
 */
abstract class StabilityEnum {

  /**
   * Stability flags.
   */
  const STABLE = 1;
  const ANY_STABLE = 2;
  const ANY_DEV = 3;
  const DEV = 4;

}
