<?php

declare(strict_types=1);

namespace Drupify\Resolver\Tests\Loader;

use Drupify\Resolver\Filesystem\FileDownloader;
use Drupify\Resolver\Loader\CacheableXmlFileLoader;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

/**
 * Loads the file.
 */
class CacheableXmlFileLoaderTest extends XmlLoaderTestBase {

  /**
   * {@inheritdoc}
   */
  public function getXmlContent(): string {
    return <<<XML
<project xmlns:dc="http://purl.org/dc/elements/1.1/">
  <title>Test Module</title>
  <short_name>test</short_name>
  <releases>
    <release>
      <version>1.0.0</version>
    </release>
  </releases>
</project>
XML;
  }

  public function testLocalFileLoad(): void {
    $loader = new CacheableXmlFileLoader();

    // Check if file is local and exists.
    $this->assertTrue($loader->validate($this->xmlFilePath));

    $loader->load($this->xmlFilePath);
    $this->assertSame([
      "title" => "Test Module",
      "short_name" => "test",
      "releases" => [
        "release" => [
          "version" => "1.0.0",
        ],
      ],
    ], $loader->load($this->xmlFilePath)->all());
  }

  public function testExternalFileLoad(): void {
    $now = date('Y-m-d H:i:s');
    // Step 1: Create a MockHandler with predefined responses.
    $mock = new MockHandler([
      new Response(200, ['Content-Type' => 'text/xml', 'Last-Modified' => [$now]], $this->getXmlContent()),
      new Response(200, ['Content-Type' => 'text/xml', 'Last-Modified' => [$now]], $this->getXmlContent()),
    ]);

    // Step 2: Use the MockHandler to create a HandlerStack.
    $handlerStack = HandlerStack::create($mock);

    // Step 3: Create a Guzzle client using the HandlerStack.
    $client = new Client(['handler' => $handlerStack]);
    $downloader = new FileDownloader($client);
    $loader = new CacheableXmlFileLoader($downloader);
    $this->externalFilePath = "https://www.xyz.com";
    $this->assertSame([
      "title" => "Test Module",
      "short_name" => "test",
      "releases" => [
        "release" => [
          "version" => "1.0.0",
        ],
      ],
    ], $loader->load($this->externalFilePath)->all());
  }

  public function testCacheableLocalFile(): void {
    $now = date('Y-m-d H:i:s');
    $this->externalFilePath = "https://www.xyz.com";

    $mock = new MockHandler([
      new Response(200, ['Content-Type' => 'text/xml', 'Last-Modified' => [$now]], $this->getXmlContent()),
      new Response(200, ['Content-Type' => 'text/xml', 'Last-Modified' => [$now]], $this->getXmlContent()),
    ]);

    // Step 2: Use the MockHandler to create a HandlerStack.
    $handlerStack = HandlerStack::create($mock);

    // Step 3: Create a Guzzle client using the HandlerStack.
    $client = new Client(['handler' => $handlerStack]);
    $downloader = new FileDownloader($client);
    $loader = new CacheableXmlFileLoader($downloader);

    $file_name = $this->fileSystem->getTempFileName(VERSION_RESOLVER_DEFAULT_DIRECTORY, $this->externalFilePath);
    touch($file_name, strtotime($now));
    file_put_contents($file_name, $this->getXmlContent());

    $this->assertSame([
      "title" => "Test Module",
      "short_name" => "test",
      "releases" => [
        "release" => [
          "version" => "1.0.0",
        ],
      ],
    ], $loader->load($this->externalFilePath)->all());

  }

}
