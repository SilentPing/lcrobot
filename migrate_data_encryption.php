<?php
/**
 * Data Migration Script for Encryption
 * 
 * This script encrypts existing plaintext data in the database.
 * Run this script after running migrate_encryption.php and before
 * removing the old plaintext columns.
 */

require_once __DIR__ . '/db.php';

echo "Starting data migration for encryption...\n";

try {
    // Check if encryption is supported
    if (!checkEncryptionSupport()) {
        throw new Exception("OpenSSL extension not available or AES-256-GCM not supported");
    }
    
    // Migrate birthceno_tbl data
    echo "Migrating birthceno_tbl data...\n";
    $result = $conn->query("SELECT id_birth_ceno, lastname, firstname, middlename, dob, sex FROM birthceno_tbl WHERE lastname_enc IS NULL OR lastname_enc = ''");
    
    if ($result && $result->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE birthceno_tbl SET 
            lastname_enc = ?, lastname_tok = ?,
            firstname_enc = ?, firstname_tok = ?,
            middlename_enc = ?, middlename_tok = ?,
            dob_enc = ?, dob_tok = ?,
            sex_enc = ?, sex_tok = ?
            WHERE id_birth_ceno = ?");
        
        while ($row = $result->fetch_assoc()) {
            // Encrypt and tokenize each field
            $lastname_data = encryptAndTokenize($row['lastname']);
            $firstname_data = encryptAndTokenize($row['firstname']);
            $middlename_data = encryptAndTokenize($row['middlename']);
            $dob_data = encryptAndTokenize($row['dob']);
            $sex_data = encryptAndTokenize($row['sex']);
            
            $stmt->bind_param("ssssssssssi",
                $lastname_data['encrypted'], $lastname_data['token'],
                $firstname_data['encrypted'], $firstname_data['token'],
                $middlename_data['encrypted'], $middlename_data['token'],
                $dob_data['encrypted'], $dob_data['token'],
                $sex_data['encrypted'], $sex_data['token'],
                $row['id_birth_ceno']
            );
            
            if ($stmt->execute()) {
                echo "✓ Migrated birthceno_tbl record ID: " . $row['id_birth_ceno'] . "\n";
            } else {
                echo "✗ Failed to migrate birthceno_tbl record ID: " . $row['id_birth_ceno'] . " - " . $stmt->error . "\n";
            }
        }
        $stmt->close();
    }
    
    // Migrate death_tbl data
    echo "Migrating death_tbl data...\n";
    $result = $conn->query("SELECT id_death, deceased_ln, deceased_fn, deceased_mn, dob FROM death_tbl WHERE deceased_ln_enc IS NULL OR deceased_ln_enc = ''");
    
    if ($result && $result->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE death_tbl SET 
            deceased_ln_enc = ?, deceased_ln_tok = ?,
            deceased_fn_enc = ?, deceased_fn_tok = ?,
            deceased_mn_enc = ?, deceased_mn_tok = ?,
            dob_enc = ?, dob_tok = ?
            WHERE id_death = ?");
        
        while ($row = $result->fetch_assoc()) {
            // Encrypt and tokenize each field
            $deceased_ln_data = encryptAndTokenize($row['deceased_ln']);
            $deceased_fn_data = encryptAndTokenize($row['deceased_fn']);
            $deceased_mn_data = encryptAndTokenize($row['deceased_mn']);
            $dob_data = encryptAndTokenize($row['dob']);
            
            $stmt->bind_param("ssssssssi",
                $deceased_ln_data['encrypted'], $deceased_ln_data['token'],
                $deceased_fn_data['encrypted'], $deceased_fn_data['token'],
                $deceased_mn_data['encrypted'], $deceased_mn_data['token'],
                $dob_data['encrypted'], $dob_data['token'],
                $row['id_death']
            );
            
            if ($stmt->execute()) {
                echo "✓ Migrated death_tbl record ID: " . $row['id_death'] . "\n";
            } else {
                echo "✗ Failed to migrate death_tbl record ID: " . $row['id_death'] . " - " . $stmt->error . "\n";
            }
        }
        $stmt->close();
    }
    
    // Migrate marriage_tbl data
    echo "Migrating marriage_tbl data...\n";
    $result = $conn->query("SELECT id_marriage, husband_ln, husband_fn, husband_mn, maiden_wife_ln, maiden_wife_fn, maiden_wife_mn FROM marriage_tbl WHERE husband_ln_enc IS NULL OR husband_ln_enc = ''");
    
    if ($result && $result->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE marriage_tbl SET 
            husband_ln_enc = ?, husband_ln_tok = ?,
            husband_fn_enc = ?, husband_fn_tok = ?,
            husband_mn_enc = ?, husband_mn_tok = ?,
            maiden_wife_ln_enc = ?, maiden_wife_ln_tok = ?,
            maiden_wife_fn_enc = ?, maiden_wife_fn_tok = ?,
            maiden_wife_mn_enc = ?, maiden_wife_mn_tok = ?
            WHERE id_marriage = ?");
        
        while ($row = $result->fetch_assoc()) {
            // Encrypt and tokenize each field
            $husband_ln_data = encryptAndTokenize($row['husband_ln']);
            $husband_fn_data = encryptAndTokenize($row['husband_fn']);
            $husband_mn_data = encryptAndTokenize($row['husband_mn']);
            $wife_ln_data = encryptAndTokenize($row['maiden_wife_ln']);
            $wife_fn_data = encryptAndTokenize($row['maiden_wife_fn']);
            $wife_mn_data = encryptAndTokenize($row['maiden_wife_mn']);
            
            $stmt->bind_param("ssssssssssssi",
                $husband_ln_data['encrypted'], $husband_ln_data['token'],
                $husband_fn_data['encrypted'], $husband_fn_data['token'],
                $husband_mn_data['encrypted'], $husband_mn_data['token'],
                $wife_ln_data['encrypted'], $wife_ln_data['token'],
                $wife_fn_data['encrypted'], $wife_fn_data['token'],
                $wife_mn_data['encrypted'], $wife_mn_data['token'],
                $row['id_marriage']
            );
            
            if ($stmt->execute()) {
                echo "✓ Migrated marriage_tbl record ID: " . $row['id_marriage'] . "\n";
            } else {
                echo "✗ Failed to migrate marriage_tbl record ID: " . $row['id_marriage'] . " - " . $stmt->error . "\n";
            }
        }
        $stmt->close();
    }
    
    echo "\nData migration completed successfully!\n";
    echo "Next steps:\n";
    echo "1. Test the system thoroughly to ensure encryption/decryption works\n";
    echo "2. Verify that searches work with the new token system\n";
    echo "3. After verification, you can remove the old plaintext columns\n";
    echo "4. Update your application code to use the encrypted fields\n";
    
} catch (Exception $e) {
    echo "Data migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
