#!/bin/bash
# Enhanced Vici Call Data Export Script
# Exports comprehensive call data with additional lead information
# Includes vendor_lead_code (Brain ID) for better matching

# Database parameters
DB_NAME=${1:-"Q6hdjl67GRigMofv"}  # Default to known Vici DB

# Time range - last 5 minutes by default
END_DATE=$(date +"%Y-%m-%d %H:%M:00")
START_DATE=$(date -d "$END_DATE -5 minutes" +"%Y-%m-%d %H:%M:00")

# Allow override for historical exports
if [ ! -z "$2" ]; then
    START_DATE="$2"
fi
if [ ! -z "$3" ]; then
    END_DATE="$3"
fi

# Generate timestamp and filename
now=$(date +"%Y%m%d%H%M%S")
FILENAME="vicidial_data_${now}.csv"

# Define file paths
REPORT_PATH="/home/vici_logs/"
MYSQL_OUTPUT="/var/lib/mysql-files/${FILENAME}"
FINAL_OUTPUT="${REPORT_PATH}${FILENAME}"

# Ensure directory exists
mkdir -p "${REPORT_PATH}"

echo "Exporting Vici data from ${START_DATE} to ${END_DATE}..."

# Execute MySQL export with enhanced fields
mysql -N -B <<EOF
USE ${DB_NAME};
SET SESSION group_concat_max_len = 1000000;
(
  SELECT 'call_date', 'lead_id', 'list_id', 'phone_number', 'campaign_id', 'status', 
         'length_in_sec', 'server_ip', 'extension', 'channel', 'outbound_cid', 
         'sip_hangup_cause', 'sip_hangup_reason', 'state', 'vendor_lead_code', 
         'user', 'term_reason', 'uniqueid', 'closecallid', 'comments', 
         'created_at', 'updated_at'
)
UNION ALL
(
  SELECT 
    vl.call_date,
    vl.lead_id,
    vl.list_id,
    vl.phone_number,
    vl.campaign_id,
    vl.status,
    vl.length_in_sec,
    IFNULL(vdl.server_ip, '') AS server_ip,
    IFNULL(vdl.extension, '') AS extension,
    IFNULL(vdl.channel, '') AS channel,
    IFNULL(TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(vdl.outbound_cid, '<', -1), '>', 1)), '') AS outbound_cid,
    IFNULL(vdl.sip_hangup_cause, '') AS sip_hangup_cause,
    IFNULL(vdl.sip_hangup_reason, '') AS sip_hangup_reason,
    IFNULL(vlist.state, '') AS state,
    IFNULL(vlist.vendor_lead_code, '') AS vendor_lead_code,  -- Brain ID!
    vl.user AS agent_id,
    vl.term_reason,
    vl.uniqueid,
    vl.closecallid,
    IFNULL(vl.comments, '') AS comments,
    NOW() AS created_at,
    NOW() AS updated_at
  FROM vicidial_log vl
  LEFT JOIN vicidial_dial_log vdl ON vl.lead_id = vdl.lead_id 
    AND vl.call_date = vdl.call_date  -- Match on both lead_id and call_date
  LEFT JOIN vicidial_list vlist ON vl.lead_id = vlist.lead_id  -- Get vendor_lead_code
  WHERE vl.call_date BETWEEN '${START_DATE}' AND '${END_DATE}'
    AND vl.campaign_id IS NOT NULL 
    AND vl.campaign_id != ''
  ORDER BY vl.call_date ASC
)
INTO OUTFILE '${MYSQL_OUTPUT}'
FIELDS TERMINATED BY ',' ENCLOSED BY '"' ESCAPED BY '\\\\'
LINES TERMINATED BY '\n';
EOF

# Check if export was successful
if [ $? -eq 0 ]; then
    # Move the CSV to final location
    mv "${MYSQL_OUTPUT}" "${FINAL_OUTPUT}"
    
    # Count records (minus header)
    RECORD_COUNT=$(($(wc -l < "${FINAL_OUTPUT}") - 1))
    
    echo "✅ Export successful!"
    echo "📊 Records exported: ${RECORD_COUNT}"
    echo "📁 CSV file: ${FINAL_OUTPUT}"
    
    # Optional: Trigger Brain import via webhook
    if [ ! -z "$BRAIN_WEBHOOK_URL" ]; then
        curl -X POST "$BRAIN_WEBHOOK_URL/vici-proxy/process-csv" \
             -H "Content-Type: application/json" \
             -d "{\"file\": \"${FINAL_OUTPUT}\", \"records\": ${RECORD_COUNT}}"
    fi
else
    echo "❌ Export failed!"
    exit 1
fi

# Cleanup old exports (keep last 7 days)
find ${REPORT_PATH} -name "vicidial_data_*.csv" -mtime +7 -delete

echo "Script completed at $(date)"

