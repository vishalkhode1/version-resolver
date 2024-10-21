<?php

declare(strict_types=1);

namespace DrupalTool\Resolver\Tests\Loader;

use DrupalTool\Resolver\Filesystem\Filesystem;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

abstract class XmlLoaderTestBase extends TestCase {

  /**
   * The drupal.org release history base path.
   */
  const DRUPAL_RELEASE_HISTORY_PATH = 'https://updates.drupal.org/release-history';

  /**
   * Holds the file path.
   */
  protected string $xmlFilePath;

  protected $externalFilePath;

  protected $fileSystem;

  /**
   * {@inheritdoc}
   */
  public function __construct(?string $name = NULL, array $data = [], $dataName = '') {
    parent::__construct($name, $data, $dataName);
    $this->fileSystem = new Filesystem();
    $this->externalFilePath = "";
  }

  protected function setUp(): void {
    parent::setUp();
    $this->xmlFilePath = tempnam(sys_get_temp_dir(), 'vs-test');
    file_put_contents($this->xmlFilePath, $this->getXmlContent());
  }

  /**
   * Test loading XML data with a valid response.
   */
  protected function getClient(): Client {

    // Step 1: Create a MockHandler with predefined responses.
    $mock = new MockHandler([
      new Response(200, ['Content-Type' => 'text/xml'], $this->getXmlContent()),
    ]);

    // Step 2: Use the MockHandler to create a HandlerStack.
    $handlerStack = HandlerStack::create($mock);

    // Step 3: Create a Guzzle client using the HandlerStack.
    return new Client(['handler' => $handlerStack]);
  }

  /**
   * Returns the XML content.
   */
  abstract public function getXmlContent(): string;

  /**
   * {@inheritdoc}
   */
  protected function tearDown(): void {
    parent::tearDown();
    if ($this->xmlFilePath) {
      @unlink($this->xmlFilePath);
    }
    if ($this->externalFilePath) {
      @unlink($this->fileSystem->getTempFileName(VERSION_RESOLVER_DEFAULT_DIRECTORY, $this->externalFilePath));
    }
  }

}
