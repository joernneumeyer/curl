<?php

  declare(strict_types=1);

  namespace Neu\Curl;

  /**
   * @package Neu\Curl
   */
  class MultiCurl {
    /**
     * @var \CurlMultiHandle|resource
     */
    private $handle;
    /**
     * @var array<Curl>
     */
    private $subHandles = [];

    public const PARSE_BODY = 1;

    /**
     * @param null|resource|\CurlMultiHandle $handle
     */
    public function __construct($handle = null) {
      $this->handle = $handle ?? curl_multi_init();
    }

    /**
     * @return \CurlMultiHandle|resource
     */
    public function handle() {
      return $this->handle;
    }

    public function __destruct() {
      $this->close();
    }

    /**
     * @link curl_multi_close()
     */
    public function close(): void {
      curl_multi_close($this->handle);
    }

    /**
     * @link curl_multi_setopt()
     * @param int $opt
     * @param mixed $value
     * @return bool
     */
    public function setOpt(int $opt, $value): bool {
      return curl_multi_setopt($this->handle, $opt, $value);
    }

    /**
     * @link curl_multi_errno()
     * @return int
     */
    public function errno(): int {
      return curl_multi_errno($this->handle);
    }

    /**
     * @link curl_multi_strerror()
     * @return string|null
     */
    public function strError(): ?string {
      return curl_multi_strerror($this->errno());
    }

    /**
     * @link curl_multi_select()
     * @param float $timeout
     * @return int
     */
    public function select(float $timeout = 1.0): int {
      return curl_multi_select($this->handle, $timeout);
    }

    /**
     * Executes the underlying cURL multi handle.
     *
     * All sub-handles are added to and removed from the multi-handle inside this method.
     * if the $export parameter is set to @link MultiCurl::PARSE_BODY, all incoming JSON responses
     * will be parsed and then added to the result set.
     * All other responses are added as strings.
     * @param int $export
     * @return array<mixed>
     */
    public function exec(int $export = 0): array {
      foreach ($this->subHandles as $sh) {
        curl_multi_add_handle($this->handle, $sh->handle());
      }
      // https://www.php.net/manual/en/function.curl-multi-exec.php
      do {
        $status = curl_multi_exec($this->handle, $active);
        if ($active) {
          $this->select();
        }
      } while ($active && $status == CURLM_OK);
      $result = [];
      foreach($this->subHandles as $sh) {
        if ($export === self::PARSE_BODY) {
          $info = curl_getinfo($sh->handle());
          if ($info['content_type'] === 'application/json') {
            $result[] = json_decode(curl_multi_getcontent($sh->handle()), true);
            continue;
          }
        }
        $result[] = curl_multi_getcontent($sh->handle());
      }
      foreach ($this->subHandles as $sh) {
        curl_multi_remove_handle($this->handle, $sh->handle());
      }
      return $result;
    }

    /**
     * Add a sub-handle for later execution.
     * @param Curl $handle
     * @return void
     */
    public function addHandle(Curl $handle): void {
      $this->subHandles[] = $handle;
    }

    /**
     * Remove a sub-handle.
     * @param Curl $handle
     * @return void
     */
    public function removeHandle(Curl $handle): void {
      $index = array_search($handle, $this->subHandles);
      if ($index === false) {
        return;
      }
      array_splice($this->subHandles, $index, 1);
    }
  }
