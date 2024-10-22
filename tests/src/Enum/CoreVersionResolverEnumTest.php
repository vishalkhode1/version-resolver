<?php

declare(strict_types=1);

namespace Drupify\Resolver\Tests\Enum;

use Drupify\Resolver\Enum\CoreVersionResolverEnum;
use PHPUnit\Framework\TestCase;

class CoreVersionResolverEnumTest extends TestCase {

  public function testGetAllResolver(): void {
    $resolvers = CoreVersionResolverEnum::getAllResolver();
    $this->assertSame([
      'current',
      'current_dev',
      'next_major',
      'next_major_dev',
      'next_minor',
      'next_minor_dev',
      'previous_minor',
      'previous_minor_dev',
      'previous_major',
      'previous_major_dev',
      'oldest_supported',
      'oldest_supported_dev',
      'latest_eol_major',
      'latest_eol_major_dev',
    ], $resolvers);
  }

  public function testAllDescriptions(): void {
    $this->assertSame([
      'current' => "Current Drupal core version",
      'current_dev' => "Current Drupal core dev version",
      'next_major' => "Next Major Drupal core version",
      'next_major_dev' => "Next Major dev Drupal core version",
      'next_minor' => "Next Minor Drupal core version",
      'next_minor_dev' => "Next Minor dev Drupal core version",
      'previous_minor' => "Previous Minor Drupal core version",
      'previous_minor_dev' => "Previous Minor dev Drupal core version",
      'previous_major' => "Previous Major Drupal core version",
      'previous_major_dev' => "Previous Major dev Drupal core version",
      'oldest_supported' => "Oldest Supported Drupal core version",
      'oldest_supported_dev' => "Oldest Supported dev Drupal core version",
      'latest_eol_major' => "Latest End-of-Life Drupal core version",
      'latest_eol_major_dev' => "Latest End-of-Life dev Drupal core version",
    ], CoreVersionResolverEnum::descriptions());
  }

  public function testDescription(): void {
    $current_dev_desc = CoreVersionResolverEnum::getDescription('current');
    $this->assertSame("Current Drupal core version", $current_dev_desc);
  }

}
