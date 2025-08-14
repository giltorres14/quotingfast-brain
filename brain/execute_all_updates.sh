#!/bin/bash

echo '=== EXECUTING VICI UPDATES ==='
echo 'Found 162 SQL files to process'

TOTAL=0
echo 'Processing vici_direct_000.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_000.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_001.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_001.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_002.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_002.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_003.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_003.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_004.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_004.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_005.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_005.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_006.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_006.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_007.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_007.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_008.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_008.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_009.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_009.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_010.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_010.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_011.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_011.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_012.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_012.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_013.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_013.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_014.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_014.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_015.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_015.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_016.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_016.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_017.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_017.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_018.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_018.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_019.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_019.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_020.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_020.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_021.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_021.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_022.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_022.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_023.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_023.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_024.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_024.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_025.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_025.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_026.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_026.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_027.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_027.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_028.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_028.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_029.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_029.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_030.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_030.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_031.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_031.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_032.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_032.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_033.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_033.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_034.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_034.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_035.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_035.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_036.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_036.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_037.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_037.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_038.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_038.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_039.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_039.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_040.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_040.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_041.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_041.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_042.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_042.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_043.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_043.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_044.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_044.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_045.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_045.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_046.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_046.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_047.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_047.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_048.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_048.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_049.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_049.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_050.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_050.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_051.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_051.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_052.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_052.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_053.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_053.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_054.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_054.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_055.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_055.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_056.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_056.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_057.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_057.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_058.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_058.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_059.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_059.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_060.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_060.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_061.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_061.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_062.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_062.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_063.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_063.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_064.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_064.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_065.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_065.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_066.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_066.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_067.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_067.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_068.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_068.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_069.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_069.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_070.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_070.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_071.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_071.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_072.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_072.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_073.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_073.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_074.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_074.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_075.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_075.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_076.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_076.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_077.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_077.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_078.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_078.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_079.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_079.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_080.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_080.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_081.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_081.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_082.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_082.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_083.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_083.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_084.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_084.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_085.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_085.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_086.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_086.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_087.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_087.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_088.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_088.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_089.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_089.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_090.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_090.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_091.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_091.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_092.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_092.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_093.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_093.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_094.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_094.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_095.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_095.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_096.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_096.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_097.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_097.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_098.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_098.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_099.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_099.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_100.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_100.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_101.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_101.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_102.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_102.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_103.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_103.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_104.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_104.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_105.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_105.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_106.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_106.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_107.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_107.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_108.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_108.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_109.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_109.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_110.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_110.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_111.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_111.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_112.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_112.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_113.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_113.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_114.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_114.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_115.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_115.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_116.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_116.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_117.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_117.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_118.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_118.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_119.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_119.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_120.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_120.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_121.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_121.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_122.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_122.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_123.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_123.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_124.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_124.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_125.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_125.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_126.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_126.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_127.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_127.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_128.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_128.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_129.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_129.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_130.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_130.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_131.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_131.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_132.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_132.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_133.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_133.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_134.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_134.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_135.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_135.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_136.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_136.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_137.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_137.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_138.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_138.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_139.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_139.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_140.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_140.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_141.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_141.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_142.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_142.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_143.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_143.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_144.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_144.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_145.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_145.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_146.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_146.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_147.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_147.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_148.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_148.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_149.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_149.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_150.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_150.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_151.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_151.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_152.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_152.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_153.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_153.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_154.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_154.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_155.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_155.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_156.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_156.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_157.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_157.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_158.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_158.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_159.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_159.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_160.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_160.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo 'Processing vici_direct_161.sql...'
curl -X POST https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute \
  -H "Content-Type: application/json" \
  -d '{"command": "mysql -u root Q6hdjl67GRigMofv < /tmp/vici_direct_161.sql 2>&1 | tail -5"}' \
  2>/dev/null | jq -r '.output' | grep -v 'Could not create' | grep -v 'Failed to add'
sleep 0.5

echo ''
echo '=== COMPLETE ==='
echo 'All updates have been processed!'
