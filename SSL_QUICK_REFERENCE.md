# Kidney Tales SSL Quick Reference

## 🚀 Quick Start

Access your application:
- **https://kidneytales.local** ← Use this!
- http://kidneytales.local (auto-redirects to HTTPS)

## 🔧 Server Management

```powershell
# Switch to Nginx
.\server-manager.ps1 nginx

# Switch to Apache  
.\server-manager.ps1 apache

# Check what's running
.\server-manager.ps1 status

# Stop all servers
.\server-manager.ps1 stop
```

## ✅ Current Status

Run from project root: `.\server-manager.ps1 status`

## 📍 Key Files

### Nginx
- Config: `C:\laragon\etc\nginx\sites-enabled\kidneytales.local.conf`
- Executable: `C:\laragon\bin\nginx\nginx-1.27.3\nginx.exe`

### Apache
- Config: `C:\laragon\etc\apache2\sites-enabled\kidneytales.local.conf`
- Executable: `C:\laragon\bin\apache\httpd-2.4.62-240904-win64-VS17\bin\httpd.exe`

### SSL Certificates
- Location: `C:\laragon\etc\ssl\kidneytales.local\`
- Cert: `kidneytales.local+1.pem`
- Key: `kidneytales.local+1-key.pem`

## 🔍 Testing

```powershell
# Test HTTPS (skip certificate validation)
curl -k -I https://kidneytales.local

# Test HTTP redirect
curl -I http://kidneytales.local

# Check listening ports
netstat -ano | Select-String ":80|:443" | Select-String "LISTENING"
```

## 🎯 What's Configured

✅ SSL/TLS encryption (TLS 1.2 + 1.3)  
✅ HTTP to HTTPS redirect  
✅ HTTP/2 support (Nginx)  
✅ Security headers (HSTS, CSP, X-Frame-Options, etc.)  
✅ Static asset caching  
✅ Hidden sensitive files (.env, .git, etc.)  
✅ Gzip compression (Nginx)  
✅ Access & error logs  

## 📚 Full Documentation

See `docs/LARAGON_SSL_SETUP.md` for complete details.

---

**Last Updated**: October 14, 2025