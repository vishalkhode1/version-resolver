<?php

declare(strict_types=1);

namespace Drupify\Resolver\Tests\Resolvers;

class UnsupportedVersionResolverTest extends VersionResolverTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->mockXmlPath = $this->getFixtureDirectory() . "/fixtures/releases/unsupported_module.xml";
    parent::setUp();
    // $this->resolver = new VersionResolver("test", $this->getLoader());
  }

  protected function getSupportedReleases(): array {
    return [];
  }

  protected function getReleaseTypes(): array {
    return [
      "getCurrent" => NULL,
      "getCurrentDev" => NULL,
    ];
  }

  protected function getAllReleases(): array {
    return [
      "1.x" => [
        "8.x-1" => [
          "8.x-1.x-dev",
        ],
      ],
    ];
  }

}
