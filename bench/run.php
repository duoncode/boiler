<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

if (!is_dir('./cache')) {
    mkdir('./cache', 0755, true);
}
if (!is_dir('./cache/bladeone')) {
    mkdir('./cache/bladeone', 0755, true);
}

const RUNS = 10000;
const ITERATIONS = 5;
const CONTEXT = [
    'title' => 'Engine',
    'array' => ['string1', 'string2', '<b>string3</b>'],
    'htmlval' => '<p>lorem ipsum</p>'
];

class BenchResult
{
    public string $name;
    public string $output;
    public float $min = PHP_FLOAT_MAX;
    public float $max = 0.0;
    public float $total = 0.0;
    public int $count = 0;
    public int $peakMemory = 0;

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
        $this->peakMemory = max($this->peakMemory, $memory);
    }

    public function avg(): float
    {
        return $this->count > 0 ? $this->total / $this->count : 0;
    }

    public function print(): void
    {
        printf(
            "%-12s avg: %6.3fs  min: %6.3fs  max: %6.3fs  mem: %5.1fMB\n",
            $this->name . ':',
            $this->avg(),
            $this->min,
            $this->max,
            $this->peakMemory / 1024 / 1024
        );
    }
}

function benchTwigRealistic(): BenchResult
{
    $result = new BenchResult('Twig');
    $loader = new \Twig\Loader\FilesystemLoader('./twig');
    $engine = new \Twig\Environment($loader, [
        'cache' => './cache/twig',
    ]);

    // Warmup - populate cache, trigger autoloading
    $engine->render('page.html', CONTEXT);
    gc_collect_cycles();

    for ($iter = 0; $iter < ITERATIONS; $iter++) {
        $memBefore = memory_get_usage();
        $start = hrtime(true);

        for ($i = 0; $i < RUNS; $i++) {
            $t = $engine->render('page.html', CONTEXT);
        }

        $elapsed = (hrtime(true) - $start) / 1e9;
        $memPeak = memory_get_peak_usage();

        $result->add($elapsed, $memPeak);
        $result->output = $t;

        gc_collect_cycles();
    }

    return $result;
}

function benchBladeOneRealistic(): BenchResult
{
    $result = new BenchResult('BladeOne');
    $engine = new \eftec\bladeone\BladeOne('./bladeone', './cache/bladeone');

    // Warmup
    $engine->run('page', CONTEXT);
    gc_collect_cycles();

    for ($iter = 0; $iter < ITERATIONS; $iter++) {
        $memBefore = memory_get_usage();
        $start = hrtime(true);

        for ($i = 0; $i < RUNS; $i++) {
            $t = $engine->run('page', CONTEXT);
        }

        $elapsed = (hrtime(true) - $start) / 1e9;
        $memPeak = memory_get_peak_usage();

        $result->add($elapsed, $memPeak);
        $result->output = $t;

        gc_collect_cycles();
    }

    return $result;
}

function benchBoilerRealistic(): BenchResult
{
    $result = new BenchResult('Boiler');
    $engine = Duon\Boiler\Engine::create('./boiler');

    // Warmup
    $engine->render('page', CONTEXT);
    gc_collect_cycles();

    for ($iter = 0; $iter < ITERATIONS; $iter++) {
        $memBefore = memory_get_usage();
        $start = hrtime(true);

        for ($i = 0; $i < RUNS; $i++) {
            $t = $engine->render('page', CONTEXT);
        }

        $elapsed = (hrtime(true) - $start) / 1e9;
        $memPeak = memory_get_peak_usage();

        $result->add($elapsed, $memPeak);
        $result->output = $t;

        gc_collect_cycles();
    }

    return $result;
}

function benchPlatesRealistic(): BenchResult
{
    $result = new BenchResult('Plates');
    $engine = new League\Plates\Engine('./plates');

    // Warmup
    $engine->render('page', CONTEXT);
    gc_collect_cycles();

    for ($iter = 0; $iter < ITERATIONS; $iter++) {
        $memBefore = memory_get_usage();
        $start = hrtime(true);

        for ($i = 0; $i < RUNS; $i++) {
            $t = $engine->render('page', CONTEXT);
        }

        $elapsed = (hrtime(true) - $start) / 1e9;
        $memPeak = memory_get_peak_usage();

        $result->add($elapsed, $memPeak);
        $result->output = $t;

        gc_collect_cycles();
    }

    return $result;
}

function benchBoilerUnescapedRealistic(): BenchResult
{
    $result = new BenchResult('Boiler');
    $engine = Duon\Boiler\Engine::unescaped('./boiler');

    // Warmup
    $engine->render('pagenoescape', CONTEXT);
    gc_collect_cycles();

    for ($iter = 0; $iter < ITERATIONS; $iter++) {
        $memBefore = memory_get_usage();
        $start = hrtime(true);

        for ($i = 0; $i < RUNS; $i++) {
            $t = $engine->render('pagenoescape', CONTEXT);
        }

        $elapsed = (hrtime(true) - $start) / 1e9;
        $memPeak = memory_get_peak_usage();

        $result->add($elapsed, $memPeak);
        $result->output = $t;

        gc_collect_cycles();
    }

    return $result;
}

function fulltrim(string $text): string
{
    return trim(
        preg_replace(
            '/> </',
            '><',
            preg_replace(
                '/\s+/',
                ' ',
                preg_replace('/\n/', '', $text)
            )
        )
    );
}

function main(): void
{
    echo "Benchmark: " . number_format(RUNS) . " renders × " . ITERATIONS . " iterations\n";
    echo str_repeat("=", 70) . "\n\n";

    // Realistic benchmark (engine reused)
    echo "ESCAPED\n";
    echo str_repeat("-", 70) . "\n";

    $twig = benchTwigRealistic();
    $blade = benchBladeOneRealistic();
    $boiler = benchBoilerRealistic();

    $twig->print();
    $blade->print();
    $boiler->print();

    echo "\nUNESCAPED\n";
    echo str_repeat("-", 70) . "\n";

    $plates = benchPlatesRealistic();
    $boilerUn = benchBoilerUnescapedRealistic();

    $plates->print();
    $boilerUn->print();

    // Verify output consistency
    echo "\n" . str_repeat("=", 70) . "\n";
    echo "Output verification: ";

    $expected = fulltrim($plates->output);
    $allMatch = true;

    foreach ([$twig, $blade, $boiler, $boilerUn] as $result) {
        if (fulltrim($result->output) !== $expected) {
            echo "MISMATCH in {$result->name}!\n";
            $allMatch = false;
        }
    }

    if ($allMatch) {
        echo "All outputs match ✓\n";
    }
}

main();
