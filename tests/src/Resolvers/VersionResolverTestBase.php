<?php

declare(strict_types=1);

namespace Drupify\Resolver\Tests\Resolvers;

use Drupify\Resolver\Filesystem\FileDownloader;
use Drupify\Resolver\Filesystem\Filesystem;
use Drupify\Resolver\Loader\CacheableXmlFileLoader;
use Drupify\Resolver\Loader\LoaderInterface;
use Drupify\Resolver\VersionResolver;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class VersionResolverTestBase extends TestCase {

  protected $resolver;

  protected $mockXmlPath;

  protected $remoteProjectPath;

  protected $fileSystem;

  public function __construct(?string $name = NULL, array $data = [], $dataName = '') {
    parent::__construct($name, $data, $dataName);
    $this->fileSystem = new Filesystem();
    $this->mockXmlPath = "";
    $this->remoteProjectPath = VersionResolver::BASE_URL . "/test/current";
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->assertNotEmpty($this->mockXmlPath, "Please provide mock XML file path.");
    $this->resolver = new VersionResolver("test", $this->getLoader());
  }

  protected function getFixtureDirectory(): string {
    return dirname(__DIR__, 2);
  }

  /**
   * Returns an object of loader.
   */
  protected function getLoader(): LoaderInterface {
    $now = date('Y-m-d H:i:s');

    $xml_content = file_get_contents($this->mockXmlPath);

    $mock = new MockHandler([
      new Response(200, ['Content-Type' => 'text/xml', 'Last-Modified' => [$now]], $xml_content),
      new Response(200, ['Content-Type' => 'text/xml', 'Last-Modified' => [$now]], $xml_content),
    ]);

    // Step 2: Use the MockHandler to create a HandlerStack.
    $handlerStack = HandlerStack::create($mock);

    // Step 3: Create a Guzzle client using the HandlerStack.
    $client = new Client(['handler' => $handlerStack]);
    $downloader = new FileDownloader($client);
    return new CacheableXmlFileLoader($downloader);
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

  protected function tearDown(): void {
    parent::tearDown();
    @unlink($this->fileSystem->getTempFileName(VERSION_RESOLVER_DEFAULT_DIRECTORY, $this->remoteProjectPath));
  }

}
