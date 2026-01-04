<?php
// Verify Leads API
$url = 'http://demo-crm.mysoftheaven.com/index.php/leads_api/save';

$data = [
    'owner_email' => 'mafiz@mysoftheaven.com',
    'entity_type' => 'organization',
    'client_name' => 'API Company ' . time(),
    'contact_person' => 'John Doe ' . time(),
    'contact_email' => 'api_contact_' . time() . '@example.com',
    'contact_phone' => '123-456-7890',
    'contact_job_title' => 'Director of API',
    'remarks' => 'This is a test remark.',
    'feedback' => 'This is test feedback.'
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo 'Error: ' . curl_error($ch);
} else {
    echo "HTTP Code: $httpCode\n";
    echo "Response: $response\n";
}

curl_close($ch);
