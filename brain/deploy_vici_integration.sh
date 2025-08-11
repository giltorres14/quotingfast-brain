#!/bin/bash

# Brain to Vici Integration Deployment Script
# Run this on the production server after pulling latest code

echo "=========================================="
echo "  BRAIN TO VICI INTEGRATION DEPLOYMENT"
echo "=========================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}✓${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

# Step 1: Run database migrations
echo "Step 1: Running database migrations..."
php artisan migrate --force
if [ $? -eq 0 ]; then
    print_status "Database migrations completed"
else
    print_error "Database migrations failed"
    exit 1
fi

# Step 2: Clear caches
echo ""
echo "Step 2: Clearing application caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
if [ $? -eq 0 ]; then
    print_status "Caches cleared"
else
    print_warning "Some caches could not be cleared"
fi

# Step 3: Test Vici connection
echo ""
echo "Step 3: Testing ViciDial connection..."
php test_brain_to_vici_push.php
if [ $? -eq 0 ]; then
    print_status "ViciDial connection test completed"
else
    print_warning "ViciDial connection test had issues - check logs"
fi

# Step 4: Create SQL scripts directory
echo ""
echo "Step 4: Setting up ViciDial SQL scripts..."
VICI_SCRIPTS_DIR="/opt/vici_scripts"
if [ ! -d "$VICI_SCRIPTS_DIR" ]; then
    sudo mkdir -p $VICI_SCRIPTS_DIR
    print_status "Created $VICI_SCRIPTS_DIR directory"
else
    print_status "$VICI_SCRIPTS_DIR already exists"
fi

# Step 5: Display cron job instructions
echo ""
echo "Step 5: Cron Job Setup Instructions"
echo "====================================="
echo ""
print_warning "Add these lines to the ViciDial server crontab (crontab -e):"
echo ""
echo "# Brain to Vici Lead Flow Automation"
echo "# Every 15 minutes - Fast moves"
echo "*/15 * * * * mysql -u vicidial -p'password' asterisk < /opt/vici_scripts/move_101_102.sql"
echo "*/15 * * * * mysql -u vicidial -p'password' asterisk < /opt/vici_scripts/move_101_103_callbk.sql"
echo "*/15 * * * * mysql -u vicidial -p'password' asterisk < /opt/vici_scripts/move_103_104_lvm.sql"
echo "*/15 * * * * mysql -u vicidial -p'password' asterisk < /opt/vici_scripts/move_105_106_lvm.sql"
echo ""
echo "# Daily at 12:01 AM - Workday-based moves"
echo "1 0 * * * mysql -u vicidial -p'password' asterisk < /opt/vici_scripts/move_102_103_workdays.sql"
echo "1 0 * * * mysql -u vicidial -p'password' asterisk < /opt/vici_scripts/move_104_105_phase1.sql"
echo "1 0 * * * mysql -u vicidial -p'password' asterisk < /opt/vici_scripts/move_106_107_phase2.sql"
echo "1 0 * * * mysql -u vicidial -p'password' asterisk < /opt/vici_scripts/move_107_108_cooldown.sql"
echo "1 0 * * * mysql -u vicidial -p'password' asterisk < /opt/vici_scripts/move_108_110_archive.sql"
echo ""
echo "# Hourly - TCPA compliance check"
echo "0 * * * * mysql -u vicidial -p'password' asterisk < /opt/vici_scripts/tcpa_compliance_check.sql"
echo ""

# Step 6: Check environment configuration
echo "Step 6: Verifying environment configuration..."
echo ""
if grep -q "VICI_TEST_MODE=false" .env; then
    print_status "Production mode enabled (VICI_TEST_MODE=false)"
else
    print_warning "Test mode may be enabled - check VICI_TEST_MODE in .env"
fi

if grep -q "VICI_WEB_SERVER=philli.callix.ai" .env; then
    print_status "ViciDial server configured correctly"
else
    print_warning "ViciDial server may not be configured - check VICI_WEB_SERVER in .env"
fi

# Step 7: Test webhook endpoint
echo ""
echo "Step 7: Testing webhook endpoint..."
WEBHOOK_URL="https://quotingfast-brain-ohio.onrender.com/webhook.php"
HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X POST $WEBHOOK_URL)
if [ "$HTTP_STATUS" -eq 200 ] || [ "$HTTP_STATUS" -eq 422 ]; then
    print_status "Webhook endpoint is accessible (HTTP $HTTP_STATUS)"
else
    print_warning "Webhook returned HTTP $HTTP_STATUS - may need investigation"
fi

# Step 8: Summary
echo ""
echo "=========================================="
echo "  DEPLOYMENT SUMMARY"
echo "=========================================="
echo ""
print_status "Brain to Vici integration deployed"
echo ""
echo "Next Steps:"
echo "1. Copy SQL queries from VICIDIAL_CALL_FLOW_SQL_PLAYBOOK.md to $VICI_SCRIPTS_DIR"
echo "2. Add cron jobs to ViciDial server (shown above)"
echo "3. Run calendar table setup on ViciDial MySQL"
echo "4. Test with a real lead through the webhook"
echo "5. Monitor logs: tail -f storage/logs/laravel.log"
echo ""
echo "Documentation:"
echo "- BRAIN_TO_VICI_INTEGRATION.md"
echo "- VICIDIAL_CALL_FLOW_SQL_PLAYBOOK.md"
echo "- LEAD_FLOW_DOCUMENTATION.md"
echo ""
print_status "Deployment complete!"
echo ""
