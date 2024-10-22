<?php

declare(strict_types=1);

namespace Drupify\Resolver\Loader;

use Drupify\Resolver\Exception\FileNotFoundException;
use Drupify\Resolver\Filesystem\Filesystem;
use Drupify\Resolver\Filesystem\FilesystemInterface;
use Noodlehaus\Config;
use Noodlehaus\ConfigInterface;
use Noodlehaus\Parser\Xml;

define("VERSION_RESOLVER_DEFAULT_DIRECTORY", sys_get_temp_dir() . DIRECTORY_SEPARATOR . "version-resolver");

/**
 * Loads the file.
 */
class XmlFileLoader implements LoaderInterface {

  /**
   * Holds file system object.
   *
   * @var \Drupify\Resolver\Filesystem\Filesystem
   */
  protected $fileSystem;

  /**
   * Holds an instance of parser object.
   *
   * @var \Noodlehaus\Parser\ParserInterface
   */
  protected $parser;

  /**
   * Construct a file loader object.
   *
   * @param \Drupify\Resolver\Filesystem\Filesystem|null $file_system
   *   Given file_system object or NULL.
   */
  public function __construct(?FilesystemInterface $file_system = NULL) {
    $this->fileSystem = $file_system ?? new Filesystem();
    $this->parser = new Xml();
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupify\Resolver\Exception\FileNotFoundException
   */
  public function load(string $path): ?ConfigInterface {
    if ($this->validate($path)) {
      return new Config($path, $this->parser);
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupify\Resolver\Exception\FileNotFoundException
   * @throws \Exception
   */
  public function validate(string $path, $data = NULL): bool {
    if ($path == "") {
      throw new \Exception("The file path can not be left empty.");
    }

    if (!$this->supports($path)) {
      return FALSE;
    }

    if (!$this->fileSystem->fileExists($path)) {
      throw new FileNotFoundException(sprintf("The file doesn't exist at path '%s'.", $path));
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function supports(string $path): bool {
    return $this->fileSystem->isLocal($path);
  }

}
