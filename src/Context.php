<?php

declare(strict_types=1);

namespace Duon\Boiler;

use Duon\Boiler\Exception\RuntimeException;
use Duon\Boiler\Proxy\ObjectProxy;
use Duon\Boiler\Proxy\ProxyInterface;
use Duon\Boiler\Proxy\StringProxy;
use Stringable;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/** @api */
abstract class Context
{
	private const int ESCAPE_FLAGS = ENT_QUOTES | ENT_SUBSTITUTE;
	private const string ESCAPE_ENCODING = 'UTF-8';

	/**
	 * @psalm-param list<class-string> $whitelist
	 */
	public function __construct(
		protected readonly Contract\Template $template,
		protected array $context,
		public readonly array $whitelist,
		public readonly bool $autoescape,
	) {}

	public function __call(string $name, array $args): mixed
	{
		$callable = $this->template->getMethods()->get($name);

		/** @var array<array-key, mixed> $args */
		$args = $this->raw($args);

		return $this->templateValue($callable(...$args));
	}

	public function context(array $values = []): array
	{
		$merged = array_merge($this->context, $values);

		if (!$this->autoescape) {
			return $merged;
		}

		return $this->wrapAll($merged);
	}

	/**
	 * @psalm-param array<array-key, mixed> $values
	 * @psalm-return array<array-key, mixed>
	 */
	protected function wrapAll(array $values): array
	{
		/** @var array<array-key, mixed> */
		$wrapped = [];

		/** @var mixed $value */
		foreach ($values as $key => $value) {
			if ($value instanceof ProxyInterface) {
				$wrapped[$key] = $value;

				continue;
			}

			if (is_object($value)) {
				foreach ($this->whitelist as $whitelisted) {
					if (!$value instanceof $whitelisted) {
						continue;
					}

					$wrapped[$key] = $value;

					continue 2;
				}
			}

			/** @psalm-suppress MixedAssignment wrapper returns mixed by design */
			$wrapped[$key] = Wrapper::wrap($value);
		}

		return $wrapped;
	}

	public function raw(mixed $value): mixed
	{
		if ($value instanceof ProxyInterface) {
			return $value->unwrap();
		}

		if (!is_array($value)) {
			return $value;
		}

		return $this->rawArray($value);
	}

	public function add(string $key, mixed $value): mixed
	{
		$this->context[$key] = $value;

		return $this->templateValue($value);
	}

	public function esc(
		StringProxy|ObjectProxy|string|Stringable $value,
		int $flags = self::ESCAPE_FLAGS,
		string $encoding = self::ESCAPE_ENCODING,
	): string {
		if ($value instanceof StringProxy) {
			return htmlspecialchars($value->unwrap(), $flags, $encoding);
		}

		if ($value instanceof ObjectProxy) {
			$value = $value->unwrap();
		}

		if (is_string($value)) {
			return htmlspecialchars($value, $flags, $encoding);
		}

		if ($value instanceof Stringable) {
			return htmlspecialchars((string) $value, $flags, $encoding);
		}

		throw new RuntimeException('Value cannot be escaped as string');
	}

	/**
	 * @param array<array-key, mixed> $value
	 * @return array<array-key, mixed>
	 */
	private function rawArray(array $value): array
	{
		return array_map($this->raw(...), $value);
	}

	private function templateValue(mixed $value): mixed
	{
		return $this->autoescape
			? Wrapper::wrap($value)
			: $this->raw($value);
	}

	public function clean(
		string $value,
		?HtmlSanitizerConfig $config = null,
	): string {
		return new Sanitizer($config)->clean($value);
	}

	/**
	 * @psalm-param non-empty-string $path
	 */
	public function layout(string $path, ?array $context = null): void
	{
		$this->template->setLayout(new LayoutValue($path, $context));
	}

	/**
	 * Includes another template into the current template.
	 *
	 * If no context is passed it shares the context of the calling template.
	 *
	 * @psalm-param non-empty-string $path
	 */
	public function insert(string $path, array $context = []): void
	{
		$path = $this->template->engine->getFile($path);
		$template = new Template(
			$path,
			sections: $this->template->sections,
			engine: $this->template->engine,
		);

		echo
			$this->autoescape
				? $template->renderEscaped($this->context($context), $this->whitelist)
				: $template->renderUnescaped($this->context($context), $this->whitelist)
		;
	}

	public function begin(string $name): void
	{
		$this->template->sections->begin($name);
	}

	public function append(string $name): void
	{
		$this->template->sections->append($name);
	}

	public function prepend(string $name): void
	{
		$this->template->sections->prepend($name);
	}

	public function end(): void
	{
		$this->template->sections->end();
	}

	public function section(string $name, string $default = ''): string
	{
		if (func_num_args() > 1) {
			return $this->template->sections->getOr($name, $default);
		}

		return $this->template->sections->get($name);
	}

	public function has(string $name): bool
	{
		return $this->template->sections->has($name);
	}
}
