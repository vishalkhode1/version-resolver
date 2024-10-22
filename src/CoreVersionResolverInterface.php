<?php

declare(strict_types=1);

namespace Drupify\Resolver;

/**
 * Interface to resolve drupal core version releases.
 */
interface CoreVersionResolverInterface extends VersionResolverInterface {

  /**
   * Returns the Next Major of current drupal release.
   */
  public function getNextMajor(): ?string;

  /**
   * Returns the Next Major Dev of current drupal release.
   */
  public function getNextMajorDev(): ?string;

  /**
   * Returns the Next Minor of current drupal release.
   */
  public function getNextMinor(): ?string;

  /**
   * Returns the Next Minor dev of current drupal release.
   */
  public function getNextMinorDev(): ?string;

  /**
   * Returns the Previous Minor of current drupal release.
   */
  public function getPreviousMinor(): ?string;

  /**
   * Returns the Previous Minor dev of current drupal release.
   */
  public function getPreviousMinorDev(): ?string;

  /**
   * Returns the Previous Major of current drupal release.
   */
  public function getPreviousMajor(): ?string;

  /**
   * Returns the Next Major dev of current drupal release.
   */
  public function getPreviousMajorDev(): ?string;

  /**
   * Returns the Oldest Supported version of current drupal release.
   */
  public function getOldestSupported(): ?string;

  /**
   * Returns the Oldest Supported dev version of current drupal release.
   */
  public function getOldestSupportedDev(): ?string;

  /**
   * Returns the Latest End-of-Life version of drupal release.
   */
  public function getLatestEolMajor(): ?string;

  /**
   * Returns the Latest End-of-Life dev version of drupal release.
   */
  public function getLatestEolMajorDev(): ?string;

}
