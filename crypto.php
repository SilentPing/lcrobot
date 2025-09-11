<?php
/**
 * Civil Registry Encryption Helper Functions
 * 
 * This file provides encryption and decryption functions for PII data
 * in the civil registry system. It uses AES-256-GCM for authenticated encryption.
 */

/**
 * Get encryption key from environment
 * @param string $name Key name (ENC_KEY or LOOKUP_KEY)
 * @return string Binary key
 * @throws RuntimeException if key is missing or invalid
 */
function getEnvKey(string $name): string {
    $val = getenv($name);
    if (!$val) { 
        throw new RuntimeException("Environment variable $name is missing. Please set it in your .env file."); 
    }
    $key = base64_decode($val, true);
    if ($key === false || strlen($key) !== 32) { 
        throw new RuntimeException("Environment variable $name is invalid. Must be base64-encoded 32-byte key."); 
    }
    return $key;
}

/**
 * Normalize text for consistent encryption and searching
 * @param string|null $value Input text
 * @return string Normalized text
 */
function normalizeText(?string $value): string {
    if ($value === null) return '';
    $value = trim(mb_strtolower($value, 'UTF-8'));
    $value = preg_replace('/\s+/', ' ', $value);
    return $value;
}

/**
 * Encrypt a field using AES-256-GCM
 * @param string $plaintext Text to encrypt
 * @return string Base64-encoded encrypted data (iv|tag|ciphertext)
 * @throws RuntimeException if encryption fails
 */
function encryptField(string $plaintext): string {
    $key = getEnvKey('ENC_KEY');
    $iv = random_bytes(12); // 12 bytes for GCM
    $cipher = 'aes-256-gcm';
    $tag = '';
    
    $ciphertext = openssl_encrypt($plaintext, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag);
    if ($ciphertext === false) { 
        throw new RuntimeException('Encryption failed. Check your encryption key.'); 
    }
    
    // Store as base64(iv|tag|ciphertext)
    return base64_encode($iv . $tag . $ciphertext);
}

/**
 * Decrypt a field using AES-256-GCM
 * @param string $b64 Base64-encoded encrypted data
 * @return string Decrypted plaintext
 * @throws RuntimeException if decryption fails
 */
function decryptField(string $b64): string {
    $key = getEnvKey('ENC_KEY');
    $raw = base64_decode($b64, true);
    if ($raw === false || strlen($raw) < 12 + 16) { 
        throw new RuntimeException('Invalid encrypted data format.'); 
    }
    
    $iv = substr($raw, 0, 12);
    $tag = substr($raw, 12, 16);
    $ciphertext = substr($raw, 28);
    
    $plain = openssl_decrypt($ciphertext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
    if ($plain === false) { 
        throw new RuntimeException('Decryption failed. Data may be corrupted or key may be wrong.'); 
    }
    
    return $plain;
}

/**
 * Generate a search token for exact-match lookups
 * @param string $value Value to tokenize
 * @return string HMAC-SHA256 token (hex string)
 */
function tokenForLookup(string $value): string {
    $key = getEnvKey('LOOKUP_KEY');
    return hash_hmac('sha256', $value, $key);
}

/**
 * Encrypt and tokenize a PII field for database storage
 * @param string $plaintext Original text
 * @return array ['encrypted' => string, 'token' => string]
 */
function encryptAndTokenize(string $plaintext): array {
    $normalized = normalizeText($plaintext);
    return [
        'encrypted' => encryptField($plaintext),
        'token' => tokenForLookup($normalized)
    ];
}

/**
 * Decrypt multiple fields from database row
 * @param array $row Database row
 * @param array $fields Array of field names to decrypt
 * @return array Decrypted fields
 */
function decryptFields(array $row, array $fields): array {
    $decrypted = [];
    foreach ($fields as $field) {
        $encField = $field . '_enc';
        if (isset($row[$encField]) && !empty($row[$encField])) {
            try {
                $decrypted[$field] = decryptField($row[$encField]);
            } catch (Exception $e) {
                // Log error but don't break the application
                error_log("Failed to decrypt field $field: " . $e->getMessage());
                $decrypted[$field] = '[ENCRYPTED]';
            }
        } else {
            $decrypted[$field] = '';
        }
    }
    return $decrypted;
}

/**
 * Generate search tokens for multiple fields
 * @param array $fields Array of field names and values ['field' => 'value']
 * @return array Array of tokens ['field_tok' => 'token']
 */
function generateSearchTokens(array $fields): array {
    $tokens = [];
    foreach ($fields as $field => $value) {
        $tokens[$field . '_tok'] = tokenForLookup(normalizeText($value));
    }
    return $tokens;
}

/**
 * Check if OpenSSL supports required ciphers
 * @return bool True if supported
 */
function checkEncryptionSupport(): bool {
    return extension_loaded('openssl') && 
           in_array('aes-256-gcm', openssl_get_cipher_methods());
}

/**
 * Generate new encryption keys (for setup)
 * @return array ['ENC_KEY' => string, 'LOOKUP_KEY' => string]
 */
function generateNewKeys(): array {
    if (!checkEncryptionSupport()) {
        throw new RuntimeException('OpenSSL extension not available or AES-256-GCM not supported');
    }
    
    return [
        'ENC_KEY' => base64_encode(random_bytes(32)),
        'LOOKUP_KEY' => base64_encode(random_bytes(32))
    ];
}
?>
