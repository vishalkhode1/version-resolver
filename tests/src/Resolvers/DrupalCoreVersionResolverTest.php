<?php

declare(strict_types=1);

namespace DrupalTool\Resolver\Tests\Resolvers;

use DrupalTool\Resolver\CoreVersionResolver;

class DrupalCoreVersionResolverTest extends VersionResolverTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->mockXmlPath = $this->getFixtureDirectory() . "/fixtures/releases/core_release.xml";
    parent::setUp();
    $this->resolver = new CoreVersionResolver("drupal", $this->getReleaseLoader());
  }

  public function getSupportedReleases(): array {
    return [
      "11.0.x" => [
        "stable" => "11.0.1",
        "dev" => "11.0.x-dev",
      ],
      "10.4.x" => [
        "stable" => NULL,
        "dev" => "10.4.x-dev",
      ],
      "10.3.x" => [
        "stable" => "10.3.2",
        "dev" => "10.3.x-dev",
      ],
      "10.2.x" => [
        "stable" => "10.2.7",
        "dev" => "10.2.x-dev",
      ],
    ];
  }

  public function getReleaseTypes(): array {
    return [
      "getCurrent" => "11.0.1",
      "getCurrentDev" => "11.0.x-dev",
      "getNextMajor" => NULL,
      "getNextMajorDev" => NULL,
      "getNextMinor" => NULL,
      "getNextMinorDev" => NULL,
      "getPreviousMinor" => NULL,
      "getPreviousMinorDev" => NULL,
      "getPreviousMajor" => "10.3.2",
      "getPreviousMajorDev" => "10.4.x-dev",
      "getOldestSupported" => "10.2.7",
      "getOldestSupportedDev" => "10.2.x-dev",
      "getLatestEolMajor" => "9.5.11",
      "getLatestEolMajorDev" => "9.5.x-dev",
    ];
  }

  public function getAllReleases(): array {
    return [
      "11.x" => [
        "11.0" => [
          "11.0.1",
          "11.0.0",
          "11.0.0-rc1",
          "11.0.x-dev",
        ],
        "11.x-dev" => [
          "11.x-dev",
        ],
      ],
      "10.x" => [
        "10.4" => [
          "10.4.x-dev",
        ],
        "10.3" => [
          "10.3.2",
          "10.3.0",
          "10.3.x-dev",
        ],
        "10.2" => [
          "10.2.7",
          "10.2.6",
          "10.2.5",
          "10.2.x-dev",
        ],
        "10.1" => [
          "10.1.8",
          "10.1.x-dev",
        ],
        "10.0" => [
          "10.0.11",
          "10.0.10",
          "10.0.x-dev",
        ],
      ],
      "9.x" => [
        "9.5" => [
          "9.5.11",
          "9.5.x-dev",
        ],
      ],
    ];
  }

}
