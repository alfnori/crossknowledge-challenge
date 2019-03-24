<?php

namespace CrossKnowledgeTest;

define('REDIS_SERVER', '127.0.0.1');
define('REDIS_SERVER_PORT', 6379);

class Redis
{
    private static $instance = null;

    /**
     * Get instance from Redis.
     *
     * @return \Redis
     */
    private function it()
    {
        if (self::$instance == null) {
            try {
                $redis = new \Redis();
                $redis->connect(REDIS_SERVER, REDIS_SERVER_PORT);
                $redis->ping();
                self::$instance = $redis;
            } catch (\Exception $e) {
                echo 'Failed to connect to a Redis server!'.PHP_EOL;
                throw $e;
            }
        }

        return self::$instance;
    }

    /**
     * Ping function.
     *
     * @return bool true if Redis was reached
     */
    public function ping()
    {
        try {
            $this->it()->ping();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Exists function.
     *
     * @param string $key
     *
     * @return bool True if at least one key is found
     */
    public function exists($key)
    {
        return $this->it()->exists($key) > 0;
    }

    /**
     * Get function. Retrieve value for key.
     *
     * @param string $key
     */
    public function get($key)
    {
        echo 'Retrieving key '.$key.PHP_EOL;

        return $this->it()->get($key);
    }

    /**
     * Set function, for the test proposal a random timeout will be used
     * to emulate cache behavior for the requests.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function set($key, $value)
    {
        $timeout = 5 * rand(1, 6);
        echo 'Caching '.$key.' for '.$timeout.' seconds'.PHP_EOL;

        return $this->it()->set($key, $value, $timeout);
    }

    /**
     * Dump all Redis database content.
     */
    public function dump()
    {
        $i = 0;
        $json = array();
        foreach ($this->it()->keys('*') as $key) {
            $data = array();
            $data['key'] = $key;
            $data['ttl'] = $this->it()->ttl($key);
            $data['value'] = \json_encode($this->it()->get($key));
            $json[$i] = $data;
            ++$i;
        }

        return $json;
    }
}

/**
 * SimpleJsonRequest class to retrieve a request data.
 */
class SimpleJsonRequest
{
    /**
     * MakeRequest function.
     *
     * @param string $method     The http method to be used
     * @param string $url        The url to look up for the request
     * @param array  $parameters An array with the request params to be used in query string
     * @param array  $data       An array with the request params to be used in body
     *
     * @return string|false The current result from request or false if an error
     */
    private static function makeRequest(string $method, string $url, array $parameters = null, array $data = null)
    {
        $opts = [
            'http' => [
                'method' => $method,
                'header' => 'Content-type: application/json',
                'content' => $data ? json_encode($data) : null,
            ],
        ];

        $url .= ($parameters ? '?'.http_build_query($parameters) : '');

        echo 'Executing a '.$method.' request to URL: '.$url.PHP_EOL;
        echo 'Using the following options: '.json_encode($opts).PHP_EOL;

        /*
         * For the cache proposal test, we will cache all request that are retrieved by
         * GET or when a POST is done with the param 'cache-it'.
         * In real world, we prefer to cache GET and in some especial cases a POST.
         */
        $inCache = false;
        $redis = new Redis();

        $willUseCache = $method == 'GET' || ($method == 'POST' && isset($parameters['cache-it']));
        $cachedRequestKey = 'CrossKnowledgeTest-'.\md5($method.$url.$opts['http']['content']);

        if ($willUseCache && $redis->ping()) {
            echo 'Verifying Cache, searching for key '.$cachedRequestKey.PHP_EOL;
            $inCache = $redis->exists($cachedRequestKey);
        }

        if (!$inCache) {
            echo 'Cache not hit, calling HTTP request'.PHP_EOL;
            $data = file_get_contents($url, false, stream_context_create($opts));
            if ($willUseCache) {
                echo 'Caching result for this request'.PHP_EOL;
                $redis->set($cachedRequestKey, $data);
            }
        } else {
            echo 'Cache hit, retrieving value'.PHP_EOL;
            $data = $redis->get($cachedRequestKey);
        }

        return $data;
    }

    /**
     * Get function.
     *
     * @param string $url        Request PATH
     * @param array  $parameters Request query string data
     *
     * @return mixed Decoded json from request result
     */
    public static function get(string $url, array $parameters = null)
    {
        return json_decode(self::makeRequest('GET', $url, $parameters));
    }

    /**
     * Post function.
     *
     * @param string $url        Request PATH
     * @param array  $parameters Request query string data
     * @param array  $data       Request body data
     *
     * @return mixed Decoded json from request result
     */
    public static function post(string $url, array $parameters = null, array $data)
    {
        return json_decode(self::makeRequest('POST', $url, $parameters, $data));
    }

    /**
     * Put function.
     *
     * @param string $url        Request PATH
     * @param array  $parameters Request query string data
     * @param array  $data       Request body data
     *
     * @return mixed Decoded json from request result
     */
    public static function put(string $url, array $parameters = null, array $data)
    {
        return json_decode(self::makeRequest('PUT', $url, $parameters, $data));
    }

    /**
     * Patch function.
     *
     * @param string $url        Request PATH
     * @param array  $parameters Request query string data
     * @param array  $data       Request body data
     *
     * @return mixed Decoded json from request result
     */
    public static function patch(string $url, array $parameters = null, array $data)
    {
        return json_decode(self::makeRequest('PATCH', $url, $parameters, $data));
    }

    /**
     * Delete function.
     *
     * @param string $url        Request PATH
     * @param array  $parameters Request query string data
     * @param array  $data       Request body data
     *
     * @return mixed Decoded json from request result
     */
    public static function delete(string $url, array $parameters = null, array $data = null)
    {
        return json_decode(self::makeRequest('DELETE', $url, $parameters, $data));
    }
}
