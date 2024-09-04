<?php

declare(strict_types=1);

namespace DrupalTool\Resolver\Tests\Resolvers;

class MultipleVersionResolverTest extends VersionResolverTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->mockXmlPath = $this->getFixtureDirectory() . "/fixtures/releases/new_legacy_module.xml";
    parent::setUp();
  }

  protected function getSupportedReleases(): array {
    return [
      "2.1.x" => [
        "stable" => "2.1.0-alpha1",
        "dev" => "2.x-dev",
      ],
      "2.0.x" => [
        "stable" => "2.0.4",
        "dev" => "2.x-dev",
      ],
      "8.x-1.x" => [
        "stable" => "8.x-1.4-beta1",
        "dev" => "8.x-1.x-dev",
      ],
    ];
  }

  protected function getReleaseTypes(): array {
    return [
      "getCurrent" => "2.1.0-alpha1",
      "getCurrentDev" => "2.x-dev",
    ];
  }

  protected function getAllReleases(): array {
    return [
      "2.x" => [
        "2.1" => [
          "2.1.0-alpha1",
        ],
        "2.0" => [
          "2.0.4",
          "2.0.3",
        ],
        "2.x-dev" => [
          "2.x-dev",
        ],
      ],
      "1.x" => [
        "1.9" => [
          "1.9.3",
          "1.9.2",
          "1.9.x-dev",
        ],
        "8.x-1" => [
          "8.x-1.4-beta1",
          "8.x-1.x-dev",
        ],
      ],
    ];
  }

}
