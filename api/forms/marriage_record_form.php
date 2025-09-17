<?php
// Marriage Record Form for LCRO Staff
?>

<div class="row">
    <div class="col-md-4">
        <div class="mb-3">
            <label for="husband_ln" class="form-label">Husband's Last Name</label>
            <input type="text" class="form-control" id="husband_ln" name="husband_ln" required>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3">
            <label for="husband_fn" class="form-label">Husband's First Name</label>
            <input type="text" class="form-control" id="husband_fn" name="husband_fn" required>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3">
            <label for="husband_mn" class="form-label">Husband's Middle Name <span class="text-muted">(Optional)</span></label>
            <input type="text" class="form-control" id="husband_mn" name="husband_mn" placeholder="Middle Name (Optional)">
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="mb-3">
            <label for="maiden_wife_ln" class="form-label">Wife's Maiden Last Name</label>
            <input type="text" class="form-control" id="maiden_wife_ln" name="maiden_wife_ln" required>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3">
            <label for="maiden_wife_fn" class="form-label">Wife's Maiden First Name</label>
            <input type="text" class="form-control" id="maiden_wife_fn" name="maiden_wife_fn" required>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3">
            <label for="maiden_wife_mn" class="form-label">Wife's Maiden Middle Name <span class="text-muted">(Optional)</span></label>
            <input type="text" class="form-control" id="maiden_wife_mn" name="maiden_wife_mn" placeholder="Middle Name (Optional)">
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="marriage_date" class="form-label">Marriage Date</label>
            <input type="date" class="form-control" id="marriage_date" name="marriage_date" required>
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="place_of_marriage" class="form-label">Place of Marriage</label>
            <input type="text" class="form-control" id="place_of_marriage" name="place_of_marriage" required>
        </div>
    </div>
</div>

<div class="row">
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
    <div class="col-md-6">
        <div class="mb-3">
            <label for="registration_date" class="form-label">Registration Date</label>
            <input type="date" class="form-control" id="registration_date" name="registration_date" required>
        </div>
    </div>
</div>
