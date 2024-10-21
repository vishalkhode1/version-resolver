<?php

declare(strict_types=1);

namespace DrupalTool\Resolver;

use DrupalTool\Resolver\Enum\CoreVersionResolverEnum;
use DrupalTool\Resolver\Enum\StabilityEnum;

/**
 * Resolve and manages Drupal core versions based on data from an XML resource.
 */
class VersionResolver extends VersionResolverBase {

  /**
   * {@inheritdoc}
   */
  public function getCurrent(): ?string {
    $this->load();
    $version = $this->resolvedReleases[CoreVersionResolverEnum::CURRENT] ?? NULL;
    if (!$version) {
      $releases = $this->getFilteredReleases();
      foreach ($releases as $version => $major_releases) {
        $version = $this->findReleaseInMajor($version, StabilityEnum::ANY_STABLE);
        if ($version) {
          break;
        }
      }
      $this->resolvedReleases[CoreVersionResolverEnum::CURRENT] = $version['version'] ?? NULL;
    }
    return $this->resolvedReleases[CoreVersionResolverEnum::CURRENT];
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrentDev(): ?string {
    $this->load();
    $current_dev = $this->resolvedReleases[CoreVersionResolverEnum::CURRENT_DEV] ?? NULL;
    if (!$current_dev) {
      $current = $this->resolvedReleases[CoreVersionResolverEnum::CURRENT] ?? $this->getCurrent();
      $current_dev = $current ? $this->findReleaseInMinor($current, StabilityEnum::ANY_DEV) : NULL;
      $this->resolvedReleases[CoreVersionResolverEnum::CURRENT_DEV] = $current_dev['version'] ?? NULL;
    }
    return $this->resolvedReleases[CoreVersionResolverEnum::CURRENT_DEV];
  }

}
