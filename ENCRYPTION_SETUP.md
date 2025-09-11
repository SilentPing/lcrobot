# Civil Registry Encryption Setup Guide

This guide explains how to implement encryption for PII (Personally Identifiable Information) data in your civil registry system.

## Overview

The encryption system protects the following PII fields:
- **Date of Birth (dob)**
- **First Name (firstname)**
- **Last Name (lastname)**
- **Middle Name (middlename)**
- **Sex**

## How It Works

1. **Encryption**: PII data is encrypted using AES-256-GCM (authenticated encryption)
2. **Search Tokens**: HMAC-SHA256 tokens are generated for exact-match searches
3. **Dual Storage**: Both encrypted data and search tokens are stored in the database
4. **Backward Compatibility**: Original plaintext fields are kept during transition

## Setup Instructions

### Step 1: Run Setup Script

```bash
php setup_encryption.php
```

This will:
- Generate encryption keys
- Create `.env` file with configuration
- Verify OpenSSL support

### Step 2: Database Migration

```bash
php migrate_encryption.php
```

This will:
- Add encrypted columns to existing tables
- Create search indexes for performance

### Step 3: Data Migration

```bash
php migrate_data_encryption.php
```

This will:
- Encrypt existing plaintext data
- Generate search tokens for existing records

### Step 4: Test the System

1. Submit a new form to test encryption
2. Verify data can be decrypted and displayed
3. Test search functionality

### Step 5: Remove Old Columns (After Verification)

Once you're confident everything works:

```sql
-- Remove old plaintext columns (run after thorough testing)
ALTER TABLE birthceno_tbl DROP COLUMN lastname, DROP COLUMN firstname, DROP COLUMN middlename, DROP COLUMN dob, DROP COLUMN sex;
ALTER TABLE death_tbl DROP COLUMN deceased_ln, DROP COLUMN deceased_fn, DROP COLUMN deceased_mn, DROP COLUMN dob;
ALTER TABLE marriage_tbl DROP COLUMN husband_ln, DROP COLUMN husband_fn, DROP COLUMN husband_mn, DROP COLUMN maiden_wife_ln, DROP COLUMN maiden_wife_fn, DROP COLUMN maiden_wife_mn;
```

## Database Schema Changes

### birthceno_tbl
```sql
-- New encrypted columns
lastname_enc VARBINARY(255)
lastname_tok CHAR(64)
firstname_enc VARBINARY(255)
firstname_tok CHAR(64)
middlename_enc VARBINARY(255)
middlename_tok CHAR(64)
dob_enc VARBINARY(255)
dob_tok CHAR(64)
sex_enc VARBINARY(64)
sex_tok CHAR(64)
```

### death_tbl
```sql
-- New encrypted columns
deceased_ln_enc VARBINARY(255)
deceased_ln_tok CHAR(64)
deceased_fn_enc VARBINARY(255)
deceased_fn_tok CHAR(64)
deceased_mn_enc VARBINARY(255)
deceased_mn_tok CHAR(64)
dob_enc VARBINARY(255)
dob_tok CHAR(64)
```

### marriage_tbl
```sql
-- New encrypted columns
husband_ln_enc VARBINARY(255)
husband_ln_tok CHAR(64)
husband_fn_enc VARBINARY(255)
husband_fn_tok CHAR(64)
husband_mn_enc VARBINARY(255)
husband_mn_tok CHAR(64)
maiden_wife_ln_enc VARBINARY(255)
maiden_wife_ln_tok CHAR(64)
maiden_wife_fn_enc VARBINARY(255)
maiden_wife_fn_tok CHAR(64)
maiden_wife_mn_enc VARBINARY(255)
maiden_wife_mn_tok CHAR(64)
```

## Usage Examples

### Encrypting Data (Form Submission)
```php
// Encrypt PII fields
$lastname_data = encryptAndTokenize($lastname);
$firstname_data = encryptAndTokenize($firstname);

// Store in database
$stmt = $conn->prepare("INSERT INTO birthceno_tbl (lastname_enc, lastname_tok, firstname_enc, firstname_tok, ...) VALUES (?, ?, ?, ?, ...)");
$stmt->bind_param("ssss", $lastname_data['encrypted'], $lastname_data['token'], $firstname_data['encrypted'], $firstname_data['token']);
```

### Decrypting Data (Display)
```php
// Decrypt fields for display
$decrypted = decryptFields($row, ['lastname', 'firstname', 'middlename', 'dob', 'sex']);
echo "Name: " . $decrypted['firstname'] . " " . $decrypted['lastname'];
```

### Searching Data
```php
// Search using tokens
$search_token = tokenForLookup(normalizeText($search_name));
$stmt = $conn->prepare("SELECT * FROM birthceno_tbl WHERE lastname_tok = ?");
$stmt->bind_param("s", $search_token);
```

## Security Features

1. **Authenticated Encryption**: Uses AES-256-GCM to prevent tampering
2. **Unique IVs**: Each encryption uses a random initialization vector
3. **Search Tokens**: HMAC-based tokens for secure searching
4. **Key Management**: Keys stored in environment variables
5. **Error Handling**: Graceful handling of decryption failures

## Important Security Notes

1. **Backup Keys**: Store encryption keys in a secure location
2. **Environment Security**: Keep `.env` file secure and never commit to version control
3. **Key Rotation**: Consider rotating keys periodically
4. **Access Control**: Limit access to encryption functions
5. **Audit Logging**: Log encryption/decryption operations

## Troubleshooting

### Common Issues

1. **"Encryption key missing"**: Check `.env` file exists and has correct keys
2. **"Decryption failed"**: Data may be corrupted or key may be wrong
3. **"OpenSSL not supported"**: Install OpenSSL extension for PHP
4. **"Invalid encrypted data"**: Check data format and encoding

### Testing Encryption

```php
// Test encryption/decryption
$test_data = "John Doe";
$encrypted = encryptField($test_data);
$decrypted = decryptField($encrypted);
echo $decrypted === $test_data ? "✓ Encryption works" : "✗ Encryption failed";
```

## Production Deployment

1. **Generate Production Keys**: Use `setup_encryption.php` on production server
2. **Secure Environment**: Ensure `.env` file is not accessible via web
3. **Backup Strategy**: Include encryption keys in backup procedures
4. **Monitoring**: Monitor encryption/decryption operations
5. **Performance**: Test with production data volumes

## Support

For issues or questions:
1. Check error logs for detailed error messages
2. Verify OpenSSL extension is installed
3. Ensure database has proper permissions
4. Test with sample data first

Remember: Always test thoroughly in a development environment before deploying to production!
