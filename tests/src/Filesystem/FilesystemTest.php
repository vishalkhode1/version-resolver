<?php

declare(strict_types=1);

namespace DrupalTool\Resolver\Tests\Filesystem;

use DrupalTool\Resolver\Filesystem\Filesystem;
use PHPUnit\Framework\TestCase;

class FilesystemTest extends TestCase {

  protected $filePath;

  protected $fileSystem;

  public function __construct(?string $name = NULL, array $data = [], $dataName = '') {
    parent::__construct($name, $data, $dataName);
    $this->fileSystem = new Filesystem();
  }

  protected function setUp(): void {
    parent::setUp();
    $this->filePath = tempnam(sys_get_temp_dir(), 'vs-test');
  }

  public function testFileExists(): void {
    $this->assertFalse($this->fileSystem->fileExists("/no-file-exist"));
    file_put_contents($this->filePath, "test-data");
    $this->assertTrue($this->fileSystem->fileExists($this->filePath));
  }

  public function testFileModificationTime(): void {
    $now = time();
    $this->fileSystem->setFileModificationTime($this->filePath, $now);
    file_put_contents($this->filePath, "test-data");
    $actual = $this->fileSystem->getFileModificationTime($this->filePath);
    $this->assertSame($now, $actual);
  }

  public function testEnsureDirectoryExists(): void {
    // Create a mock of Filesystem class.
    $directoryHandler = $this->getMockBuilder(Filesystem::class)
      ->onlyMethods(['createDirectory'])
      ->getMock();

    // Simulate failure of mkdir by making createDirectory return false.
    $directoryHandler->method('createDirectory')->willReturn(FALSE);

    // Set the file path to a dummy path.
    $file_path = '/path/to/dummy/file.txt';

    // Expect the RuntimeException to be thrown.
    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage(sprintf('Directory "%s" could not be created', dirname($file_path)));

    $directoryHandler->ensureDirectoryExists($file_path);
  }

  public function testCreateDirectory(): void {
    // Set up the mock for Filesystem.
    $directoryHandler = $this->getMockBuilder(Filesystem::class)
    // Only mock createDirectory.
      ->onlyMethods(['createDirectory'])
      ->getMock();

    $this->filePath = sys_get_temp_dir() . "/test";

    // Use Reflection to access the protected method.
    $reflectionMethod = new \ReflectionMethod(Filesystem::class, 'createDirectory');
    $reflectionMethod->setAccessible(TRUE);

    // Simulate successful directory creation.
    $this->assertTrue($reflectionMethod->invoke($directoryHandler, $this->filePath, 0777));
  }

  protected function tearDown(): void {
    parent::tearDown();
    @unlink($this->filePath);
    @rmdir($this->filePath);
  }

}
