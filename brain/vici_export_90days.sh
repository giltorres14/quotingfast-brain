#!/bin/bash

# Database parameters
DB_NAME="Q6hdjl67GRigMofv"
START_DATE="2025-05-16 00:00:00"
END_DATE="2025-08-14 23:59:59"

# Generate filename
now=$(date +"%Y%m%d%H%M%S")
FILENAME="vici_call_logs_${now}.csv"

# File paths
REPORT_PATH="/tmp/"
MYSQL_OUTPUT="/var/lib/mysql-files/${FILENAME}"
FINAL_OUTPUT="${REPORT_PATH}${FILENAME}"

# Create directory if needed
mkdir -p "${REPORT_PATH}"

echo "Exporting Vici call logs..."
echo "Date range: ${START_DATE} to ${END_DATE}"

# Export the data with header
mysql -u root ${DB_NAME} -N -B <<EOF
SET SESSION group_concat_max_len = 1000000;
(
  SELECT 'call_date', 'lead_id', 'list_id', 'phone_number', 'campaign_id', 'status', 'length_in_sec', 
         'server_ip', 'extension', 'channel', 'outbound_cid', 'sip_hangup_cause', 'sip_hangup_reason', 
         'vendor_lead_code', 'user', 'term_reason', 'uniqueid'
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
    IFNULL(vdl.server_ip, ''),
    IFNULL(vdl.extension, ''),
    IFNULL(vdl.channel, ''),
    IFNULL(TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(vdl.outbound_cid, '<', -1), '>', 1)), ''),
    IFNULL(vdl.sip_hangup_cause, ''),
    IFNULL(vdl.sip_hangup_reason, ''),
    IFNULL(vl.vendor_lead_code, ''),
    IFNULL(vl.user, ''),
    IFNULL(vl.term_reason, ''),
    IFNULL(vl.uniqueid, '')
  FROM vicidial_log vl
  LEFT JOIN vicidial_dial_log vdl ON vl.lead_id = vdl.lead_id 
    AND vl.call_date = vdl.call_date
  WHERE vl.call_date BETWEEN '${START_DATE}' AND '${END_DATE}'
    AND vl.campaign_id IS NOT NULL 
    AND vl.campaign_id != ''
  ORDER BY vl.call_date DESC
  LIMIT 100000
)
INTO OUTFILE '${MYSQL_OUTPUT}'
FIELDS TERMINATED BY ',' ENCLOSED BY '"' ESCAPED BY '\\\\'
LINES TERMINATED BY '\n';
EOF

# Move file to accessible location
if [ -f "${MYSQL_OUTPUT}" ]; then
    mv "${MYSQL_OUTPUT}" "${FINAL_OUTPUT}"
    echo "Success! CSV exported to: ${FINAL_OUTPUT}"
    
    # Show file info
    wc -l "${FINAL_OUTPUT}"
    ls -lh "${FINAL_OUTPUT}"
    
    # Show first few lines
    echo ""
    echo "First 5 lines of data:"
    head -5 "${FINAL_OUTPUT}"
else
    echo "Error: Export failed"
    exit 1
fi