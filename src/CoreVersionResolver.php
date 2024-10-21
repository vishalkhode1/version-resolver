<?php

declare(strict_types=1);

namespace DrupalTool\Resolver;

use DrupalTool\Resolver\Enum\CoreVersionResolverEnum;
use DrupalTool\Resolver\Enum\StabilityEnum;
use DrupalTool\Resolver\Loader\LoaderInterface;

/**
 * Resolve and manages Drupal core versions based on data from an XML resource.
 */
class CoreVersionResolver extends VersionResolver implements CoreVersionResolverInterface {

  /**
   * {@inheritdoc}
   */
  public function __construct(string $project_name, ?LoaderInterface $release_loader = NULL) {
    parent::__construct("drupal", $release_loader);
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrent(): ?string {
    $version = $this->resolvedReleases[CoreVersionResolverEnum::CURRENT] ?? NULL;
    if (!$version) {
      $releases = $this->getFilteredReleases();
      foreach ($releases as $version => $major_releases) {
        $release = $this->findReleaseInMajor($version, StabilityEnum::STABLE);
        if ($release) {
          $version = $release['version'];
          ;
          break;
        }
      }
    }
    $this->resolvedReleases[CoreVersionResolverEnum::CURRENT] = $version ?? NULL;
    return $this->resolvedReleases[CoreVersionResolverEnum::CURRENT];
  }

  /**
   * Finds the next or previous release version of current release.
   *
   * @param string $type
   *   Given release type.
   * @param int $stability_flag
   *   Given stability flag.
   */
  private function findNextOrPreviousForCurrentVersion(string $type, int $stability_flag): ?array {
    $current = $this->getCurrent();
    [$major, $minor] = explode('.', $current);
    switch ($type) {
      case CoreVersionResolverEnum::NEXT_MAJOR:
        $key = ($major + 1) . '.0';
        break;

      case CoreVersionResolverEnum::NEXT_MINOR:
        $key = "$major." . ($minor + 1);
        break;

      case CoreVersionResolverEnum::PREVIOUS_MAJOR:
        $key = ($major - 1) . '.x';
        break;

      case CoreVersionResolverEnum::PREVIOUS_MINOR:
        $key = "$major." . ($minor - 1);
        break;

    }
    return $this->findNextOrPrevious($key, $stability_flag);
  }

  /**
   * Finds next or previous release version.
   *
   * @param string $key
   *   Given release key.
   * @param int $stability_flag
   *   Given stability flag.
   */
  protected function findNextOrPrevious(string $key, int $stability_flag): ?array {
    [, $minor] = explode('.', $key);
    if ($minor == "x") {
      return $this->findReleaseInMajor($key, $stability_flag);
    }
    else {
      return $this->findReleaseInMinor($key, $stability_flag);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getNextMajor(): ?string {
    $release = $this->findNextOrPreviousForCurrentVersion(CoreVersionResolverEnum::NEXT_MAJOR, StabilityEnum::ANY_STABLE) ?? [];
    return $release['version'] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getNextMajorDev(): ?string {
    $release = $this->findNextOrPreviousForCurrentVersion(CoreVersionResolverEnum::NEXT_MAJOR, StabilityEnum::ANY_DEV);
    return $release['version'] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getNextMinor(): ?string {
    $release = $this->findNextOrPreviousForCurrentVersion(CoreVersionResolverEnum::NEXT_MINOR, StabilityEnum::ANY_STABLE);
    return $release['version'] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getNextMinorDev(): ?string {
    $release = $this->findNextOrPreviousForCurrentVersion(CoreVersionResolverEnum::NEXT_MINOR, StabilityEnum::DEV);
    return $release['version'] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getPreviousMinor(): ?string {
    $release = $this->findNextOrPreviousForCurrentVersion(CoreVersionResolverEnum::PREVIOUS_MINOR, StabilityEnum::ANY_STABLE);
    return $release['version'] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getPreviousMinorDev(): ?string {
    $release = $this->findNextOrPreviousForCurrentVersion(CoreVersionResolverEnum::PREVIOUS_MINOR, StabilityEnum::DEV);
    return $release['version'] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getPreviousMajor(): ?string {
    $release = $this->findNextOrPreviousForCurrentVersion(CoreVersionResolverEnum::PREVIOUS_MAJOR, StabilityEnum::ANY_STABLE);
    return $release['version'] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getPreviousMajorDev(): ?string {
    $release = $this->findNextOrPreviousForCurrentVersion(CoreVersionResolverEnum::PREVIOUS_MAJOR, StabilityEnum::DEV);
    return $release['version'] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getOldestSupported(): ?string {
    $releases = $this->getSupportedReleases();
    $release = array_pop($releases);
    return $release['stable']['version'] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getOldestSupportedDev(): ?string {
    $releases = $this->getSupportedReleases();
    $release = array_pop($releases);
    return $release['dev']['version'] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getLatestEolMajor(): ?string {
    $release = $this->getOldestSupported();
    [$major] = explode('.', $release);
    $major = $major - 1;
    $all_releases = $this->getAllReleases();
    $releases = $all_releases["$major.x"] ?? [];
    $releases = reset($releases);
    $release = ($releases) ? reset($releases) : NULL;
    return $release['version'] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getLatestEolMajorDev(): ?string {
    $release = $this->getLatestEolMajor();
    [$major, $minor] = explode('.', $release);
    $all_releases = $this->getAllReleases();
    $release = $all_releases["$major.x"]["$major.$minor"]["$major.$minor.x-dev"] ?? [];
    return $release['version'] ?? NULL;
  }

}
