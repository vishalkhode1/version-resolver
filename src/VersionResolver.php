<?php

declare(strict_types=1);

namespace DrupalTool\Resolver;

use DrupalTool\Resolver\Enum\CoreVersionResolverEnum;
use DrupalTool\Resolver\Enum\StabilityEnum;
use DrupalTool\Resolver\Loader\ExternalXmlLoader;
use DrupalTool\Resolver\Release\ReleaseLoader;
use DrupalTool\Resolver\Release\ReleaseLoaderInterface;
use DrupalTool\Resolver\Traits\ReleaseTrait;

/**
 * Resolve and manages Drupal core versions based on data from an XML resource.
 */
class VersionResolver implements VersionResolverInterface {
  use ReleaseTrait;

  /**
   * Instance of XmlLoader for loading XML data.
   *
   * @var \DrupalTool\Resolver\Loader\LoaderInterface
   */
  protected $loader;

  /**
   * Returns an array of resolved releases.
   *
   * @var array
   */
  protected $resolvedReleases;

  /**
   * An array of supported releases.
   *
   * @var array
   */
  protected $supportedReleases;

  /**
   * An array of all releases.
   *
   * @var array
   */
  protected $releases;

  /**
   * An array of all filtered releases.
   *
   * @var array
   */
  protected $filteredReleases;

  /**
   * Construct an object.
   *
   * @param string $project_name
   *   Given project name.
   * @param \DrupalTool\Resolver\Release\ReleaseLoaderInterface|null $release_loader
   *   An object of release loader.
   */
  public function __construct(string $project_name, ?ReleaseLoaderInterface $release_loader = NULL) {
    $this->loader = $release_loader ?? new ReleaseLoader(new ExternalXmlLoader(), $project_name);
  }

  /**
   * Loads the release history data from the XML resource and processes it.
   *
   * This method fetches all releases, filters supported releases,
   * and then groups and sorts them.
   */
  protected function load(): void {
    $all_data = $this->loader->load();

    // Normalize single release to array.
    $all_releases = isset($all_data['releases']['release']['version'])
      ? [$all_data['releases']['release']] : $all_data['releases']['release'];

    $this->supportedReleases = $this->buildSupportedReleases($all_data);

    // Sort supported releases to ensure this are returned in correct order.
    $this->sortVersions($this->supportedReleases['supported'], "value");

    // Process releases by filtering, grouping, and sorting them.
    $this
      ->groupReleases($all_releases)
      ->sortReleases($all_releases);
    $this->releases = $all_releases;

    $this->filteredReleases = $this->filterSupportedReleases($all_releases);
  }

  /**
   * Builds an array or supported and supported-dev releases.
   *
   * @param array $all_data
   *   An array of all release data.
   */
  protected function buildSupportedReleases(array $all_data): array {
    $supported_releases = $all_data['supported_branches'] ?? [];
    $supported = ["supported" => $supported_releases, "supported-dev" => []];
    foreach ($supported_releases as $supported_release) {
      $supported_release = str_replace("8.x-", "", $supported_release);
      [$major, $minor] = explode('.', $supported_release);
      $major_dev_release = "$major.x-dev";
      if ($minor !== "") {
        if (!in_array($major_dev_release, $supported['supported-dev'])) {
          $supported['supported-dev'][] = $major_dev_release;
        }
        $major_minor_dev_release = "$major.$minor.x-dev";
        if (!in_array($major_minor_dev_release, $supported['supported-dev'])) {
          $supported['supported-dev'][] = $major_minor_dev_release;
        }
      }
    }
    return $supported;
  }

  /**
   * Filters the supported versions from the release history data.
   *
   * @param array $releases
   *   An array of all releases.
   *
   * @return \DrupalTool\Resolver\Resolver\VersionResolverInterface
   *   Returns the instance of VersionResolver for method chaining.
   */
  protected function filterSupportedReleases(array $releases): array {
    $filteredReleases = [];
    foreach ($this->supportedReleases['supported'] as $supported) {
      $supported = str_replace("8.x-", "", $supported);
      [$major, $minor] = explode('.', $supported);
      $majorMinorVersion = ($minor !== "") ? "$major.$minor" : "8.x-$major";
      if (isset($releases["$major.x"][$majorMinorVersion])) {
        $filteredReleases["$major.x"][$majorMinorVersion] = $releases["$major.x"][$majorMinorVersion];
      }
    }
    foreach ($this->supportedReleases['supported-dev'] as $supported) {
      [$major] = explode('.', $supported);
      if (isset($releases["$major.x"][$supported])) {
        $filteredReleases["$major.x"][$supported] = $releases["$major.x"][$supported];
      }
    }
    return $filteredReleases;
  }

  /**
   * Groups and sorts the filtered data by major and minor version.
   *
   * @param array $releases
   *   An array of releases.
   *
   * @return \DrupalTool\Resolver\Resolver\VersionResolverInterface
   *   Returns the instance of VersionResolver for method chaining.
   */
  protected function groupReleases(array &$releases): VersionResolverInterface {
    $releases = array_reduce($releases, function ($carry, $release) {
      // $release['version'] = str_replace("8.x-", "", $release['version']);
      $release_array = explode('.', $release['version']);
      $major = $release_array[0];
      $minor = $release_array[1] ?? 0;
      $majorMinorVersion = $major . '.' . $minor;
      $is_legacy = substr($majorMinorVersion, 0, 4) === "8.x-";
      if ($is_legacy) {
        [, $minor] = explode('-', $majorMinorVersion);
        $major = "$minor";
      }
      $carry[$major . '.x'][$majorMinorVersion][$release['version']] = $release;
      return $carry;
    }, []);

    return $this;
  }

  /**
   * Sorts the releases by major, minor, and patch versions.
   *
   * @param array $releases
   *   An array of releases to be sorted.
   */
  protected function sortReleases(array &$releases): VersionResolverInterface {
    // Step 1: Sort the top-level keys (major versions) using a custom key
    // function.
    uksort($releases, function ($a, $b) {
      $majorA = (int) filter_var($a, FILTER_SANITIZE_NUMBER_INT);
      $majorB = (int) filter_var($b, FILTER_SANITIZE_NUMBER_INT);
      // Sort in descending order.
      return $majorB <=> $majorA;
    });

    // Step 2: Sort the second-level keys (minor versions) within each major.
    foreach ($releases as &$minor_versions) {
      $this->sortVersions($minor_versions);

      // Step 3: Sort the third-level keys (patch versions) within each minor.
      foreach ($minor_versions as &$versions) {
        $this->sortVersions($versions);
      }
    }
    return $this;
  }

  /**
   * Sorts versions in descending order.
   *
   * @param array $versions
   *   An array of versions to be sorted.
   * @param string $type
   *   The type of sorting ('key' or 'value').
   */
  private function sortVersions(array &$versions, string $type = "key"): bool {
    $function = $type == "key" ? "uksort" : "usort";
    return $function($versions, function ($a, $b) {
      $b = preg_replace("/.*-/", "", $b);
      $a = preg_replace("/.*-/", "", $a);
      return version_compare($b, $a);
    });
  }

  /**
   * Finds the release version for given major key.
   *
   * @param string $major_key
   *   The major key to search.
   * @param int $stability_flag
   *   Given stability flag.
   */
  protected function findReleaseInMajor(string $major_key, int $stability_flag): ?array {
    $releases = $this->getAllReleases();
    $major_releases = $releases[$major_key] ?? [];
    foreach ($major_releases as $version => $minor_releases) {
      $release = $this->findReleaseInMinor($version, $stability_flag);
      if ($release) {
        return $release;
      }
    }
    return NULL;
  }

  /**
   * Finds the release version for given minor key.
   *
   * @param string $version
   *   Given release version.
   * @param int $stability_flag
   *   Given stability flag.
   */
  protected function findReleaseInMinor(string $version, int $stability_flag): ?array {
    $is_legacy = substr($version, 0, 4) === "8.x-";
    if ($is_legacy) {
      $version = str_replace("8.x-", "", $version);
    }
    $version_array = explode('.', $version);
    $version_array[1] = $is_legacy && isset($version_array[1]) ? NULL : $version_array[1] ?? NULL;

    [$major, $minor] = [$version_array[0], $version_array[1] ?? NULL];

    // If minor is empty, then release should be probably legacy 8.x release.
    $majorMinorVersion = (!is_null($minor)) ? "$major.$minor" : "8.x-$major";

    $releases = $this->getAllReleases();
    $minor_releases = $releases["$major.x"][$majorMinorVersion] ?? [];
    $isSelectedRelease = FALSE;
    foreach ($minor_releases as $current_version => $releaseDetails) {
      switch ($stability_flag) {
        case StabilityEnum::STABLE:
          $isSelectedRelease = $this->isStable($current_version);
          break;

        case StabilityEnum::ANY_STABLE:
          $isSelectedRelease = $this->isPreferredStable($current_version);
          break;

        case StabilityEnum::DEV:
        case StabilityEnum::ANY_DEV:
          $isSelectedRelease = $this->isDev($current_version);
          break;
      }
      if ($isSelectedRelease) {
        return $releaseDetails;
      }
    }
    if ($stability_flag == StabilityEnum::ANY_DEV) {
      $releaseDetails = $releases["$major.x"]["$major.x-dev"] ?? [];
      return $releaseDetails["$major.x-dev"] ?? NULL;
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrent(): ?string {
    $version = $this->resolvedReleases[CoreVersionResolverEnum::CURRENT] ?? NULL;
    if (!$version) {
      $releases = $this->getFilteredReleases();
      foreach ($releases as $version => $major_releases) {
        $version = $this->findReleaseInMajor($version, StabilityEnum::ANY_STABLE);
        if ($version) {
          break;
        }
      }
    }
    $this->resolvedReleases[CoreVersionResolverEnum::CURRENT] = $version['version'] ?? NULL;
    return $this->resolvedReleases[CoreVersionResolverEnum::CURRENT];
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrentDev(): ?string {
    $current_dev = $this->resolvedReleases[CoreVersionResolverEnum::CURRENT_DEV] ?? NULL;
    if (!$current_dev) {
      $current = $this->resolvedReleases[CoreVersionResolverEnum::CURRENT] ?? $this->getCurrent();
      $current_dev = $current ? $this->findReleaseInMinor($current, StabilityEnum::ANY_DEV) : NULL;
    }
    $this->resolvedReleases[CoreVersionResolverEnum::CURRENT_DEV] = $current_dev['version'] ?? NULL;
    return $this->resolvedReleases[CoreVersionResolverEnum::CURRENT_DEV];
  }

  /**
   * {@inheritdoc}
   */
  public function getSupportedReleases(): array {
    $all_supported_releases = [];
    $this->getFilteredReleases();
    foreach ($this->supportedReleases['supported'] as $release) {
      $all_supported_releases[$release . "x"]['stable'] = $this->findReleaseInMinor($release, StabilityEnum::ANY_STABLE);
      $all_supported_releases[$release . "x"]['dev'] = $this->findReleaseInMinor($release, StabilityEnum::ANY_DEV);
    }
    return $all_supported_releases;
  }

  /**
   * Returns all releases.
   */
  public function getAllReleases(): array {
    if (is_null($this->releases)) {
      $this->load();
    }
    return $this->releases;
  }

  /**
   * Returns all filtered releases.
   */
  protected function getFilteredReleases(): array {
    if (is_null($this->filteredReleases)) {
      $this->load();
    }
    return $this->filteredReleases;
  }

}
