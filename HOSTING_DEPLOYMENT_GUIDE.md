# 🚀 Hosting Deployment Guide - Fixing PHP Version Compatibility

## 🔴 **The Problem You Had:**

Your hosting site (`lcrobot.pcbics.net`) was showing **HTTP ERROR 500** because:
- Your localhost uses PHP 8.2
- Your hosting server likely uses PHP 8.0 or 8.1
- The `vendor` folder you uploaded was compiled for PHP 8.2+, causing compatibility issues

## ✅ **The Solution Applied:**

I've updated your dependencies to be compatible with **PHP 8.0+** instead of requiring PHP 8.2+.

### **Changes Made:**

1. **Updated `composer.json`:**
   - Changed PhpSpreadsheet from `^5.1` (requires PHP 8.2) → `^1.29` (requires PHP 8.0)
   - Changed PHPMailer from `^7.0` (requires PHP 8.2) → `^6.8` (requires PHP 8.0)
   - Added platform config to target PHP 8.0.0

2. **Regenerated `vendor` folder:**
   - Ran `composer update --no-dev`
   - All dependencies now compatible with PHP 8.0+

---

## 📋 **Deployment Steps for Your Hosting Site:**

### **Step 1: Upload Updated Files**

Upload these files/folders to your hosting (`lcrobot.pcbics.net`):

```
✅ composer.json (updated)
✅ composer.lock (updated)
✅ vendor/ (entire folder - regenerated)
✅ api/download_excel_template.php
✅ api/upload_excel_form.php
✅ api/generate_timely_birth_excel.php
✅ timely_birth_upload.php
✅ timely_birth_records.php
✅ civ_dashboard.php
✅ templates/birth_cert_template.xlsx
```

### **Step 2: Verify Folder Structure**

Make sure your hosting has this structure:

```
lcrobot.pcbics.net/
├── vendor/
│   ├── autoload.php
│   ├── phpoffice/
│   ├── twilio/
│   └── ... (all composer packages)
├── templates/
│   └── birth_cert_template.xlsx
├── api/
│   ├── download_excel_template.php
│   ├── upload_excel_form.php
│   └── generate_timely_birth_excel.php
└── ... (other files)
```

### **Step 3: Check Permissions**

Make sure these folders have write permissions (755 or 775):
```bash
chmod 755 templates/
chmod 755 uploads/timely_birth/
```

### **Step 4: Test the Features**

1. **Test Template Download:**
   - Visit: `https://lcrobot.pcbics.net/civ_dashboard.php`
   - Click "Download Form" button
   - Should download `birth_cert_template[timestamp].xlsx`

2. **Test Form Submission:**
   - Fill up the downloaded Excel template
   - Visit: `https://lcrobot.pcbics.net/timely_birth_upload.php`
   - Upload the filled Excel file
   - Fill in requestor information
   - Click "Submit Excel Form"
   - Should show success message

3. **Test Admin View:**
   - Login to admin dashboard
   - Go to Timely Birth Records
   - Should see submitted records
   - Try "View Details", "Download Original", "Generate Formatted Excel"

---

## 🔧 **PHP Version Requirements:**

### **Minimum Requirements:**
- **PHP**: 8.0.0 or higher
- **Extensions**: 
  - php-xml
  - php-zip
  - php-gd
  - php-mbstring
  - php-mysqli

### **Check Your Hosting PHP Version:**

Create a file `phpinfo.php` on your hosting:
```php
<?php
phpinfo();
?>
```

Visit: `https://lcrobot.pcbics.net/phpinfo.php`

Look for: **PHP Version** (should be 8.0.0 or higher)

⚠️ **Important**: Delete `phpinfo.php` after checking for security reasons!

---

## 🐛 **Troubleshooting:**

### **If you still get HTTP 500 error:**

1. **Check Error Logs:**
   - Access your hosting control panel (cPanel, Plesk, etc.)
   - Look for PHP error logs or Apache error logs
   - The logs will show the exact error

2. **Common Issues:**

   **Issue**: "vendor/autoload.php not found"
   - **Fix**: Make sure you uploaded the entire `vendor` folder

   **Issue**: "Class 'PhpOffice\PhpSpreadsheet\Spreadsheet' not found"
   - **Fix**: Re-upload the `vendor` folder completely

   **Issue**: "templates/birth_cert_template.xlsx not found"
   - **Fix**: Upload the `templates` folder with the Excel file

   **Issue**: "Permission denied"
   - **Fix**: Set proper permissions (755) on folders

3. **PHP Version Too Old:**
   - If your hosting has PHP < 8.0, you'll need to:
     - Upgrade your hosting PHP version to 8.0+, OR
     - Contact your hosting provider to enable PHP 8.0+

---

## 📊 **What Each File Does:**

### **Template Download** (`api/download_excel_template.php`):
- Serves the blank Excel template to users
- Users download this, fill it up offline
- Works with PhpSpreadsheet

### **Form Submission** (`api/upload_excel_form.php`):
- Receives uploaded Excel files
- Parses Excel data using PhpSpreadsheet
- Saves data to database
- Stores original Excel file

### **Admin Excel Generation** (`api/generate_timely_birth_excel.php`):
- Loads the original template
- Populates it with database data
- Generates formatted Excel for printing
- Admin-only feature

---

## 🎯 **Quick Checklist:**

Before uploading to hosting, make sure:
- [ ] `vendor` folder is completely regenerated (from this fix)
- [ ] `composer.json` and `composer.lock` are updated
- [ ] `templates/birth_cert_template.xlsx` exists
- [ ] `uploads/timely_birth/` folder exists with write permissions
- [ ] Your hosting PHP version is 8.0 or higher

After uploading:
- [ ] Test template download
- [ ] Test form submission
- [ ] Test admin view
- [ ] Check error logs if any issues

---

## 💡 **Performance Tips:**

1. **Enable OPcache** on your hosting for better PHP performance
2. **Use .htaccess** to set memory limits if needed:
   ```apache
   php_value memory_limit 256M
   php_value upload_max_filesize 10M
   php_value post_max_size 10M
   ```

3. **Monitor File Uploads:** Excel files can be large, ensure your hosting allows sufficient upload sizes

---

## 📞 **Still Having Issues?**

If the deployment still doesn't work:

1. **Provide the exact error message** from your hosting error logs
2. **Check PHP version** on your hosting (must be 8.0+)
3. **Verify all files uploaded** correctly
4. **Check folder permissions** (755 for folders, 644 for files)

---

## ✅ **Expected Result:**

After following these steps:
- ✅ Template download should work on hosting
- ✅ Form submission should work on hosting
- ✅ Admin Excel generation should work on hosting
- ✅ No more HTTP 500 errors
- ✅ All features work the same as localhost

Good luck with your deployment! 🚀
