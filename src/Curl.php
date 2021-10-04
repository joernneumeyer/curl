<?php

  declare(strict_types=1);

  namespace Neu\Curl;

  class Curl {
    private $handle;

    public function handle() {
      return $this->handle;
    }

    public function __construct($handle = null) {
      $this->handle = $handle ?? curl_init();
    }

    public function __destruct() {
      $this->close();
    }

    public function setOpt(int $opt, $value): bool {
      return curl_setopt($this->handle, $opt, $value);
    }

    public function error(): string {
      return curl_error($this->handle);
    }

    public function errno(): int {
      return curl_errno($this->handle);
    }

    public function exec() {
      return curl_exec($this->handle);
    }

    public function info(?int $option = null) {
      return curl_getinfo($this->handle, $option);
    }

    public function pause(int $flags): int {
      return curl_pause($this->handle, $flags);
    }

    public function reset(): void {
      curl_reset($this->handle);
    }

    public function escape(string $string) {
      return curl_escape($this->handle, $string);
    }

    public function strError(): ?string {
      return curl_strerror($this->errno());
    }

    public function close(): void {
      curl_close($this->handle);
    }
  }
