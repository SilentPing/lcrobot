<?php
/**
 * Encryption Setup Script
 * 
 * This script helps set up encryption for the civil registry system.
 * Run this script once to generate encryption keys and create the .env file.
 */

require_once __DIR__ . '/crypto.php';

echo "Civil Registry Encryption Setup\n";
echo "==============================\n\n";

try {
    // Check if OpenSSL is available
    if (!checkEncryptionSupport()) {
        throw new Exception("OpenSSL extension not available or AES-256-GCM not supported");
    }
    
    echo "✓ OpenSSL support verified\n";
    
    // Generate new encryption keys
    $keys = generateNewKeys();
    
    echo "✓ Encryption keys generated\n";
    
    // Create .env file
    $envContent = "# Civil Registry Environment Configuration
# IMPORTANT: Keep this file secure and never commit it to version control

# Database Configuration
DB_HOST=localhost
DB_USERNAME=root
DB_PASSWORD=
DB_NAME=civ_reg

# Encryption Keys (Base64-encoded 32-byte keys)
ENC_KEY={$keys['ENC_KEY']}
LOOKUP_KEY={$keys['LOOKUP_KEY']}

# Security Settings
SESSION_LIFETIME=3600
MAX_LOGIN_ATTEMPTS=5
LOCKOUT_DURATION=900

# Email Configuration (if using email notifications)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your_email@gmail.com
SMTP_PASSWORD=your_app_password
SMTP_FROM_NAME=Civil Registry System

# SMS Configuration (if using SMS notifications)
SMS_API_KEY=your_sms_api_key
SMS_SENDER_ID=CIVREG

# Application Settings
APP_NAME=Civil Registry System
APP_URL=http://localhost/civreg
APP_ENV=development
APP_DEBUG=true
";
    
    if (file_put_contents(__DIR__ . '/.env', $envContent)) {
        echo "✓ .env file created with encryption keys\n";
    } else {
        echo "✗ Failed to create .env file\n";
        echo "Please create .env file manually with the following keys:\n";
        echo "ENC_KEY={$keys['ENC_KEY']}\n";
        echo "LOOKUP_KEY={$keys['LOOKUP_KEY']}\n";
    }
    
    echo "\nSetup completed successfully!\n\n";
    echo "Next steps:\n";
    echo "1. Run: php migrate_encryption.php (to add encrypted columns to database)\n";
    echo "2. Run: php migrate_data_encryption.php (to encrypt existing data)\n";
    echo "3. Test the system thoroughly\n";
    echo "4. Update your application code to use encrypted fields\n";
    echo "5. Remove old plaintext columns after verification\n\n";
    
    echo "IMPORTANT SECURITY NOTES:\n";
    echo "- Keep your .env file secure and never commit it to version control\n";
    echo "- Backup your encryption keys in a secure location\n";
    echo "- Test encryption/decryption thoroughly before going live\n";
    echo "- Consider using environment-specific keys for production\n";
    
} catch (Exception $e) {
    echo "Setup failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
