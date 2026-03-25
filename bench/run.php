<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

const DEFAULT_RUNS = 1000;
const DEFAULT_ITERATIONS = 3;
const DEFAULT_LIFECYCLE = 'classic';
const LIFECYCLE_WORKER = 'worker';
const LIFECYCLE_CLASSIC = 'classic';
const LIFECYCLE_BOTH = 'both';

function resetCacheDir(string $path): void
{
	if (!is_dir($path)) {
		mkdir($path, 0o755, true);

		return;
	}

	$iterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::CHILD_FIRST,
	);

	foreach ($iterator as $entry) {
		if ($entry->isDir() && !$entry->isLink()) {
			rmdir($entry->getPathname());

			continue;
		}

		unlink($entry->getPathname());
	}
}

function resetBenchmarkCaches(): void
{
	resetCacheDir(__DIR__ . '/cache/twig');
	resetCacheDir(__DIR__ . '/cache/bladeone');
}

function benchmarkContext(): array
{
	return [
		'title' => 'Product Catalog',
		'isLoggedIn' => true,
		'isAdmin' => false,
		'user' => [
			'id' => 42,
			'name' => 'John Doe',
			'email' => 'john@example.com',
			'profile' => [
				'bio' => '<script>alert("xss")</script>Web developer & designer',
				'avatar' => '/img/avatars/john.jpg',
				'location' => 'New York, NY',
			],
		],
		'products' => [
			[
				'id' => 1,
				'name' => 'Laptop Pro',
				'price' => 1299.99,
				'inStock' => true,
				'tags' => ['electronics', 'computers'],
			],
			[
				'id' => 2,
				'name' => 'Wireless Mouse',
				'price' => 49.95,
				'inStock' => true,
				'tags' => ['electronics', 'accessories'],
			],
			[
				'id' => 3,
				'name' => 'USB-C Hub',
				'price' => 79.00,
				'inStock' => false,
				'tags' => ['electronics', 'accessories'],
			],
			[
				'id' => 4,
				'name' => 'Mechanical Keyboard',
				'price' => 159.99,
				'inStock' => true,
				'tags' => ['electronics', 'accessories'],
			],
			[
				'id' => 5,
				'name' => '27" Monitor',
				'price' => 399.00,
				'inStock' => true,
				'tags' => ['electronics', 'displays'],
			],
			[
				'id' => 6,
				'name' => 'Webcam HD',
				'price' => 89.99,
				'inStock' => false,
				'tags' => ['electronics', 'video'],
			],
			[
				'id' => 7,
				'name' => 'Desk Lamp',
				'price' => 45.00,
				'inStock' => true,
				'tags' => ['office', 'lighting'],
			],
			[
				'id' => 8,
				'name' => 'Ergonomic Chair',
				'price' => 549.00,
				'inStock' => true,
				'tags' => ['office', 'furniture'],
			],
			[
				'id' => 9,
				'name' => 'Standing Desk',
				'price' => 699.00,
				'inStock' => false,
				'tags' => ['office', 'furniture'],
			],
			[
				'id' => 10,
				'name' => 'Notebook Set',
				'price' => 24.99,
				'inStock' => true,
				'tags' => ['office', 'supplies'],
			],
		],
		'stats' => [
			'totalProducts' => 156,
			'totalOrders' => 1247,
			'revenue' => 98432.50,
		],
		'store' => (object) [
			'name' => 'Duon Store',
			'support' => (object) [
				'email' => 'support@duon.run',
				'timezone' => 'Europe/Berlin',
			],
		],
		'announcement' => '<p class="alert"><strong>Holiday Sale:</strong> 20% off all items!</p>',
		'breadcrumbs' => new ArrayIterator([
			['label' => 'Home', 'url' => '/'],
			['label' => 'Products', 'url' => '/products'],
			['label' => 'Electronics', 'url' => '/products/electronics'],
		]),
	];
}

// @mago-expect lint:file-name
class BenchResult
{
	public string $name;
	public string $output;
	public float $min = PHP_FLOAT_MAX;
	public float $max = 0.0;
	public float $total = 0.0;
	public int $count = 0;
	public int $peakMemoryDelta = 0;

	public function __construct(string $name)
	{
		$this->name = $name;
	}

	public function add(float $time, int $memory): void
	{
		$this->min = min($this->min, $time);
		$this->max = max($this->max, $time);
		$this->total += $time;
		$this->count++;
		$this->peakMemoryDelta = max($this->peakMemoryDelta, $memory);
	}

	public function avg(): float
	{
		return $this->count > 0 ? $this->total / $this->count : 0;
	}

	public function print(): void
	{
		printf(
			"%-12s avg: %6.3fs  min: %6.3fs  max: %6.3fs  peak+: %7s\n",
			$this->name . ':',
			$this->avg(),
			$this->min,
			$this->max,
			formatBytes($this->peakMemoryDelta),
		);
	}
}

/** @return array{runs: int, iterations: int, lifecycle: string} */
function benchmarkConfig(): array
{
	static $config;

	if (is_array($config)) {
		return $config;
	}

	$options = getopt('', ['runs:', 'iterations:', 'lifecycle:']);
	assert(is_array($options), 'getopt() must return an array of CLI options');

	return $config = [
		'runs' => intOption($options, 'runs', DEFAULT_RUNS),
		'iterations' => intOption($options, 'iterations', DEFAULT_ITERATIONS),
		'lifecycle' => stringOption(
			$options,
			'lifecycle',
			DEFAULT_LIFECYCLE,
			[LIFECYCLE_WORKER, LIFECYCLE_CLASSIC, LIFECYCLE_BOTH],
		),
	];
}

/** @param array<string, mixed> $options */
function intOption(array $options, string $name, int $default): int
{
	$value = $options[$name] ?? null;

	if ($value === null) {
		return $default;
	}

	if (is_array($value) || !is_string($value) || !ctype_digit($value)) {
		throw new InvalidArgumentException("Option --{$name} must be a positive integer");
	}

	$int = (int) $value;

	if ($int < 1) {
		throw new InvalidArgumentException("Option --{$name} must be greater than 0");
	}

	return $int;
}

/**
 * @param array<string, mixed> $options
 * @param list<string> $allowed
 */
function stringOption(array $options, string $name, string $default, array $allowed): string
{
	$value = $options[$name] ?? null;

	if ($value === null) {
		return $default;
	}

	if (is_array($value) || !is_string($value)) {
		throw new InvalidArgumentException("Option --{$name} must be one of: " . implode(', ', $allowed));
	}

	$value = strtolower(trim($value));

	if (!in_array($value, $allowed, true)) {
		throw new InvalidArgumentException("Option --{$name} must be one of: " . implode(', ', $allowed));
	}

	return $value;
}

function runs(): int
{
	return benchmarkConfig()['runs'];
}

function iterations(): int
{
	return benchmarkConfig()['iterations'];
}

function lifecycle(): string
{
	return benchmarkConfig()['lifecycle'];
}

/** @return list<string> */
function lifecycles(): array
{
	return (
		lifecycle() === LIFECYCLE_BOTH
			? [LIFECYCLE_WORKER, LIFECYCLE_CLASSIC]
			: [lifecycle()]
	);
}

function formatBytes(int $bytes): string
{
	if ($bytes >= (1024 * 1024)) {
		return sprintf('%.1fMB', ($bytes / 1024) / 1024);
	}

	if ($bytes >= 1024) {
		return sprintf('%.0fKB', $bytes / 1024);
	}

	return $bytes . 'B';
}

function lifecycleLabel(string $lifecycle): string
{
	return match ($lifecycle) {
		LIFECYCLE_WORKER => 'worker (engine reused)',
		LIFECYCLE_CLASSIC => 'classic (engine recreated per render)',
		default => $lifecycle,
	};
}

/**
 * @template TEngine
 *
 * @param callable(): TEngine $createEngine
 * @param callable(TEngine, array): string $render
 */
function benchEngine(
	string $name,
	callable $createEngine,
	callable $render,
	string $lifecycle,
): BenchResult {
	$result = new BenchResult($name);
	$context = benchmarkContext();
	$runs = runs();
	$iterations = iterations();
	$engine = $createEngine();

	// Warmup - populate caches and trigger autoloading.
	$result->output = $render($engine, $context);
	gc_collect_cycles();

	for ($iter = 0; $iter < $iterations; $iter++) {
		memory_reset_peak_usage();
		$memBefore = memory_get_usage();
		$start = hrtime(true);

		if ($lifecycle === LIFECYCLE_WORKER) {
			for ($i = 0; $i < $runs; $i++) {
				$t = $render($engine, $context);
			}
		} else {
			for ($i = 0; $i < $runs; $i++) {
				$currentEngine = $createEngine();
				$t = $render($currentEngine, $context);
				unset($currentEngine);
			}
		}

		$elapsed = (hrtime(true) - $start) / 1e9;
		$memPeak = max(0, memory_get_peak_usage() - $memBefore);

		$result->add($elapsed, $memPeak);
		$result->output = $t;

		gc_collect_cycles();
	}

	return $result;
}

function benchTwigAutoEscaping(string $lifecycle): BenchResult
{
	return benchEngine(
		'Twig',
		static fn() => new \Twig\Environment(
			new \Twig\Loader\FilesystemLoader(__DIR__ . '/twig'),
			['cache' => __DIR__ . '/cache/twig'],
		),
		static fn(\Twig\Environment $engine, array $context): string => $engine->render(
			'page.html',
			$context,
		),
		$lifecycle,
	);
}

function benchBladeOneAutoEscaping(string $lifecycle): BenchResult
{
	return benchEngine(
		'BladeOne',
		static fn() => new \eftec\bladeone\BladeOne(__DIR__ . '/bladeone', __DIR__ . '/cache/bladeone'),
		static fn(\eftec\bladeone\BladeOne $engine, array $context): string => $engine->run(
			'page',
			$context,
		),
		$lifecycle,
	);
}

function benchBoilerAutoEscaping(string $lifecycle): BenchResult
{
	return benchEngine(
		'Boiler',
		static fn() => Duon\Boiler\Engine::create(__DIR__ . '/boiler'),
		static fn(Duon\Boiler\Engine $engine, array $context): string => $engine->render(
			'page',
			$context,
		),
		$lifecycle,
	);
}

function benchPlatesManualEscaping(string $lifecycle): BenchResult
{
	return benchEngine(
		'Plates',
		static fn() => new League\Plates\Engine(__DIR__ . '/plates'),
		static fn(League\Plates\Engine $engine, array $context): string => $engine->render(
			'page',
			$context,
		),
		$lifecycle,
	);
}

function benchBoilerManualEscaping(string $lifecycle): BenchResult
{
	return benchEngine(
		'Boiler',
		static fn() => Duon\Boiler\Engine::unescaped(__DIR__ . '/boiler'),
		static fn(Duon\Boiler\Engine $engine, array $context): string => $engine->render(
			'pagemanualescaping',
			$context,
		),
		$lifecycle,
	);
}

function fulltrim(string $text): string
{
	// Remove all whitespace for comparison - engines differ in indentation.
	return preg_replace('/\s+/', '', $text);
}

/** @param list<BenchResult> $results */
function verifyOutputs(array $results): void
{
	echo "\n" . str_repeat('=', 70) . "\n";
	echo 'Output verification: ';

	$expected = fulltrim($results[array_key_first($results)]->output);
	$allMatch = true;

	foreach ($results as $result) {
		if (fulltrim($result->output) === $expected) {
			continue;
		}

		echo "MISMATCH in {$result->name}!\n";
		$allMatch = false;
	}

	if ($allMatch) {
		echo "All outputs match ✓\n";
	}
}

function runScenario(string $lifecycle): void
{
	resetBenchmarkCaches();

	echo 'Lifecycle: ' . lifecycleLabel($lifecycle) . "\n";
	echo str_repeat('-', 70) . "\n\n";

	echo "AUTOMATIC ESCAPING\n";
	echo str_repeat('-', 70) . "\n";

	$twigAutoEscaping = benchTwigAutoEscaping($lifecycle);
	$bladeOneAutoEscaping = benchBladeOneAutoEscaping($lifecycle);
	$boilerAutoEscaping = benchBoilerAutoEscaping($lifecycle);

	$twigAutoEscaping->print();
	$bladeOneAutoEscaping->print();
	$boilerAutoEscaping->print();

	echo "\nMANUAL ESCAPING\n";
	echo str_repeat('-', 70) . "\n";

	$platesManualEscaping = benchPlatesManualEscaping($lifecycle);
	$boilerManualEscaping = benchBoilerManualEscaping($lifecycle);

	$platesManualEscaping->print();
	$boilerManualEscaping->print();

	verifyOutputs([
		$platesManualEscaping,
		$twigAutoEscaping,
		$bladeOneAutoEscaping,
		$boilerAutoEscaping,
		$boilerManualEscaping,
	]);
}

function main(): int
{
	try {
		$runs = runs();
		$iterations = iterations();
	} catch (InvalidArgumentException $e) {
		fwrite(STDERR, $e->getMessage() . PHP_EOL);

		return 1;
	}

	echo 'Benchmark: ' . number_format($runs) . ' renders × ' . $iterations . " iterations\n";
	echo str_repeat('=', 70) . "\n\n";

	foreach (lifecycles() as $index => $lifecycle) {
		if ($index > 0) {
			echo "\n";
		}

		runScenario($lifecycle);
	}

	return 0;
}

exit(main());
