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

    public function __construct() {
    }

    public function addInstance(Curl $curl) {
      $this->instances[] = $curl;
    }

    /**
     * @param string $url
     * @param string $method
     * @param string|array|null $body
     * @return Curl
     */
    public function queue(string $url, string $method = 'GET', $body = null): Curl {
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
      if ($body) {
        if ($method === 'GET') {
          throw new InvalidArgumentException('Cannot set a body on a GET request!');
        }
        $c->setOpt(CURLOPT_POSTFIELDS, $body);
      }
      $c->setOpt(CURLOPT_URL, $url);
      $c->setOpt(CURLOPT_RETURNTRANSFER, 1);
      $this->addInstance($c);
      return $c;
    }

    public function exec() {
      $multi = new MultiCurl();
      foreach ($this->instances as $instance) {
        $multi->addHandle($instance);
      }

      $result = $multi->exec();

      foreach ($this->instances as $instance) {
        $multi->removeHandle($instance);
      }

      return $result;
    }
  }
