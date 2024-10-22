<?php

declare(strict_types=1);

namespace Drupify\Resolver\Tests\Resolvers;

class LegacyVersionResolverTest extends VersionResolverTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->mockXmlPath = $this->getFixtureDirectory() . "/fixtures/releases/legacy_module.xml";
    parent::setUp();
  }

  protected function getSupportedReleases(): array {
    return [
      "8.x-2.x" => [
        "stable" => "8.x-2.6",
        "dev" => "8.x-2.x-dev",
      ],
      "8.x-1.x" => [
        "stable" => "8.x-1.12",
        "dev" => "8.x-1.x-dev",
      ],
    ];
  }

  protected function getReleaseTypes(): array {
    return [
      "getCurrent" => "8.x-2.6",
      "getCurrentDev" => "8.x-2.x-dev",
    ];
  }

  protected function getAllReleases(): array {
    return [
      "2.x" => [
        "8.x-2" => [
          "8.x-2.6",
          "8.x-2.5",
          "8.x-2.4",
          "8.x-2.0-rc1",
          "8.x-2.x-dev",
        ],
      ],
      "1.x" => [
        "8.x-1" => [
          "8.x-1.12",
          "8.x-1.11",
          "8.x-1.0-beta3",
          "8.x-1.x-dev",
        ],
      ],
    ];
  }

}
