# 📋 WALK-IN REQUEST SYSTEM - DOKUMENTASYON SA FILIPINO

## 🎯 PAGLALARAWAN NG SISTEMA

Ang **Walk-in Request System** ay isang feature na nagbibigay-daan sa mga admin na mag-input ng mga request para sa mga walk-in applicants na hindi marunong gumamit ng computer o device. Ito ay para sa mga taong pumupunta sa LCR office para humingi ng civil registry documents.

---

## 🔧 MGA FUNCTIONS AT KUNG SAAN NAKALAGAY

### 1. **Walk-in Request Button** 
**📍 Lokasyon:** `manage_request.php` (linya 25-27)
```php
<button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#walkinModal">
    <i class="bi bi-person-plus-fill"></i> Walk-in Request
</button>
```
**🎯 Tungkulin:** 
- Nagbubukas ng modal para sa walk-in request
- Makikita sa header ng manage request page
- Green button na may person-plus icon

### 2. **Modal Form Selection**
**📍 Lokasyon:** `manage_request.php` (linya 340-395)
```html
<div class="modal fade" id="walkinModal">
    <!-- Form type selection buttons -->
    <input type="radio" name="requestType" value="birth"> Birth Certificate
    <input type="radio" name="requestType" value="ceno"> CENOMAR
    <input type="radio" name="requestType" value="death"> Death Certificate
    <input type="radio" name="requestType" value="marriage"> Marriage Certificate
</div>
```
**🎯 Tungkulin:**
- Nagpapakita ng 4 na button para sa iba't ibang uri ng certificate
- Dynamic form loading base sa napiling type
- Responsive design para sa mobile at desktop

### 3. **Dynamic Form Loading**
**📍 Lokasyon:** `get_walkin_form.php`
**🎯 Tungkulin:**
- **`getBirthForm()`** (linya 36-207) - Naglalabas ng Birth Certificate form
- **`getCenoForm()`** (linya 210-381) - Naglalabas ng CENOMAR form  
- **`getDeathForm()`** (linya 384-479) - Naglalabas ng Death Certificate form
- **`getMarriageForm()`** (linya 482-613) - Naglalabas ng Marriage Certificate form
- **`getProvinceOptions()`** (linya 616-627) - Naglalabas ng listahan ng mga lalawigan

### 4. **Form Validation (Client-side)**
**📍 Lokasyon:** `manage_request.php` (linya 446-502)
```javascript
function validateWalkinForm() {
    // Tinitignan kung may empty fields
    // Tinitignan kung valid ang mga dates
    // Nagpapakita ng error messages
}
```
**🎯 Tungkulin:**
- **Empty Field Validation** - Tinitignan kung may mga walang laman na required fields
- **Date Validation** - Tinitignan kung valid ang mga petsa (hindi future birth date, etc.)
- **Contact Validation** - Tinitignan kung valid ang phone number at email format
- **Error Display** - Nagpapakita ng SweetAlert na may listahan ng mga errors

### 5. **Review Modal**
**📍 Lokasyon:** `manage_request.php` (linya 561-594)
```javascript
function showReviewModal() {
    // Nagpapakita ng lahat ng entered data
    // May "Submit Request" at "Edit Details" buttons
}
```
**🎯 Tungkulin:**
- Nagpapakita ng lahat ng na-input na data bago i-submit
- May option na mag-edit pa o i-submit na
- Security feature para maiwasan ang accidental submission

### 6. **Form Submission**
**📍 Lokasyon:** `submit_walkin_request.php`
**🎯 Tungkulin:**
- **`validateWalkinRequest()`** (linya 55-156) - Server-side validation
- **`submitBirthRequest()`** (linya 159-210) - Nag-save ng Birth/CENO request
- **`submitDeathRequest()`** (linya 213-263) - Nag-save ng Death request
- **`submitMarriageRequest()`** (linya 266-321) - Nag-save ng Marriage request

---

## 📝 MGA REQUIRED FIELDS SA BAWAT FORM

### **Birth Certificate & CENOMAR:**
- Last Name, First Name, Middle Name
- Place of Birth (Country, Province, City/Municipality)
- Date of Birth, Sex, Relationship
- Father's Full Name, Mother's Maiden Name
- Purpose of Request
- **Applicant Full Name** ⭐ (BAGO)
- **Contact Number** ⭐ (BAGO)
- **Email Address** ⭐ (BAGO)

### **Death Certificate:**
- Deceased Full Name
- Date of Birth, Date of Death, Place of Death
- Purpose of Request
- **Applicant Full Name** ⭐ (BAGO)
- **Contact Number** ⭐ (BAGO)
- **Email Address** ⭐ (BAGO)

### **Marriage Certificate:**
- Husband's Full Name, Wife's Maiden Name
- Place of Birth details
- Date of Birth, Place of Marriage
- Purpose of Request
- **Applicant Full Name** ⭐ (BAGO)
- **Contact Number** ⭐ (BAGO)
- **Email Address** ⭐ (BAGO)

---

## 🔒 SECURITY FEATURES

### **Client-side Security:**
- **Empty Field Prevention** - Hindi ma-submit kung may empty fields
- **Date Validation** - Hindi pwedeng future birth date
- **Contact Validation** - Phone number dapat 09XXXXXXXXX format
- **Email Validation** - Dapat valid email format
- **Review Step** - Double confirmation bago i-submit

### **Server-side Security:**
- **XSS Protection** - Hindi pwedeng mag-inject ng malicious scripts
- **Input Sanitization** - Nililinis ang lahat ng input data
- **Length Limits** - Maximum 255 characters per field
- **SQL Injection Protection** - Gumagamit ng prepared statements
- **Data Encryption** - Naka-encrypt ang sensitive data (PII)

---

## 📊 DATABASE INTEGRATION

### **Tables na Ginagamit:**
1. **`birthceno_tbl`** - Para sa Birth at CENO requests
2. **`death_tbl`** - Para sa Death requests
3. **`marriage_tbl`** - Para sa Marriage requests
4. **`civ_record`** - Para sa record keeping
5. **`reqtracking_tbl`** - Para sa request tracking (DITO NAKALAGAY ANG CONTACT INFO)

### **Contact Information Storage:**
```sql
INSERT INTO reqtracking_tbl (
    type_request, 
    registration_date, 
    registrar_name, 
    user_id, 
    status, 
    contact_no,    -- ⭐ APPLICANT'S PHONE
    email          -- ⭐ APPLICANT'S EMAIL
) VALUES (...)
```

---

## 🎯 USER FLOW (PAGKAKASUNOD-SUNOD)

1. **Admin clicks "Walk-in Request"** → Bumubukas ang modal
2. **Select form type** → Napipili ang uri ng certificate
3. **Fill out form** → Admin nag-iinput ng applicant details
4. **Click Submit** → Nagsisimula ang validation
5. **Validation check** → Tinitignan kung complete at valid ang data
6. **Review modal** → Nagpapakita ng lahat ng data para sa final review
7. **Confirm submission** → Nai-save sa database
8. **Success message** → Nagpapakita ng confirmation
9. **Page refresh** → Nagre-refresh ang manage request page

---

## 🛠️ TECHNICAL DETAILS

### **JavaScript Functions:**
- **`loadWalkinForm(type)`** - Naglo-load ng form base sa type
- **`validateWalkinForm()`** - Client-side validation
- **`getRequiredFields(formType)`** - Naglalabas ng required fields
- **`showReviewModal()`** - Nagpapakita ng review modal
- **`submitWalkinRequest()`** - Nag-submit ng request

### **PHP Functions:**
- **`validateWalkinRequest($data, $formType)`** - Server-side validation
- **`submitBirthRequest($data)`** - Birth/CENO submission
- **`submitDeathRequest($data)`** - Death submission
- **`submitMarriageRequest($data)`** - Marriage submission

### **AJAX Integration:**
- **Form Loading:** `get_walkin_form.php`
- **Form Submission:** `submit_walkin_request.php`
- **Error Handling:** SweetAlert2 notifications

---

## 🎉 BENEFITS NG WALK-IN SYSTEM

### **Para sa Admin:**
- ✅ Hindi na kailangan mag-register ang walk-in applicants
- ✅ Mabilis na pag-input ng request details
- ✅ Complete validation at security
- ✅ Review step para maiwasan ang errors

### **Para sa Walk-in Applicants:**
- ✅ Hindi na kailangan gumamit ng computer
- ✅ Admin ang nag-aasikaso ng request
- ✅ Tama ang contact information para sa notifications
- ✅ Secure at encrypted ang data

### **Para sa System:**
- ✅ Consistent data format
- ✅ Proper contact information storage
- ✅ Security compliance
- ✅ Audit trail para sa lahat ng requests

---

## 🔧 MAINTENANCE AT TROUBLESHOOTING

### **Common Issues:**
1. **Form hindi naglo-load** → Check JavaScript console for errors
2. **Validation hindi gumagana** → Check kung naka-load ang jQuery
3. **Contact info hindi nai-save** → Check database table structure
4. **Modal hindi bumubukas** → Check Bootstrap JavaScript

### **Files na Kailangan i-check:**
- `manage_request.php` - Main walk-in interface
- `get_walkin_form.php` - Form generation
- `submit_walkin_request.php` - Form submission
- `includes/script.php` - JavaScript libraries
- `includes/header.php` - CSS libraries

---

## 📞 SUPPORT

Para sa mga technical issues o questions tungkol sa Walk-in Request System, i-contact ang development team o i-check ang system logs para sa error details.

**System Status:** ✅ ACTIVE at READY FOR PRODUCTION
**Last Updated:** Disyembre 2024
**Version:** 1.0.0
