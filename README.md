[![CI](https://github.com/grasmash/yaml-expander/actions/workflows/php.yml/badge.svg)](https://github.com/vishalkhode1/version-resolver/actions/workflows/ci.yml) [![Coverage Status](https://coveralls.io/repos/github/vishalkhode1/version-resolver/badge.svg)](https://coveralls.io/github/vishalkhode1/version-resolver)

## Drupal Tool Version Resolver Library

The **Drupal Tool Version Resolver** Library is a PHP library designed to help you
easily resolve Drupal project versions, supported releases, and retrieve all releases hosted
on [Drupal.org](https://www.drupal.org/). Whether you need to fetch release information for Drupal core or
contributed modules/themes, this library offers a simple API to access such data.

### Features
- Fetch supported releases for a given Drupal module or theme.
- Get all available releases for any Drupal project.
- Resolve different Drupal core versions, including the current version, development versions, and next minor versions.

### Requirements
- **PHP 8.1** and above.
- **Composer** for installation.
- Internet access to query data from drupal.org.

### Installation
To install, you can run below command:

```shell
composer require drupify/version-resolver
```

### Example Usage
Here’s an example to retrieve the releases of a module hosted on Drupal.org:

```php
use Drupify\Resolver\VersionResolver;

// Initialize the resolver with the project name.
$resolver = new VersionResolver('token');

// Get supported releases for the project.
$supported = $resolver->getSupportedReleases();

// Output format example:
[
  "8.x-1.x" => [
    "stable" => [
      "name" => "token 8.x-1.15",
      "version" => "8.x-1.15",
      "tag" => "8.x-1.15",
      "core_compatibility" => "^9.2 || ^10 || ^11"
    ],
    "dev" => [
      "name" => "token 8.x-1.x-dev",
      "version" => "8.x-1.x-dev",
      "tag" => "8.x-1.x",
      "core_compatibility" => "^9.2 || ^10 || ^11"
    ]
  ]
]
```
#### Fetching All Releases
If you need to retrieve all releases (including older versions), you can do so with the following:

```php
$all = $resolver->getAllReleases();
```
**Note:** This method returns all releases, but it does not include projects compatible with Drupal Core 7.x and below.

#### Resolving Drupal Core Versions
The library also provides methods to resolve various types of Drupal core versions:

- **Current Stable**: Returns the current stable version (e.g., `10.3`).
- **Current Dev**: Returns the current development version (e.g., `10.3.x-dev`).
- **Next Minor**: Returns the next minor version (e.g., `11.0.0-rc1`).
- **Next Minor Dev**: Returns the next minor development version (e.g., `11.0.x-dev`) and so on.

Example usage:
```php
use Drupify\Resolver\CoreVersionResolver;

$resolver = new CoreVersionResolver();

$currentVersion = $resolver->findCurrent();
$currentDevVersion = $resolver->findCurrentDev();
$nextMinorVersion = $resolver->findNextMinor();
$nextMinorDevVersion = $resolver->findNextMinorDev();
```

Example Output
```php
echo $currentVersion; // 10.3
echo $currentDevVersion; // 10.3.x-dev
echo $nextMinorVersion; // 11.0.0-rc1
echo $nextMinorDevVersion; // 11.0.x-dev
```

### Contact
If you have any questions or issues, feel free to open a GitHub issue or contact the maintainers directly.