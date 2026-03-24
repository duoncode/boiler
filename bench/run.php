<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

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

resetCacheDir(__DIR__ . '/cache/twig');
resetCacheDir(__DIR__ . '/cache/bladeone');

const DEFAULT_RUNS = 1000;

const DEFAULT_ITERATIONS = 3;

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

/** @return array{runs: int, iterations: int} */
function benchmarkConfig(): array
{
	static $config;

	if (is_array($config)) {
		return $config;
	}

	$options = getopt('', ['runs:', 'iterations:']);
	assert(is_array($options), 'getopt() must return an array of CLI options');

	return $config = [
		'runs' => intOption($options, 'runs', DEFAULT_RUNS),
		'iterations' => intOption($options, 'iterations', DEFAULT_ITERATIONS),
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

function runs(): int
{
	return benchmarkConfig()['runs'];
}

function iterations(): int
{
	return benchmarkConfig()['iterations'];
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

function benchTwigRealistic(): BenchResult
{
	$result = new BenchResult('Twig');
	$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/twig');
	$engine = new \Twig\Environment($loader, [
		'cache' => __DIR__ . '/cache/twig',
	]);
	$context = benchmarkContext();

	// Warmup - populate cache, trigger autoloading
	$engine->render('page.html', $context);
	gc_collect_cycles();

	$runs = runs();
	$iterations = iterations();

	for ($iter = 0; $iter < $iterations; $iter++) {
		memory_reset_peak_usage();
		$memBefore = memory_get_usage();
		$start = hrtime(true);

		for ($i = 0; $i < $runs; $i++) {
			$t = $engine->render('page.html', $context);
		}

		$elapsed = (hrtime(true) - $start) / 1e9;
		$memPeak = max(0, memory_get_peak_usage() - $memBefore);

		$result->add($elapsed, $memPeak);
		$result->output = $t;

		gc_collect_cycles();
	}

	return $result;
}

function benchBladeOneRealistic(): BenchResult
{
	$result = new BenchResult('BladeOne');
	$engine = new \eftec\bladeone\BladeOne(__DIR__ . '/bladeone', __DIR__ . '/cache/bladeone');
	$context = benchmarkContext();

	// Warmup
	$engine->run('page', $context);
	gc_collect_cycles();

	$runs = runs();
	$iterations = iterations();

	for ($iter = 0; $iter < $iterations; $iter++) {
		memory_reset_peak_usage();
		$memBefore = memory_get_usage();
		$start = hrtime(true);

		for ($i = 0; $i < $runs; $i++) {
			$t = $engine->run('page', $context);
		}

		$elapsed = (hrtime(true) - $start) / 1e9;
		$memPeak = max(0, memory_get_peak_usage() - $memBefore);

		$result->add($elapsed, $memPeak);
		$result->output = $t;

		gc_collect_cycles();
	}

	return $result;
}

function benchBoilerRealistic(): BenchResult
{
	$result = new BenchResult('Boiler');
	$engine = Duon\Boiler\Engine::create(__DIR__ . '/boiler');
	$context = benchmarkContext();

	// Warmup
	$engine->render('page', $context);
	gc_collect_cycles();

	$runs = runs();
	$iterations = iterations();

	for ($iter = 0; $iter < $iterations; $iter++) {
		memory_reset_peak_usage();
		$memBefore = memory_get_usage();
		$start = hrtime(true);

		for ($i = 0; $i < $runs; $i++) {
			$t = $engine->render('page', $context);
		}

		$elapsed = (hrtime(true) - $start) / 1e9;
		$memPeak = max(0, memory_get_peak_usage() - $memBefore);

		$result->add($elapsed, $memPeak);
		$result->output = $t;

		gc_collect_cycles();
	}

	return $result;
}

function benchPlatesRealistic(): BenchResult
{
	$result = new BenchResult('Plates');
	$engine = new League\Plates\Engine(__DIR__ . '/plates');
	$context = benchmarkContext();

	// Warmup
	$engine->render('page', $context);
	gc_collect_cycles();

	$runs = runs();
	$iterations = iterations();

	for ($iter = 0; $iter < $iterations; $iter++) {
		memory_reset_peak_usage();
		$memBefore = memory_get_usage();
		$start = hrtime(true);

		for ($i = 0; $i < $runs; $i++) {
			$t = $engine->render('page', $context);
		}

		$elapsed = (hrtime(true) - $start) / 1e9;
		$memPeak = max(0, memory_get_peak_usage() - $memBefore);

		$result->add($elapsed, $memPeak);
		$result->output = $t;

		gc_collect_cycles();
	}

	return $result;
}

function benchBoilerUnescapedRealistic(): BenchResult
{
	$result = new BenchResult('Boiler');
	$engine = Duon\Boiler\Engine::unescaped(__DIR__ . '/boiler');
	$context = benchmarkContext();

	// Warmup
	$engine->render('pagenoescape', $context);
	gc_collect_cycles();

	$runs = runs();
	$iterations = iterations();

	for ($iter = 0; $iter < $iterations; $iter++) {
		memory_reset_peak_usage();
		$memBefore = memory_get_usage();
		$start = hrtime(true);

		for ($i = 0; $i < $runs; $i++) {
			$t = $engine->render('pagenoescape', $context);
		}

		$elapsed = (hrtime(true) - $start) / 1e9;
		$memPeak = max(0, memory_get_peak_usage() - $memBefore);

		$result->add($elapsed, $memPeak);
		$result->output = $t;

		gc_collect_cycles();
	}

	return $result;
}

function fulltrim(string $text): string
{
	// Remove all whitespace for comparison - engines differ in indentation
	return preg_replace('/\s+/', '', $text);
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

	// Realistic benchmark (engine reused)
	echo "ESCAPED\n";
	echo str_repeat('-', 70) . "\n";

	$twig = benchTwigRealistic();
	$blade = benchBladeOneRealistic();
	$boiler = benchBoilerRealistic();

	$twig->print();
	$blade->print();
	$boiler->print();

	echo "\nUNESCAPED\n";
	echo str_repeat('-', 70) . "\n";

	$plates = benchPlatesRealistic();
	$boilerUn = benchBoilerUnescapedRealistic();

	$plates->print();
	$boilerUn->print();

	// Verify output consistency
	echo "\n" . str_repeat('=', 70) . "\n";
	echo 'Output verification: ';

	$expected = fulltrim($plates->output);
	$allMatch = true;

	foreach ([$twig, $blade, $boiler, $boilerUn] as $result) {
		if (fulltrim($result->output) === $expected) {
			continue;
		}

		echo "MISMATCH in {$result->name}!\n";
		$allMatch = false;
	}

	if ($allMatch) {
		echo "All outputs match ✓\n";
	}

	return 0;
}

exit(main());
