<?php

declare(strict_types=1);

namespace DrupalTool\Resolver\Release;

/**
 * Interface to manage drupal releases.
 */
interface ReleaseLoaderInterface {
  const BASE_URL = "https://updates.drupal.org/release-history";

  /**
   * Loads the release history of given package.
   */
  public function load(): array;

}
