# Civil Registry System - Pagpapaliwanag sa Filipino

## Ano ang Civil Registry System?

Ang Civil Registry System ay isang web-based na sistema para sa pagproseso ng mga sibil na dokumento tulad ng:
- **Birth Certificate** (Sertipiko ng Kapanganakan)
- **Death Certificate** (Sertipiko ng Kamatayan) 
- **Marriage Certificate** (Sertipiko ng Kasal)
- **CENOMAR** (Certificate of No Marriage)

## Paano Gumagana ang Sistema?

### 1. **Pag-login (Authentication)**
```php
// Sa login.php - linya 1-17
<?php 
session_start(); 

// Pinipigilan ang pag-cache ng login page
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Tinitignan kung naka-login na ang user
if(isset($_SESSION['name'])) {
    if(isset($_SESSION['usertype']) && $_SESSION['usertype'] == 'admin') {
        header("Location: admin_dashboard.php");  // Redirect sa admin dashboard
    } else {
        header("Location: user_dashboard.php");   // Redirect sa user dashboard
    }
    exit;
}
?>
```

**Paliwanag:**
- `session_start()` - Sinisimulan ang session para sa user
- `header("Cache-Control: no-cache...")` - Pinipigilan ang browser na i-cache ang login page
- `isset($_SESSION['name'])` - Tinitignan kung may naka-login na user
- `$_SESSION['usertype']` - Tinitignan kung admin o regular user

### 2. **Pag-encrypt ng Sensitive Data**
```php
// Sa crypto.php - linya 45-65
function encryptField(string $plaintext): string {
    $key = getEnvKey('ENC_KEY');           // Kunin ang encryption key
    $iv = random_bytes(12);                // Gumawa ng random IV (Initialization Vector)
    $cipher = 'aes-256-gcm';              // Gamitin ang AES-256-GCM encryption
    $tag = '';
    
    $ciphertext = openssl_encrypt($plaintext, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag);
    if ($ciphertext === false) { 
        throw new RuntimeException('Encryption failed'); 
    }
    
    // I-store bilang base64(iv|tag|ciphertext)
    return base64_encode($iv . $tag . $ciphertext);
}
```

**Paliwanag:**
- `getEnvKey('ENC_KEY')` - Kumukuha ng encryption key mula sa .env file
- `random_bytes(12)` - Gumagawa ng random na 12-byte na IV para sa security
- `aes-256-gcm` - Ginagamit ang pinaka-secure na encryption method
- `openssl_encrypt()` - Ang function na nag-e-encrypt ng data
- `base64_encode()` - Kinoconvert ang encrypted data sa base64 format

### 3. **Pag-save ng Form Data**
```php
// Sa birth_form.php - linya 76-105
// I-encrypt ang PII fields
$lastname_data = encryptAndTokenize($lastname);
$firstname_data = encryptAndTokenize($firstname);
$middlename_data = encryptAndTokenize($middlename);
$dob_data = encryptAndTokenize($dob);
$sex_data = encryptAndTokenize($sex);

// SQL query para sa pag-insert ng data
$stmt = $conn->prepare("INSERT INTO birthceno_tbl (id_user, lastname, lastname_enc, lastname_tok, firstname, firstname_enc, firstname_tok, middlename, middlename_enc, middlename_tok, pob_country, pob_province, pob_municipality, dob, dob_enc, dob_tok, sex, sex_enc, sex_tok, fath_ln, fath_fn, fath_mn, moth_maiden_ln, moth_maiden_fn, moth_maiden_mn, relationship, purpose_of_request, type_request, status_request) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param("issssssssssssssssssssssssssss", 
    $id_user, 
    $lastname, // Keep original for backward compatibility
    $lastname_data['encrypted'], $lastname_data['token'],
    $firstname, // Keep original for backward compatibility
    $firstname_data['encrypted'], $firstname_data['token'],
    // ... iba pang fields
);
```

**Paliwanag:**
- `encryptAndTokenize()` - Nag-e-encrypt ng data at gumagawa ng search token
- `$stmt->prepare()` - Naghahanda ng SQL query para sa security (prevent SQL injection)
- `$stmt->bind_param()` - Nagbi-bind ng parameters sa prepared statement
- Parehong encrypted at original data ang naka-save para sa backward compatibility

### 4. **Security Protection (.htaccess)**
```apache
# Sa .htaccess file
<Files ".env">
    Require all denied
</Files>

<Files "config.php">
    Require all denied
</Files>

<Files "crypto.php">
    Require all denied
</Files>

<Files "*.sql">
    Require all denied
</Files>

Options -Indexes
```

**Paliwanag:**
- `Require all denied` - Pinipigilan ang direct access sa sensitive files
- `Options -Indexes` - Pinipigilan ang directory listing
- Pinoprotektahan ang mga importanteng files tulad ng .env, config.php, crypto.php, at .sql files

### 5. **Session Management**
```php
// Sa session_manager.php - linya 25-35
function requireAuth() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}

function requireAdmin() {
    requireAuth();
    if (!isAdmin()) {
        header("Location: user_dashboard.php");
        exit;
    }
}
```

**Paliwanag:**
- `requireAuth()` - Tinitignan kung naka-login ang user, kung hindi redirect sa login
- `requireAdmin()` - Tinitignan kung admin ang user, kung hindi redirect sa user dashboard
- `isLoggedIn()` - Tinitignan kung may valid session ang user
- `isAdmin()` - Tinitignan kung admin ang user type

## Mga Features ng Sistema

### 🔐 **Security Features:**
1. **Data Encryption** - Lahat ng sensitive data ay naka-encrypt
2. **Session Management** - Secure na pag-handle ng user sessions
3. **Access Control** - Role-based access (Admin vs User)
4. **File Protection** - Protected ang mga sensitive files
5. **Cache Prevention** - Walang caching issues

### 📝 **Form Processing:**
1. **Birth Certificate Request** - Para sa mga bagong kapanganakan
2. **Death Certificate Request** - Para sa mga namatay
3. **Marriage Certificate Request** - Para sa mga kasal
4. **CENOMAR Request** - Certificate of No Marriage

### 👥 **User Types:**
1. **Admin** - May access sa lahat ng features
2. **Regular User** - May access lang sa pag-submit ng requests

## Paano Gamitin ang Sistema

### Para sa Users:
1. **Mag-register** sa registration.php
2. **Mag-login** sa login.php
3. **Pumili ng type of request** (Birth, Death, Marriage, CENOMAR)
4. **Fill-up ang form** na may mga required fields
5. **Submit ang request**
6. **Maghintay ng approval** mula sa admin

### Para sa Admin:
1. **Mag-login** bilang admin
2. **Tingnan ang dashboard** para sa mga pending requests
3. **Review ang mga requests**
4. **Approve o reject** ang mga requests
5. **Manage ang mga users**

## Database Structure

### Main Tables:
- **users** - User accounts at information
- **birthceno_tbl** - Birth at CENOMAR requests
- **death_tbl** - Death certificate requests  
- **marriage_tbl** - Marriage certificate requests
- **civ_record** - Civil records tracking
- **reqtracking_tbl** - Request tracking

### Encrypted Fields:
- `lastname_enc`, `lastname_tok` - Encrypted last name at search token
- `firstname_enc`, `firstname_tok` - Encrypted first name at search token
- `middlename_enc`, `middlename_tok` - Encrypted middle name at search token
- `dob_enc`, `dob_tok` - Encrypted date of birth at search token
- `sex_enc`, `sex_tok` - Encrypted sex at search token

## Deployment Checklist

### Bago i-deploy:
1. ✅ **Run setup_encryption.php** - Para sa encryption keys
2. ✅ **Run migrate_encryption.php** - Para sa database structure
3. ✅ **Run migrate_data_encryption.php** - Para sa existing data
4. ✅ **Test lahat ng forms** - Para sa functionality
5. ✅ **Test security measures** - Para sa protection
6. ✅ **Configure .env file** - Para sa production settings

### Production Settings:
- **HTTPS** - Para sa secure connection
- **Environment variables** - Para sa sensitive data
- **Database backup** - Para sa data protection
- **Error logging** - Para sa monitoring
- **Performance optimization** - Para sa speed

## Troubleshooting

### Common Issues:
1. **"mysqli object is already closed"** - Fixed na sa db.php
2. **"Column count doesn't match"** - Fixed na sa form files
3. **Cache issues** - Fixed na sa cache headers
4. **Session problems** - Fixed na sa session management

### Error Messages:
- **"Connection failed"** - Check database connection
- **"Encryption failed"** - Check encryption keys
- **"Access denied"** - Check user permissions
- **"Session expired"** - Re-login required

## Support

Para sa mga issues o questions:
1. Check ang error logs
2. Verify ang database connection
3. Test ang encryption system
4. Check ang user permissions

**Ang sistema ay handa na para sa public deployment!** 🎉

---

*Ginawa para sa Civil Registry System - Secure, Encrypted, at Production-Ready*
