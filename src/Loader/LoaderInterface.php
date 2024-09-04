<?php

declare(strict_types=1);

namespace DrupalTool\Resolver\Loader;

/**
 * Interface to manage files.
 */
interface LoaderInterface {

  /**
   * Loads the given file.
   *
   * @param string $path
   *   The path/url for data to load.
   */
  public function load(string $path): array;

  /**
   * Validates the data for given path/url.
   *
   * @param string $path
   *   The path/url for data to load.
   * @param mixed $data
   *   The data to validate.
   */
  public function validate(string $path, $data = NULL): bool;

  /**
   * Decides which file/path type it supports.
   *
   * @param string $path
   *   The path/url for data to load.
   */
  public function supports(string $path): bool;

}
