<?php

declare(strict_types=1);

namespace Duon\Boiler;

use Duon\Boiler\Exception\RuntimeException;
use Duon\Boiler\Exception\UnexpectedValueException;
use Override;

/** @api */
final class Environment implements Contract\Environment
{
	private ?Contract\Wrapper $wrapper = null;
	private ?ConfiguredFilters $filters = null;
	private ?ConfiguredEscapers $escapers = null;
	private bool $sealed = false;

	public function setWrapper(Contract\Wrapper $wrapper): void
	{
		$this->assertNotSealed();

		if ($this->filters !== null || $this->escapers !== null) {
			throw new RuntimeException('Cannot set wrapper after filters or escapers are configured');
		}

		if ($this->wrapper !== null) {
			throw new RuntimeException('Wrapper is already configured');
		}

		$this->wrapper = $wrapper;
	}

	public function setFilters(Contract\Filters $filters): void
	{
		$this->assertNotSealed();

		if ($this->wrapper !== null) {
			throw new RuntimeException('Cannot set filters after wrapper is configured');
		}

		if ($this->filters !== null) {
			throw new RuntimeException('Filters are already configured');
		}

		$this->filters = new ConfiguredFilters($filters);
	}

	public function setEscapers(Contract\Escapers $escapers): void
	{
		$this->assertNotSealed();

		if ($this->wrapper !== null) {
			throw new RuntimeException('Cannot set escapers after wrapper is configured');
		}

		if ($this->escapers !== null) {
			throw new RuntimeException('Escapers are already configured');
		}

		$this->escapers = new ConfiguredEscapers($escapers);
	}

	#[Override]
	public function wrapper(): Contract\Wrapper
	{
		$this->sealed = true;

		return $this->wrapper ??= new Wrapper($this->escapers(), $this->filters());
	}

	#[Override]
	public function registerFilter(string $name, Contract\Filter $filter): void
	{
		$this->assertNotSealed();
		self::assertFilterName($name);

		if ($this->wrapper !== null) {
			throw new RuntimeException('Cannot register filter after wrapper is configured');
		}

		$this->filters()->register($name, $filter);
	}

	#[Override]
	public function registerEscaper(string $name, Contract\Escaper $escaper): void
	{
		$this->assertNotSealed();
		self::assertEscaperName($name);

		if ($this->wrapper !== null) {
			throw new RuntimeException('Cannot register escaper after wrapper is configured');
		}

		$this->escapers()->register($name, $escaper);
	}

	private function filters(): ConfiguredFilters
	{
		return $this->filters ??= new ConfiguredFilters();
	}

	private function escapers(): ConfiguredEscapers
	{
		return $this->escapers ??= new ConfiguredEscapers();
	}

	private function assertNotSealed(): void
	{
		if ($this->sealed) {
			throw new RuntimeException('Engine configuration is sealed');
		}
	}

	/** @psalm-assert non-empty-string $name */
	private static function assertFilterName(string $name): void
	{
		if (!preg_match('/^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$/', $name)) {
			throw new UnexpectedValueException(
				"Filter name `{$name}` is not a valid PHP method name",
			);
		}
	}

	/** @psalm-assert non-empty-string $name */
	private static function assertEscaperName(string $name): void
	{
		if ($name === '') {
			throw new UnexpectedValueException('Escaper name must be a non-empty string');
		}
	}
}
