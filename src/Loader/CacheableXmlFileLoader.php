<?php

declare(strict_types=1);

namespace DrupalTool\Resolver\Loader;

use DrupalTool\Resolver\Filesystem\FileDownloader;
use DrupalTool\Resolver\Filesystem\FileDownloaderInterface;
use DrupalTool\Resolver\Filesystem\FilesystemInterface;
use Noodlehaus\ConfigInterface;

/**
 * Class to load external xml file.
 */
class CacheableXmlFileLoader extends XmlFileLoader {


  /**
   * Holds file downloader object.
   *
   * @var \DrupalTool\Resolver\Filesystem\FileDownloader
   */
  protected $downloader;

  /**
   * {@inheritdoc}
   */
  public function __construct(?FileDownloaderInterface $downloader = NULL, ?FilesystemInterface $file_system = NULL) {
    parent::__construct($file_system);
    $this->downloader = $downloader ?? new FileDownloader();
  }

  /**
   * {@inheritdoc}
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function load(string $path): ConfigInterface {
    // If the path is remote, download the file first.
    if (!$this->supports($path)) {
      $tempFile = $this->fileSystem->getTempFileName(VERSION_RESOLVER_DEFAULT_DIRECTORY, $path);
      $this->fileSystem->ensureDirectoryExists($tempFile);
      if ($this->fileSystem->fileExists($tempFile)) {
        // Check if the file is up-to-date.
        $existingFileMtime = $this->fileSystem->getFileModificationTime($tempFile);
        $lastModifiedTime = $this->downloader->getLastModifiedTime($path);
        if ($lastModifiedTime && $existingFileMtime == $lastModifiedTime) {
          // File is up-to-date, return the cached file.
          return parent::load($tempFile);
        }
      }

      // Download the file and store it.
      $this->downloader->downloadFile($path, $tempFile);

      // Set the file modification time to the Last-Modified header.
      $lastModifiedTime = $this->downloader->getLastModifiedTime($path);
      if ($lastModifiedTime) {
        $this->fileSystem->setFileModificationTime($tempFile, $lastModifiedTime);
      }
      return parent::load($tempFile);
    }
    return parent::load($path);
  }

}
