<?php
/**
 * Civil Registry Configuration File
 * 
 * This file loads environment variables and provides configuration
 * for the civil registry system.
 */

// Load environment variables from .env file if it exists
function loadEnv($path) {
    if (!file_exists($path)) {
        return false;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
        putenv(trim($name) . '=' . trim($value));
    }
    return true;
}

// Try to load .env file
if (!loadEnv(__DIR__ . '/.env')) {
    // If .env doesn't exist, set default values for development
    putenv('DB_HOST=localhost');
    putenv('DB_USERNAME=root');
    putenv('DB_PASSWORD=');
    putenv('DB_NAME=civ_reg');
    
    // Generate default encryption keys (WARNING: Change these in production!)
    if (!getenv('ENC_KEY')) {
        putenv('ENC_KEY=' . base64_encode(random_bytes(32)));
    }
    if (!getenv('LOOKUP_KEY')) {
        putenv('LOOKUP_KEY=' . base64_encode(random_bytes(32)));
    }
}

// Database configuration
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USERNAME', getenv('DB_USERNAME') ?: 'root');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'civ_reg');

// Security configuration
define('SESSION_LIFETIME', getenv('SESSION_LIFETIME') ?: 3600);
define('MAX_LOGIN_ATTEMPTS', getenv('MAX_LOGIN_ATTEMPTS') ?: 5);
define('LOCKOUT_DURATION', getenv('LOCKOUT_DURATION') ?: 900);

// Application configuration
define('APP_NAME', getenv('APP_NAME') ?: 'Civil Registry System');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost/civreg');
define('APP_ENV', getenv('APP_ENV') ?: 'development');
define('APP_DEBUG', getenv('APP_DEBUG') === 'true');

// Email configuration
define('SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp.gmail.com');
define('SMTP_PORT', getenv('SMTP_PORT') ?: 587);
define('SMTP_USERNAME', getenv('SMTP_USERNAME') ?: '');
define('SMTP_PASSWORD', getenv('SMTP_PASSWORD') ?: '');
define('SMTP_FROM_NAME', getenv('SMTP_FROM_NAME') ?: 'Civil Registry System');

// SMS configuration
define('SMS_API_KEY', getenv('SMS_API_KEY') ?: '');
define('SMS_SENDER_ID', getenv('SMS_SENDER_ID') ?: 'CIVREG');

// Error reporting based on environment
if (APP_ENV === 'development' && APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Session configuration removed to prevent header conflicts
// Session settings should be configured in individual files before session_start()
?>
