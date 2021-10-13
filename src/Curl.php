<?php

  declare(strict_types=1);

  namespace Neu\Curl;

  /**
   * @package Neu\Curl
   */
  class Curl {
    /** @var resource|\CurlHandle */
    private $handle;

    /**
     * Retrieve the native cURL handle.
     * @return \CurlHandle|resource
     */
    public function handle() {
      return $this->handle;
    }

    /**
     * @param resource|\CurlHandle|null $handle
     */
    public function __construct($handle = null) {
      $this->handle = $handle ?? curl_init();
    }

    public function __destruct() {
      $this->close();
    }

    /**
     * @link curl_setopt()
     * @param int $opt
     * @param mixed $value
     * @return bool
     */
    public function setOpt(int $opt, $value): bool {
      return curl_setopt($this->handle, $opt, $value);
    }

    /**
     * @link curl_error()
     * @return string
     */
    public function error(): string {
      return curl_error($this->handle);
    }

    /**
     * @link curl_errno()
     * @return int
     */
    public function errno(): int {
      return curl_errno($this->handle);
    }

    /**
     * @link curl_exec()
     * @return bool|string
     */
    public function exec() {
      return curl_exec($this->handle);
    }

    /**
     * @link curl_getinfo()
     * @param int|null $option
     * @return mixed
     */
    public function info(?int $option = null) {
      return curl_getinfo($this->handle, $option);
    }

    /**
     * @link curl_pause()
     * @param int $flags
     * @return int
     */
    public function pause(int $flags): int {
      return curl_pause($this->handle, $flags);
    }

    /**
     * @link curl_reset()
     */
    public function reset(): void {
      curl_reset($this->handle);
    }

    /**
     * @link curl_escape()
     * @param string $string
     * @return false|string
     */
    public function escape(string $string) {
      return curl_escape($this->handle, $string);
    }

    /**
     * @link curl_strerror()
     * @return string|null
     */
    public function strError(): ?string {
      return curl_strerror($this->errno());
    }

    /**
     * @link curl_close()
     */
    public function close(): void {
      curl_close($this->handle);
    }
  }
