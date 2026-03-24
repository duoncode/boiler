# Boiler benchmark

This benchmark measures one feature-rich page render across Boiler, Twig,
BladeOne, and Plates. Use it to catch regressions in a realistic steady-state
render, not to benchmark every feature in isolation.

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
