<?php

declare(strict_types=1);

namespace DrupalTool\Resolver\Loader;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise\RejectionException;

/**
 * Loads the data from external path.
 */
class ExternalXmlLoader implements LoaderInterface {

  /**
   * Holds an object of http_client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  private $client;

  /**
   * An array of fetched XML data.
   *
   * @var \GuzzleHttp\Psr7\Response
   */
  protected $response;

  /**
   * Construct on object.
   *
   * @param \GuzzleHttp\ClientInterface|null $client
   *   Given http client object or null.
   */
  public function __construct(ClientInterface $client = NULL) {
    $this->client = $client ?? new Client();
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Exception
   */
  public function load(string $path): array {
    $this->validate($path);
    // Enable internal error handling.
    libxml_use_internal_errors(TRUE);
    try {
      $xml = simplexml_load_string($this->response->getBody()->getContents());
      $json = json_encode($xml);
      $data = json_decode($json, TRUE);
      if ($data === FALSE) {
        // Collect all errors.
        $errors = libxml_get_errors();
        $errorMessages = [];

        foreach ($errors as $error) {
          if ($error->message) {
            $errorMessages[] = trim(str_replace(PHP_EOL, ". ", $error->message));
          }
        }

        // Clear the error buffer.
        libxml_clear_errors();

        // Throw custom exception with detailed error messages.
        throw new \Exception(implode(",", $errorMessages));
      }

      elseif (!empty($data[0])) {
        throw new \Exception($data[0]);
      }
    }
    catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }

    // Restore the previous error handling behavior.
    libxml_use_internal_errors(FALSE);

    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function supports(string $path): bool {
    $is_supported = preg_match('/^https?:\/\/.*/', $path);
    if (!$is_supported) {
      throw new RejectionException(sprintf("The resource of given path '%s' is not supported.", $path));
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function validate(string $path, $data = NULL): bool {
    $this->supports($path);
    $this->response = $this->client->request('GET', $path);
    return TRUE;
  }

}
