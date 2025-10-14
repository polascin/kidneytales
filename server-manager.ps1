# Kidney Tales Server Management Script
# This script helps switch between Apache and Nginx for kidneytales.local

param(
    [Parameter(Mandatory=$true)]
    [ValidateSet("nginx", "apache", "stop", "status")]
    [string]$Action
)

$NginxPath = "C:\laragon\bin\nginx\nginx-1.27.3\nginx.exe"
$ApachePath = "C:\laragon\bin\apache\httpd-2.4.62-240904-win64-VS17\bin\httpd.exe"

function Stop-AllServers {
    Write-Host "Stopping all web servers..." -ForegroundColor Yellow
    
    # Stop Nginx
    $nginxProcesses = Get-Process -Name "nginx" -ErrorAction SilentlyContinue
    if ($nginxProcesses) {
        $nginxProcesses | Stop-Process -Force
        Write-Host "✓ Nginx stopped" -ForegroundColor Green
    }
    
    # Stop Apache
    $apacheProcesses = Get-Process -Name "httpd" -ErrorAction SilentlyContinue
    if ($apacheProcesses) {
        $apacheProcesses | Stop-Process -Force
        Write-Host "✓ Apache stopped" -ForegroundColor Green
    }
    
    Start-Sleep -Seconds 2
}

function Start-Nginx {
    Write-Host "Starting Nginx..." -ForegroundColor Blue
    Stop-AllServers
    
    Set-Location (Split-Path $NginxPath)
    Start-Process -FilePath $NginxPath -WindowStyle Hidden
    Start-Sleep -Seconds 3
    
    $nginxProcesses = Get-Process -Name "nginx" -ErrorAction SilentlyContinue
    if ($nginxProcesses) {
        Write-Host "✓ Nginx started successfully" -ForegroundColor Green
        Write-Host "📱 HTTPS: https://kidneytales.local" -ForegroundColor Cyan
        Write-Host "🌐 HTTP: http://kidneytales.local (redirects to HTTPS)" -ForegroundColor Cyan
    } else {
        Write-Host "❌ Failed to start Nginx" -ForegroundColor Red
    }
}

function Start-Apache {
    Write-Host "Starting Apache..." -ForegroundColor Blue
    Stop-AllServers
    
    Set-Location (Split-Path $ApachePath)
    Start-Process -FilePath $ApachePath -WindowStyle Hidden
    Start-Sleep -Seconds 3
    
    $apacheProcesses = Get-Process -Name "httpd" -ErrorAction SilentlyContinue
    if ($apacheProcesses) {
        Write-Host "✓ Apache started successfully" -ForegroundColor Green
        Write-Host "📱 HTTPS: https://kidneytales.local" -ForegroundColor Cyan
        Write-Host "🌐 HTTP: http://kidneytales.local (redirects to HTTPS)" -ForegroundColor Cyan
    } else {
        Write-Host "❌ Failed to start Apache" -ForegroundColor Red
    }
}

function Show-Status {
    Write-Host "=== Kidney Tales Server Status ===" -ForegroundColor Magenta
    
    # Check processes
    $nginxProcesses = Get-Process -Name "nginx" -ErrorAction SilentlyContinue
    $apacheProcesses = Get-Process -Name "httpd" -ErrorAction SilentlyContinue
    
    if ($nginxProcesses) {
        Write-Host "🟢 Nginx: Running (PIDs: $($nginxProcesses.Id -join ', '))" -ForegroundColor Green
    } else {
        Write-Host "🔴 Nginx: Stopped" -ForegroundColor Red
    }
    
    if ($apacheProcesses) {
        Write-Host "🟢 Apache: Running (PIDs: $($apacheProcesses.Id -join ', '))" -ForegroundColor Green
    } else {
        Write-Host "🔴 Apache: Stopped" -ForegroundColor Red
    }
    
    # Check ports
    Write-Host "`n=== Port Status ===" -ForegroundColor Magenta
    $port80 = netstat -ano | Select-String ":80.*LISTENING"
    $port443 = netstat -ano | Select-String ":443.*LISTENING"
    
    if ($port80) {
        $pid80 = ($port80 -split '\s+')[-1]
        Write-Host "🟢 Port 80: In use by PID $pid80" -ForegroundColor Green
    } else {
        Write-Host "🔴 Port 80: Free" -ForegroundColor Red
    }
    
    if ($port443) {
        $pid443 = ($port443 -split '\s+')[-1]
        Write-Host "🟢 Port 443: In use by PID $pid443" -ForegroundColor Green
    } else {
        Write-Host "🔴 Port 443: Free" -ForegroundColor Red
    }
    
    # Test HTTPS
    Write-Host "`n=== Connection Test ===" -ForegroundColor Magenta
    try {
        $response = Invoke-WebRequest -Uri "https://kidneytales.local" -UseBasicParsing -SkipCertificateCheck -TimeoutSec 5
        Write-Host "🟢 HTTPS: Accessible (Status: $($response.StatusCode))" -ForegroundColor Green
        Write-Host "   Server: $($response.Headers.Server)" -ForegroundColor Gray
    } catch {
        Write-Host "🔴 HTTPS: Not accessible" -ForegroundColor Red
    }
}

# Main execution
switch ($Action) {
    "nginx" { Start-Nginx }
    "apache" { Start-Apache }
    "stop" { Stop-AllServers }
    "status" { Show-Status }
}

Write-Host "`n=== Quick Commands ===" -ForegroundColor Magenta
Write-Host "Switch to Nginx:  .\server-manager.ps1 nginx" -ForegroundColor Gray
Write-Host "Switch to Apache: .\server-manager.ps1 apache" -ForegroundColor Gray
Write-Host "Stop all:         .\server-manager.ps1 stop" -ForegroundColor Gray
Write-Host "Check status:     .\server-manager.ps1 status" -ForegroundColor Gray