# CyberPanel WHMCS Module Connection Test (PowerShell)
# Windows PowerShell version of the connection test
# 
# Author: Romaine (https://github.com/DrBrainlessLol / https://github.com/InstaTechHD)
# Version: 2.0.0

param(
    [string]$ServerIP = "",
    [string]$Port = "8090",
    [string]$Username = "admin",
    [string]$Password = "",
    [switch]$UseHTTPS = $false
)

# Colors for output
$colors = @{
    Red = "Red"
    Green = "Green"
    Yellow = "Yellow"
    Blue = "Cyan"
    White = "White"
}

function Write-Status {
    param([string]$Message, [string]$Color = "Cyan")
    Write-Host "[INFO] $Message" -ForegroundColor $Color
}

function Write-Success {
    param([string]$Message)
    Write-Host "✅ $Message" -ForegroundColor Green
}

function Write-Warning {
    param([string]$Message)
    Write-Host "⚠️  $Message" -ForegroundColor Yellow
}

function Write-Error {
    param([string]$Message)
    Write-Host "❌ $Message" -ForegroundColor Red
}

Write-Host "===============================================" -ForegroundColor Blue
Write-Host "CyberPanel WHMCS Module Connection Test" -ForegroundColor Blue
Write-Host "Windows PowerShell Version" -ForegroundColor Blue
Write-Host "===============================================" -ForegroundColor Blue
Write-Host ""

# Get server details if not provided
if (-not $ServerIP) {
    $ServerIP = Read-Host "Enter your CyberPanel server IP or hostname"
}

if (-not $Port) {
    $Port = Read-Host "Enter CyberPanel port (default: 8090)"
    if (-not $Port) { $Port = "8090" }
}

$httpsInput = Read-Host "Use HTTPS? (y/N)"
$UseHTTPS = ($httpsInput -eq "y" -or $httpsInput -eq "Y" -or $httpsInput -eq "yes")

if (-not $Username) {
    $usernameInput = Read-Host "Enter admin username (default: admin)"
    if ($usernameInput) { $Username = $usernameInput }
}

if (-not $Password) {
    $securePassword = Read-Host "Enter admin password" -AsSecureString
    $Password = [Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToBSTR($securePassword))
}

Write-Host ""
Write-Status "Starting connection tests..."
Write-Host ""

# Test 1: Basic connectivity
Write-Status "1. Testing basic connectivity..."

$protocol = if ($UseHTTPS) { "https" } else { "http" }
$baseUrl = "$protocol://$ServerIP`:$Port"

try {
    $response = Invoke-WebRequest -Uri "$baseUrl/login/" -Method Head -TimeoutSec 10 -SkipCertificateCheck -ErrorAction Stop
    Write-Success "Server is reachable on $protocol://$ServerIP`:$Port"
} catch {
    Write-Error "Cannot reach server at $baseUrl"
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "Check: Server IP, port accessibility, firewall settings" -ForegroundColor Yellow
}

Write-Host ""

# Test 2: SSL Certificate (if HTTPS)
if ($UseHTTPS) {
    Write-Status "2. Testing SSL certificate..."
    
    try {
        $response = Invoke-WebRequest -Uri $baseUrl -Method Head -TimeoutSec 10 -ErrorAction Stop
        Write-Success "SSL certificate is valid"
    } catch {
        Write-Warning "SSL certificate may have issues"
        Write-Host "Consider using HTTP for testing or fixing SSL certificate" -ForegroundColor Yellow
    }
    Write-Host ""
}

# Test 3: API endpoint availability
Write-Status "3. Testing API endpoints..."

$endpoints = @{
    "verifyConn" = "Connection verification"
    "loginAPI" = "Single sign-on"
    "createWebsite" = "Website creation"
    "deleteWebsite" = "Website deletion"
}

foreach ($endpoint in $endpoints.GetEnumerator()) {
    $apiUrl = "$baseUrl/api/$($endpoint.Key)"
    
    try {
        $response = Invoke-WebRequest -Uri $apiUrl -Method Head -TimeoutSec 5 -SkipCertificateCheck -ErrorAction Stop
        Write-Success "$($endpoint.Key) ($($endpoint.Value))"
    } catch {
        $statusCode = $_.Exception.Response.StatusCode.value__
        if ($statusCode -eq 405) {
            Write-Success "$($endpoint.Key) ($($endpoint.Value)) - Endpoint available"
        } else {
            Write-Error "$($endpoint.Key) ($($endpoint.Value)) - HTTP $statusCode"
        }
    }
}

Write-Host ""

# Test 4: API Authentication
Write-Status "4. Testing API authentication..."

$authData = @{
    adminUser = $Username
    adminPass = $Password
} | ConvertTo-Json

$headers = @{
    'Content-Type' = 'application/json'
    'Accept' = 'application/json'
}

try {
    $authUrl = "$baseUrl/api/verifyConn"
    $response = Invoke-RestMethod -Uri $authUrl -Method Post -Body $authData -Headers $headers -TimeoutSec 15 -SkipCertificateCheck -ErrorAction Stop
    
    if ($response.verifyConn -eq $true) {
        Write-Success "API authentication successful"
        Write-Success "Credentials are valid and API access is working"
    } else {
        Write-Error "API authentication failed"
        Write-Host "Response: $($response | ConvertTo-Json)" -ForegroundColor Red
    }
} catch {
    Write-Error "API authentication test failed"
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
    Write-Warning "Check username, password, and API access permissions"
}

Write-Host ""

# Test 5: System requirements (Windows/PowerShell environment)
Write-Status "5. Checking system requirements..."

# PowerShell version
$psVersion = $PSVersionTable.PSVersion
Write-Host "PowerShell Version: $psVersion " -NoNewline
if ($psVersion.Major -ge 5) {
    Write-Success ""
} else {
    Write-Error "(Requires PowerShell 5.0+)"
}

# .NET Framework
$netVersion = [System.Environment]::Version
Write-Host ".NET Version: $netVersion " -NoNewline
Write-Success ""

# Web request capability
Write-Host "Web Request Support: " -NoNewline
try {
    Invoke-WebRequest -Uri "https://httpbin.org/status/200" -Method Head -TimeoutSec 5 -ErrorAction Stop | Out-Null
    Write-Success "Available"
} catch {
    Write-Error "Limited (may affect HTTPS connectivity)"
}

Write-Host ""

# Test 6: Security recommendations
Write-Status "6. Security recommendations..."

if ($UseHTTPS) {
    Write-Success "HTTPS enabled for secure communication"
} else {
    Write-Warning "Consider enabling HTTPS for production use"
}

if ($Password -eq "admin" -or $Password -eq "password" -or $Password.Length -lt 8) {
    Write-Error "Weak password detected - use a strong password!"
} else {
    Write-Success "Strong password configured"
}

Write-Host ""
Write-Host "===============================================" -ForegroundColor Blue
Write-Host "Test Complete" -ForegroundColor Blue
Write-Host "===============================================" -ForegroundColor Blue

Write-Host ""
Write-Host "Next Steps:" -ForegroundColor Yellow
Write-Host "1. If all tests pass, configure WHMCS server settings" -ForegroundColor White
Write-Host "2. Set up hosting products with matching package names" -ForegroundColor White
Write-Host "3. Test account creation with a sample order" -ForegroundColor White
Write-Host "4. Check README.md for detailed setup instructions" -ForegroundColor White

Write-Host ""
Write-Host "Need Help?" -ForegroundColor Yellow
Write-Host "- GitHub: https://github.com/DrBrainlessLol/cyberpanel-whmcs-module" -ForegroundColor White
Write-Host "- Discord: @brainlessintellect" -ForegroundColor White
Write-Host "- Donate: https://paypal.me/rawflyanime" -ForegroundColor White
