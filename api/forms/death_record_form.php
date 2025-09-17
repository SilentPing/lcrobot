<?php
// Death Record Form for LCRO Staff
?>

<div class="row">
    <div class="col-md-4">
        <div class="mb-3">
            <label for="deceased_ln" class="form-label">Deceased Last Name</label>
            <input type="text" class="form-control" id="deceased_ln" name="deceased_ln" required>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3">
            <label for="deceased_fn" class="form-label">Deceased First Name</label>
            <input type="text" class="form-control" id="deceased_fn" name="deceased_fn" required>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3">
            <label for="deceased_mn" class="form-label">Deceased Middle Name <span class="text-muted">(Optional)</span></label>
            <input type="text" class="form-control" id="deceased_mn" name="deceased_mn" placeholder="Middle Name (Optional)">
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="dob" class="form-label">Date of Birth</label>
            <input type="date" class="form-control" id="dob" name="dob" required>
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="dod" class="form-label">Date of Death</label>
            <input type="date" class="form-control" id="dod" name="dod" required>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="place_of_death" class="form-label">Place of Death</label>
            <input type="text" class="form-control" id="place_of_death" name="place_of_death" required>
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="purpose_of_request" class="form-label">Purpose of Request</label>
            <select class="form-control" id="purpose_of_request" name="purpose_of_request" required>
                <option value="">Select Purpose</option>
                <option value="Registration">Registration</option>
                <option value="Credentials Update">Credentials Update</option>
                <option value="Record Keeping">Record Keeping</option>
                <option value="Other">Other</option>
            </select>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="registration_date" class="form-label">Registration Date</label>
            <input type="date" class="form-control" id="registration_date" name="registration_date" required>
        </div>
    </div>
</div>
