# Laragon SSL Setup for Kidney Tales

## Overview
This document describes the SSL configuration for the Kidney Tales application running on Laragon with both Apache and Nginx support.

## 🔒 SSL Configuration

### Domain
- **Local Domain**: `kidneytales.local`
- **Wildcard Support**: `*.kidneytales.local`

### SSL Certificates
- **Location**: `C:\laragon\etc\ssl\kidneytales.local\`
- **Certificate**: `kidneytales.local+1.pem`
- **Private Key**: `kidneytales.local+1-key.pem`

### Hosts File
The domain is configured in Windows hosts file:
```
127.0.0.1      kidneytales.local    #laragon magic!
```
**Location**: `C:\Windows\System32\drivers\etc\hosts`

## 🌐 Server Configurations

### Nginx Configuration
**File**: `C:\laragon\etc\nginx\sites-enabled\kidneytales.local.conf`

**Features**:
- ✅ HTTP to HTTPS redirect (301)
- ✅ TLS 1.2 and TLS 1.3 support
- ✅ HTTP/2 enabled
- ✅ Strong cipher suites
- ✅ Security headers (HSTS, CSP, X-Frame-Options, etc.)
- ✅ PHP-FPM integration
- ✅ Static asset caching (1 year)
- ✅ Gzip compression
- ✅ Hidden dot files and sensitive files

**Document Root**: `C:/Users/polas/OneDrive/www/kidneytales/public`

**Ports**:
- HTTP: 80 (redirects to HTTPS)
- HTTPS: 443

### Apache Configuration
**File**: `C:\laragon\etc\apache2\sites-enabled\kidneytales.local.conf`

**Features**:
- ✅ HTTP to HTTPS redirect (301)
- ✅ TLS 1.2 and TLS 1.3 support
- ✅ Modern cipher suites
- ✅ Security headers (HSTS, CSP, X-Frame-Options, etc.)
- ✅ PHP integration via mod_fcgid
- ✅ Hidden dot files and sensitive files
- ✅ AllowOverride All for .htaccess

**Document Root**: `C:/Users/polas/OneDrive/www/kidneytales/public`

**Ports**:
- HTTP: 80 (redirects to HTTPS)
- HTTPS: 443

## 🛠️ Server Management

### Using the Server Manager Script
A PowerShell script `server-manager.ps1` is provided for easy server management.

#### Switch to Nginx
```powershell
.\server-manager.ps1 nginx
```

#### Switch to Apache
```powershell
.\server-manager.ps1 apache
```

#### Stop All Servers
```powershell
.\server-manager.ps1 stop
```

#### Check Status
```powershell
.\server-manager.ps1 status
```

### Manual Management

#### Nginx Commands
```powershell
# Start
cd C:\laragon\bin\nginx\nginx-1.27.3
.\nginx.exe

# Test configuration
.\nginx.exe -t

# Reload configuration
.\nginx.exe -s reload

# Stop
.\nginx.exe -s stop
```

#### Apache Commands
```powershell
# Start
cd C:\laragon\bin\apache\httpd-2.4.62-240904-win64-VS17\bin
.\httpd.exe

# Test configuration
.\httpd.exe -t

# Stop
.\httpd.exe -k stop

# Restart
.\httpd.exe -k restart
```

## 🔐 Security Features

### SSL/TLS Security
- **Protocols**: TLS 1.2, TLS 1.3
- **Cipher Suites**: Modern, strong ciphers only
- **Session Cache**: Enabled for performance
- **Session Tickets**: Disabled (Nginx), Off (Apache)

### HTTP Security Headers
Both servers implement the following security headers:

```
X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Referrer-Policy: no-referrer-when-downgrade
Content-Security-Policy: default-src 'self' http: https: data: blob: 'unsafe-inline'
Strict-Transport-Security: max-age=31536000; includeSubDomains
```

### File Protection
- ✅ Dot files (.env, .git, .htaccess) are denied
- ✅ Sensitive file extensions blocked (.sql, .log, .config, etc.)
- ✅ Directory listing disabled
- ✅ Symbolic links allowed

## 📊 Logging

### Nginx Logs
- **Access Log**: `C:/laragon/data/logs/kidneytales.local.access.log`
- **Error Log**: `C:/laragon/data/logs/kidneytales.local.error.log`

### Apache Logs
- **Access Log**: `C:/laragon/data/logs/kidneytales.local.access.log`
- **Error Log**: `C:/laragon/data/logs/kidneytales.local.error.log`
- **Log Level**: warn

## 🚀 Accessing the Application

### URLs
- **HTTPS (Secure)**: https://kidneytales.local
- **HTTP (Redirects)**: http://kidneytales.local → https://kidneytales.local

### Browser Setup
Since we're using a self-signed certificate, you'll need to:
1. Accept the security warning in your browser (first visit only)
2. Or install the certificate in your browser's trusted certificate store

### Testing from Command Line
```powershell
# Test HTTPS connection
curl -k -I https://kidneytales.local

# Test HTTP redirect
curl -I http://kidneytales.local
```

## 🔧 Troubleshooting

### Port Conflicts
Check what's using ports 80 and 443:
```powershell
netstat -ano | Select-String ":80|:443" | Select-String "LISTENING"
```

### Configuration Errors

#### Nginx
```powershell
cd C:\laragon\bin\nginx\nginx-1.27.3
.\nginx.exe -t
```

#### Apache
```powershell
cd C:\laragon\bin\apache\httpd-2.4.62-240904-win64-VS17\bin
.\httpd.exe -t
```

### Common Issues

#### "Cannot define multiple Listeners"
- Fixed by commenting out duplicate `Listen 443` in `C:\laragon\etc\apache2\httpd-ssl.conf`

#### "ExpiresActive command not found"
- The expires module is not enabled by default
- Configuration adjusted to not require mod_expires

#### SSL Certificate Issues
- Certificates are located in `C:\laragon\etc\ssl\kidneytales.local\`
- Laragon automatically generates these certificates

## 📝 Configuration Files Modified

### Nginx
1. `C:\laragon\etc\nginx\sites-enabled\kidneytales.local.conf` - Main site configuration
2. `C:\laragon\bin\nginx\nginx-1.27.3\conf\nginx.conf` - Main Nginx config (references sites-enabled)

### Apache
1. `C:\laragon\etc\apache2\sites-enabled\kidneytales.local.conf` - Main site configuration
2. `C:\laragon\etc\apache2\httpd-ssl.conf` - SSL configuration (Listen 443 commented out)
3. `C:\laragon\etc\apache2\sites-enabled\ssl.conf` - Disabled (renamed to .disabled)

## 🎯 Performance Optimizations

### Nginx
- HTTP/2 support for multiplexing
- Gzip compression for text files
- Static asset caching (1 year)
- FastCGI caching parameters

### Apache
- mod_fcgid for PHP processing
- Security headers with low overhead
- Optimized SSL session management

## 🔄 Switching Between Servers

Only one server can run at a time (both use ports 80 and 443).

**Current Active Server**: Check with `.\server-manager.ps1 status`

**To Switch**:
1. The script automatically stops the current server
2. Starts the requested server
3. Verifies the new server is running

## 📚 Additional Resources

- [Laragon Documentation](https://laragon.org/docs/)
- [Nginx SSL Configuration](https://nginx.org/en/docs/http/configuring_https_servers.html)
- [Apache SSL/TLS Configuration](https://httpd.apache.org/docs/2.4/ssl/)
- [Mozilla SSL Configuration Generator](https://ssl-config.mozilla.org/)

## ✅ Setup Checklist

- [x] SSL certificates generated for kidneytales.local
- [x] Domain added to hosts file
- [x] Nginx configuration with SSL
- [x] Apache configuration with SSL
- [x] HTTP to HTTPS redirect
- [x] Security headers implemented
- [x] File protection configured
- [x] Server management script created
- [x] Logging configured
- [x] Both servers tested and working

## 🎉 Success!

Your Kidney Tales application is now accessible via:
- **https://kidneytales.local** (Secure, SSL/TLS encrypted)
- All HTTP requests automatically redirect to HTTPS
- Both Nginx and Apache configurations are ready to use
- Easy switching between servers with the management script

---

**Last Updated**: October 14, 2025  
**Configured By**: AI Assistant  
**Status**: ✅ Production Ready