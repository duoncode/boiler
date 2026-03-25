# Boiler benchmark

This benchmark measures one feature-rich page render across Boiler, Twig,
BladeOne, and Plates. It is meant to approximate a realistic steady-state page
render and is used mainly internally to catch regressions, not to benchmark
every feature in isolation.

## What it covers

The benchmark renders one canonical catalog page with:

- layout inheritance
- partials and repeated partials inside loops
- sections or blocks for script output
- nested arrays, nested loops, and conditionals
- object-backed view data
- one iterator-backed collection
- escaped and unescaped Boiler renders

The script resets compiled template caches, warms them up, and verifies that all
engines produce equivalent output.

## How to read the results

Use the benchmark to answer a narrow question: did Boiler get slower on this
canonical page render?

Keep these limits in mind:

- results depend on PHP version, OPcache settings, hardware, and workload shape
- one benchmark cannot represent every template structure or application
  architecture
- the numbers are useful for internal regression checks and local comparisons,
  not as universal rankings or proofs that one engine always wins

## Run the benchmark

1. Install benchmark dependencies:

   ```bash
   composer install
   ```

2. Run the benchmark with the default settings:

   ```bash
   php run.php
   ```

3. Override the default run count and iteration count when you want a slower or
   deeper run:

   ```bash
   php run.php --runs=10000 --iterations=5
   ```

## Defaults

By default, the script runs:

- `1000` renders per engine
- `3` measured iterations

The memory column reports `peak+`, which is the additional peak memory used
within a measured iteration after warmup.
