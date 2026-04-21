# Changelog
The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and uses [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.4.0] - 2026-04-21
### Added
- Documentation: README and usage guide for developers
- Support for additional modified versions in compatibility list

### Changed
- Refactored module structure to use MMLC src-mmlc feature for better organization

### Fixed
- Fixed missing task property declaration
- Fixed missing processRow() base method
- Fixed code style issues (spacing after control structures)

## [1.3.0] - 2020-05-19
### Added
- PSR-4 autoload configuration in moduleinfo.json for namespace RobinTheHood\ModifiedCsvImporter

### Fixed
- Fixed syntax errors in CsvImporter class

## [1.2.0] - 2020-02-25
### Added
- Added preProcess() method hook for pre-import initialization
- Added deleteAllProductToCategorysFromProduct() method for cleanup
- Added deleteAllTagsFromProduct() method for cleanup
- Cached methods now use internal class variables as default parameters

### Changed
- Documentation: Added price and module category information

## [1.1.0] - 2019-04-05
### Added
- Added createOrGetProductVpeHashed() and createOrGetProductVpe() methods for VPE handling
- Automatic version management and modified compatibility declarations

### Fixed
- Removed warning about missing parameter

## [1.0.0] - 2018-11-12
### Added
- Initial release for Modified Module Loader Client (MMLC)
- CsvImporter base class with delimiter and encoding configuration
- Task-based progress logging system
- Helper methods for category, shipping status, manufacturer, and product tag handling
- Support for cached lookups to improve import performance

[Unreleased]: https://github.com/RobinTheHood/modified-csv-importer/compare/1.4.0...HEAD
[1.4.0]: https://github.com/RobinTheHood/modified-csv-importer/compare/1.3.0...1.4.0
[1.3.0]: https://github.com/RobinTheHood/modified-csv-importer/compare/1.2.0...1.3.0
[1.2.0]: https://github.com/RobinTheHood/modified-csv-importer/compare/1.1.0...1.2.0
[1.1.0]: https://github.com/RobinTheHood/modified-csv-importer/compare/1.0.0...1.1.0
[1.0.0]: https://github.com/RobinTheHood/modified-csv-importer/releases/tag/1.0.0
