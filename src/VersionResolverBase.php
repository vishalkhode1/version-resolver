<?php

declare(strict_types=1);

namespace Drupify\Resolver;

use Drupify\Resolver\Enum\StabilityEnum;
use Drupify\Resolver\Loader\CacheableXmlFileLoader;
use Drupify\Resolver\Loader\LoaderInterface;
use Drupify\Resolver\Traits\ReleaseTrait;

/**
 * Resolve and manages Drupal core versions based on data from an XML resource.
 */
abstract class VersionResolverBase implements VersionResolverInterface {
  use ReleaseTrait;

  /**
   * The base url for release history.
   */
  const BASE_URL = "https://updates.drupal.org/release-history";

  /**
   * Instance of XmlLoader for loading XML data.
   *
   * @var \Drupify\Resolver\Loader\LoaderInterface
   */
  protected $loader;

  /**
   * Holds the given project name.
   *
   * @var string
   */
  private $project;

  /**
   * Holds an object of project data or null.
   *
   * @var \Noodlehaus\ConfigInterface|null
   */
  protected $config;

  /**
   * Stores all information regarding resolved releases.
   *
   * @var array
   */
  protected $resolvedReleases;

  /**
   * Construct an object.
   *
   * @param string $project_name
   *   Given project name.
   * @param \Drupify\Resolver\Loader\LoaderInterface|null $loader
   *   An object of release loader.
   */
  public function __construct(string $project_name, ?LoaderInterface $loader = NULL) {
    $this->loader = $loader ?? new CacheableXmlFileLoader();
    $this->project = $project_name;
    $this->config = NULL;
    $this->resolvedReleases = [];
  }

  /**
   * Loads the release history data from the XML resource and processes it.
   *
   * This method fetches all releases, filters supported releases,
   * and then groups and sorts them.
   *
   * @throws \Drupify\Resolver\Exception\FileNotFoundException
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  protected function load(): void {
    if ($this->config === NULL) {
      $this->config = $this->loader->load(static::BASE_URL . "/$this->project/current");

      $supported_branches = $this->config->get('supported_branches');
      $supported_branches = $supported_branches ? explode(",", $supported_branches) : [];
      $all_releases = $this->config->get("releases");
      // Normalize single release to array.
      $all_releases = isset($all_releases['release']['version'])
        ? [$all_releases['release']] : $all_releases['release'];

      $this->createDevBranchList($supported_branches);

      // Sort supported releases to ensure this are returned in correct order.
      $this->sortVersions($supported_branches, "value");

      $this->config->set('supported_branches', $supported_branches);

      // Process releases by filtering, grouping, and sorting them.
      $this
        ->groupReleases($all_releases)
        ->sortReleases($all_releases);
      $this->config->set("releases", $all_releases);

      $this->config->set("filtered_releases", $this->filterSupportedReleases($all_releases));
    }
  }

  /**
   * Builds an array or supported and supported-dev releases.
   *
   * @param array $supported_branches
   *   An array of supported branches.
   */
  protected function createDevBranchList(array $supported_branches): void {
    // $supported_releases = $this->config->get('supported_branches', []);
    // $supported = ["supported" => $supported_releases, "supported-dev" => []];
    $supportedDevBranches = [];
    foreach ($supported_branches as $supported_branch) {
      $supported_branch = str_replace("8.x-", "", $supported_branch);
      [$major, $minor] = explode('.', $supported_branch);
      $major_dev_release = "$major.x-dev";
      if ($minor !== "") {
        if (!in_array($major_dev_release, $supportedDevBranches)) {
          $supportedDevBranches[] = $major_dev_release;
        }
        $major_minor_dev_release = "$major.$minor.x-dev";
        if (!in_array($major_minor_dev_release, $supportedDevBranches)) {
          $supportedDevBranches[] = $major_minor_dev_release;
        }
      }
    }
    $this->config->set('supported_dev_branches', $supportedDevBranches);
  }

  /**
   * Filters the supported versions from the release history data.
   *
   * @param array $releases
   *   An array of all releases.
   *
   * @return \Drupify\Resolver\Resolver\VersionResolverInterface
   *   Returns the instance of VersionResolver for method chaining.
   */
  protected function filterSupportedReleases(array $releases): array {
    $filteredReleases = [];
    foreach ($this->config->get('supported_branches') as $supported) {
      $supported = str_replace("8.x-", "", $supported);
      [$major, $minor] = explode('.', $supported);
      $majorMinorVersion = ($minor !== "") ? "$major.$minor" : "8.x-$major";
      if (isset($releases["$major.x"][$majorMinorVersion])) {
        $filteredReleases["$major.x"][$majorMinorVersion] = $releases["$major.x"][$majorMinorVersion];
      }
    }
    foreach ($this->config->get('supported_dev_branches') as $supported) {
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
   * @return \Drupify\Resolver\Resolver\VersionResolverInterface
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
  public function getSupportedReleases(): array {
    $all_supported_releases = [];
    $this->load();
    foreach ($this->config->get('supported_branches') as $release) {
      $all_supported_releases[$release . "x"]['stable'] = $this->findReleaseInMinor($release, StabilityEnum::ANY_STABLE);
      $all_supported_releases[$release . "x"]['dev'] = $this->findReleaseInMinor($release, StabilityEnum::ANY_DEV);
    }
    return $all_supported_releases;
  }

  /**
   * Returns all releases.
   */
  public function getAllReleases(): array {
    if ($this->config === NULL) {
      $this->load();
    }
    return $this->config->get("releases");
  }

  /**
   * Returns all filtered releases.
   */
  protected function getFilteredReleases(): array {
    if ($this->config === NULL) {
      $this->load();
    }
    return $this->config->get("filtered_releases");
  }

}
