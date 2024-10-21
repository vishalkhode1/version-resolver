<?php

declare(strict_types=1);

namespace DrupalTool\Resolver\Filesystem;

/**
 * Interface to manage file system.
 */
interface FilesystemInterface {

  /**
   * Checks if a file exists.
   *
   * @param string $file_path
   *   The path of the file.
   */
  public function fileExists(string $file_path): bool;

  /**
   * Returns the last modification time of the file.
   *
   * @param string $filePath
   *   The file path.
   *
   * @return int|false
   *   The unix timestamp of last modification, or false if it doesn't exist.
   */
  public function getFileModificationTime($filePath);

  /**
   * Sets the last modification time for a file.
   *
   * @param string $file_path
   *   The file path.
   * @param int $timestamp
   *   The Unix timestamp to set.
   */
  public function setFileModificationTime(string $file_path, int $timestamp): bool;

  /**
   * Returns the temporary file name.
   *
   * @param string $base_directory
   *   Given base directory path.
   * @param string $url
   *   External file url.
   */
  public function getTempFileName(string $base_directory, string $url): string;

  /**
   * Ensure that directory for the given path exists. Create if it doesn't.
   *
   * @param string $file_path
   *   The full file path for which the directory should be ensured.
   * @param int $permissions
   *   The permissions to apply to the directories if created. Default is 0777.
   *
   * @throws \RuntimeException
   *   If the directory could not be created.
   */
  public function ensureDirectoryExists(string $file_path, int $permissions = 0777): void;

  /**
   * Check if given path is local or is external path.
   *
   * @param string $path
   *   Given path.
   */
  public function isLocal(string $path): bool;

}
