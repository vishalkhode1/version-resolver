<?php

declare(strict_types=1);

namespace DrupalTool\Resolver\Enum;

/**
 * Class to manage all Drupal Core version resolver.
 */
abstract class CoreVersionResolverEnum {

  const CURRENT = 'current';
  const CURRENT_DEV = 'current_dev';
  const NEXT_MAJOR = 'next_major';
  const NEXT_MAJOR_DEV = 'next_major_dev';
  const NEXT_MINOR = 'next_minor';
  const NEXT_MINOR_DEV = 'next_minor_dev';
  const PREVIOUS_MINOR = 'previous_minor';
  const PREVIOUS_MINOR_DEV = 'previous_minor_dev';
  const PREVIOUS_MAJOR = 'previous_major';
  const PREVIOUS_MAJOR_DEV = 'previous_major_dev';
  const OLDEST_SUPPORTED = 'oldest_supported';
  const OLDEST_SUPPORTED_DEV = 'oldest_supported_dev';
  const LATEST_EOL_MAJOR = 'latest_eol_major';
  const LATEST_EOL_MAJOR_DEV = 'latest_eol_major_dev';

  /**
   * Returns an array of all version to resolve.
   */
  public static function getAllResolver(): array {
    return [
      self::CURRENT,
      self::CURRENT_DEV,
      self::NEXT_MAJOR,
      self::NEXT_MAJOR_DEV,
      self::NEXT_MINOR,
      self::NEXT_MINOR_DEV,
      self::PREVIOUS_MINOR,
      self::PREVIOUS_MINOR_DEV,
      self::PREVIOUS_MAJOR,
      self::PREVIOUS_MAJOR_DEV,
      self::OLDEST_SUPPORTED,
      self::OLDEST_SUPPORTED_DEV,
      self::LATEST_EOL_MAJOR,
      self::LATEST_EOL_MAJOR_DEV,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function descriptions(): array {
    return [
      self::CURRENT => "Current Drupal core version",
      self::CURRENT_DEV => "Current Drupal core dev version",
      self::NEXT_MAJOR => "Next Major Drupal core version",
      self::NEXT_MAJOR_DEV => "Next Major dev Drupal core version",
      self::NEXT_MINOR => "Next Minor Drupal core version",
      self::NEXT_MINOR_DEV => "Next Minor dev Drupal core version",
      self::PREVIOUS_MINOR => "Previous Minor Drupal core version",
      self::PREVIOUS_MINOR_DEV => "Previous Minor dev Drupal core version",
      self::PREVIOUS_MAJOR => "Previous Major Drupal core version",
      self::PREVIOUS_MAJOR_DEV => "Previous Major dev Drupal core version",
      self::OLDEST_SUPPORTED => "Oldest Supported Drupal core version",
      self::OLDEST_SUPPORTED_DEV => "Oldest Supported dev Drupal core version",
      self::LATEST_EOL_MAJOR => "Latest End-of-Life Drupal core version",
      self::LATEST_EOL_MAJOR_DEV => "Latest End-of-Life dev Drupal core version",
    ];
  }

  /**
   * Gets the description for given version resolver.
   *
   * @param string $version_resolver_type
   *   Given version resolver type.
   */
  public static function getDescription(string $version_resolver_type): ?string {
    $all_descriptions = self::descriptions();
    return $all_descriptions[$version_resolver_type] ?? NULL;
  }

}
