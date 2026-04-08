<?php

declare(strict_types=1);

namespace Duon\Boiler;

use Duon\Boiler\Exception\LogicException;

final class Sections
{
	/** @var array<string, Section> */
	private array $sections = [];
	private array $capture = [];
	private SectionMode $sectionMode = SectionMode::Closed;

	public function begin(string $name): void
	{
		$this->open($name, SectionMode::Assign);
	}

	public function append(string $name): void
	{
		$this->open($name, SectionMode::Append);
	}

	public function prepend(string $name): void
	{
		$this->open($name, SectionMode::Prepend);
	}

	public function end(): void
	{
		if ($this->sectionMode === SectionMode::Closed) {
			throw new LogicException('No section started');
		}

		$content = (string) ob_get_clean();
		$name = (string) array_pop($this->capture);

		$this->sections[$name] = match ($this->sectionMode) {
			SectionMode::Assign => new Section($content),
			SectionMode::Append => ($this->sections[$name] ?? new Section(''))->append($content),
			SectionMode::Prepend => ($this->sections[$name] ?? new Section(''))->prepend($content),
		};

		$this->sectionMode = SectionMode::Closed;
	}

	public function get(string $name): string
	{
		return $this->sections[$name]->get();
	}

	public function getOr(string $name, string $default): string
	{
		$section = $this->sections[$name] ?? null;

		if (is_null($section)) {
			return $default;
		}

		if ($section->empty()) {
			$section->setValue($default);
		}

		return $section->get();
	}

	public function has(string $name): bool
	{
		return isset($this->sections[$name]);
	}

	public function assertClosed(): void
	{
		if ($this->sectionMode === SectionMode::Closed) {
			return;
		}

		assert($this->capture !== [], 'Capture stack must contain a section name while open');
		$name = (string) end($this->capture);

		throw new LogicException("Unclosed section capture block `{$name}`");
	}

	private function open(string $name, SectionMode $mode): void
	{
		if ($this->sectionMode !== SectionMode::Closed) {
			throw new LogicException('Nested sections are not allowed');
		}

		$this->sectionMode = $mode;
		$this->capture[] = $name;
		ob_start();
	}
}
