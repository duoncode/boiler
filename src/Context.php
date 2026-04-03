<?php

declare(strict_types=1);

namespace Duon\Boiler;

use Duon\Boiler\Contract\Wrapper;
use Duon\Boiler\Proxy\ObjectProxy;
use Duon\Boiler\Proxy\Proxy;
use Duon\Boiler\Proxy\StringProxy;
use Stringable;

/** @api */
abstract class Context
{
	/** @var array<array-key, mixed>|null */
	private ?array $wrappedContext = null;
	protected readonly Wrapper $wrapper;

	/**
	 * @psalm-param list<class-string> $whitelist
	 */
	public function __construct(
		protected readonly Contract\Template $template,
		protected array $context,
		public readonly array $whitelist,
		public readonly bool $autoescape,
	) {
		$this->wrapper = $template->engine->wrapper();
	}

	public function __call(string $name, array $args): mixed
	{
		$callable = $this->template->getMethods()->get($name);

		/** @var array<array-key, mixed> $args */
		$args = $this->unwrap($args);

		return $this->templateValue($callable(...$args));
	}

	public function context(array $values = []): array
	{
		if (!$this->autoescape) {
			return $values === []
				? $this->context
				: array_merge($this->context, $values);
		}

		if ($values === []) {
			return $this->wrappedContext();
		}

		return array_merge($this->wrappedContext(), $this->wrapAll($values));
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
			if ($value instanceof Proxy) {
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
			$wrapped[$key] = $this->wrapper->wrap($value);
		}

		return $wrapped;
	}

	public function unwrap(mixed $value): mixed
	{
		return $this->wrapper->unwrap($value);
	}

	public function add(string $key, mixed $value): mixed
	{
		$this->context[$key] = $value;
		$this->wrappedContext = null;

		return $this->templateValue($value);
	}

	public function escape(
		StringProxy|ObjectProxy|string|Stringable $value,
		?string $strategy = null,
	): string {
		return $this->wrapper->escape($value, $strategy);
	}

	/** @return array<array-key, mixed> */
	private function wrappedContext(): array
	{
		return $this->wrappedContext ??= $this->wrapAll($this->context);
	}

	private function templateValue(mixed $value): mixed
	{
		return $this->autoescape
			? $this->wrapper->wrap($value)
			: $this->wrapper->unwrap($value);
	}

	public function filter(
		string $name,
		StringProxy|ObjectProxy|string|Stringable $value,
		mixed ...$args,
	): string {
		if ($value instanceof Proxy) {
			$value = (string) $value->unwrap();
		} elseif ($value instanceof Stringable) {
			$value = (string) $value;
		}

		return $this->wrapper->applyFilter($name, $value, ...$args);
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
