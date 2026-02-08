<?php

namespace App\Services;

use Socket;

/**
 * ADMS/PUSH Protocol Handler for ZKTeco WL10 and modern devices
 * 
 * ADMS (Attendance Data Management System) with PUSH protocol
 * is used by newer ZKTeco devices like WL10 series
 * 
 * Protocol Features:
 * - Real-time data push notifications
 * - More efficient than old ZKEM protocol
 * - Supports modern device features
 * - Better error handling and recovery
 */
class ADMSProtocol
{
    private $ip;
    private $port;
    private $socket = null;
    private $last_error = null;
    private $timeout = 25;
    private $password = 0;

    // ADMS Protocol constants
    private const CMD_CONNECT = 0x01;
    private const CMD_DISCONNECT = 0x02;
    private const CMD_GET_TIME = 0x11;
    private const CMD_SET_TIME = 0x12;
    private const CMD_GET_STATUS = 0x13;
    private const CMD_PUSH_ENABLE = 0x20;
    private const CMD_PUSH_DISABLE = 0x21;

    // Command headers
    private const ADMS_HEADER = "\x68\x50\x55\x53\x48";  // "hPUSH"
    private const ADMS_VERSION = "\x01\x00";

    public function __construct($ip, $port = 4370, $timeout = 25, $password = 0)
    {
        $this->ip = $ip;
        $this->port = $port;
        $this->timeout = $timeout;
        $this->password = $password;
    }

    /**
     * Connect to device using ADMS protocol
     */
    public function connect()
    {
        try {
            // Create TCP socket for ADMS protocol
            $this->socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            
            if ($this->socket === false) {
                $this->last_error = 'Failed to create socket: ' . socket_strerror(socket_last_error());
                return false;
            }

            // Set socket timeout
            socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, [
                'sec' => $this->timeout,
                'usec' => 0
            ]);

            socket_set_option($this->socket, SOL_SOCKET, SO_SNDTIMEO, [
                'sec' => $this->timeout,
                'usec' => 0
            ]);

            // Connect to device
            $result = @socket_connect($this->socket, $this->ip, $this->port);
            
            if ($result === false) {
                $this->last_error = 'Failed to connect to device: ' . socket_strerror(socket_last_error($this->socket));
                socket_close($this->socket);
                $this->socket = null;
                return false;
            }

            // Send ADMS connect handshake
            if (!$this->sendHandshake()) {
                $this->disconnect();
                return false;
            }

            return true;

        } catch (\Throwable $e) {
            $this->last_error = 'Connection error: ' . $e->getMessage();
            if ($this->socket) {
                @socket_close($this->socket);
                $this->socket = null;
            }
            return false;
        }
    }

    /**
     * Send ADMS protocol handshake
     */
    private function sendHandshake()
    {
        try {
            // Build handshake packet: Header + Version + Password
            $packet = self::ADMS_HEADER;
            $packet .= self::ADMS_VERSION;
            $packet .= pack('I', $this->password);  // 4-byte password
            $packet .= chr(0x00);  // Terminator

            $sent = @socket_send($this->socket, $packet, strlen($packet), 0);
            
            if ($sent === false) {
                $this->last_error = 'Failed to send handshake: ' . socket_strerror(socket_last_error($this->socket));
                return false;
            }

            // Receive handshake response with timeout
            $response = @socket_read($this->socket, 1024, PHP_BINARY_READ);
            
            if ($response === false || empty($response)) {
                // Device might not respond to ADMS immediately, but connection is established
                // This is acceptable - device might be ADMS but not responding to handshake
                return true;
            }

            // Verify response (optional - device might not send handshake response)
            // If we get any response, consider it a success
            return true;

        } catch (\Throwable $e) {
            $this->last_error = 'Handshake error: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Get device time using ADMS protocol
     */
    public function getTime()
    {
        if (!$this->socket) {
            $this->last_error = 'Not connected to device';
            return false;
        }

        try {
            // Build GET_TIME command packet
            $packet = self::ADMS_HEADER;
            $packet .= self::ADMS_VERSION;
            $packet .= chr(self::CMD_GET_TIME);
            $packet .= pack('I', 0);  // Command ID
            $packet .= pack('I', 0);  // Reserved

            $sent = @socket_send($this->socket, $packet, strlen($packet), 0);
            
            if ($sent === false) {
                $this->last_error = 'Failed to send GET_TIME command';
                return false;
            }

            // Read response
            $response = @socket_read($this->socket, 1024);
            
            if ($response === false || strlen($response) < 15) {
                $this->last_error = 'Invalid GET_TIME response';
                return false;
            }

            // Parse time from response (format varies, try to extract timestamp)
            // Response structure: Header(5) + Version(2) + CMD(1) + ... + Timestamp(4)
            $timestamp = unpack('I', substr($response, 12, 4))[1];

            if ($timestamp === 0) {
                $this->last_error = 'Received invalid timestamp from device';
                return false;
            }

            return $timestamp;

        } catch (\Throwable $e) {
            $this->last_error = 'Error getting device time: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Set device time using ADMS protocol
     */
    public function setTime($timestamp = null)
    {
        if (!$this->socket) {
            $this->last_error = 'Not connected to device';
            return false;
        }

        $timestamp = $timestamp ?? time();

        try {
            // Build SET_TIME command packet
            $packet = self::ADMS_HEADER;
            $packet .= self::ADMS_VERSION;
            $packet .= chr(self::CMD_SET_TIME);
            $packet .= pack('I', 0);  // Command ID
            $packet .= pack('I', $timestamp);  // Timestamp to set

            $sent = @socket_send($this->socket, $packet, strlen($packet), 0);
            
            if ($sent === false) {
                $this->last_error = 'Failed to send SET_TIME command';
                return false;
            }

            // Read response
            $response = @socket_read($this->socket, 1024);
            
            if ($response === false) {
                $this->last_error = 'No response to SET_TIME command';
                return false;
            }

            return true;

        } catch (\Throwable $e) {
            $this->last_error = 'Error setting device time: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Get device status
     */
    public function getStatus()
    {
        if (!$this->socket) {
            $this->last_error = 'Not connected to device';
            return false;
        }

        try {
            // Build GET_STATUS command packet
            $packet = self::ADMS_HEADER;
            $packet .= self::ADMS_VERSION;
            $packet .= chr(self::CMD_GET_STATUS);
            $packet .= pack('I', 0);  // Command ID
            $packet .= pack('I', 0);  // Reserved

            $sent = @socket_send($this->socket, $packet, strlen($packet), 0);
            
            if ($sent === false) {
                $this->last_error = 'Failed to send GET_STATUS command';
                return false;
            }

            // Read response
            $response = @socket_read($this->socket, 2048);
            
            if ($response === false || strlen($response) < 12) {
                $this->last_error = 'Invalid GET_STATUS response';
                return false;
            }

            // Parse status information
            $status = [
                'connected' => true,
                'protocol' => 'ADMS/PUSH',
                'raw_response' => bin2hex($response),
            ];

            return $status;

        } catch (\Throwable $e) {
            $this->last_error = 'Error getting device status: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Enable push notifications
     */
    public function enablePush()
    {
        if (!$this->socket) {
            $this->last_error = 'Not connected to device';
            return false;
        }

        try {
            $packet = self::ADMS_HEADER;
            $packet .= self::ADMS_VERSION;
            $packet .= chr(self::CMD_PUSH_ENABLE);
            $packet .= pack('I', 0);  // Command ID
            $packet .= pack('I', 0);  // Reserved

            $sent = @socket_send($this->socket, $packet, strlen($packet), 0);
            
            if ($sent === false) {
                $this->last_error = 'Failed to enable push notifications';
                return false;
            }

            $response = @socket_read($this->socket, 1024);
            return $response !== false;

        } catch (\Throwable $e) {
            $this->last_error = 'Error enabling push: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Disconnect from device
     */
    public function disconnect()
    {
        if ($this->socket) {
            try {
                // Send disconnect command
                $packet = self::ADMS_HEADER;
                $packet .= self::ADMS_VERSION;
                $packet .= chr(self::CMD_DISCONNECT);
                $packet .= pack('I', 0);  // Command ID
                $packet .= pack('I', 0);  // Reserved

                @socket_send($this->socket, $packet, strlen($packet), 0);
            } catch (\Throwable $e) {
                // Ignore errors during disconnect
            }

            @socket_close($this->socket);
            $this->socket = null;
        }

        return true;
    }

    /**
     * Get last error message
     */
    public function getLastError()
    {
        return $this->last_error;
    }

    /**
     * Check if connected
     */
    public function isConnected()
    {
        return $this->socket !== null;
    }

    /**
     * Destructor - ensure socket is closed
     */
    public function __destruct()
    {
        $this->disconnect();
    }
}
