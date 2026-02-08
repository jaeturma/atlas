<?php

namespace App\Services;

/**
 * ZKTeco Web API Handler for WL10 and modern devices
 * 
 * Supports HTTP/HTTPS REST API endpoints
 * Alternative to ADMS/PUSH protocol for modern ZKTeco devices
 */
class ZKTecoWebAPI
{
    private $ip;
    private $port;
    private $base_url;
    private $last_error = null;
    private $timeout = 10;
    private $username = 'admin';
    private $password = '123456';
    private $token = null;

    public function __construct($ip, $port = 8080, $timeout = 10)
    {
        $this->ip = $ip;
        $this->port = $port;
        $this->timeout = $timeout;
        
        // Try to determine the correct protocol and port
        $this->base_url = "http://{$ip}:{$port}";
    }

    /**
     * Authenticate with the device
     */
    public function authenticate()
    {
        try {
            // Try to login
            $response = $this->request('POST', '/api/login', [
                'username' => $this->username,
                'password' => $this->password,
            ]);

            if ($response && isset($response['token'])) {
                $this->token = $response['token'];
                return true;
            }

            $this->last_error = 'Authentication failed: Invalid credentials';
            return false;

        } catch (\Throwable $e) {
            $this->last_error = 'Authentication error: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Get device info
     */
    public function getDeviceInfo()
    {
        try {
            $response = $this->request('GET', '/api/device/info');
            return $response;
        } catch (\Throwable $e) {
            $this->last_error = 'Error getting device info: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Get device time
     */
    public function getTime()
    {
        try {
            $response = $this->request('GET', '/api/device/time');
            
            if ($response && isset($response['timestamp'])) {
                return $response['timestamp'];
            }
            
            if ($response && isset($response['time'])) {
                return $response['time'];
            }

            $this->last_error = 'Invalid time response from device';
            return false;

        } catch (\Throwable $e) {
            $this->last_error = 'Error getting device time: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Set device time
     */
    public function setTime($timestamp = null)
    {
        try {
            $timestamp = $timestamp ?? time();

            $response = $this->request('POST', '/api/device/time', [
                'timestamp' => $timestamp,
            ]);

            if ($response && isset($response['success']) && $response['success']) {
                return true;
            }

            $this->last_error = 'Failed to set device time';
            return false;

        } catch (\Throwable $e) {
            $this->last_error = 'Error setting device time: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Get attendance logs
     */
    public function getAttendanceLogs($start = null, $end = null)
    {
        try {
            $params = [];
            if ($start) {
                $params['start'] = is_numeric($start) ? $start : strtotime($start);
            }
            if ($end) {
                $params['end'] = is_numeric($end) ? $end : strtotime($end);
            }

            $query = !empty($params) ? '?' . http_build_query($params) : '';
            $response = $this->request('GET', '/api/attendance/logs' . $query);

            if ($response && isset($response['logs'])) {
                return $response['logs'];
            }

            $this->last_error = 'Invalid logs response from device';
            return false;

        } catch (\Throwable $e) {
            $this->last_error = 'Error getting attendance logs: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Clear attendance logs
     */
    public function clearLogs()
    {
        try {
            $response = $this->request('POST', '/api/attendance/clear');

            if ($response && isset($response['success']) && $response['success']) {
                return true;
            }

            $this->last_error = 'Failed to clear logs';
            return false;

        } catch (\Throwable $e) {
            $this->last_error = 'Error clearing logs: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Restart device
     */
    public function restart()
    {
        try {
            $response = $this->request('POST', '/api/device/restart');

            if ($response && isset($response['success']) && $response['success']) {
                return true;
            }

            $this->last_error = 'Failed to restart device';
            return false;

        } catch (\Throwable $e) {
            $this->last_error = 'Error restarting device: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Test connection to device
     */
    public function testConnection()
    {
        try {
            $response = $this->request('GET', '/api/device/ping');
            return $response !== false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Make HTTP request to device
     */
    private function request($method, $endpoint, $data = null)
    {
        try {
            $url = $this->base_url . $endpoint;
            
            $ch = curl_init();
            
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            // Set method
            if ($method === 'POST') {
                curl_setopt($ch, CURLOPT_POST, true);
                if ($data) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
            } elseif ($method === 'GET') {
                curl_setopt($ch, CURLOPT_HTTPGET, true);
            }

            // Add headers
            $headers = ['Content-Type: application/json'];
            if ($this->token) {
                $headers[] = 'Authorization: Bearer ' . $this->token;
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                $this->last_error = "HTTP Error: $error";
                return false;
            }

            if ($http_code >= 400) {
                $this->last_error = "HTTP Error: {$http_code}";
                return false;
            }

            if (empty($response)) {
                return true; // Success with no body
            }

            // Try to decode JSON response
            $decoded = json_decode($response, true);
            if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
                // Response is not JSON, return as-is
                return $response;
            }

            return $decoded;

        } catch (\Throwable $e) {
            $this->last_error = 'Request error: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Get last error
     */
    public function getLastError()
    {
        return $this->last_error;
    }

    /**
     * Try different ports for Web API
     */
    public static function findWebAPI($ip, $ports = [8080, 80, 8081, 8000, 9001])
    {
        foreach ($ports as $port) {
            $api = new self($ip, $port, 2);
            
            if ($api->testConnection()) {
                return $api;
            }
        }

        return null;
    }
}
