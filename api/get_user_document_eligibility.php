<?php
/**
 * Get User Document Eligibility API
 * 
 * This API retrieves document eligibility for the current logged-in user
 */

session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../db.php';

// Botolan, Zambales codes
define('BOTOLAN_MUNICIPALITY_CODE', '037101');
define('ZAMBALES_PROVINCE_CODE', '0371');

function checkDocumentEligibility($birthplaceMunicipality, $birthplaceProvince) {
    $isBornInBotolan = ($birthplaceMunicipality === BOTOLAN_MUNICIPALITY_CODE && 
                       $birthplaceProvince === ZAMBALES_PROVINCE_CODE);
    
    return [
        'isBornInBotolan' => $isBornInBotolan,
        'canRequestPSA' => true, // Everyone can request PSA documents
        'canRequestLCRO' => $isBornInBotolan, // Only those born in Botolan can request LCRO documents
        'eligibleDocuments' => [
            'psa' => [
                'birth' => true,
                'marriage' => true,
                'death' => true,
                'cenomar' => true
            ],
            'lcro' => [
                'ctc' => $isBornInBotolan,
                'varied_forms' => $isBornInBotolan
            ]
        ],
        'message' => $isBornInBotolan ? 
            'You can request both PSA and LCRO documents since you were born in Botolan, Zambales.' :
            'You can only request PSA documents. LCRO documents are only available for those born in Botolan, Zambales.'
    ];
}

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode([
        'success' => false,
        'error' => 'User not logged in'
    ]);
    exit;
}

$username = $_SESSION['username'];

// Get user's birthplace information from database
$query = "SELECT birthplace_municipality, birthplace_province FROM users WHERE username = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    $birthplaceMunicipality = $row['birthplace_municipality'];
    $birthplaceProvince = $row['birthplace_province'];
    
    if (empty($birthplaceMunicipality) || empty($birthplaceProvince)) {
        echo json_encode([
            'success' => false,
            'error' => 'Birthplace information not found. Please complete your profile.'
        ]);
    } else {
        $eligibility = checkDocumentEligibility($birthplaceMunicipality, $birthplaceProvince);
        
        // Store in session for quick access
        $_SESSION['document_eligibility'] = $eligibility;
        
        echo json_encode([
            'success' => true,
            'data' => $eligibility
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => 'User not found'
    ]);
}

mysqli_close($conn);
?>
