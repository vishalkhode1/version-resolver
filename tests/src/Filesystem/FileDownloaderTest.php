<?php

declare(strict_types=1);

namespace DrupalTool\Resolver\Tests\Filesystem;

use DrupalTool\Resolver\Filesystem\FileDownloader;
use DrupalTool\Resolver\Filesystem\Filesystem;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class FileDownloaderTest extends TestCase {

  protected $filePath;

  protected $fileSystem;

  public function __construct(?string $name = NULL, array $data = [], $dataName = '') {
    parent::__construct($name, $data, $dataName);
    $this->fileSystem = new Filesystem();
  }

  protected function setUp(): void {
    parent::setUp();
    $this->filePath = tempnam(sys_get_temp_dir(), 'vs-test');
  }

  public function testNullModifiedTime(): void {
    $xml_content = file_get_contents($this->filePath);

    $mock = new MockHandler([
      new Response(200, ['Content-Type' => 'text/xml'], $xml_content),
      new Response(200, ['Content-Type' => 'text/xml'], $xml_content),
    ]);

    // Step 2: Use the MockHandler to create a HandlerStack.
    $handlerStack = HandlerStack::create($mock);

    // Step 3: Create a Guzzle client using the HandlerStack.
    $client = new Client(['handler' => $handlerStack]);
    $downloader = new FileDownloader($client);
    $this->assertNull($downloader->getLastModifiedTime($this->filePath));
  }

  protected function tearDown(): void {
    parent::tearDown();
    @unlink($this->filePath);
  }

}
