
-- Create vici_call_metrics table if not exists
CREATE TABLE IF NOT EXISTS vici_call_metrics (
    id BIGSERIAL PRIMARY KEY,
    lead_id BIGINT,
    vendor_lead_code VARCHAR(255),
    uniqueid VARCHAR(255),
    call_date TIMESTAMP,
    phone_number VARCHAR(255),
    status VARCHAR(255),
    "user" VARCHAR(255),
    campaign_id VARCHAR(255),
    list_id INTEGER,
    length_in_sec INTEGER,
    call_status VARCHAR(255),
    matched_lead_id BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes if not exists
CREATE INDEX IF NOT EXISTS idx_vici_vendor_lead_code ON vici_call_metrics(vendor_lead_code);
CREATE INDEX IF NOT EXISTS idx_vici_matched_lead_id ON vici_call_metrics(matched_lead_id);
CREATE INDEX IF NOT EXISTS idx_vici_call_date ON vici_call_metrics(call_date);

-- Create orphan_call_logs table if not exists
CREATE TABLE IF NOT EXISTS orphan_call_logs (
    id BIGSERIAL PRIMARY KEY,
    uniqueid VARCHAR(255),
    lead_id VARCHAR(255),
    list_id INTEGER,
    campaign_id VARCHAR(255),
    call_date TIMESTAMP,
    start_epoch BIGINT,
    end_epoch BIGINT,
    length_in_sec INTEGER,
    status VARCHAR(255),
    phone_code VARCHAR(255),
    phone_number VARCHAR(255),
    "user" VARCHAR(255),
    comments TEXT,
    processed BOOLEAN DEFAULT FALSE,
    term_reason VARCHAR(255),
    vendor_lead_code VARCHAR(255),
    source_id VARCHAR(255),
    matched BOOLEAN DEFAULT FALSE,
    matched_lead_id BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes if not exists
CREATE INDEX IF NOT EXISTS idx_orphan_vendor_lead_code ON orphan_call_logs(vendor_lead_code);
CREATE INDEX IF NOT EXISTS idx_orphan_matched ON orphan_call_logs(matched);
CREATE INDEX IF NOT EXISTS idx_orphan_phone_number ON orphan_call_logs(phone_number);
