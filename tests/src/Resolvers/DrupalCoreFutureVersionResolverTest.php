<?php

declare(strict_types=1);

namespace Drupify\Resolver\Tests\Resolvers;

use Drupify\Resolver\CoreVersionResolver;
use Drupify\Resolver\VersionResolver;

class DrupalCoreFutureVersionResolverTest extends VersionResolverTestBase {

  public function __construct(?string $name = NULL, array $data = [], $dataName = '') {
    parent::__construct($name, $data, $dataName);
    $this->remoteProjectPath = VersionResolver::BASE_URL . "/drupal/current";
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->mockXmlPath = $this->getFixtureDirectory() . "/fixtures/releases/core_release_future.xml";
    parent::setUp();
    $this->resolver = new CoreVersionResolver("test", $this->getLoader());
  }

  public function getSupportedReleases(): array {
    return [
      "11.1.x" => [
        "stable" => "11.1.0-alpha1",
        "dev" => "11.x-dev",
      ],
      "11.0.x" => [
        "stable" => "11.0.1",
        "dev" => "11.0.x-dev",
      ],
      "10.4.x" => [
        "stable" => "10.4.1-rc1",
        "dev" => "10.4.x-dev",
      ],
      "10.3.x" => [
        "stable" => "10.3.2",
        "dev" => "10.3.x-dev",
      ],
    ];
  }

  public function getReleaseTypes(): array {
    return [
      "getCurrent" => "11.0.1",
      "getCurrentDev" => "11.0.x-dev",
      "getNextMajor" => NULL,
      "getNextMajorDev" => NULL,
      "getNextMinor" => "11.1.0-alpha1",
      "getNextMinorDev" => NULL,
      "getPreviousMinor" => NULL,
      "getPreviousMinorDev" => NULL,
      "getPreviousMajor" => "10.4.1-rc1",
      "getPreviousMajorDev" => "10.4.x-dev",
      "getOldestSupported" => "10.3.2",
      "getOldestSupportedDev" => "10.3.x-dev",
      "getLatestEolMajor" => "9.5.11",
      "getLatestEolMajorDev" => "9.5.x-dev",
    ];
  }

  public function getAllReleases(): array {
    return [
      "11.x" => [
        "11.1" => [
          "11.1.0-alpha1",
        ],
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
          "10.4.1-rc1",
          "10.4.1-beta3",
          "10.4.1-alpha1",
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
