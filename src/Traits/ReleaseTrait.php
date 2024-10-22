<?php

declare(strict_types=1);

namespace Drupify\Resolver\Traits;

/**
 * Trait to manage release.
 */
trait ReleaseTrait {

  /**
   * Checks if given release version is stable release or not.
   *
   * @param string $release
   *   Given release version.
   */
  protected function isStable(string $release): bool {
    return preg_match('/(-rc|-alpha|-beta|-dev)\d*$/', $release) === 0;
  }

  /**
   * Checks if given release version is preferred stable release.
   *
   * @param string $release
   *   Given release version.
   */
  protected function isPreferredStable(string $release): bool {
    return (preg_match('/(-rc|-alpha|-beta)\d*$/', $release) === 1) || ($this->isStable($release));
  }

  /**
   * Checks if given release version is dev release.
   *
   * @param string $release
   *   Given release version.
   */
  protected function isDev(string $release): bool {
    return substr($release, -5) === "x-dev";
  }

}
