<?php

declare(strict_types=1);

namespace Duon\Boiler;

use Duon\Boiler\Exception\LookupException;
use Duon\Boiler\Exception\RenderException;
use Duon\Boiler\Exception\RuntimeException;
use Throwable;

abstract class BaseTemplate
{
	protected ?LayoutValue $layout = null;
	protected Methods $methods;
	protected readonly bool $ownsSections;

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
		$this->ownsSections = $sections === null;
		$this->sections = $sections ?? new Sections();
		$this->methods = new Methods();

		if ($engine === null) {
			$dir = dirname($path);

			if ($dir === '' || $path === '') {
				throw new LookupException('No directory given or empty path');
			}

			$this->engine = new Engine(new Resolver($dir), true);

			if (!is_file($path)) {
				throw new LookupException('Template not found: ' . $path);
			}

			return;
		}

		$this->engine = $engine;
	}

	/**
	 * @psalm-param list<class-string> $trusted
	 * @psalm-suppress PossiblyUnusedMethod Called through inherited API on concrete templates
	 */
	public function render(array $context = [], array $trusted = []): string
	{
		return $this->renderIsolated($context, $trusted, autoescape: $this->engine->autoescape);
	}

	/** @psalm-param list<class-string> $trusted */
	public function renderEscaped(array $context = [], array $trusted = []): string
	{
		return $this->renderIsolated($context, $trusted, autoescape: true);
	}

	/** @psalm-param list<class-string> $trusted */
	public function renderUnescaped(array $context = [], array $trusted = []): string
	{
		return $this->renderIsolated($context, $trusted, autoescape: false);
	}

	/**
	 * Defines a layout template that will be wrapped around this instance.
	 *
	 * Typically it’s placed at the top of the file.
	 */
	public function setLayout(LayoutValue $layout): void
	{
		if ($this->layout === null) {
			$this->layout = $layout;

			return;
		}

		throw new RuntimeException('Template error: layout already set');
	}

	public function layout(): ?LayoutValue
	{
		return $this->layout;
	}

	/** @internal */
	public function setMethods(Methods $methods): void
	{
		$this->methods = $methods;
	}

	/** @internal */
	public function methods(): Methods
	{
		return $this->methods;
	}

	/** @psalm-param list<class-string> $trusted */
	protected function renderIsolated(array $context, array $trusted, bool $autoescape): string
	{
		$this->resetRenderState();

		try {
			return $this->renderTemplate($context, $trusted, $autoescape);
		} finally {
			$this->resetRenderState();
		}
	}

	protected function resetRenderState(): void
	{
		$this->layout = null;

		if ($this->ownsSections) {
			$this->sections = new Sections();
		}
	}

	/** @psalm-param list<class-string> $trusted */
	abstract protected function context(
		array $context,
		array $trusted,
		bool $autoescape,
	): Context;

	/** @psalm-param list<class-string> $trusted */
	protected function renderTemplate(array $context, array $trusted, bool $autoescape): string
	{
		$content = $this->getContent($context, $trusted, $autoescape);

		if ($this instanceof Layout) {
			return $content->content;
		}

		return $this->renderLayouts(
			$this,
			$content->templateContext,
			$trusted,
			$content->content,
			$autoescape,
		);
	}

	/** @psalm-param list<class-string> $trusted */
	protected function getContent(array $context, array $trusted, bool $autoescape): Content
	{
		$templateContext = $this->context($context, $trusted, $autoescape);

		/** @mago-expect lint:prefer-static-closure Closure::call() binds $this to the template context at runtime. */
		$load = function (string $templatePath, array $context = []): void {
			// Must stay non-static so Closure::call() can bind $this to the template context.
			// Hide $templatePath. Could be overwritten if $context['templatePath'] exists.
			$____template_path____ = $templatePath;

			extract($context, EXTR_SKIP);

			/** @psalm-suppress UnresolvableInclude */
			include $____template_path____;
		};

		$level = ob_get_level();

		try {
			ob_start();

			$load->call(
				$templateContext,
				$this->path,
				$autoescape
					? $templateContext->get()
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

	/** @psalm-param list<class-string> $trusted */
	protected function renderLayouts(
		BaseTemplate $template,
		Context $context,
		array $trusted,
		string $content,
		bool $autoescape,
	): string {
		while ($layout = $template->layout()) {
			$file = $template->engine->resolve($layout->layout);
			$methods = $template->methods();
			$template = new Layout(
				$file,
				$content,
				$this->sections,
				$template->engine,
			);
			$template->setMethods($methods);

			$layoutContext = is_null($layout->context)
				? $context->get()
				: $context->get($layout->context);

			$content = $template->renderTemplate($layoutContext, $trusted, $autoescape);
		}

		return $content;
	}
}
