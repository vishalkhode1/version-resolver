<?php

declare(strict_types=1);

namespace Drupify\Resolver\Filesystem;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

/**
 * Class to download external file.
 */
class FileDownloader implements FileDownloaderInterface {

  /**
   * Holds an object of http_client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  private $client;

  /**
   * Construct an object.
   *
   * @param \GuzzleHttp\ClientInterface|null $client
   *   Given http_client object or NULL.
   */
  public function __construct(?ClientInterface $client = NULL) {
    $this->client = $client ?? new Client();
  }

  /**
   * {@inheritdoc}
   */
  public function downloadFile(string $url, string $file_path): void {
    // Download the file and store it.
    $response = $this->client->get($url, ['stream' => TRUE]);
    $fp = fopen($file_path, 'wb');
    while (!$response->getBody()->eof()) {
      fwrite($fp, $response->getBody()->read(8192));
    }
    fclose($fp);
  }

  /**
   * {@inheritdoc}
   */
  public function getLastModifiedTime(string $url): ?int {
    $response = $this->client->head($url);
    $lastModifiedHeader = $response->getHeader('Last-Modified');
    if ($lastModifiedHeader) {
      return strtotime($lastModifiedHeader[0]);
    }
    return NULL;
  }

}
