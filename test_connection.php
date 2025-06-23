<?php
/**
 * CyberPanel WHMCS Module Connection Test
 * 
 * Test connectivity to your CyberPanel server before deployment
 * 
 * Usage: php test_connection.php
 * 
 * @author Romaine (https://github.com/DrBrainlessLol / https://github.com/InstaTechHD)
 * @version 2.0.0
 */

// ====== CONFIGURATION ======
// Default configuration - will be prompted for user input if not set
$config = [
    'serverhostname' => '',      // Will prompt user for CyberPanel server IP/hostname
    'serverport' => '8090',      // CyberPanel port (usually 8090)
    'serversecure' => false,     // Set to true if using HTTPS
    'serverusername' => 'admin', // CyberPanel admin username (default)
    'serverpassword' => '',      // Will prompt user for admin password
];

// ====== INTERACTIVE CONFIGURATION ======
// Always prompt for configuration to avoid exposing server details
if (empty($config['serverhostname']) || empty($config['serverpassword'])) {
    
    echo "=== CyberPanel Connection Configuration ===\n";
    echo "Please configure your CyberPanel server details:\n\n";
    
    // Get server hostname
    echo "Enter your CyberPanel server IP or domain: ";
    $config['serverhostname'] = trim(fgets(STDIN));
    
    // Get port (with default)
    echo "Enter CyberPanel port [8090]: ";
    $port = trim(fgets(STDIN));
    if (!empty($port)) {
        $config['serverport'] = $port;
    }
    
    // Ask about HTTPS
    echo "Use HTTPS? [y/N]: ";
    $https = trim(fgets(STDIN));
    $config['serversecure'] = (strtolower($https) === 'y' || strtolower($https) === 'yes');
    
    // Get username (with default)
    echo "Enter admin username [admin]: ";
    $username = trim(fgets(STDIN));
    if (!empty($username)) {
        $config['serverusername'] = $username;
    }
    
    // Get password
    echo "Enter admin password: ";
    $config['serverpassword'] = trim(fgets(STDIN));
    
    echo "\n";
}

// ====== TEST FUNCTIONS ======

require_once 'api.php';

echo "=== CyberPanel WHMCS Module Connection Test ===\n\n";

// Test 1: Basic connectivity
echo "1. Testing basic connectivity...\n";
$api = new CyberApi();

try {
    $response = $api->verify_connection($config);
    
    if (isset($response['verifyConn']) && $response['verifyConn']) {
        echo "✅ Connection successful!\n";
        echo "   Server is reachable and credentials are valid.\n";
    } else {
        echo "❌ Connection failed!\n";
        echo "   Error: " . ($response['error_message'] ?? 'Unknown error') . "\n";
    }
} catch (Exception $e) {
    echo "❌ Connection test failed with exception!\n";
    echo "   Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: SSL Certificate Check (if HTTPS is enabled)
if ($config['serversecure']) {
    echo "2. Testing SSL certificate...\n";
    $url = "https://{$config['serverhostname']}:{$config['serverport']}";
    
    $context = stream_context_create([
        "ssl" => [
            "verify_peer" => true,
            "verify_peer_name" => true,
        ],
    ]);
    
    $result = @file_get_contents($url, false, $context);
    if ($result !== false) {
        echo "✅ SSL certificate is valid\n";
    } else {
        echo "⚠️  SSL certificate may have issues\n";
        echo "   Consider setting 'serversecure' to false for testing\n";
    }
    echo "\n";
}

// Test 3: API Endpoint availability
echo "3. Testing API endpoints...\n";

$endpoints = [
    'verifyConn' => 'Connection verification',
    'loginAPI' => 'Single sign-on'
];

foreach ($endpoints as $endpoint => $description) {
    $protocol = $config['serversecure'] ? 'https' : 'http';
    $url = "$protocol://{$config['serverhostname']}:{$config['serverport']}/api/$endpoint";
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_NOBODY => true,  // HEAD request
    ]);
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 || $httpCode === 405) { // 405 = Method Not Allowed (expected for GET on POST endpoint)
        echo "✅ $endpoint ($description)\n";
    } else {
        echo "❌ $endpoint ($description) - HTTP $httpCode\n";
    }
}

// Test actual API functionality
echo "\n3b. Testing actual API functionality...\n";
try {
    // Test the main API functions that will be used by WHMCS
    $testResponse = $api->verify_connection($config);
    if (isset($testResponse['verifyConn']) && $testResponse['verifyConn']) {
        echo "✅ API verify_connection working\n";
    } else {
        echo "❌ API verify_connection failed\n";
    }
} catch (Exception $e) {
    echo "❌ API test failed: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 4: System requirements
echo "4. Checking system requirements...\n";

// PHP Version
$phpVersion = PHP_VERSION;
echo "PHP Version: $phpVersion ";
if (version_compare($phpVersion, '7.4.0', '>=')) {
    echo "✅\n";
} else {
    echo "❌ (Requires PHP 7.4+)\n";
}

// cURL extension
echo "cURL Extension: ";
if (extension_loaded('curl')) {
    echo "✅ Available\n";
} else {
    echo "❌ Not available (Required)\n";
}

// JSON extension
echo "JSON Extension: ";
if (extension_loaded('json')) {
    echo "✅ Available\n";
} else {
    echo "❌ Not available (Required)\n";
}

echo "\n";

// Test 5: Security recommendations
echo "5. Security recommendations...\n";

if ($config['serversecure']) {
    echo "✅ HTTPS enabled for secure communication\n";
} else {
    echo "⚠️  Consider enabling HTTPS for production use\n";
}

if ($config['serverpassword'] === 'admin' || empty($config['serverpassword'])) {
    echo "❌ Default or empty password detected - please set a secure password!\n";
} else {
    echo "✅ Custom password configured\n";
}

echo "\n=== Test Complete ===\n";
echo "If all tests pass, your CyberPanel server is ready for WHMCS integration.\n";
echo "Configure your WHMCS server settings with the details you provided.\n";
echo "\nNeed help? Contact @brainlessintellect on Discord\n";
echo "Support development: https://paypal.me/rawflyanime\n";
