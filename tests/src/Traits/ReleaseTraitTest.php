<?php

declare(strict_types=1);

namespace DrupalTool\Resolver\Tests\Traits;

use DrupalTool\Resolver\Traits\ReleaseTrait;
use PHPUnit\Framework\TestCase;

class ReleaseTraitTest extends TestCase {
  use ReleaseTrait;

  public function testIsStable(): void {
    $this->assertTrue($this->isStable("10.3.0"));
    $this->assertFalse($this->isStable("10.3.0-alpha1"));
    $this->assertFalse($this->isStable("10.3.0-beta1"));
    $this->assertFalse($this->isStable("10.3.0-rc1"));
    $this->assertFalse($this->isStable("10.3.0-dev"));
    $this->assertFalse($this->isStable("10.3.0-dev"));
  }

  public function testIsPreferredStable(): void {
    $this->assertTrue($this->isPreferredStable("10.3.0"));
    $this->assertTrue($this->isPreferredStable("10.3.0-alpha1"));
    $this->assertTrue($this->isPreferredStable("10.3.0-beta1"));
    $this->assertTrue($this->isPreferredStable("10.3.0-rc1"));
    $this->assertFalse($this->isPreferredStable("10.3.0-dev"));
  }

  public function testIsDev(): void {
    $this->assertFalse($this->isDev("10.3.0"));
    $this->assertFalse($this->isDev("10.3.0-alpha1"));
    $this->assertFalse($this->isDev("10.3.0-beta1"));
    $this->assertFalse($this->isDev("10.3.0-rc1"));
    $this->assertTrue($this->isDev("8.x.-1.x-dev"));
  }

}
