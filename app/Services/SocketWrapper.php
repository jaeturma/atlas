<?php

namespace App\Services;

/**
 * Wrapper class to safely handle PHP socket functions
 * This addresses issues where socket_create() may not be available
 */
class SocketWrapper
{
    /**
     * Create a socket resource
     * 
     * @param int $domain
     * @param int $type
     * @param int $protocol
     * @return resource|false
     */
    public static function create($domain, $type, $protocol)
    {
        if (!extension_loaded('sockets')) {
            return false;
        }

        // Use function_exists to check if the function is available
        if (!function_exists('socket_create')) {
            return false;
        }

        try {
            // Ensure socket functions are in global namespace
            return \socket_create($domain, $type, $protocol);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Set socket option
     * 
     * @param resource $socket
     * @param int $level
     * @param int $optname
     * @param mixed $optval
     * @return bool
     */
    public static function setOption($socket, $level, $optname, $optval)
    {
        if (!function_exists('socket_set_option')) {
            return false;
        }

        try {
            return \socket_set_option($socket, $level, $optname, $optval);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Send data via socket
     * 
     * @param resource $socket
     * @param string $buf
     * @param int $len
     * @param int $flags
     * @param string $addr
     * @param int $port
     * @return int|false
     */
    public static function sendTo($socket, $buf, $len, $flags, $addr, $port)
    {
        if (!function_exists('socket_sendto')) {
            return false;
        }

        try {
            return \socket_sendto($socket, $buf, $len, $flags, $addr, $port);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Receive data from socket
     * 
     * @param resource $socket
     * @param string &$buf
     * @param int $len
     * @param int $flags
     * @param string &$name
     * @param int &$port
     * @return int|false
     */
    public static function recvFrom($socket, &$buf, $len, $flags, &$name, &$port)
    {
        if (!function_exists('socket_recvfrom')) {
            return false;
        }

        try {
            return \socket_recvfrom($socket, $buf, $len, $flags, $name, $port);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Close socket
     * 
     * @param resource $socket
     * @return void
     */
    public static function close($socket)
    {
        if (function_exists('socket_close')) {
            try {
                \socket_close($socket);
            } catch (\Exception $e) {
                // Silently fail
            }
        }
    }

    /**
     * Check if sockets extension is available
     * 
     * @return bool
     */
    public static function isAvailable()
    {
        return extension_loaded('sockets') && function_exists('socket_create');
    }
}
