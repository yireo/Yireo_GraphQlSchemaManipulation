# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.0.3] - 25 November 2026
### Added
- Schema config manipulators (`SchemaConfigManipulatorInterface`) that receive the GraphQL schema config keyed by type name, so fields, arguments and enum values can be removed from one specific type; removing from an interface also removes it from the implementing types
- Field manipulators now also apply to interface fields, which were skipped before
- Support for PHP 8.4 and 8.5
- README documentation of schema config manipulators and their limits

### Changed
- The field-level `ManipulationInterface` is deprecated in favour of schema config manipulators
- Manipulator output is now validated, giving a clear error when a manipulator returns invalid data

### Removed
- Support for PHP 8.1
