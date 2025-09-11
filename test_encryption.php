<?php
/**
 * Test Encryption System
 * 
 * This script tests the encryption system without requiring authentication.
 * Use this to verify everything is working before testing the forms.
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Civil Registry Encryption Test</h1>";

try {
    // Load configuration and encryption functions
    require_once __DIR__ . '/config.php';
    require_once __DIR__ . '/crypto.php';
    
    echo "<h2>✓ Configuration and encryption functions loaded</h2>";
    
    // Test encryption support
    if (checkEncryptionSupport()) {
        echo "<h2>✓ OpenSSL support verified</h2>";
    } else {
        throw new Exception("OpenSSL not supported");
    }
    
    // Test encryption/decryption
    $test_data = "John Doe";
    $encrypted = encryptField($test_data);
    $decrypted = decryptField($encrypted);
    
    if ($decrypted === $test_data) {
        echo "<h2>✓ Encryption/Decryption test passed</h2>";
        echo "<p>Original: $test_data</p>";
        echo "<p>Encrypted: " . substr($encrypted, 0, 50) . "...</p>";
        echo "<p>Decrypted: $decrypted</p>";
    } else {
        throw new Exception("Encryption/Decryption test failed");
    }
    
    // Test token generation
    $token = tokenForLookup($test_data);
    echo "<h2>✓ Search token generated</h2>";
    echo "<p>Token: $token</p>";
    
    // Test database connection
    require_once __DIR__ . '/db.php';
    if ($conn && !$conn->connect_error) {
        echo "<h2>✓ Database connection successful</h2>";
        
        // Test if encrypted columns exist
        $result = $conn->query("SHOW COLUMNS FROM birthceno_tbl LIKE '%_enc'");
        if ($result && $result->num_rows > 0) {
            echo "<h2>✓ Encrypted columns exist in database</h2>";
            echo "<p>Found " . $result->num_rows . " encrypted columns</p>";
        } else {
            echo "<h2>⚠ Encrypted columns not found - run migration</h2>";
        }
    } else {
        throw new Exception("Database connection failed: " . ($conn->connect_error ?? "Unknown error"));
    }
    
    echo "<h2>🎉 All tests passed! Encryption system is ready.</h2>";
    echo "<p><a href='login.php'>Go to Login</a> | <a href='birth_form.php?type_request=Birth%20Certificate'>Test Birth Form</a></p>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error: " . $e->getMessage() . "</h2>";
    echo "<p>Please check the error and try again.</p>";
}
?>
