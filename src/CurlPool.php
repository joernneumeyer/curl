<?php

  declare(strict_types=1);

  namespace Neu\Curl;

  use InvalidArgumentException;

  /**
   * @package Neu\Curl
   */
  class CurlPool {
    /** @var Curl[] */
    private $instances = [];

    /**
     * @var MultiCurl
     */
    private $handle;

    /**
     * @param MultiCurl|null $handle
     */
    public function __construct(?MultiCurl $handle = null) {
      $this->handle = $handle ?? new MultiCurl();
      $this->setOpt(CURLMOPT_PIPELINING, CURLPIPE_MULTIPLEX);
    }

    /**
     * @link curl_multi_setopt()
     * @param int $opt
     * @param mixed $value
     * @return bool
     */
    public function setOpt(int $opt, $value): bool {
      return $this->handle->setOpt($opt, $value);
    }

    /**
     * Retrieve the underlying handle.
     * @return MultiCurl
     */
    public function handle(): MultiCurl {
      return $this->handle;
    }

    /**
     * Add a @link Curl instance for batch execution.
     * @param Curl $curl
     */
    public function addInstance(Curl $curl): void {
      $this->handle->addHandle($curl);
    }

    /**
     * Queue an HTTP call for later batch execution.
     * @param string $url Target URL.
     * @param string $method HTTP method, i.e. GET, PUT, or POST.
     * @param string $body Request body as text.
     * @return Curl
     */
    public function queue(string $url, string $method = 'GET', string $body = ''): Curl {
      $c = new Curl();
      $c->setOpt(CURLOPT_POST, 1);
      switch ($method) {
        case 'GET':
          break;
        case 'POST':
          $c->setOpt(CURLOPT_POST, 1);
          break;
        case 'PUT':
          $c->setOpt(CURLOPT_PUT, 1);
          break;
        default:
          throw new InvalidArgumentException('Cannot set HTTP method to "' . $method . '"! Choose GET, POST, or PUT.');
      }
      $c->setOpt(CURLOPT_POSTFIELDS, $body);
      $c->setOpt(CURLOPT_URL, $url);
      $c->setOpt(CURLOPT_RETURNTRANSFER, 1);
      $this->addInstance($c);
      return $c;
    }

    /**
     * Returns an array containing all responses from the registered requests.
     * @return array<mixed>
     */
    public function exec(): array {
      foreach ($this->instances as $instance) {
        $this->handle->addHandle($instance);
      }

      $result = $this->handle->exec(MultiCurl::PARSE_BODY);

      foreach ($this->instances as $instance) {
        $this->handle->removeHandle($instance);
      }

      return $result;
    }
  }
