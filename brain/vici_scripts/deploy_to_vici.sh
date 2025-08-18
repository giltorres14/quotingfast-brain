#!/bin/bash
# deploy_to_vici.sh
# Deploy lead flow scripts to Vici server

# Vici server details
VICI_HOST="66.175.219.105"
VICI_PORT="22"
VICI_USER="Superman"
VICI_PASS="8ZDWGAAQRD"
REMOTE_PATH="/opt/vici_scripts"

echo "=== DEPLOYING VICI LEAD FLOW SCRIPTS ==="
echo ""

# Create remote directory if it doesn't exist
echo "📁 Creating remote directory..."
sshpass -p "$VICI_PASS" ssh -p $VICI_PORT -o StrictHostKeyChecking=no $VICI_USER@$VICI_HOST "mkdir -p $REMOTE_PATH"

# List of scripts to deploy
SCRIPTS=(
    "move_101_102.sql"
    "move_101_103_callbk.sql"
    "move_102_103_workdays.sql"
    "move_103_104_lvm.sql"
    "move_104_105_phase1.sql"
    "move_105_106_lvm.sql"
    "move_106_107_phase2.sql"
    "move_107_108_cooldown.sql"
    "move_108_110_archive.sql"
    "tcpa_compliance_check.sql"
)

# Deploy each script
echo "📤 Deploying SQL scripts..."
for script in "${SCRIPTS[@]}"; do
    echo "   Uploading $script..."
    sshpass -p "$VICI_PASS" scp -P $VICI_PORT -o StrictHostKeyChecking=no "$script" $VICI_USER@$VICI_HOST:$REMOTE_PATH/
    if [ $? -eq 0 ]; then
        echo "   ✅ $script deployed"
    else
        echo "   ❌ Failed to deploy $script"
    fi
done

echo ""
echo "📋 Setting permissions..."
sshpass -p "$VICI_PASS" ssh -p $VICI_PORT -o StrictHostKeyChecking=no $VICI_USER@$VICI_HOST "chmod 644 $REMOTE_PATH/*.sql"

echo ""
echo "🔍 Verifying deployment..."
sshpass -p "$VICI_PASS" ssh -p $VICI_PORT -o StrictHostKeyChecking=no $VICI_USER@$VICI_HOST "ls -la $REMOTE_PATH/*.sql | wc -l"

echo ""
echo "=== DEPLOYMENT COMPLETE ==="
echo ""
echo "📝 Next steps:"
echo "1. SSH to Vici server"
echo "2. Review crontab_entries.txt"
echo "3. Add cron jobs: crontab -e"
echo "4. Test with a single lead in List 101"
echo ""
echo "To test a script manually:"
echo "   mysql -u root Q6hdjl67GRigMofv < $REMOTE_PATH/move_101_102.sql"
echo ""
echo "To monitor lead flow:"
echo "   mysql -u root Q6hdjl67GRigMofv -e 'SELECT * FROM lead_flow_dashboard'"


# deploy_to_vici.sh
# Deploy lead flow scripts to Vici server

# Vici server details
VICI_HOST="66.175.219.105"
VICI_PORT="22"
VICI_USER="Superman"
VICI_PASS="8ZDWGAAQRD"
REMOTE_PATH="/opt/vici_scripts"

echo "=== DEPLOYING VICI LEAD FLOW SCRIPTS ==="
echo ""

# Create remote directory if it doesn't exist
echo "📁 Creating remote directory..."
sshpass -p "$VICI_PASS" ssh -p $VICI_PORT -o StrictHostKeyChecking=no $VICI_USER@$VICI_HOST "mkdir -p $REMOTE_PATH"

# List of scripts to deploy
SCRIPTS=(
    "move_101_102.sql"
    "move_101_103_callbk.sql"
    "move_102_103_workdays.sql"
    "move_103_104_lvm.sql"
    "move_104_105_phase1.sql"
    "move_105_106_lvm.sql"
    "move_106_107_phase2.sql"
    "move_107_108_cooldown.sql"
    "move_108_110_archive.sql"
    "tcpa_compliance_check.sql"
)

# Deploy each script
echo "📤 Deploying SQL scripts..."
for script in "${SCRIPTS[@]}"; do
    echo "   Uploading $script..."
    sshpass -p "$VICI_PASS" scp -P $VICI_PORT -o StrictHostKeyChecking=no "$script" $VICI_USER@$VICI_HOST:$REMOTE_PATH/
    if [ $? -eq 0 ]; then
        echo "   ✅ $script deployed"
    else
        echo "   ❌ Failed to deploy $script"
    fi
done

echo ""
echo "📋 Setting permissions..."
sshpass -p "$VICI_PASS" ssh -p $VICI_PORT -o StrictHostKeyChecking=no $VICI_USER@$VICI_HOST "chmod 644 $REMOTE_PATH/*.sql"

echo ""
echo "🔍 Verifying deployment..."
sshpass -p "$VICI_PASS" ssh -p $VICI_PORT -o StrictHostKeyChecking=no $VICI_USER@$VICI_HOST "ls -la $REMOTE_PATH/*.sql | wc -l"

echo ""
echo "=== DEPLOYMENT COMPLETE ==="
echo ""
echo "📝 Next steps:"
echo "1. SSH to Vici server"
echo "2. Review crontab_entries.txt"
echo "3. Add cron jobs: crontab -e"
echo "4. Test with a single lead in List 101"
echo ""
echo "To test a script manually:"
echo "   mysql -u root Q6hdjl67GRigMofv < $REMOTE_PATH/move_101_102.sql"
echo ""
echo "To monitor lead flow:"
echo "   mysql -u root Q6hdjl67GRigMofv -e 'SELECT * FROM lead_flow_dashboard'"






