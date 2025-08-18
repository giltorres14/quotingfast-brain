#!/bin/bash
# This script exports data with a header row into a CSV file.
# The CSV will include these columns in order:
# call_date, lead_id, list_id, phone_number, campaign_id, status, length_in_sec,
# server_ip, extension, channel, outbound_cid, sip_hangup_cause, sip_hangup_reason,
# state, created_at, updated_at
#
# It exports data from a RIGHT JOIN between vicidial_log and vicidial_dial_log,
# filtering by call_date and ensuring campaign_id is not NULL or empty.
# The created_at and updated_at columns are generated as NOW() values.
#
# outbound_cid is processed to extract only the number inside the angle brackets.
#
# The CSV file is first written to /var/lib/mysql-files and then moved to the REPORT_PATH.

# Database parameters
DB_NAME=$1
#DB_NAME="Colh42mUsWs40znH"
# START_DATE="2025-03-29 00:00:00"
START_DATE=$(date -d "$END_DATE 5 minutes ago" +"%Y-%m-%d %H:%M:00")
# END_DATE="2025-03-29 23:59:59"
END_DATE=$(date +"%Y-%m-%d %H:%M:00")

# Generate a timestamp and filename for the CSV
now=$(date +"%Y%m%d%H%M%S")
FILENAME="vicidial_data_${now}.csv"

# Define file paths (using your preferred REPORT_PATH)
REPORT_PATH="/home/vici_logs/"
MYSQL_OUTPUT="/var/lib/mysql-files/${FILENAME}"
FINAL_OUTPUT="${REPORT_PATH}${FILENAME}"

# Ensure the final output directory exists
mkdir -p "${REPORT_PATH}"

mysql -N -B <<EOF
USE ${DB_NAME};
SET SESSION group_concat_max_len = 1000000;
(
  SELECT 'call_date', 'lead_id', 'list_id', 'phone_number', 'campaign_id', 'status', 'length_in_sec', 'server_ip', 'extension', 'channel', 'outbound_cid', 'sip_hangup_cause', 'sip_hangup_reason', 'state', 'created_at', 'updated_at'
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
    vdl.server_ip,
    vdl.extension,
    vdl.channel,
    TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(vdl.outbound_cid, '<', -1), '>', 1)) AS outbound_cid,
    vdl.sip_hangup_cause,
    vdl.sip_hangup_reason,
    NULL,
    NOW(),
    NOW()
  FROM vicidial_log vl
  RIGHT JOIN vicidial_dial_log vdl ON vl.lead_id = vdl.lead_id
  WHERE vl.call_date BETWEEN '${START_DATE}' AND '${END_DATE}'
    AND vl.campaign_id IS NOT NULL 
    AND vl.campaign_id != ''
)
INTO OUTFILE '${MYSQL_OUTPUT}'
FIELDS TERMINATED BY ',' ENCLOSED BY '"' ESCAPED BY '\\\\'
LINES TERMINATED BY '\n';
EOF

# Move the generated CSV file to the final destination directory
mv "${MYSQL_OUTPUT}" "${FINAL_OUTPUT}"

echo "CSV file with header generated at: ${FINAL_OUTPUT}"


# This script exports data with a header row into a CSV file.
# The CSV will include these columns in order:
# call_date, lead_id, list_id, phone_number, campaign_id, status, length_in_sec,
# server_ip, extension, channel, outbound_cid, sip_hangup_cause, sip_hangup_reason,
# state, created_at, updated_at
#
# It exports data from a RIGHT JOIN between vicidial_log and vicidial_dial_log,
# filtering by call_date and ensuring campaign_id is not NULL or empty.
# The created_at and updated_at columns are generated as NOW() values.
#
# outbound_cid is processed to extract only the number inside the angle brackets.
#
# The CSV file is first written to /var/lib/mysql-files and then moved to the REPORT_PATH.

# Database parameters
DB_NAME=$1
#DB_NAME="Colh42mUsWs40znH"
# START_DATE="2025-03-29 00:00:00"
START_DATE=$(date -d "$END_DATE 5 minutes ago" +"%Y-%m-%d %H:%M:00")
# END_DATE="2025-03-29 23:59:59"
END_DATE=$(date +"%Y-%m-%d %H:%M:00")

# Generate a timestamp and filename for the CSV
now=$(date +"%Y%m%d%H%M%S")
FILENAME="vicidial_data_${now}.csv"

# Define file paths (using your preferred REPORT_PATH)
REPORT_PATH="/home/vici_logs/"
MYSQL_OUTPUT="/var/lib/mysql-files/${FILENAME}"
FINAL_OUTPUT="${REPORT_PATH}${FILENAME}"

# Ensure the final output directory exists
mkdir -p "${REPORT_PATH}"

mysql -N -B <<EOF
USE ${DB_NAME};
SET SESSION group_concat_max_len = 1000000;
(
  SELECT 'call_date', 'lead_id', 'list_id', 'phone_number', 'campaign_id', 'status', 'length_in_sec', 'server_ip', 'extension', 'channel', 'outbound_cid', 'sip_hangup_cause', 'sip_hangup_reason', 'state', 'created_at', 'updated_at'
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
    vdl.server_ip,
    vdl.extension,
    vdl.channel,
    TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(vdl.outbound_cid, '<', -1), '>', 1)) AS outbound_cid,
    vdl.sip_hangup_cause,
    vdl.sip_hangup_reason,
    NULL,
    NOW(),
    NOW()
  FROM vicidial_log vl
  RIGHT JOIN vicidial_dial_log vdl ON vl.lead_id = vdl.lead_id
  WHERE vl.call_date BETWEEN '${START_DATE}' AND '${END_DATE}'
    AND vl.campaign_id IS NOT NULL 
    AND vl.campaign_id != ''
)
INTO OUTFILE '${MYSQL_OUTPUT}'
FIELDS TERMINATED BY ',' ENCLOSED BY '"' ESCAPED BY '\\\\'
LINES TERMINATED BY '\n';
EOF

# Move the generated CSV file to the final destination directory
mv "${MYSQL_OUTPUT}" "${FINAL_OUTPUT}"

echo "CSV file with header generated at: ${FINAL_OUTPUT}"


