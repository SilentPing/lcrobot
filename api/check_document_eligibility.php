<?php
/**
 * Document Eligibility Check API
 * 
 * This API determines which document types a user can request based on their birthplace
 */

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

// Handle the request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($input['birthplace_municipality']) && isset($input['birthplace_province'])) {
        $result = checkDocumentEligibility(
            $input['birthplace_municipality'],
            $input['birthplace_province']
        );
        
        echo json_encode([
            'success' => true,
            'data' => $result
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Missing birthplace information'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request method'
    ]);
}
?>
