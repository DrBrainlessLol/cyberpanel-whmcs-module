<?php
/** * CyberPanel WHMCS Module
 * 
 * Modernized version for WHMCS 8.0+ compatibility
 * Provides automated provisioning and management for CyberPanel hosting accounts
 * 
 * @author Romaine (https://github.com/DrBrainlessLol / https://github.com/InstaTechHD)
 * @version 2.0.0
 * @requires WHMCS 8.0+
 * @requires PHP 7.4+
 * @requires CyberPanel 2.0+
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

// Include API Class
require_once __DIR__ . '/api.php';

/**
 * Fallback logging function if WHMCS logModuleCall is not available
 */
if (!function_exists('logModuleCall')) {
    function logModuleCall($module, $action, $request, $response, $error = '') {
        // Fallback logging - you can customize this as needed
        $logData = [
            'module' => $module,
            'action' => $action,
            'timestamp' => date('Y-m-d H:i:s'),
            'request' => $request,
            'response' => $response,
            'error' => $error
        ];
        
        // Log to PHP error log or custom log file
        error_log('CyberPanel Module: ' . json_encode($logData));
    }
}

/**
 * Safe logging wrapper
 */
function cyberpanel_log($action, $params, $response, $error = '') {
    try {
        if (function_exists('logModuleCall')) {
            logModuleCall('cyberpanel', $action, $params, $response, $error);
        }
    } catch (Exception $e) {
        // Ignore logging errors to prevent them from affecting the main operation
    }
}

/**
 * Define module metadata
 */
function cyberpanel_MetaData()
{
    return [
        'DisplayName' => 'CyberPanel',
        'APIVersion' => '1.1',
        'RequiresServer' => true,
        'DefaultNonSSLPort' => '8090',
        'DefaultSSLPort' => '8090',
        'ServiceSingleSignOnLabel' => 'Login to Control Panel',
        'AdminSingleSignOnLabel' => 'Admin Login',
        'ListAccountsUniqueIdentifierField' => 'domain',
    ];
}

/**
 * Define module configuration options
 */
function cyberpanel_ConfigOptions()
{
    return [
        'Package Name' => [
            'Type' => 'text',
            'Default' => 'Default',
            'Description' => 'Package name for this hosting product in CyberPanel',
            'Size' => 25,
        ],
        'ACL' => [
            'Type' => 'dropdown',
            'Options' => 'user,reseller,admin',
            'Default' => 'user',
            'Description' => 'Access Control Level to assign to the user',
        ],
        'SSL Certificate' => [
            'Type' => 'yesno',
            'Default' => 'yes',
            'Description' => 'Automatically issue SSL certificate for new websites',
        ],
        'DKIM' => [
            'Type' => 'yesno',
            'Default' => 'yes',
            'Description' => 'Enable DKIM for email domains',
        ],
        'Open Base Directory' => [
            'Type' => 'yesno',
            'Default' => 'yes',
            'Description' => 'Enable PHP open_basedir protection',
        ],
        'PHP Version' => [
            'Type' => 'dropdown',
            'Options' => 'PHP 7.4,PHP 8.0,PHP 8.1,PHP 8.2,PHP 8.3',
            'Default' => 'PHP 8.1',
            'Description' => 'Default PHP version for new websites',
        ],
    ];
}


/**
 * Create a new hosting account
 *
 * @param array $params Module parameters
 * @return string Success message or error description
 */
function cyberpanel_CreateAccount(array $params)
{
    try {
        // Validate required parameters
        if (empty($params['domain']) || empty($params['username']) || empty($params['password'])) {
            return 'Missing required parameters: domain, username, or password';
        }

        // Validate email format
        if (!filter_var($params['clientsdetails']['email'], FILTER_VALIDATE_EMAIL)) {
            return 'Invalid email address provided';
        }

        // Initialize API wrapper
        $api = new CyberApi();
        $response = $api->create_new_account($params);        // Log the API call for debugging
        cyberpanel_log(__FUNCTION__, $params, $response);

        // Check for API response errors
        if (!$response || !isset($response['createWebSiteStatus'])) {
            return 'Invalid response from CyberPanel API';
        }

        // Check for creation failure
        if (!$response['createWebSiteStatus']) {
            $errorMessage = isset($response['error_message']) ? $response['error_message'] : 'Unknown error occurred';
            return "Account creation failed: $errorMessage";
        }

        return 'success';
        
    } catch (Exception $e) {        // Log detailed error information
        cyberpanel_log(__FUNCTION__, $params, $e->getMessage(), $e->getTraceAsString());

        return "Error creating account: " . $e->getMessage();
    }
}

/**
 * Suspend a hosting account
 *
 * @param array $params Module parameters
 * @return string Success message or error description
 */
function cyberpanel_SuspendAccount(array $params)
{
    try {
        // Validate required parameters
        if (empty($params['domain'])) {
            return 'Domain name is required for suspension';
        }        $params['status'] = 'Suspend';
        $api = new CyberApi();
        $response = $api->change_account_status($params);
        
        cyberpanel_log(__FUNCTION__, $params, $response);

        // Check for API response errors
        if (!$response || !isset($response['websiteStatus'])) {
            return 'Invalid response from CyberPanel API';
        }

        // Check for suspension failure
        if (!$response['websiteStatus']) {
            $errorMessage = isset($response['error_message']) ? $response['error_message'] : 'Unknown error occurred';
            return "Account suspension failed: $errorMessage";
        }

        return 'success';
          } catch (Exception $e) {
        cyberpanel_log(__FUNCTION__, $params, $e->getMessage(), $e->getTraceAsString());

        return "Error suspending account: " . $e->getMessage();
    }
}

/**
 * Unsuspend a hosting account
 *
 * @param array $params Module parameters
 * @return string Success message or error description
 */
function cyberpanel_UnsuspendAccount(array $params)
{
    try {
        // Validate required parameters
        if (empty($params['domain'])) {
            return 'Domain name is required for unsuspension';
        }

        $params['status'] = 'Unsuspend';
        $api = new CyberApi();        $response = $api->change_account_status($params);
        
        cyberpanel_log(__FUNCTION__, $params, $response);

        // Check for API response errors
        if (!$response || !isset($response['websiteStatus'])) {
            return 'Invalid response from CyberPanel API';
        }

        // Check for unsuspension failure
        if (!$response['websiteStatus']) {
            $errorMessage = isset($response['error_message']) ? $response['error_message'] : 'Unknown error occurred';
            return "Account unsuspension failed: $errorMessage";
        }

        return 'success';
          } catch (Exception $e) {
        cyberpanel_log(__FUNCTION__, $params, $e->getMessage(), $e->getTraceAsString());

        return "Error unsuspending account: " . $e->getMessage();
    }
}
/**
 * Terminate a hosting account
 *
 * @param array $params Module parameters
 * @return string Success message or error description
 */
function cyberpanel_TerminateAccount(array $params)
{
    try {
        // Validate required parameters
        if (empty($params['domain'])) {
            return 'Domain name is required for termination';
        }

        $api = new CyberApi();        $response = $api->terminate_account($params);
        
        cyberpanel_log(__FUNCTION__, $params, $response);

        // Check for API response errors
        if (!$response || !isset($response['websiteDeleteStatus'])) {
            return 'Invalid response from CyberPanel API';
        }

        // Check for termination failure
        if (!$response['websiteDeleteStatus']) {
            $errorMessage = isset($response['error_message']) ? $response['error_message'] : 'Unknown error occurred';
            return "Account termination failed: $errorMessage";
        }

        return 'success';
          } catch (Exception $e) {
        cyberpanel_log(__FUNCTION__, $params, $e->getMessage(), $e->getTraceAsString());

        return "Error terminating account: " . $e->getMessage();
    }
}

/**
 * Change account password
 *
 * @param array $params Module parameters
 * @return string Success message or error description
 */
function cyberpanel_ChangePassword(array $params)
{
    try {
        // Validate required parameters
        if (empty($params['username']) || empty($params['password'])) {
            return 'Username and password are required';
        }

        $api = new CyberApi();        $response = $api->change_account_password($params);
        
        cyberpanel_log(__FUNCTION__, $params, $response);

        // Check for API response errors
        if (!$response || !isset($response['changeStatus'])) {
            return 'Invalid response from CyberPanel API';
        }

        // Check for password change failure
        if (!$response['changeStatus']) {
            $errorMessage = isset($response['error_message']) ? $response['error_message'] : 'Unknown error occurred';
            return "Password change failed: $errorMessage";
        }

        return 'success';
          } catch (Exception $e) {
        cyberpanel_log(__FUNCTION__, $params, $e->getMessage(), $e->getTraceAsString());

        return "Error changing password: " . $e->getMessage();
    }
}

/**
 * Change hosting package
 *
 * @param array $params Module parameters
 * @return string Success message or error description
 */
function cyberpanel_ChangePackage(array $params)
{
    try {
        // Validate required parameters
        if (empty($params['domain']) || empty($params['configoption1'])) {
            return 'Domain and package name are required';
        }

        $api = new CyberApi();        $response = $api->change_account_package($params);
        
        cyberpanel_log(__FUNCTION__, $params, $response);

        // Check for API response errors
        if (!$response || !isset($response['changePackage'])) {
            return 'Invalid response from CyberPanel API';
        }

        // Check for package change failure
        if (!$response['changePackage']) {
            $errorMessage = isset($response['error_message']) ? $response['error_message'] : 'Unknown error occurred';
            return "Package change failed: $errorMessage";
        }

        return 'success';
          } catch (Exception $e) {
        cyberpanel_log(__FUNCTION__, $params, $e->getMessage(), $e->getTraceAsString());

        return "Error changing package: " . $e->getMessage();
    }
}

/**
 * Test connection to CyberPanel API
 *
 * @param array $params Module parameters
 * @return array Test result with success/failure status
 */
function cyberpanel_TestConnection(array $params)
{
    try {
        // Validate server credentials
        if (empty($params['serverusername']) || empty($params['serverpassword'])) {
            return [
                'success' => false,
                'error' => 'Server username and password are required',
            ];
        }

        $api = new CyberApi();        $response = $api->verify_connection($params);
        
        cyberpanel_log(__FUNCTION__, $params, $response);

        // Check for API response errors
        if (!$response || !isset($response['verifyConn'])) {
            return [
                'success' => false,
                'error' => 'Invalid response from CyberPanel API',
            ];
        }

        // Check connection status
        if (!$response['verifyConn']) {
            $errorMessage = isset($response['error_message']) ? $response['error_message'] : 'Connection verification failed';
            return [
                'success' => false,
                'error' => $errorMessage,
            ];
        }

        return [
            'success' => true,
            'error' => '',
        ];
          } catch (Exception $e) {
        cyberpanel_log(__FUNCTION__, $params, $e->getMessage(), $e->getTraceAsString());

        return [
            'success' => false,
            'error' => 'Connection test failed: ' . $e->getMessage(),
        ];
    }
}

/**
 * Client area login form
 *
 * @param array $params Module parameters
 * @return string HTML form for auto-login
 */
function cyberpanel_ClientArea($params) 
{
    // Input validation and sanitization
    $protocol = !empty($params["serversecure"]) ? "https" : "http";
    $hostname = htmlspecialchars($params["serverhostname"], ENT_QUOTES, 'UTF-8');
    $port = intval($params['serverport']);
    $username = htmlspecialchars($params["username"], ENT_QUOTES, 'UTF-8');
    $password = htmlspecialchars($params["password"], ENT_QUOTES, 'UTF-8');

    $loginUrl = "$protocol://$hostname:$port/api/loginAPI";

    $loginForm = '
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Control Panel Access</h3>
        </div>
        <div class="panel-body text-center">
            <form action="' . $loginUrl . '" method="post" target="_blank" class="form-inline">
                <input type="hidden" name="username" value="' . $username . '" />
                <input type="hidden" name="password" value="' . $password . '" />
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-sign-in"></i> Login to Control Panel
                </button>
            </form>
            <p class="help-block">Click to access your hosting control panel</p>
        </div>
    </div>';
    
    return $loginForm;
}

/**
 * Admin area login form
 *
 * @param array $params Module parameters
 * @return string HTML form for admin auto-login
 */
function cyberpanel_AdminLink($params) 
{
    // Input validation and sanitization
    $protocol = !empty($params["serversecure"]) ? "https" : "http";
    $hostname = htmlspecialchars($params["serverhostname"], ENT_QUOTES, 'UTF-8');
    $port = intval($params['serverport']);
    $serverUsername = htmlspecialchars($params["serverusername"], ENT_QUOTES, 'UTF-8');
    $serverPassword = htmlspecialchars($params["serverpassword"], ENT_QUOTES, 'UTF-8');

    $loginUrl = "$protocol://$hostname:$port/api/loginAPI";

    $loginForm = '
    <form action="' . $loginUrl . '" method="post" target="_blank" style="display: inline;">
        <input type="hidden" name="username" value="' . $serverUsername . '" />
        <input type="hidden" name="password" value="' . $serverPassword . '" />
        <button type="submit" class="btn btn-info btn-sm">
            <i class="fa fa-external-link"></i> Admin Login
        </button>
    </form>';
    
    return $loginForm;
}



