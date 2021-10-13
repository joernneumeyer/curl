<?php

  declare(strict_types=1);

  namespace Neu\Curl;

  class MultiCurl {
    private $handle;
    private $subHandles = [];

    public const PARSE_BODY = 1;

    public function __construct($handle = null) {
      $this->handle = $handle ?? curl_multi_init();
    }

    public function handle() {
      return $this->handle;
    }

    public function __destruct() {
      $this->close();
    }

    public function close(): void {
      curl_multi_close($this->handle);
    }

    public function setOpt(int $opt, $value): bool {
      return curl_multi_setopt($this->handle, $opt, $value);
    }

    public function errno(): int {
      return curl_multi_errno($this->handle);
    }

    public function content(): ?string {
      return curl_multi_getcontent($this->handle);
    }

    public function strError(): ?string {
      return curl_multi_strerror($this->errno());
    }

    public function select(float $timeout = 1.0): int {
      return curl_multi_select($this->handle, $timeout);
    }

    public function exec(int $export = 0): array {
      foreach ($this->subHandles as $sh) {
        curl_multi_add_handle($this->handle, $sh->handle());
      }
      // https://www.php.net/manual/en/function.curl-multi-exec.php
      do {
        $status = curl_multi_exec($this->handle, $active);
        if ($active) {
            curl_multi_select($this->handle);
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
     * @param Curl $handle
     * @return void
     */
    public function addHandle(Curl $handle): void {
      $this->subHandles[] = $handle;
    }

    /**
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
