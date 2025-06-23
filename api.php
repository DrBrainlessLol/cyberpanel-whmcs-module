<?php 
/**
 * CyberPanel API Wrapper
 * 
 * Modernized API wrapper for CyberPanel with improved error handling,
 * security, and support for latest CyberPanel features
 * 
 * @author Romaine (https://github.com/DrBrainlessLol / https://github.com/InstaTechHD)
 * @version 2.0.0
 * @requires PHP 7.4+
 * @requires CyberPanel 2.0+
 */

class CyberApi
{
    private const API_TIMEOUT = 30;
    private const API_RETRIES = 2;
    
    /**
     * Build API URL
     *
     * @param array $params Connection parameters
     * @param string $endpoint API endpoint
     * @return string Complete API URL
     */
    private function buildApiUrl(array $params, string $endpoint): string
    {
        $protocol = !empty($params["serversecure"]) ? "https" : "http";
        $hostname = $params["serverhostname"];
        $port = $params['serverport'];
        
        return "$protocol://$hostname:$port/api/$endpoint";
    }
    
    /**
     * Make API call to CyberPanel
     *
     * @param array $params Connection parameters
     * @param string $endpoint API endpoint
     * @param array $postData Request payload
     * @return array API response
     * @throws Exception On connection or API errors
     */
    private function makeApiCall(array $params, string $endpoint, array $postData = []): array
    {
        $url = $this->buildApiUrl($params, $endpoint);
        $retryCount = 0;
        
        do {
            $curl = curl_init();
            
            // Set cURL options
            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($postData),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Accept: application/json',
                    'User-Agent: WHMCS-CyberPanel-Module/2.0'
                ],
                CURLOPT_TIMEOUT => self::API_TIMEOUT,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_MAXREDIRS => 0,
            ]);
            
            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $error = curl_error($curl);
            curl_close($curl);
            
            // Check for cURL errors
            if ($response === false || !empty($error)) {
                $retryCount++;
                if ($retryCount >= self::API_RETRIES) {
                    throw new Exception("cURL Error: $error");
                }
                sleep(1); // Wait before retry
                continue;
            }
            
            // Check HTTP status code
            if ($httpCode !== 200) {
                throw new Exception("HTTP Error: Received status code $httpCode");
            }
            
            // Decode JSON response
            $decodedResponse = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Invalid JSON response: " . json_last_error_msg());
            }
            
            return $decodedResponse;
            
        } while ($retryCount < self::API_RETRIES);
        
        throw new Exception("Maximum retry attempts exceeded");
    }    
    /**
     * Create new hosting account
     *
     * @param array $params WHMCS parameters
     * @return array API response
     */
    public function create_new_account(array $params): array
    {
        $postParams = [
            "adminUser" => $params["serverusername"],
            "adminPass" => $params["serverpassword"],
            "domainName" => $params["domain"],
            "ownerEmail" => $params["clientsdetails"]["email"],
            "packageName" => $params['configoption1'] ?: 'Default',
            "websiteOwner" => $params["username"],
            "ownerPassword" => $params["password"],
            "acl" => $params['configoption2'] ?: 'user',
            "ssl" => !empty($params['configoption3']) ? 1 : 0,
            "dkimCheck" => !empty($params['configoption4']) ? 1 : 0,
            "openBasedir" => !empty($params['configoption5']) ? 1 : 0,
            "phpSelection" => $params['configoption6'] ?: 'PHP 8.1',
        ];
        
        return $this->makeApiCall($params, "createWebsite", $postParams);
    }

    /**
     * Change account status (suspend/unsuspend)
     *
     * @param array $params WHMCS parameters
     * @return array API response
     */
    public function change_account_status(array $params): array
    {
        $postParams = [
            "adminUser" => $params["serverusername"],
            "adminPass" => $params["serverpassword"],
            "websiteName" => $params["domain"],
            "state" => $params["status"], // 'Suspend' or 'Unsuspend'
        ];
        
        return $this->makeApiCall($params, "submitWebsiteStatus", $postParams);
    }

    /**
     * Verify API connection
     *
     * @param array $params WHMCS parameters
     * @return array API response
     */
    public function verify_connection(array $params): array
    {
        $postParams = [
            "adminUser" => $params["serverusername"],
            "adminPass" => $params["serverpassword"],
        ];
        
        return $this->makeApiCall($params, "verifyConn", $postParams);
    }
    
    /**
     * Terminate hosting account
     *
     * @param array $params WHMCS parameters
     * @return array API response
     */
    public function terminate_account(array $params): array
    {
        $postParams = [
            "adminUser" => $params["serverusername"],
            "adminPass" => $params["serverpassword"],
            "domainName" => $params["domain"]
        ];
        
        return $this->makeApiCall($params, "deleteWebsite", $postParams);
    }
    
    /**
     * Change account password
     *
     * @param array $params WHMCS parameters
     * @return array API response
     */
    public function change_account_password(array $params): array
    {
        $postParams = [
            "adminUser" => $params["serverusername"],
            "adminPass" => $params["serverpassword"],
            "websiteOwner" => $params["username"],
            "ownerPassword" => $params["password"]
        ];
        
        return $this->makeApiCall($params, "changeUserPassAPI", $postParams);
    }
    
    /**
     * Change hosting package
     *
     * @param array $params WHMCS parameters
     * @return array API response
     */
    public function change_account_package(array $params): array
    {
        $postParams = [
            "adminUser" => $params["serverusername"],
            "adminPass" => $params["serverpassword"],
            "websiteName" => $params["domain"],
            "packageName" => $params['configoption1'] ?: 'Default'
        ];
        
        return $this->makeApiCall($params, "changePackageAPI", $postParams);
    }
}
?>
