# Changelog

## [Unreleased](https://github.com/duonrun/boiler/compare/0.2.0...HEAD)

### Breaking

- Renamed the `Engine` and standalone `Template` API's `whitelist` argument to `trusted`. This breaks named argument calls such as `whitelist: [...]`, which must now use `trusted: [...]`.
- Renamed `Engine::registerMethod()` and standalone `Template::registerMethod()` to `method()`.
- Changed `Engine::__construct()` to accept a `Contract\Resolver` and `Contract\Environment` instead of template directories. Use `Engine::create()` or `Engine::unescaped()` when you want Boiler's built-in resolver.
- Changed `Engine::create()` and `Engine::unescaped()` to accept only directory input. Use the constructor when you need a custom resolver.
- Renamed `Engine::getFile()` to `Engine::resolve()`.
- Renamed `Context::esc()` to `Context::escape()` and `Context::context()` to `Context::get()`.
- Changed `Wrapper` from a static helper into an instance-based API and replaced the public escaping API's `htmlspecialchars()` flags and encoding arguments with named escapers.
- `symfony/html-sanitizer` is now optional. Install it explicitly when you want the built-in `sanitize` filter.
- Changed chained filter safety semantics. Safe output no longer stays safe through arbitrary later filters. Custom filters must implement `Contract\PreservesSafety` when they preserve already-safe HTML and should keep it unescaped.

### Added

- Added `Contract\Resolver` and `Resolver` for template lookup.
- Added `Contract\Environment` and `Environment` for advanced wrapper, filter, and escaper configuration.
- Added `Contract\Wrapper`, `Contract\Escaper`, `Contract\Escapers`, `Contract\Filter`, `Contract\Filters`, `Contract\RegistersEscapers`, and `Contract\RegistersFilters`, plus the default `Wrapper`, `Escapers`, and `Filters` implementations.
- Added `Contract\PreservesSafety` for filters that preserve already-safe HTML without claiming to sanitize arbitrary input.
- Added advanced wrapper configuration through `Environment::setWrapper()`, `Environment::setFilters()`, and `Environment::setEscapers()`.
- Added `Engine::filter()` and `Engine::escape()` for registering custom filters and escapers. Wrapped strings can call registered filters as virtual methods.
- Added `Context::wrap()` so templates can opt into wrapper proxy behavior for raw values.
- Added the built-in `strip` filter and the optional `sanitize` filter when `symfony/html-sanitizer` is installed.

### Fixed

- Made engine-registered custom methods available in inserted templates and layouts.
- Hardened template path resolution to reject traversal outside configured roots, including sibling directories with shared prefixes.
- Throw a render error for unclosed section capture blocks.
- Unwrap wrapped proxy arguments before invoking object methods, setters, or `__invoke()`.

### Removed

- Removed `Context::clean()` and the `Sanitizer` class; use `wrap($value)->sanitize()` or other filter pipelines instead.
- Removed `Contract\Engine`, `Contract\Template`, and `Contract\MethodRegister`.
- Removed support for subclassing `Engine`, `Template`, and `TemplateContext`; these classes are now final.

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
- Support for custom template methods and optional trusted classes
