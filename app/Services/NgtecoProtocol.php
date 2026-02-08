<?php

namespace App\Services;

/**
 * NGTECO LAN protocol handler
 *
 * NGTECO devices expose a LAN SDK compatible with ZKEM-style commands.
 * This wrapper leverages the existing ZKTecoWrapper for socket-based
 * attendance retrieval while keeping a dedicated protocol type for
 * routing and future customization.
 */
class NgtecoProtocol extends ZKTecoWrapper
{
    public function __construct(string $ip, int $port = 4370, bool $shouldPing = false, int $timeout = 25, int $password = 0)
    {
        parent::__construct($ip, $port, $shouldPing, $timeout, $password);
    }
}
