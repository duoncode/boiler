# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.1.0] - 2026-01-25

Initial version.

### Added

- Native PHP 8.5+ template engine (no custom template syntax)
- `Engine` API to render templates from one or more directories (including
  namespaced paths and override resolution)
- Global template context with support for default values
- Automatic escaping of strings and `Stringable` values, with per-engine and
  per-render escape controls
- Layouts (including stacked layouts) and inserts/partials
- Sections with default values and append/prepend capabilities
- HTML sanitization helper powered by `symfony/html-sanitizer`
- Support for custom template methods and optional whitelisting of trusted value
  classes
