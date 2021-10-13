# neu/curl
neu/curl is a helper library for PHP's cURL extension.
It provides object-oriented wrappers for native imperative code and an easy API to queue cURL calls, execute them in parallel, and get back the result as an array.

## Usage

### Wrappers
The classes `Neu\Curl\Curl` and `Neu\Curl\MultiCurl` are wrappers for the `curl_*` and `curl_multi_*` functions respectively.
The method names follow the names of the native functions, but make use of _camelCase_ instead of _snake\_case_.

### Neu\Curl\CurlPool
`Neu\Curl\CurlPool` allows you to easily queue cURL calls.
Calls are collected either through the `queue(string $url, string $method = 'GET', $body = '')` method or by adding a manually configured `Neu\Curl\Curl` instance via `addInstance(Curl $curl)`.

In order to get execute the requests and get their results, the `exec()` method is called.
Upon resolution of all requests, an array is returned, containing all responses, ordered by their respective request.

## License
neu/curl is available under the terms of the [GNU Lesser General Public License in version 3.0 or later](./LICENSE).
