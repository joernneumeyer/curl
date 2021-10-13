<?php

  declare(strict_types=1);

  namespace Neu\Curl;

  use InvalidArgumentException;

  /**
   * @package Neu\Curl;
   */
  class CurlPool {
    /** @var Curl[] */
    private $instances = [];

    private $handle;

    public function __construct(?MultiCurl $handle = null) {
      $this->handle = $handle ?? new MultiCurl();
      $this->setOpt(CURLMOPT_PIPELINING, CURLPIPE_MULTIPLEX);
    }

    public function setOpt($opt, $value): bool {
      return $this->handle->setOpt($opt, $value);
    }

    public function handle(): MultiCurl {
      return $this->handle;
    }

    public function addInstance(Curl $curl) {
      $this->handle->addHandle($curl);
    }

    /**
     * @param string $url
     * @param string $method
     * @param string $body
     * @return Curl
     */
    public function queue(string $url, string $method = 'GET', $body = ''): Curl {
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

    public function exec() {
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
