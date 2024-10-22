<?php

declare(strict_types=1);

namespace Drupify\Resolver;

/**
 * Interface to resolve drupal project release.
 */
interface VersionResolverInterface {

  /**
   * Returns an array of all releases.
   */
  public function getAllReleases(): array;

  /**
   * Returns an array of all supported releases.
   */
  public function getSupportedReleases(): array;

  /**
   * Returns the current release version.
   */
  public function getCurrent(): ?string;

  /**
   * Returns the current dev release version.
   */
  public function getCurrentDev(): ?string;

}
