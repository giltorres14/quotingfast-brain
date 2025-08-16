# Vici Server Whitelist Instructions

## Current Status
- **Render IP to Whitelist:** 3.134.238.10
- **Vici Server:** 37.27.138.222
- **SSH Port:** 11845 (custom port, not 22!)
- **Status:** SSH port 11845 is currently BLOCKED

## Commands to Run on Vici Server

Connect to Vici server first:
```bash
ssh -p 11845 root@37.27.138.222
# Password: Monster@2213@!
```

Then run ONE of these methods to whitelist the Render IP:

### Method 1: Using iptables (immediate effect)
```bash
iptables -I INPUT -s 3.134.238.10 -j ACCEPT
iptables-save > /etc/iptables/rules.v4
```

### Method 2: Using hosts.allow (for SSH only)
```bash
echo 'sshd: 3.134.238.10' >> /etc/hosts.allow
```

### Method 3: Using firewalld (if installed)
```bash
firewall-cmd --permanent --add-source=3.134.238.10
firewall-cmd --reload
```

### Method 4: If using UFW
```bash
ufw allow from 3.134.238.10 to any port 11845
ufw reload
```

## All 3 Render IPs (for future reference)
If you want to whitelist all potential Render IPs at once:
```bash
# All three Render Ohio IPs
iptables -I INPUT -s 3.134.238.10 -j ACCEPT
iptables -I INPUT -s 3.129.111.220 -j ACCEPT
iptables -I INPUT -s 52.15.118.168 -j ACCEPT
iptables-save > /etc/iptables/rules.v4
```

## To Verify Whitelist is Working
After whitelisting, test from our side:
```bash
curl "https://quotingfast-brain-ohio.onrender.com/vici-proxy/test"
```

Should show:
- `"ssh_port_11845": "open"`
- `"message": "SSH port 11845 is open - ready to connect!"`

## Testing Vici Lead Update
Once whitelisted, we can test updating Vici leads with Brain IDs:
```bash
# From deployed Brain app:
php artisan vici:update-brain-ids --test

# Or for a specific phone:
php artisan vici:update-brain-ids --phone=8064378907
```

## Important Notes
- SSH is on port **11845**, not the default 22
- MySQL connections will go through SSH tunnel
- The Brain app will connect from Render's IP, not local machines


