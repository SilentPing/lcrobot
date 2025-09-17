<?php
// Birth Record Form for LCRO Staff
?>

<div class="row">
    <div class="col-md-4">
        <div class="mb-3">
            <label for="lastname" class="form-label">Last Name</label>
            <input type="text" class="form-control" id="lastname" name="lastname" required>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3">
            <label for="firstname" class="form-label">First Name</label>
            <input type="text" class="form-control" id="firstname" name="firstname" required>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3">
            <label for="middlename" class="form-label">Middle Name <span class="text-muted">(Optional)</span></label>
            <input type="text" class="form-control" id="middlename" name="middlename" placeholder="Middle Name (Optional)">
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="mb-3">
            <label for="pob_country" class="form-label">Place of Birth (Country)</label>
            <input type="text" class="form-control" id="pob_country" name="pob_country" required>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3">
            <label for="pob_province" class="form-label">Place of Birth (Province)</label>
            <select class="form-control" id="pob_province" name="pob_province" required>
                <option value="">Select Province</option>
                <?php 
                $sql = "SELECT provDesc, provCode FROM refprovince ORDER BY provDesc";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='" . $row['provCode'] . "'>" . $row['provDesc'] . "</option>";
                    }
                }
                ?>
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3">
            <label for="pob_municipality" class="form-label">Place of Birth (City/Municipality)</label>
            <input type="text" class="form-control" id="pob_municipality" name="pob_municipality" required>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="mb-3">
            <label for="dob" class="form-label">Date of Birth</label>
            <input type="date" class="form-control" id="dob" name="dob" required>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3">
            <label for="sex" class="form-label">Sex</label>
            <select class="form-control" id="sex" name="sex" required>
                <option value="">Select Sex</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3">
            <label for="relationship" class="form-label">Relationship to Document Owner</label>
            <select class="form-control" id="relationship" name="relationship" required>
                <option value="">Select Relationship</option>
                <option value="Registrant">Registrant</option>
                <option value="Parent">Parent</option>
                <option value="Sibling">Sibling</option>
            </select>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="mb-3">
            <label for="fath_ln" class="form-label">Father's Last Name</label>
            <input type="text" class="form-control" id="fath_ln" name="fath_ln" required>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3">
            <label for="fath_fn" class="form-label">Father's First Name</label>
            <input type="text" class="form-control" id="fath_fn" name="fath_fn" required>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3">
            <label for="fath_mn" class="form-label">Father's Middle Name</label>
            <input type="text" class="form-control" id="fath_mn" name="fath_mn" required>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="mb-3">
            <label for="moth_maiden_ln" class="form-label">Mother's Maiden Last Name</label>
            <input type="text" class="form-control" id="moth_maiden_ln" name="moth_maiden_ln" required>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3">
            <label for="moth_maiden_fn" class="form-label">Mother's Maiden First Name</label>
            <input type="text" class="form-control" id="moth_maiden_fn" name="moth_maiden_fn" required>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3">
            <label for="moth_maiden_mn" class="form-label">Mother's Maiden Middle Name</label>
            <input type="text" class="form-control" id="moth_maiden_mn" name="moth_maiden_mn" required>
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
