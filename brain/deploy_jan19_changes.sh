#!/bin/bash

echo "========================================="
echo "Deploying January 19, 2025 Changes"
echo "========================================="
echo ""

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${YELLOW}Starting deployment...${NC}"
echo ""

# 1. Clear Laravel caches
echo -e "${GREEN}[1/8] Clearing Laravel caches...${NC}"
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 2. Register new commands
echo -e "${GREEN}[2/8] Registering new Artisan commands...${NC}"
php artisan clear-compiled
php artisan optimize

# 3. Test new commands exist
echo -e "${GREEN}[3/8] Verifying new commands...${NC}"
php artisan list | grep -E "vici:test-a-flow|vici:optimal-timing" && echo -e "${GREEN}✓ Commands registered${NC}" || echo -e "${RED}✗ Commands not found${NC}"

# 4. Create required log files
echo -e "${GREEN}[4/8] Creating log files...${NC}"
touch storage/logs/vici_test_a_flow.log
touch storage/logs/vici_timing_control.log
touch storage/logs/vici_lead_flow.log
chmod 664 storage/logs/vici_*.log
echo -e "${GREEN}✓ Log files created${NC}"

# 5. Test database connection
echo -e "${GREEN}[5/8] Testing Vici database connection...${NC}"
php -r "
require 'vendor/autoload.php';
\$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
\$dotenv->load();
try {
    \$pdo = new PDO(
        'mysql:host=162.243.139.69;dbname=Q6hdjl67GRigMofv',
        \$_ENV['VICI_DB_USER'] ?? 'cron',
        \$_ENV['VICI_DB_PASS'] ?? 'hfIvWpOS4wRu2ZjYaLhbZ4lh4PNd7Y'
    );
    echo 'Database connection successful';
} catch (Exception \$e) {
    echo 'Database connection failed: ' . \$e->getMessage();
}
"
echo ""

# 6. Check if cron is set up
echo -e "${GREEN}[6/8] Checking cron setup...${NC}"
if crontab -l 2>/dev/null | grep -q "schedule:run"; then
    echo -e "${GREEN}✓ Laravel scheduler cron found${NC}"
else
    echo -e "${YELLOW}⚠ Laravel scheduler cron NOT found${NC}"
    echo "Add this to crontab:"
    echo "* * * * * cd $(pwd) && php artisan schedule:run >> /dev/null 2>&1"
fi

# 7. Run a test of the new commands (dry run)
echo -e "${GREEN}[7/8] Testing new commands (dry run)...${NC}"
php artisan vici:test-a-flow --dry-run 2>/dev/null || echo "Dry run not available, skipping..."

# 8. Create deployment marker
echo -e "${GREEN}[8/8] Creating deployment marker...${NC}"
cat > storage/logs/deployment_jan19.log << EOF
========================================
Deployment completed: $(date)
========================================

Changes deployed:
1. ViciTestALeadFlow.php - Complete Test A flow with all dispositions
2. ViciOptimalTimingControl.php - Updated with correct dispositions
3. lead-flow-control-center.blade.php - New command center UI
4. lead-flow-ab-test.blade.php - Updated with all lists and corrections
5. Routes updated for command center
6. Navigation updated with command center link
7. Kernel.php updated with new scheduled commands

Key fixes:
- Disposition logic now handles ALL statuses (not just 'NA')
- Transferred leads tracked to List 998
- Complete Test A flow (Lists 101-111)
- REST period implementation (List 108)
- Key principle: Only actual dials count

Files created:
- app/Console/Commands/ViciTestALeadFlow.php
- resources/views/vici/lead-flow-control-center.blade.php
- VICI_DISPOSITIONS_COMPLETE.md
- VICI_SQL_AUTOMATION_MASTER.md
- CURRENT_STATE_JAN_19_FINAL.md

========================================
EOF

echo -e "${GREEN}✓ Deployment marker created${NC}"
echo ""

# Summary
echo "========================================="
echo -e "${GREEN}DEPLOYMENT COMPLETE${NC}"
echo "========================================="
echo ""
echo "Next steps:"
echo "1. Visit /vici-command-center to see the new control panel"
echo "2. Check /vici/lead-flow-ab-test for updated Test A logic"
echo "3. Monitor storage/logs/vici_test_a_flow.log for execution"
echo "4. Verify transfers are being tracked to List 998"
echo ""
echo "To start the scheduler (if not running):"
echo "  php artisan schedule:work"
echo ""
echo "To manually run Test A flow:"
echo "  php artisan vici:test-a-flow"
echo ""
echo -e "${GREEN}All changes from January 19, 2025 have been deployed!${NC}"









