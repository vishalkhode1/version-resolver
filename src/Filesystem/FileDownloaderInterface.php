<?php

declare(strict_types=1);

namespace Drupify\Resolver\Filesystem;

/**
 * Interface to download files.
 */
interface FileDownloaderInterface {

  /**
   * Downloads the given file.
   *
   * @param string $url
   *   Given external file url.
   * @param string $file_path
   *   Given path to store file.
   */
  public function downloadFile(string $url, string $file_path): void;

  /**
   * Returns the last modified time of the given file.
   *
   * @param string $url
   *   Given external file url.
   *
   * @return int|null
   *   The Unix timestamp of the last modified time, or null if not found.
   */
  public function getLastModifiedTime(string $url): ?int;

}
