<?php
/**
 * CyberPanel API Debug Tool
 * Advanced debugging for CyberPanel API connectivity issues
 * 
 * @author Romaine (https://github.com/DrBrainlessLol / https://github.com/InstaTechHD)
 * @version 2.0.0
 */

require_once 'api.php';

echo "=== Detailed CyberPanel API Debug Test ===\n\n";

// Interactive configuration
echo "Enter your CyberPanel server details for debugging:\n";

echo "Server IP or hostname: ";
$hostname = trim(fgets(STDIN));

echo "Port [8090]: ";
$port = trim(fgets(STDIN));
if (empty($port)) $port = '8090';

echo "Use HTTPS? [y/N]: ";
$https = trim(fgets(STDIN));
$secure = (strtolower($https) === 'y' || strtolower($https) === 'yes');

echo "Admin username [admin]: ";
$username = trim(fgets(STDIN));
if (empty($username)) $username = 'admin';

echo "Admin password: ";
$password = trim(fgets(STDIN));

$config = [
    'serverhostname' => $hostname,
    'serverport' => $port,
    'serversecure' => $secure,
    'serverusername' => $username,
    'serverpassword' => $password,
];

echo "\n=== Starting Debug Tests ===\n\n";

$api = new CyberApi();

echo "Testing verifyConn API with full debug...\n";

// Test the verify connection with detailed output
try {
    $response = $api->verify_connection($config);
    echo "Raw API Response:\n";
    print_r($response);
    echo "\n";
    
    if (isset($response['verifyConn'])) {
        if ($response['verifyConn']) {
            echo "✅ API Connection verified successfully!\n";
        } else {
            echo "❌ API returned verifyConn = false\n";
            echo "Error message: " . ($response['error_message'] ?? 'No error message') . "\n";
        }
    } else {
        echo "❌ No 'verifyConn' field in response\n";
        echo "Available fields: " . implode(', ', array_keys($response ?: [])) . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Exception occurred: " . $e->getMessage() . "\n";
}

echo "\n";

// Test direct API call to see what's happening
echo "Testing direct API call to verifyConn...\n";

$protocol = $config['serversecure'] ? 'https' : 'http';
$url = "$protocol://{$config['serverhostname']}:{$config['serverport']}/api/verifyConn";
$postData = [
    "adminUser" => $config['serverusername'],
    "adminPass" => $config['serverpassword']
];

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($postData),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json',
    ],
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_VERBOSE => true,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "HTTP Status Code: $httpCode\n";
echo "cURL Error: " . ($curlError ?: 'None') . "\n";
echo "Raw Response: " . ($response ?: 'Empty') . "\n";

if ($response) {
    $decoded = json_decode($response, true);
    if ($decoded) {
        echo "Decoded JSON Response:\n";
        print_r($decoded);
    } else {
        echo "JSON decode error: " . json_last_error_msg() . "\n";
    }
}

echo "\n=== Debug Test Complete ===\n";
