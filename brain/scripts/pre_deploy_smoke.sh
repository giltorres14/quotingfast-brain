#!/usr/bin/env bash
set -euo pipefail

BASE="https://quotingfast-brain-ohio.onrender.com"

echo "\n=== Pre-Deploy Smoke Test ===\n"

echo "1) Running PHP pre-deploy checks..."
php "$(dirname "$0")/../pre_deploy_check.php" || { echo "Pre-deploy check failed"; exit 1; }

echo "\n2) Hitting health endpoints..."
curl -s -o /dev/null -w "health: %{http_code}\n" "$BASE/health" | tee /dev/stderr | grep -q "200$" || exit 1
curl -s -o /dev/null -w "health/ui: %{http_code}\n" "$BASE/health/ui" | tee /dev/stderr | grep -Eq "^(200|204)$" || exit 1

echo "\n3) Validating critical UI pages..."
urls=(
  "$BASE/agent/lead/491801?mode=view&cb=$(date +%s)"
  "$BASE/agent/lead/491801?mode=edit&cb=$(date +%s)"
  "$BASE/leads?cb=$(date +%s)"
  "$BASE/admin?cb=$(date +%s)"
  "$BASE/admin/lead-duplicates?cb=$(date +%s)"
)

for u in "${urls[@]}"; do
  code=$(curl -s -o /dev/null -w "%{http_code}" "$u")
  echo "$u -> $code"
  [[ "$code" =~ ^(200|204)$ ]] || { echo "Fail: $u returned $code"; exit 1; }
done

echo "\n✅ Smoke tests passed. Safe to deploy.\n"

