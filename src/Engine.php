<?php

declare(strict_types=1);

namespace Duon\Boiler;

use Duon\Boiler\Exception\LookupException;
use Duon\Boiler\Exception\RuntimeException;
use Duon\Boiler\Exception\UnexpectedValueException;

/**
 * @api
 * @psalm-type DirsInput = non-empty-string|list<non-empty-string>|array<non-empty-string, non-empty-string>
 * @psalm-type ResolverInput = Contract\Resolver|DirsInput
 */
final class Engine
{
	private readonly Environment $environment;
	private Methods $methods;
	private readonly Contract\Resolver $resolver;

	public private(set) bool $autoescape {
		get => $this->autoescape;
		set(bool $value) => $this->autoescape = $value;
	}

	/**
	 * @psalm-param list<class-string> $whitelist
	 * @psalm-param Contract\Resolver $resolver
	 */
	public function __construct(
		Contract\Resolver $resolver,
		bool $autoescape,
		protected readonly array $defaults,
		protected readonly array $whitelist,
	) {
		$this->autoescape = $autoescape;
		$this->resolver = $resolver;
		$this->environment = new Environment();
		$this->methods = new Methods();
	}

	/**
	 * @psalm-param ResolverInput $resolver
	 * @psalm-param list<class-string> $whitelist
	 */
	public static function create(
		Contract\Resolver|array|string $resolver,
		array $defaults = [],
		array $whitelist = [],
	): self {
		return new self(self::prepareResolver($resolver), true, $defaults, $whitelist);
	}

	/**
	 * @psalm-param ResolverInput $resolver
	 * @psalm-param list<class-string> $whitelist
	 */
	public static function unescaped(
		Contract\Resolver|array|string $resolver,
		array $defaults = [],
		array $whitelist = [],
	): self {
		return new self(self::prepareResolver($resolver), false, $defaults, $whitelist);
	}

	/** @psalm-param non-empty-string $name */
	public function method(string $name, callable $callable): static
	{
		$this->methods->add($name, $callable);

		return $this;
	}

	public function wrapper(): Contract\Wrapper
	{
		return $this->environment->wrapper();
	}

	public function setWrapper(Contract\Wrapper $wrapper): static
	{
		$this->environment->setWrapper($wrapper);

		return $this;
	}

	public function setFilters(Contract\Filters $filters): static
	{
		$this->environment->setFilters($filters);

		return $this;
	}

	public function setEscapers(Contract\Escapers $escapers): static
	{
		$this->environment->setEscapers($escapers);

		return $this;
	}

	public function escape(string $name, Contract\Escaper $with): static
	{
		$escapers = $this->environment->escapers();

		if (!$escapers instanceof Contract\RegistersEscapers) {
			throw new RuntimeException('Configured escapers registry does not support escaper registration');
		}

		$escapers->register($name, $with);

		return $this;
	}

	public function filter(string $name, Contract\Filter $with): static
	{
		$filters = $this->environment->filters();

		if (!$filters instanceof Contract\RegistersFilters) {
			throw new RuntimeException('Configured filters registry does not support filter registration');
		}

		$filters->register($name, $with);

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
		$this->environment->wrapper();

		$template = $this->template($path);
		$context = $this->defaults === []
			? $context
			: array_merge($this->defaults, $context);

		return $autoescape
			? $template->renderEscaped($context, $this->whitelist)
			: $template->renderUnescaped($context, $this->whitelist);
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
