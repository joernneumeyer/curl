<?php

  declare(strict_types=1);

  namespace Neu\Curl;

  class MultiCurl {
    private $handle;
    private $subHandles = [];

    public function __construct($handle = null) {
      $this->handle = $handle ?? curl_multi_init();
    }

    public function __destruct() {
      $this->close();
    }

    public function close(): void {
      curl_multi_close($this->handle);
    }

    public function opt(int $opt, $value): bool {
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

    public function exec(): array {
      $stillRunning = 0;
      curl_multi_exec($this->handle, $stillRunning);
      while ($stillRunning) usleep(20000);
      $result = [];
      while (($buffer = curl_multi_info_read($this->handle)) !== false) {
        $result[] = $buffer;
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
