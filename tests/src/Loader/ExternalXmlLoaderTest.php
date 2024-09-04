<?php

declare(strict_types=1);

namespace DrupalTool\Resolver\Tests\Loader;

use DrupalTool\Resolver\Loader\ExternalXmlLoader;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\RejectionException;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class ExternalXmlLoaderTest extends TestCase {

  /**
   * Test loading XML data with a valid response.
   */
  public function testLoadWithValidResponse(): void {
    $xml_content = <<<XML
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

    // Step 1: Create a MockHandler with predefined responses.
    $mock = new MockHandler([
      new Response(200, ['Content-Type' => 'text/xml'], $xml_content),
    ]);

    // Step 2: Use the MockHandler to create a HandlerStack.
    $handlerStack = HandlerStack::create($mock);

    // Step 3: Create a Guzzle client using the HandlerStack.
    $client = new Client(['handler' => $handlerStack]);

    $loader = new ExternalXmlLoader($client);

    $path = 'https://updates.drupal.org/release-history';
    $result = $loader->load($path);
    $this->assertSame([
      "title" => "Test Module",
      "short_name" => "test",
      "releases" => [
        "release" => [
          "version" => "1.0.0",
        ],
      ],
    ], $result);
  }

  public function testLoadWithInvalidFileType(): void {
    $this->expectException(RejectionException::class);

    $path = 'ftp://updates.drupal.org/release-history';
    $this->expectExceptionMessage("The resource of given path '$path' is not supported.");

    $xml_content = "";
    // Step 1: Create a MockHandler with predefined responses.
    $mock = new MockHandler([
      new Response(200, ['Content-Type' => 'text/xml'], $xml_content),
    ]);

    // Step 2: Use the MockHandler to create a HandlerStack.
    $handlerStack = HandlerStack::create($mock);

    // Step 3: Create a Guzzle client using the HandlerStack.
    $client = new Client(['handler' => $handlerStack]);

    $loader = new ExternalXmlLoader($client);
    $loader->load($path);
  }

  public function testLoadWithInvalidStatusCode(): void {
    $this->expectException(ClientException::class);

    $path = 'https://updates.drupal.org/release-history';
    $this->expectExceptionMessage("Client error: `GET $path` resulted in a `403 Forbidden`");

    $xml_content = "";
    // Step 1: Create a MockHandler with predefined responses.
    $mock = new MockHandler([
      new Response(403, ['Content-Type' => 'text/xml'], $xml_content),
    ]);

    // Step 2: Use the MockHandler to create a HandlerStack.
    $handlerStack = HandlerStack::create($mock);

    // Step 3: Create a Guzzle client using the HandlerStack.
    $client = new Client(['handler' => $handlerStack]);
    $loader = new ExternalXmlLoader($client);
    $loader->load($path);
  }

  public function testLoadWithInvalidDataType(): void {
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage("No release history was found for the requested project (test).");

    $xml_content = "<error>No release history was found for the requested project (test).</error>";
    $path = 'https://updates.drupal.org/release-history';

    // Step 1: Create a MockHandler with predefined responses.
    $mock = new MockHandler([
      new Response(200, ['Content-Type' => 'text/xml'], $xml_content),
    ]);

    // Step 2: Use the MockHandler to create a HandlerStack.
    $handlerStack = HandlerStack::create($mock);

    // Step 3: Create a Guzzle client using the HandlerStack.
    $client = new Client(['handler' => $handlerStack]);
    $loader = new ExternalXmlLoader($client);
    $loader->load($path);
  }

  public function testLoadWithInValidData(): void {
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage("Premature end of data in tag root line 1.");

    $xml_content = <<<XML
<root>
  <item>value</item>
XML;

    // Step 1: Create a MockHandler with predefined responses.
    $mock = new MockHandler([
      new Response(200, ['Content-Type' => 'text/xml'], $xml_content),
    ]);

    // Step 2: Use the MockHandler to create a HandlerStack.
    $handlerStack = HandlerStack::create($mock);

    // Step 3: Create a Guzzle client using the HandlerStack.
    $client = new Client(['handler' => $handlerStack]);
    $loader = new ExternalXmlLoader($client);

    $path = 'https://updates.drupal.org/release-history';
    $loader->load($path);

  }

}
