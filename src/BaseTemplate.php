<?php

declare(strict_types=1);

namespace Duon\Boiler;

use Duon\Boiler\Contract\Engine;
use Duon\Boiler\Contract\Template;
use Duon\Boiler\Engine as BoilerEngine;
use Duon\Boiler\Exception\LookupException;
use Duon\Boiler\Exception\RenderException;
use Duon\Boiler\Exception\RuntimeException;
use Override;
use Throwable;

abstract class BaseTemplate implements Template
{
	use RegistersMethod;

	protected ?LayoutValue $layout = null;
	protected CustomMethods $customMethods;

	public private(set) Engine $engine {
		get => $this->engine;
		set(Engine $value) => $this->engine = $value;
	}
	public private(set) Sections $sections {
		get => $this->sections;
		set(Sections $value) => $this->sections = $value;
	}

	public function __construct(
		public readonly string $path,
		?Sections $sections = null,
		?Engine $engine = null,
	) {
		$this->sections = $sections ?: new Sections();
		$this->customMethods = new CustomMethods();

		if ($engine === null) {
			$dir = dirname($path);

			if ($dir === '' || $path === '') {
				throw new LookupException('No directory given or empty path');
			}

			$this->engine = new BoilerEngine($dir, true, [], []);

			if (!is_file($path)) {
				throw new LookupException('Template not found: ' . $path);
			}

			return;
		}

		$this->engine = $engine;
	}

	/**
	 * @psalm-param list<class-string> $whitelist
	 */
	#[Override]
	public function render(array $context = [], array $whitelist = []): string
	{
		return $this->renderTemplate($context, $whitelist, autoescape: $this->engine->autoescape);
	}

	/**
	 * @psalm-param list<class-string> $whitelist
	 */
	public function renderEscaped(array $context = [], array $whitelist = []): string
	{
		return $this->renderTemplate($context, $whitelist, autoescape: true);
	}

	/**
	 * @psalm-param list<class-string> $whitelist
	 */
	public function renderUnescaped(array $context = [], array $whitelist = []): string
	{
		return $this->renderTemplate($context, $whitelist, autoescape: false);
	}

	/**
	 * Defines a layout template that will be wrapped around this instance.
	 *
	 * Typically it’s placed at the top of the file.
	 */
	#[Override]
	public function setLayout(LayoutValue $layout): void
	{
		if ($this->layout === null) {
			$this->layout = $layout;

			return;
		}

		throw new RuntimeException('Template error: layout already set');
	}

	#[Override]
	public function layout(): ?LayoutValue
	{
		return $this->layout;
	}

	public function setCustomMethods(CustomMethods $customMethods): void
	{
		$this->customMethods = $customMethods;
	}

	/** @psalm-param list<class-string> $whitelist */
	abstract protected function templateContext(array $context, array $whitelist, bool $autoescape): Context;

	/**
	 * @psalm-param list<class-string> $whitelist
	 */
	protected function renderTemplate(array $context, array $whitelist, bool $autoescape): string
	{
		$content = $this->getContent($context, $whitelist, $autoescape);

		if ($this instanceof Layout) {
			return $content->content;
		}

		return $this->renderLayouts(
			$this,
			$content->templateContext,
			$whitelist,
			$content->content,
			$autoescape,
		);
	}

	/**
	 * @psalm-param list<class-string> $whitelist
	 */
	protected function getContent(array $context, array $whitelist, bool $autoescape): Content
	{
		$templateContext = $this->templateContext($context, $whitelist, $autoescape);

		$load = function (string $templatePath, array $context = []): void {
			// Hide $templatePath. Could be overwritten if $context['templatePath'] exists.
			$____template_path____ = $templatePath;

			extract($context);

			/** @psalm-suppress UnresolvableInclude */
			include $____template_path____;
		};

		/** @var callable */
		$load = $load->bindTo($templateContext);
		$level = ob_get_level();

		try {
			ob_start();

			$load(
				$this->path,
				$autoescape
					? $templateContext->context()
					: $context,
			);

			$content = (string) ob_get_clean();

			return new Content($content, $templateContext);
		} catch (Throwable $e) {
			throw new RenderException(
				"Template rendering error ({$this->path}): " . $e->getMessage(),
				previous: $e,
			);
		} finally {
			while (ob_get_level() > $level) {
				ob_end_clean();
			}
		}
	}

	/** @psalm-param list<class-string> $whitelist */
	protected function renderLayouts(
		Template $template,
		Context $context,
		array $whitelist,
		string $content,
		bool $autoescape,
	): string {
		while ($layout = $template->layout()) {
			$file = $template->engine->getFile($layout->layout);
			$template = new Layout(
				$file,
				$content,
				$this->sections,
				$template->engine,
			);

			$layoutContext = is_null($layout->context)
				? $context->context()
				: $context->context($layout->context);

			$content = $template->renderTemplate($layoutContext, $whitelist, $autoescape);
		}

		return $content;
	}
}
