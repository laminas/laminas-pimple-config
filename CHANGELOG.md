# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

Versions prior to 0.2.0 were released as the package "webimpress/laminas-pimple-config".

## 1.1.1 - 2019-05-01

### Added

- [zendframework/zend-pimple-config#11](https://github.com/zendframework/zend-pimple-config/pull/11) adds support for PHP 7.3.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.1.0 - 2018-04-11

### Added

- [zendframework/zend-pimple-config#6](https://github.com/zendframework/zend-pimple-config/pull/6) and
  [zendframework/zend-pimple-config#10](https://github.com/zendframework/zend-pimple-config/pull/10) add
  support for `shared` and `shared_by_default` configuration, allowing the
  ability to selectively alter whether or not a shared instance is returned by
  the container for a given service.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-pimple-config#10](https://github.com/zendframework/zend-pimple-config/pull/10) fixes
  factory configuration support to test that a factory class name is callable
  before allowing registration of the factory.

- [zendframework/zend-pimple-config#10](https://github.com/zendframework/zend-pimple-config/pull/10) fixes
  how aliases to shared services work; they should never return a cloned
  instance of the service.

## 1.0.0 - 2018-03-15

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- [zendframework/zend-pimple-config#3](https://github.com/zendframework/zend-pimple-config/pull/3)
  removes support for PHP versions prior to PHP 7.1.

### Fixed

- [zendframework/zend-pimple-config#7](https://github.com/zendframework/zend-pimple-config/pull/7) fixes how
  invokable configuration is processed, ensuring that if the key and value are not
  the same, an alias is created, aliasing the key to the class name.

## 0.2.0 - 2017-11-21

### Added

- Nothing.

### Changed

- The package name has changed to `laminas/laminas-pimple-config`.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.1.0 - 2017-09-27

Initial Release.

### Added

- Everything.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.
