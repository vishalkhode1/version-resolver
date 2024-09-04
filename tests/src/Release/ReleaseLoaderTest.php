<?php

declare(strict_types=1);

namespace DrupalTool\Resolver\Tests\Release;

use DrupalTool\Resolver\Loader\ExternalXmlLoader;
use DrupalTool\Resolver\Release\ReleaseLoader;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class ReleaseLoaderTest extends TestCase {

  public function testLoad(): void {
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
    $release_loader = new ReleaseLoader($loader, "drupal");
    $this->assertSame([
      "title" => "Test Module",
      "short_name" => "test",
      "releases" => [
        "release" => [
          "version" => "1.0.0",
        ],
      ],
      "supported_branches" => [],
    ], $release_loader->load());
  }

}
