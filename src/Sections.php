<?php

declare(strict_types=1);

namespace Duon\Boiler;

use Duon\Boiler\Exception\LogicException;

final class Sections
{
	/** @var array<string, Section> */
	private array $sections = [];
	/** @var list<string> */
	private array $capture = [];
	private SectionMode $sectionMode = SectionMode::Closed;
	private ?int $captureLevel = null;

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

		$name = $this->name();

		if ($this->captureLevel !== ob_get_level()) {
			throw new LogicException("Section capture block `{$name}` is not the active output buffer");
		}

		$content = (string) ob_get_clean();
		array_pop($this->capture);

		$this->sections[$name] = match ($this->sectionMode) {
			SectionMode::Assign => new Section($content),
			SectionMode::Append => ($this->sections[$name] ?? new Section(''))->append($content),
			SectionMode::Prepend => ($this->sections[$name] ?? new Section(''))->prepend($content),
		};

		$this->sectionMode = SectionMode::Closed;
		$this->captureLevel = null;
	}

	public function get(string $name): string
	{
		return $this->sections[$name]->get();
	}

	public function getOr(string $name, string $default): string
	{
		$section = $this->sections[$name] ?? null;

		if ($section === null) {
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

	/** @return array{mode: SectionMode, name: string|null, level: int|null} */
	public function checkpoint(): array
	{
		return [
			'mode' => $this->sectionMode,
			'name' => $this->sectionMode === SectionMode::Closed ? null : $this->name(),
			'level' => $this->captureLevel,
		];
	}

	/** @param array{mode: SectionMode, name: string|null, level: int|null}|null $checkpoint */
	public function assertClosed(?array $checkpoint = null): void
	{
		if ($checkpoint !== null && $this->checkpoint() === $checkpoint) {
			return;
		}

		if ($this->sectionMode === SectionMode::Closed) {
			if ($checkpoint === null || $checkpoint['mode'] === SectionMode::Closed) {
				return;
			}

			$name = $checkpoint['name'] ?? 'unknown';

			throw new LogicException("Section capture block `{$name}` was closed unexpectedly");
		}

		throw new LogicException("Unclosed section capture block `{$this->name()}`");
	}

	private function open(string $name, SectionMode $mode): void
	{
		if ($this->sectionMode !== SectionMode::Closed) {
			throw new LogicException('Nested sections are not allowed');
		}

		$this->sectionMode = $mode;
		$this->capture[] = $name;
		ob_start();
		$this->captureLevel = ob_get_level();
	}

	private function name(): string
	{
		$last = array_key_last($this->capture);

		if ($last === null) {
			throw new LogicException('No section started');
		}

		return $this->capture[$last];
	}
}
