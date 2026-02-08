<?php

namespace App\Services;

use App\Models\Device;

/**
 * Device Protocol Manager
 * 
 * Handles multi-protocol support for different ZKTeco device versions:
 * - ADMS/PUSH: Modern devices (ZKTeco WL10, etc.)
 * - ZKEM: Older devices (legacy ZKTeco models)
 * 
 * Features:
 * - Automatic protocol detection based on device model
 * - Protocol fallback mechanism
 * - Per-device protocol override capability
 */
class DeviceProtocolManager
{
    private $device;
    private $protocol_handler = null;
    private $protocol_type = null;
    private $last_error = null;

    // Supported protocols
    public const PROTOCOL_ADMS = 'adms';
    public const PROTOCOL_ZKEM = 'zkem';
    public const PROTOCOL_WEBAPI = 'webapi';
    public const PROTOCOL_NGTECO = 'ngteco';

    // Device model to protocol mapping
    private const DEVICE_PROTOCOL_MAP = [
        'WL10' => self::PROTOCOL_ADMS,
        'WL20' => self::PROTOCOL_ADMS,
        'WL30' => self::PROTOCOL_ADMS,
        'WL40' => self::PROTOCOL_ADMS,
        'WL50' => self::PROTOCOL_ADMS,
        'K21' => self::PROTOCOL_ZKEM,
        'K40' => self::PROTOCOL_ZKEM,
        'K50' => self::PROTOCOL_ZKEM,
        'K60' => self::PROTOCOL_ZKEM,
        'U100' => self::PROTOCOL_ZKEM,
        'U200' => self::PROTOCOL_ZKEM,
        'iClock' => self::PROTOCOL_ZKEM,
        'LX17' => self::PROTOCOL_ZKEM,
        'P160' => self::PROTOCOL_ZKEM,
        'NGTECO' => self::PROTOCOL_NGTECO,
    ];

    public function __construct(Device $device)
    {
        $this->device = $device;
    }

    /**
     * Detect and initialize the appropriate protocol handler
     */
    public function initialize()
    {
        $protocol = $this->detectProtocol();

        switch ($protocol) {
            case self::PROTOCOL_ADMS:
                $this->protocol_handler = new ADMSProtocol(
                    $this->device->ip_address,
                    $this->device->port ?? 4370,
                    25,
                    0
                );
                $this->protocol_type = self::PROTOCOL_ADMS;
                break;

            case self::PROTOCOL_NGTECO:
                $this->protocol_handler = new NgtecoProtocol(
                    $this->device->ip_address,
                    $this->device->port ?? 4370,
                    false,
                    25,
                    0
                );
                $this->protocol_type = self::PROTOCOL_NGTECO;
                break;

            case self::PROTOCOL_ZKEM:
            default:
                $this->protocol_handler = new ZKTecoWrapper(
                    $this->device->ip_address,
                    $this->device->port ?? 4370,
                    false,
                    25,
                    0
                );
                $this->protocol_type = self::PROTOCOL_ZKEM;
                break;
        }

        return $this;
    }

    /**
     * Detect protocol based on device model or explicit configuration
     */
    public function detectProtocol()
    {
        // First priority: Explicit protocol setting on device (but not 'auto')
        if ($this->device->protocol && $this->device->protocol !== 'auto') {
            return $this->device->protocol;
        }

        // Second priority: Device model mapping
        if ($this->device->model) {
            foreach (self::DEVICE_PROTOCOL_MAP as $model_pattern => $protocol) {
                if (stripos($this->device->model, $model_pattern) !== false) {
                    return $protocol;
                }
            }
        }

        // Default: Try ADMS first (newer), fallback to ZKEM (legacy)
        return self::PROTOCOL_ADMS;
    }

    /**
     * Connect to device using the appropriate protocol
     */
    public function connect()
    {
        if (!$this->protocol_handler) {
            $this->initialize();
        }

        try {
            // Attempt connection with detected protocol
            if (@$this->protocol_handler->connect()) {
                return [
                    'success' => true,
                    'protocol' => $this->protocol_type,
                    'message' => "Connected using {$this->protocol_type} protocol",
                ];
            }

            $error = $this->protocol_handler->getLastError() ?? 'Unknown error';

            // If ADMS fails, try ZKEM as fallback
            if ($this->protocol_type === self::PROTOCOL_ADMS) {
                \Log::warning("ADMS protocol failed for device {$this->device->id}: {$error}. Trying ZKEM fallback.");
                
                $this->protocol_handler = new ZKTecoWrapper(
                    $this->device->ip_address,
                    $this->device->port ?? 4370,
                    false,
                    25,
                    0
                );
                $this->protocol_type = self::PROTOCOL_ZKEM;

                if (@$this->protocol_handler->connect()) {
                    return [
                        'success' => true,
                        'protocol' => self::PROTOCOL_ZKEM,
                        'message' => "ADMS failed, connected using ZKEM fallback protocol",
                        'fallback' => true,
                    ];
                }
                
                $zkem_error = $this->protocol_handler->getLastError() ?? 'Unknown error';
                
                // If ZKEM also fails, try Web API as final fallback
                \Log::warning("ZKEM protocol also failed for device {$this->device->id}: {$zkem_error}. Trying Web API fallback.");
                
                $webapi = ZKTecoWebAPI::findWebAPI($this->device->ip_address);
                
                if ($webapi) {
                    if (@$webapi->authenticate()) {
                        $this->protocol_handler = $webapi;
                        $this->protocol_type = self::PROTOCOL_WEBAPI;
                        
                        return [
                            'success' => true,
                            'protocol' => self::PROTOCOL_WEBAPI,
                            'message' => "ADMS and ZKEM failed, connected using Web API fallback",
                            'fallback' => true,
                        ];
                    }
                }
            }

            // All protocols failed
            $this->last_error = "Failed to connect with {$this->protocol_type} protocol: {$error}";
            return [
                'success' => false,
                'protocol' => null,
                'error' => $this->last_error,
            ];

        } catch (\Exception $e) {
            $this->last_error = "Connection exception: " . $e->getMessage();
            \Log::error("Device protocol connection exception: " . $e->getMessage());
            return [
                'success' => false,
                'protocol' => null,
                'error' => $this->last_error,
            ];
        }
    }

    /**
     * Get device time
     */
    public function getTime()
    {
        if (!$this->protocol_handler) {
            $this->last_error = 'Protocol handler not initialized';
            return false;
        }

        try {
            $result = $this->protocol_handler->getTime();

            if ($result === false) {
                $this->last_error = $this->protocol_handler->getLastError() ?? 'Failed to get device time';
                return false;
            }

            return $result;

        } catch (\Throwable $e) {
            $this->last_error = "Error getting time: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Set device time
     */
    public function setTime($timestamp = null)
    {
        if (!$this->protocol_handler) {
            $this->last_error = 'Protocol handler not initialized';
            return false;
        }

        try {
            $timestamp = $timestamp ?? time();

            // ADMS protocol has setTime, ZKEM wrapper might not
            if (method_exists($this->protocol_handler, 'setTime')) {
                return $this->protocol_handler->setTime($timestamp);
            }

            $this->last_error = 'setTime not supported by current protocol handler';
            return false;

        } catch (\Throwable $e) {
            $this->last_error = "Error setting time: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Get device status
     */
    public function getStatus()
    {
        if (!$this->protocol_handler) {
            $this->last_error = 'Protocol handler not initialized';
            return false;
        }

        try {
            // Different signature for different protocol handlers
            if (method_exists($this->protocol_handler, 'getStatus')) {
                return $this->protocol_handler->getStatus();
            }

            $this->last_error = 'getStatus not supported by current protocol handler';
            return false;

        } catch (\Throwable $e) {
            $this->last_error = "Error getting status: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Get current protocol type
     */
    public function getProtocolType()
    {
        return $this->protocol_type;
    }

    /**
     * Get protocol handler
     */
    public function getHandler()
    {
        return $this->protocol_handler;
    }

    /**
     * Get last error
     */
    public function getLastError()
    {
        return $this->last_error;
    }

    /**
     * Check if handler is connected
     */
    public function isConnected()
    {
        if (!$this->protocol_handler) {
            return false;
        }

        if (method_exists($this->protocol_handler, 'isConnected')) {
            return $this->protocol_handler->isConnected();
        }

        return false;
    }


    /**
     * Disconnect
     */
    public function disconnect()
    {
        if ($this->protocol_handler) {
            if (method_exists($this->protocol_handler, 'disconnect')) {
                $this->protocol_handler->disconnect();
            }
        }

        return true;
    }

    /**
     * Get attendance logs from device
     * 
     * For ZKEM devices, this delegates directly to the protocol handler.
     * For ADMS devices (WL10, etc.), this attempts ZKEM as a fallback since
     * some ADMS devices support ZKEM protocol for backward compatibility.
     */
    public function getAttendances($callback = null)
    {
        if (!$this->protocol_handler) {
            $this->last_error = 'Protocol handler not initialized';
            return false;
        }

        try {
            // Check if current handler supports getAttendances
            if (method_exists($this->protocol_handler, 'getAttendances')) {
                return $this->protocol_handler->getAttendances($callback);
            }

            // For ADMS devices, try falling back to ZKEM protocol
            if ($this->protocol_type === self::PROTOCOL_ADMS) {
                $this->last_error = 'ADMS protocol does not support getAttendances. Attempting ZKEM fallback...';
                \Log::info('ADMS device attempting ZKEM fallback for attendance logs', [
                    'device_ip' => $this->device->ip_address,
                    'device_model' => $this->device->model,
                ]);

                // Disconnect from ADMS and try ZKEM
                $this->disconnect();

                // Create ZKEM handler as fallback
                $zkem_handler = new ZKTecoWrapper(
                    $this->device->ip_address,
                    $this->device->port ?? 4370,
                    false,
                    25,
                    0
                );

                if ($zkem_handler->connect()) {
                    $result = $zkem_handler->getAttendances($callback);
                    $zkem_handler->disconnect();
                    return $result;
                } else {
                    $this->last_error = 'ZKEM fallback also failed: ' . $zkem_handler->getLastError();
                    return false;
                }
            }

            $this->last_error = 'getAttendances not supported by current protocol handler';
            return false;

        } catch (\Throwable $e) {
            $this->last_error = "Error getting attendances: " . $e->getMessage();
            return false;
        }
    }
}

