<?php

declare(strict_types=1);

namespace DrupalTool\Resolver\Tests\Loader;

use DrupalTool\Resolver\Exception\FileNotFoundException;
use DrupalTool\Resolver\Loader\XmlFileLoader;

/**
 * Loads the file.
 */
class XmlFileLoaderTest extends XmlLoaderTestBase {

  /**
   * {@inheritdoc}
   */
  public function getXmlContent(): string {
    return <<<XML
<project xmlns:dc="http://purl.org/dc/elements/1.1/">
  <title>Test Module</title>
  <short_name>test</short_name>
  <releases>
    <release>
      <version>1.0.0</version>
    </release>
  </releases>
</project>
XML;
  }

  public function testLoad(): void {
    $loader = new XmlFileLoader();

    // Check if file is local and exists.
    $this->assertTrue($loader->validate($this->xmlFilePath));

    $loader->load($this->xmlFilePath);
    $this->assertSame([
      "title" => "Test Module",
      "short_name" => "test",
      "releases" => [
        "release" => [
          "version" => "1.0.0",
        ],
      ],
    ], $loader->load($this->xmlFilePath)->all());
  }

  public function testNoConfigLoad(): void {
    $loader = new XmlFileLoader();

    $this->externalFilePath = "https://www.xyz.com";
    // Check if file is local and exists.
    $this->assertFalse($loader->validate($this->externalFilePath));

    $this->assertNull($loader->load($this->externalFilePath));
  }

  /**
   * @dataProvider validateMessageDataProvider
   */
  public function testValidate(string $file_path, ?bool $return = NULL, string $exception_class = "", string $message = ""): void {
    if ($exception_class) {
      $this->expectException($exception_class);
    }
    if ($message) {
      $this->expectExceptionMessage($message);
    }
    $loader = new XmlFileLoader();
    $is_valid = $loader->validate($file_path);
    if ($return !== NULL) {
      $this->assertSame($return, $is_valid);
    }
  }

  public function validateMessageDataProvider(): array {
    return [
      [
        "",
        NULL,
        \Exception::class,
        "The file path can not be left empty.",
      ],
      [
        sys_get_temp_dir() . "/random-file.xml",
        NULL,
        FileNotFoundException::class,
        "The file doesn't exist at path '" . sys_get_temp_dir() . "/random-file.xml'.",
      ],
      [
        "https://www.xyz.com",
        FALSE,
      ],
    ];
  }

}
