<?php

declare(strict_types=1);

namespace DrupalTool\Resolver\Release;

use DrupalTool\Resolver\Loader\LoaderInterface;

/**
 * Class to load release history for given package.
 */
class ReleaseLoader implements ReleaseLoaderInterface {

  /**
   * Holds an object of release loader.
   *
   * @var \DrupalTool\Resolver\Loader\LoaderInterface
   */
  private $loader;

  /**
   * Given project name.
   *
   * @var string
   */
  private $project;

  /**
   * Construct an object.
   *
   * @param \DrupalTool\Resolver\Loader\LoaderInterface $loader
   *   Given release loader.
   * @param string $project_name
   *   Given project name.
   */
  public function __construct(LoaderInterface $loader, string $project_name) {
    $this->loader = $loader;
    $this->project = $project_name;
  }

  /**
   * {@inheritdoc}
   */
  public function load(): array {
    $current_data = $this->loader->load(static::BASE_URL . "/$this->project/current");
    $supported_branches = $current_data['supported_branches'] ?? "";
    $current_data['supported_branches'] = $supported_branches ? explode(",", $supported_branches) : [];
    return $current_data;
  }

}
