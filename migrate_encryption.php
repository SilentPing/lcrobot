<?php
/**
 * Database Migration Script for Encryption
 * 
 * This script adds encrypted columns to existing tables for PII data.
 * Run this script once before deploying the encryption system.
 */

require_once __DIR__ . '/db.php';

echo "Starting database migration for encryption...\n";

try {
    // Check if encryption is supported
    if (!checkEncryptionSupport()) {
        throw new Exception("OpenSSL extension not available or AES-256-GCM not supported");
    }
    
    // Migration queries for each table
    $migrations = [
        // Birth/CENO table
        "ALTER TABLE birthceno_tbl 
         ADD COLUMN lastname_enc VARBINARY(255) AFTER lastname,
         ADD COLUMN lastname_tok CHAR(64) AFTER lastname_enc,
         ADD COLUMN firstname_enc VARBINARY(255) AFTER firstname,
         ADD COLUMN firstname_tok CHAR(64) AFTER firstname_enc,
         ADD COLUMN middlename_enc VARBINARY(255) AFTER middlename,
         ADD COLUMN middlename_tok CHAR(64) AFTER middlename_enc,
         ADD COLUMN dob_enc VARBINARY(255) AFTER dob,
         ADD COLUMN dob_tok CHAR(64) AFTER dob_enc,
         ADD COLUMN sex_enc VARBINARY(64) AFTER sex,
         ADD COLUMN sex_tok CHAR(64) AFTER sex_enc",
         
        // Death table
        "ALTER TABLE death_tbl 
         ADD COLUMN deceased_ln_enc VARBINARY(255) AFTER deceased_ln,
         ADD COLUMN deceased_ln_tok CHAR(64) AFTER deceased_ln_enc,
         ADD COLUMN deceased_fn_enc VARBINARY(255) AFTER deceased_fn,
         ADD COLUMN deceased_fn_tok CHAR(64) AFTER deceased_fn_enc,
         ADD COLUMN deceased_mn_enc VARBINARY(255) AFTER deceased_mn,
         ADD COLUMN deceased_mn_tok CHAR(64) AFTER deceased_mn_enc,
         ADD COLUMN dob_enc VARBINARY(255) AFTER dob,
         ADD COLUMN dob_tok CHAR(64) AFTER dob_enc",
         
        // Marriage table
        "ALTER TABLE marriage_tbl 
         ADD COLUMN husband_ln_enc VARBINARY(255) AFTER husband_ln,
         ADD COLUMN husband_ln_tok CHAR(64) AFTER husband_ln_enc,
         ADD COLUMN husband_fn_enc VARBINARY(255) AFTER husband_fn,
         ADD COLUMN husband_fn_tok CHAR(64) AFTER husband_fn_enc,
         ADD COLUMN husband_mn_enc VARBINARY(255) AFTER husband_mn,
         ADD COLUMN husband_mn_tok CHAR(64) AFTER husband_mn_enc,
         ADD COLUMN maiden_wife_ln_enc VARBINARY(255) AFTER maiden_wife_ln,
         ADD COLUMN maiden_wife_ln_tok CHAR(64) AFTER maiden_wife_ln_enc,
         ADD COLUMN maiden_wife_fn_enc VARBINARY(255) AFTER maiden_wife_fn,
         ADD COLUMN maiden_wife_fn_tok CHAR(64) AFTER maiden_wife_fn_enc,
         ADD COLUMN maiden_wife_mn_enc VARBINARY(255) AFTER maiden_wife_mn,
         ADD COLUMN maiden_wife_mn_tok CHAR(64) AFTER maiden_wife_mn_enc"
    ];
    
    // Create indexes for search tokens
    $indexes = [
        "CREATE INDEX idx_birthceno_lastname_tok ON birthceno_tbl (lastname_tok)",
        "CREATE INDEX idx_birthceno_firstname_tok ON birthceno_tbl (firstname_tok)",
        "CREATE INDEX idx_birthceno_dob_tok ON birthceno_tbl (dob_tok)",
        "CREATE INDEX idx_death_deceased_ln_tok ON death_tbl (deceased_ln_tok)",
        "CREATE INDEX idx_death_deceased_fn_tok ON death_tbl (deceased_fn_tok)",
        "CREATE INDEX idx_death_dob_tok ON death_tbl (dob_tok)",
        "CREATE INDEX idx_marriage_husband_ln_tok ON marriage_tbl (husband_ln_tok)",
        "CREATE INDEX idx_marriage_husband_fn_tok ON marriage_tbl (husband_fn_tok)",
        "CREATE INDEX idx_marriage_wife_ln_tok ON marriage_tbl (maiden_wife_ln_tok)",
        "CREATE INDEX idx_marriage_wife_fn_tok ON marriage_tbl (maiden_wife_fn_tok)"
    ];
    
    // Execute migrations
    foreach ($migrations as $i => $sql) {
        echo "Executing migration " . ($i + 1) . "...\n";
        if ($conn->query($sql) === TRUE) {
            echo "✓ Migration " . ($i + 1) . " completed successfully\n";
        } else {
            echo "✗ Migration " . ($i + 1) . " failed: " . $conn->error . "\n";
        }
    }
    
    // Create indexes
    foreach ($indexes as $i => $sql) {
        echo "Creating index " . ($i + 1) . "...\n";
        if ($conn->query($sql) === TRUE) {
            echo "✓ Index " . ($i + 1) . " created successfully\n";
        } else {
            echo "✗ Index " . ($i + 1) . " failed: " . $conn->error . "\n";
        }
    }
    
    echo "\nMigration completed successfully!\n";
    echo "Next steps:\n";
    echo "1. Update your forms to use encryption\n";
    echo "2. Run the data migration script to encrypt existing data\n";
    echo "3. Test the system thoroughly\n";
    echo "4. Remove old plaintext columns after verification\n";
    
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
