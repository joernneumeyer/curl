<?php

  require_once 'vendor/autoload.php';

  const API_ENDPOINT    = 'http://127.0.0.1:8833/dummy-api.php';
  const WARMUP_COUNT    = 50;
  const BENCHMARK_COUNT = 5000;
  const PROGRESS_MARKER = 200;

  echo 'warming up... ';
  for ($i = 0; $i < WARMUP_COUNT; ++$i) {
    file_get_contents(API_ENDPOINT);
  }
  echo 'done' . PHP_EOL;

  echo 'starting baseline benchmark... ' . PHP_EOL;
  $baseStart = microtime(true);
  $result = [];
  for ($i = 0; $i < BENCHMARK_COUNT; ++$i) {
    $c = curl_init();
    curl_setopt($c, CURLOPT_URL, API_ENDPOINT);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
    curl_exec($c);
    $result[] = curl_getinfo($c);
    curl_close($c);
    if ($i % PROGRESS_MARKER === 0 && $i) {
      $progress = 100.0 * $i / BENCHMARK_COUNT;
      echo "\r$progress%";
    }
  }
  $baseEnd = microtime(true);
  echo "\rdone" . PHP_EOL;

  echo 'starting parallel benchmark... ' . PHP_EOL;
  $parallelStart = microtime(true);
  $pool = new \Neu\Curl\CurlPool();
  for ($i = 0; $i < BENCHMARK_COUNT; ++$i) {
    $pool->queue(API_ENDPOINT);
    if ($i % PROGRESS_MARKER === 0 && $i) {
      $progress = 100.0 * $i / BENCHMARK_COUNT;
      echo "\r$progress%";
    }
  }
  $response = $pool->exec();
  $parallelEnd = microtime(true);
  echo "\rdone" . PHP_EOL;

  $baseTime = round($baseEnd - $baseStart, 3);
  $parallelTime = round($parallelEnd - $parallelStart, 3);
  $improvement = round($baseTime / $parallelTime * 100 - 100, 3);
  echo "base time: {$baseTime}s; parallel time: {$parallelTime}s; improvement: {$improvement}%\r\n";
