# Changelog

## [Unreleased](https://github.com/duonrun/boiler/compare/0.2.0...HEAD)

### Breaking

- `Engine`, `Template`, and `TemplateContext` are now final.
- Renamed `Engine::getFile()` to `Engine::resolve()`.
- Renamed `Context::esc()` to `Context::escape()`, so templates now call `$this->escape()` instead of `$this->esc()`.
- Renamed `Context::context()` to `Context::get()`.
- Replaced the public escaping API's `htmlspecialchars()` flags and encoding arguments with named escapers.
- Renamed the `BaseTemplate` extension hook from `templateContext()` to `context()`.

### Added

- Added `Contract\Escaper`, `Contract\Escapers`, `Contract\RegistersEscapers`, `Contract\Filters`, `Contract\RegistersFilters`, `Contract\Wrapper`, and `Contract\Filter`.
- Added `Contract\Resolver` and `Resolver` for template lookup.
- Added `Context::wrap()` as an explicit way to opt into wrapper proxy behavior inside templates.
- Added `Escapers` as the default escaper registry implementation.
- Added lazy engine-owned wrapper composition with `setWrapper()`, `setFilters()`, and `setEscapers()`.
- Added `Engine::escape()` and `Engine::filter()` for registering custom escapers and filters on engine-managed registries.
- Added the built-in `strip` filter and the optional `sanitize` filter when `symfony/html-sanitizer` is installed.
- Added a Composer suggestion for `symfony/html-sanitizer` to enable the built-in `sanitize` filter.

### Changed

- Changed `Wrapper` from a static helper into an instance-based API that drives wrapping, unwrapping, escaping, and filter lookup.
- Changed `StringProxy` to dispatch registered filters as virtual methods.

### Removed

- Removed `Context::clean()` and the `Sanitizer` class; use `wrap($value)->sanitize()` / filter pipelines instead.
- Removed `Contract\Engine`, `Contract\Template`, and `Contract\MethodRegister`.
- Removed support for subclassing `Engine`, `Template`, and `TemplateContext`.

## [0.2.0](https://github.com/duonrun/boiler/releases/tag/0.2.0) (2026-03-25)

### Breaking

- Renamed `Context::raw()` to `Context::unwrap()`.
- Changed registered template methods to receive normal PHP values instead of wrapped proxies. In escaped renders, Boiler now wraps returned values again before exposing them to templates.
- Replaced the old `ValueProxy` wrapper with dedicated `StringProxy` and `ObjectProxy` types, and renamed `ProxyInterface` to `Proxy`.

### Added

- Added support for extending `Engine` and `Template` in custom integrations.
- Added `$this->unwrap($value)` so templates can recover original values from escaped proxies.

### Changed

- Improved template render hot-path performance.

### Fixed

- Fixed path traversal bypass for template names ending in `.php`.
- Fixed `ArrayProxy::offsetSet` dropping string keys when assigning via array syntax.
- Fixed `Sections::end()` consuming an unrelated output buffer when called without a matching `begin()`.
- Allowed resources in template context values without triggering unsupported type errors.
- Reset per-render template state so a `Template` instance can be reused safely across multiple renders.

## [0.1.2](https://github.com/duonrun/boiler/releases/tag/0.1.2) (2026-01-30)

### Added

- Added Composer post-install and post-update hooks to sync Duon development configuration.

### Changed

- Updated `symfony/html-sanitizer` requirement to `^8.0`.
- Simplified development and CI tooling by relying on `duon/dev` (updated to `^2.4`).
- Removed `minimum-stability` and `prefer-stable` from `composer.json`.

### Removed

- Removed MkDocs-based documentation tooling.

## [0.1.1](https://github.com/duonrun/boiler/releases/tag/0.1.1) (2026-01-26)

### Fixed

- Fixed `Engine` handling of `is_null` condition checks.

## [0.1.0](https://github.com/duonrun/boiler/releases/tag/0.1.0) (2026-01-25)

Initial version.

### Added

- Native PHP 8.5+ template engine (no custom template syntax)
- `Engine` API to render templates from one or more directories (including namespaced paths and override resolution)
- Global template context with support for default values
- Automatic escaping of strings and `Stringable` values, with per-engine and per-render escape controls
- Layouts (including stacked layouts) and inserts/partials
- Sections with default values and append/prepend capabilities
- HTML sanitization helper powered by `symfony/html-sanitizer`
- Support for custom template methods and optional whitelisting of trusted value classes
