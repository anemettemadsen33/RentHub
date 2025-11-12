# 🔑 SSH Key Setup for Forge - Guide

## Option 1: Use Existing SSH Key (Fastest)

### Step 1: Check if you already have an SSH key
```powershell
# Check for existing SSH keys
Get-ChildItem $env:USERPROFILE\.ssh
```

**Look for files like:**
- `id_rsa` and `id_rsa.pub` (RSA key)
- `id_ed25519` and `id_ed25519.pub` (ED25519 key - recommended)

---

### Step 2: Display your public key
```powershell
# If you have id_rsa.pub
Get-Content $env:USERPROFILE\.ssh\id_rsa.pub

# OR if you have id_ed25519.pub
Get-Content $env:USERPROFILE\.ssh\id_ed25519.pub
```

**Copy the entire output** (starts with `ssh-rsa` or `ssh-ed25519`)

---

### Step 3: Add to Forge

1. Go to: https://forge.laravel.com/servers/YOUR_SERVER_ID/keys
2. Click **"Add SSH Key"**
3. Paste your public key
4. Give it a name (e.g., "My Windows PC")
5. Click **Save**

---

## Option 2: Create New SSH Key (If you don't have one)

### Step 1: Generate new SSH key
```powershell
# Create .ssh directory if it doesn't exist
New-Item -ItemType Directory -Force -Path $env:USERPROFILE\.ssh

# Generate new ED25519 key (recommended)
ssh-keygen -t ed25519 -C "your-email@example.com"
```

**When prompted:**
- File location: Press `Enter` (use default: `C:\Users\YourName\.ssh\id_ed25519`)
- Passphrase: Press `Enter` twice (no passphrase for automation)

---

### Step 2: Display your new public key
```powershell
Get-Content $env:USERPROFILE\.ssh\id_ed25519.pub
```

Copy the entire output!

---

### Step 3: Add to Forge (same as Option 1, Step 3)

---

## Option 3: Use Forge's SSH Key (Download from Forge)

### Step 1: Download Forge's private key

1. Go to Forge dashboard: https://forge.laravel.com
2. Select your server
3. Go to **Meta** tab
4. Find **"SSH Key"** section
5. Click **"View Private Key"**
6. Copy the entire private key (including `-----BEGIN` and `-----END` lines)

---

### Step 2: Save to your computer
```powershell
# Create .ssh directory
New-Item -ItemType Directory -Force -Path $env:USERPROFILE\.ssh

# Save the key (paste key content when notepad opens)
notepad $env:USERPROFILE\.ssh\forge_key
```

**Paste the private key**, then save and close.

---

### Step 3: Set correct permissions
```powershell
# Fix permissions (important!)
icacls "$env:USERPROFILE\.ssh\forge_key" /inheritance:r
icacls "$env:USERPROFILE\.ssh\forge_key" /grant:r "$env:USERNAME:(R)"
```

---

### Step 4: Test connection
```powershell
# Test with explicit key
ssh -i $env:USERPROFILE\.ssh\forge_key forge@178.128.135.24
```

---

## Quick Test After Setup

```powershell
# Test connection
ssh forge@178.128.135.24

# If successful, you should see:
# forge@RentHub:~$

# Then test artisan:
cd renthub-tbj7yxj7.on-forge.com
php artisan --version
```

---

## Troubleshooting

### Error: "Permission denied (publickey)"
- Your public key is not added to Forge
- Follow Option 1 or Option 2 to add your key

### Error: "Connection timed out"
- Check firewall
- Verify server IP: `178.128.135.24`

### Error: "Too open permissions"
- Run the `icacls` commands from Option 3, Step 3

---

## After SSH is Working

Run the deployment script:
```powershell
pwsh .\deploy-to-forge.ps1
```

This will automatically:
1. ✅ Run migration (add is_admin column)
2. ✅ Seed database (5 test properties)
3. ✅ Create admin user (filament@renthub.com)
4. ✅ Verify API

---

## Quick Commands Summary

```powershell
# 1. Check existing keys
Get-ChildItem $env:USERPROFILE\.ssh

# 2. Show public key (copy this to Forge)
Get-Content $env:USERPROFILE\.ssh\id_ed25519.pub

# 3. Test connection
ssh forge@178.128.135.24

# 4. Run deployment
pwsh .\deploy-to-forge.ps1
```

---

**Choose your option and let me know if you need help!** 🚀
