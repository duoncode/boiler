<?php

declare(strict_types=1);

namespace Duon\Boiler;

use Duon\Boiler\Exception\RuntimeException;

final class EngineRuntime
{
	private ?Contract\Wrapper $wrapper = null;
	private ?Contract\Filters $filters = null;
	private ?Contract\Escapers $escapers = null;
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

		$this->filters = $filters;
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

		$this->escapers = $escapers;
	}

	public function wrapper(): Contract\Wrapper
	{
		$this->sealed = true;

		return $this->wrapper ??= new Wrapper($this->escapers(), $this->filters());
	}

	public function filters(): Contract\Filters
	{
		return $this->filters ??= new Filters();
	}

	public function escapers(): Contract\Escapers
	{
		return $this->escapers ??= new Escapers();
	}

	private function assertNotSealed(): void
	{
		if ($this->sealed) {
			throw new RuntimeException('Engine configuration is sealed');
		}
	}
}
