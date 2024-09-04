<?php

declare(strict_types=1);

namespace DrupalTool\Resolver\Tests\Resolvers;

use DrupalTool\Resolver\CoreVersionResolver;

class DrupalCoreUpcomingVersionResolverTest extends VersionResolverTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->mockXmlPath = $this->getFixtureDirectory() . "/fixtures/releases/core_release_12.x.xml";
    parent::setUp();
    $this->resolver = new CoreVersionResolver("drupal", $this->getReleaseLoader());
  }

  public function getSupportedReleases(): array {
    return [
      "12.0.x" => [
        "stable" => "12.0.0-alpha1",
        "dev" => "12.0.x-dev",
      ],
      "11.4.x" => [
        "stable" => "11.4.0-rc1",
        "dev" => "11.4.x-dev",
      ],
      "11.3.x" => [
        "stable" => "11.3.2",
        "dev" => "11.3.x-dev",
      ],
    ];
  }

  public function getReleaseTypes(): array {
    return [
      "getCurrent" => "11.3.2",
      "getCurrentDev" => "11.3.x-dev",
      "getNextMajor" => "12.0.0-alpha1",
      "getNextMajorDev" => "12.0.x-dev",
      "getNextMinor" => "11.4.0-rc1",
      "getNextMinorDev" => "11.4.x-dev",
      "getPreviousMinor" => "11.2.1",
      "getPreviousMinorDev" => "11.2.x-dev",
      "getPreviousMajor" => "10.4.1",
      "getPreviousMajorDev" => "10.4.x-dev",
      "getOldestSupported" => "11.3.2",
      "getOldestSupportedDev" => "11.3.x-dev",
      "getLatestEolMajor" => "10.4.1",
      "getLatestEolMajorDev" => "10.4.x-dev",
    ];
  }

  public function getAllReleases(): array {
    return [
      "12.x" => [
        "12.0" => [
          "12.0.0-alpha1",
          "12.0.x-dev",
        ],
        "12.x-dev" => [
          "12.x-dev",
        ],
      ],
      "11.x" => [
        "11.4" => [
          "11.4.0-rc1",
          "11.4.x-dev",
        ],
        "11.3" => [
          "11.3.2",
          "11.3.1",
          "11.3.0",
          "11.3.x-dev",
        ],
        "11.2" => [
          "11.2.1",
          "11.2.0",
          "11.2.x-dev",
        ],
        "11.0" => [
          "11.0.x-dev",
        ],
        "11.x-dev" => [
          "11.x-dev",
        ],
      ],
      "10.x" => [
        "10.4" => [
          "10.4.1",
          "10.4.1-rc1",
          "10.4.1-beta3",
          "10.4.x-dev",
        ],
        "10.3" => [
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
        "10.x-dev" => [
          "10.x-dev",
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
