<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    /**
     * Override cache and log directories to work around OneDrive file-locking issues.
     * Uses local temp directory for cache and a local directory for logs.
     */
    public function getCacheDir(): string
    {
        // Use Windows temp directory for cache to avoid OneDrive locks
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'symfony_cache_' . $this->getEnvironment();
    }

    public function getLogDir(): string
    {
        // Use a local directory outside OneDrive for logs
        // Falls back to var/log if temp directory is not writable
        $tempLogDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'symfony_logs';
        
        // Create directory if it doesn't exist
        if (!is_dir($tempLogDir)) {
            @mkdir($tempLogDir, 0777, true);
        }

        // Check if temp directory is writable, otherwise fallback to project directory
        if (is_writable($tempLogDir)) {
            return $tempLogDir;
        }

        // Last resort: use D:\symfony_logs or C:\symfony_logs
        $fallbackDir = 'D:\\symfony_logs';
        if (!is_dir($fallbackDir) || !is_writable($fallbackDir)) {
            $fallbackDir = 'C:\\symfony_logs';
        }

        if (!is_dir($fallbackDir)) {
            @mkdir($fallbackDir, 0777, true);
        }

        return $fallbackDir;
    }
}
