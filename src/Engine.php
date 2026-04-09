<?php

declare(strict_types=1);

namespace Duon\Boiler;

use Duon\Boiler\Exception\LookupException;
use Duon\Boiler\Exception\UnexpectedValueException;

/**
 * @api
 * @psalm-type DirsInput = non-empty-string|list<non-empty-string>|array<non-empty-string, non-empty-string>
 * @psalm-type ResolverInput = Contract\Resolver|DirsInput
 */
final class Engine
{
	private readonly Contract\Environment $environment;
	private readonly Contract\Resolver $resolver;
	private Methods $methods;
	private ?Contract\Wrapper $wrapper = null;

	/**
	 * @psalm-param list<class-string> $trusted
	 * @psalm-param Contract\Resolver $resolver
	 */
	public function __construct(
		Contract\Resolver $resolver,
		Contract\Environment $environment,
		public readonly bool $autoescape,
		private readonly array $defaults = [],
		private readonly array $trusted = [],
	) {
		$this->resolver = $resolver;
		$this->environment = $environment;
		$this->methods = new Methods();
	}

	/**
	 * @psalm-param ResolverInput $resolver
	 * @psalm-param list<class-string> $trusted
	 */
	public static function create(
		Contract\Resolver|array|string $resolver,
		array $defaults = [],
		array $trusted = [],
	): self {
		return new self(self::prepareResolver($resolver), new Environment(), true, $defaults, $trusted);
	}

	/**
	 * @psalm-param ResolverInput $resolver
	 * @psalm-param list<class-string> $trusted
	 */
	public static function unescaped(
		Contract\Resolver|array|string $resolver,
		array $defaults = [],
		array $trusted = [],
	): self {
		return new self(self::prepareResolver($resolver), new Environment(), false, $defaults, $trusted);
	}

	/** @psalm-param non-empty-string $name */
	public function method(string $name, callable $callable): static
	{
		$this->methods->add($name, $callable);

		return $this;
	}

	public function wrapper(): Contract\Wrapper
	{
		return $this->wrapper ??= $this->environment->wrapper();
	}

	public function escape(string $name, Contract\Escaper $with): static
	{
		$this->environment->registerEscaper($name, $with);

		return $this;
	}

	public function filter(string $name, Contract\Filter $with): static
	{
		$this->environment->registerFilter($name, $with);

		return $this;
	}

	/** @psalm-param non-empty-string $path */
	public function template(string $path): Template
	{
		$file = $this->resolve($path);
		$template = new Template($file, engine: $this);
		$template->setMethods($this->methods);

		return $template;
	}

	/** @psalm-param non-empty-string $path */
	public function render(
		string $path,
		array $context = [],
	): string {
		return $this->renderTemplate($path, $context, $this->autoescape);
	}

	/** @psalm-param non-empty-string $path */
	public function renderEscaped(
		string $path,
		array $context = [],
	): string {
		return $this->renderTemplate($path, $context, true);
	}

	/** @psalm-param non-empty-string $path */
	public function renderUnescaped(
		string $path,
		array $context = [],
	): string {
		return $this->renderTemplate($path, $context, false);
	}

	/** @psalm-param non-empty-string $path */
	private function renderTemplate(
		string $path,
		array $context,
		bool $autoescape,
	): string {
		$template = $this->template($path);
		$context = $this->defaults === []
			? $context
			: array_merge($this->defaults, $context);

		return $autoescape
			? $template->renderEscaped($context, $this->trusted)
			: $template->renderUnescaped($context, $this->trusted);
	}

	/**
	 * @psalm-param non-empty-string $path
	 *
	 * @psalm-return non-empty-string
	 */
	public function resolve(string $path): string
	{
		return $this->resolver->resolve($path);
	}

	/** @psalm-param non-empty-string $path */
	public function exists(string $path): bool
	{
		try {
			$this->resolve($path);

			return true;
		} catch (LookupException|UnexpectedValueException) {
			return false;
		}
	}

	/** @psalm-param ResolverInput $resolver */
	private static function prepareResolver(Contract\Resolver|array|string $resolver): Contract\Resolver
	{
		if ($resolver instanceof Contract\Resolver) {
			return $resolver;
		}

		return new Resolver($resolver);
	}
}
