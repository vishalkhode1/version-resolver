<?php

declare(strict_types=1);

namespace DrupalTool\Resolver\Filesystem;

/**
 * Class to manage file.
 */
class Filesystem implements FilesystemInterface {

  /**
   * {@inheritdoc}
   */
  public function fileExists(string $file_path): bool {
    return file_exists($file_path);
  }

  /**
   * {@inheritdoc}
   */
  public function getFileModificationTime($filePath) {
    return filemtime($filePath);
  }

  /**
   * {@inheritdoc}
   */
  public function setFileModificationTime(string $file_path, int $timestamp): bool {
    return touch($file_path, $timestamp);
  }

  /**
   * {@inheritdoc}
   */
  public function getTempFileName(string $base_directory, string $url): string {
    $tempFileName = md5($url);
    return $base_directory . DIRECTORY_SEPARATOR . $tempFileName;
  }

  /**
   * {@inheritdoc}
   */
  public function ensureDirectoryExists(string $file_path, int $permissions = 0777): void {
    // Extract the directory from the file path.
    $directoryPath = dirname($file_path);

    // Check if the directory already exists.
    if (!is_dir($directoryPath)) {
      // Try to create the directory.
      if (!$this->createDirectory($directoryPath, $permissions, TRUE)) {
        // If it fails, throw an exception.
        throw new \RuntimeException(sprintf('Directory "%s" could not be created', $directoryPath));
      }
    }
  }

  /**
   * Creates the given directory.
   *
   * @param string $directory_path
   *   Given directory path.
   * @param int $permissions
   *   Given permissions.
   */
  protected function createDirectory(string $directory_path, int $permissions): bool {
    return mkdir($directory_path, $permissions, TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function isLocal(string $path): bool {
    return '' !== $path && strpos($path, '://') === FALSE;
  }

}
