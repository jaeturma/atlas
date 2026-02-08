<?php

namespace App\Services;

use CodingLibs\ZktecoPhp\Libs\ZKTeco as BaseZKTeco;

/**
 * Enhanced ZKTeco wrapper that handles socket function issues
 * particularly on Windows environments where socket functions might not be available
 */
class ZKTecoWrapper
{
    private $zk = null;
    private $ip;
    private $port;
    private $password;
    private $timeout;
    private $socket_available = false;
    private $last_error = null;

    public function __construct($ip, $port = 4370, $shouldPing = false, $timeout = 25, $password = 0)
    {
        $this->ip = $ip;
        $this->port = $port;
        $this->password = $password;
        $this->timeout = $timeout;
        $this->socket_available = extension_loaded('sockets');

        if ($this->socket_available) {
            try {
                // Try to create ZKTeco instance with socket functions
                $this->zk = new BaseZKTeco($ip, $port, $shouldPing, $timeout, $password);
            } catch (\Throwable $e) {
                // Socket functions available but creation failed
                $this->socket_available = false;
                $this->last_error = $e->getMessage();
                $this->zk = null;
            }
        }
    }

    /**
     * Connect to the device
     */
    public function connect()
    {
        if (!$this->zk) {
            $this->last_error = 'ZKTeco instance not initialized. Sockets extension may not be available.';
            return false;
        }

        try {
            $result = @$this->zk->connect();
            if (!$result) {
                $this->last_error = 'Failed to establish connection with device';
            }
            return $result;
        } catch (\Throwable $e) {
            $this->last_error = 'Connection error: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Get device time
     */
    public function getTime()
    {
        if (!$this->zk) {
            return false;
        }

        try {
            return @$this->zk->getTime();
        } catch (\Throwable $e) {
            $this->last_error = 'Failed to get device time: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Get last error message
     */
    public function getLastError()
    {
        return $this->last_error;
    }

    /**
     * Check if socket functions are available
     */
    public function isSocketAvailable()
    {
        return $this->socket_available;
    }

    /**
     * Get magic method calls
     */
    public function __call($method, $arguments)
    {
        if (!$this->zk) {
            return false;
        }

        try {
            return @$this->zk->{$method}(...$arguments);
        } catch (\Throwable $e) {
            $this->last_error = $method . ' error: ' . $e->getMessage();
            return false;
        }
    }
}
