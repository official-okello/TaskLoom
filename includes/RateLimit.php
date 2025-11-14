<?php
require_once __DIR__ . '/bootstrap.php';

class RateLimit {
    private const MAX_REQUESTS = 100; // 100 Maximum requests per window
    private const TIME_WINDOW = 3600; // Time window in seconds (1 hour)
    private $redis;
    
    public function __construct() {
        // Using file-based rate limiting
        if (!is_dir(__DIR__ . '/../cache')) {
            mkdir(__DIR__ . '/../cache', 0755, true);
        }
    }
    
    public function checkLimit(string $identifier): bool {
        $cacheFile = $this->getCacheFile($identifier);
        $current = $this->getCurrentRequests($cacheFile);
        
        if ($current['count'] >= self::MAX_REQUESTS) {
            if (time() - $current['timestamp'] < self::TIME_WINDOW) {
                return false; // Rate limit exceeded
            }
            // Reset if time window has passed
            $current = ['count' => 0, 'timestamp' => time()];
        }
        
        // Increment request count
        $current['count']++;
        $this->saveRequests($cacheFile, $current);
        
        return true;
    }
    
    private function getCacheFile(string $identifier): string {
        return __DIR__ . '/../cache/rate_' . md5($identifier) . '.json';
    }
    
    private function getCurrentRequests(string $cacheFile): array {
        if (!file_exists($cacheFile)) {
            return ['count' => 0, 'timestamp' => time()];
        }
        
        $data = json_decode(file_get_contents($cacheFile), true);
        return $data ?: ['count' => 0, 'timestamp' => time()];
    }
    
    private function saveRequests(string $cacheFile, array $data): void {
        file_put_contents($cacheFile, json_encode($data));
    }
}