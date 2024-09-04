<?php

declare(strict_types=1);

namespace DrupalTool\Resolver\Tests\Resolvers;

use DrupalTool\Resolver\Loader\ExternalXmlLoader;
use DrupalTool\Resolver\Loader\LoaderInterface;
use DrupalTool\Resolver\Release\ReleaseLoader;
use DrupalTool\Resolver\Release\ReleaseLoaderInterface;
use DrupalTool\Resolver\VersionResolver;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class VersionResolverTestBase extends TestCase {

  protected $resolver;

  protected $mockXmlPath = "";

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->assertNotEmpty($this->mockXmlPath, "Please provide mock XML file path.");
    $this->resolver = new VersionResolver("test", $this->getReleaseLoader());
  }

  protected function getFixtureDirectory(): string {
    return dirname(__DIR__, 2);
  }

  protected function getReleaseLoader(): ReleaseLoaderInterface {
    return new ReleaseLoader($this->getLoader(), "test");
  }

  protected function getLoader(): LoaderInterface {

    $path = $this->mockXmlPath;
    $this->assertFileExists($this->mockXmlPath);
    $xml_content = file_get_contents($path);
    // Step 1: Create a MockHandler with predefined responses.
    $mock = new MockHandler([
      new Response(200, ['Content-Type' => 'text/xml'], $xml_content),
    ]);

    // Step 2: Use the MockHandler to create a HandlerStack.
    $handlerStack = HandlerStack::create($mock);

    // Step 3: Create a Guzzle client using the HandlerStack.
    $client = new Client(['handler' => $handlerStack]);
    return new ExternalXmlLoader($client);
  }

  public function testGetSupportedReleases(): void {
    $actual_releases = $this->resolver->getSupportedReleases();

    $expected_releases = $this->getSupportedReleases();

    $this->assertSame(array_keys($expected_releases), array_keys($actual_releases));

    foreach ($expected_releases as $version => $item) {
      if (is_null($item['stable'])) {
        $this->assertNull($actual_releases[$version]['stable']);
      }
      else {
        $this->assertSame($item['stable'], $actual_releases[$version]['stable']['version']);
      }

      if (is_null($item['dev'])) {
        $this->assertNull($actual_releases[$version]['dev']);
      }
      else {
        $this->assertSame($item['dev'], $actual_releases[$version]['dev']['version']);
      }
    }
  }

  public function testGetReleaseType(): void {
    $release_types = $this->getReleaseTypes();
    foreach ($release_types as $method => $version) {
      $this->assertTrue(method_exists($this->resolver, $method), "Method '" . get_class($this->resolver) . "::$method' not exist.");
      $this->assertSame($version, $this->resolver->$method());
    }
  }

  public function testGetAllReleases(): void {
    $actual_releases = $this->resolver->getAllReleases();

    $this->assertNotEmpty($actual_releases);

    $major_release_types = array_keys($actual_releases);
    $this->assertNotEmpty($major_release_types);

    $expected_releases = $this->getAllReleases();

    foreach ($actual_releases as $actual_major_release => $actual_major_releases) {
      $this->assertArrayHasKey($actual_major_release, $expected_releases, "Expected '$actual_major_release' major release.");
      foreach ($actual_major_releases as $actual_minor_release => $actual_minor_releases) {
        $this->assertArrayHasKey($actual_minor_release, $expected_releases[$actual_major_release], "Expected '$actual_major_release' major release.");
        $this->assertSame(array_keys($actual_minor_releases), $expected_releases[$actual_major_release][$actual_minor_release]);
      }
    }
  }

}
