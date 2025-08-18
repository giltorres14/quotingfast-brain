<?php

// Read the current file
$content = file_get_contents('resources/views/agent/lead-display.blade.php');

// Find and replace the IP Address section
$ipAddressOld = '                <!-- IP Address -->
                <div class="info-item">
                    <div class="info-label">IP Address</div>
                    <div class="info-value">
                        {{ $lead->ip_address ?: \'Not provided\' }}
                        @if($lead->ip_address)
                            <button class="copy-btn" onclick="copyToClipboard(\'{{ $lead->ip_address }}\', this)" style="background: #10b981; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; margin-left: 8px;">📋</button>
                        @endif
                    </div>
                </div>';

$ipAddressNew = '                <!-- IP Address -->
                <div class="info-item">
                    <div class="info-label">IP Address</div>
                    <div class="info-value">
                        @php
                            $ipAddress = $lead->ip_address;
                            // Check meta field
                            if (!$ipAddress && $lead->meta) {
                                $meta = is_string($lead->meta) ? json_decode($lead->meta, true) : $lead->meta;
                                $ipAddress = $meta[\'ip_address\'] ?? null;
                            }
                            // Check payload
                            if (!$ipAddress && $lead->payload) {
                                $payload = is_string($lead->payload) ? json_decode($lead->payload, true) : $lead->payload;
                                $ipAddress = $payload[\'contact\'][\'ip_address\'] ?? $payload[\'meta\'][\'ip_address\'] ?? null;
                            }
                        @endphp
                        {{ $ipAddress ?: \'Not provided\' }}
                        @if($ipAddress)
                            <button class="copy-btn" onclick="copyToClipboard(\'{{ $ipAddress }}\', this)" style="background: #10b981; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; margin-left: 8px;">📋</button>
                        @endif
                    </div>
                </div>';

$content = str_replace($ipAddressOld, $ipAddressNew, $content);

// Find and replace the TrustedForm Certificate section
$trustedFormOld = '                <!-- TrustedForm Certificate -->
                <div class="info-item">
                    <div class="info-label">TrustedForm Certificate</div>
                    <div class="info-value">
                        @if($lead->trusted_form_cert)
                            <span style="color: #28a745;">✓ Certificate available</span>
                            <button class="copy-btn" onclick="copyToClipboard(\'{{ $lead->trusted_form_cert }}\', this)" style="background: #10b981; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; margin-left: 8px;">📋</button>
                        @else
                            <span style="color: #6b7280;">Not provided</span>
                        @endif
                    </div>
                </div>';

$trustedFormNew = '                <!-- TrustedForm Certificate -->
                <div class="info-item">
                    <div class="info-label">TrustedForm Certificate</div>
                    <div class="info-value">
                        @php
                            $trustedFormCert = $lead->trusted_form_cert;
                            // Check meta field
                            if (!$trustedFormCert && $lead->meta) {
                                $meta = is_string($lead->meta) ? json_decode($lead->meta, true) : $lead->meta;
                                $trustedFormCert = $meta[\'trusted_form_cert_url\'] ?? $meta[\'trusted_form_cert\'] ?? null;
                            }
                            // Check payload
                            if (!$trustedFormCert && $lead->payload) {
                                $payload = is_string($lead->payload) ? json_decode($lead->payload, true) : $lead->payload;
                                $trustedFormCert = $payload[\'meta\'][\'trusted_form_cert_url\'] ?? $payload[\'meta\'][\'trusted_form_cert\'] ?? null;
                            }
                        @endphp
                        @if($trustedFormCert)
                            <span style="color: #28a745;">✓ Certificate available</span>
                            <button class="copy-btn" onclick="copyToClipboard(\'{{ $trustedFormCert }}\', this)" style="background: #10b981; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; margin-left: 8px;">📋</button>
                        @else
                            <span style="color: #6b7280;">Not provided</span>
                        @endif
                    </div>
                </div>';

$content = str_replace($trustedFormOld, $trustedFormNew, $content);

// Find and replace the Landing Page URL section
$landingPageOld = '                <!-- Landing Page URL -->
                <div class="info-item">
                    <div class="info-label">Landing Page URL</div>
                    <div class="info-value">
                        @if($lead->landing_page_url)
                            <a href="{{ $lead->landing_page_url }}" target="_blank" style="color: #3b82f6; text-decoration: none;">{{ parse_url($lead->landing_page_url, PHP_URL_HOST) ?: $lead->landing_page_url }}</a>
                            <button class="copy-btn" onclick="copyToClipboard(\'{{ $lead->landing_page_url }}\', this)" style="background: #10b981; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; margin-left: 8px;">📋</button>
                        @else
                            <span style="color: #6b7280;">Not provided</span>
                        @endif
                    </div>
                </div>';

$landingPageNew = '                <!-- Landing Page URL -->
                <div class="info-item">
                    <div class="info-label">Landing Page URL</div>
                    <div class="info-value">
                        @php
                            $landingPageUrl = $lead->landing_page_url;
                            // Check meta field
                            if (!$landingPageUrl && $lead->meta) {
                                $meta = is_string($lead->meta) ? json_decode($lead->meta, true) : $lead->meta;
                                $landingPageUrl = $meta[\'landing_page_url\'] ?? null;
                            }
                            // Check payload
                            if (!$landingPageUrl && $lead->payload) {
                                $payload = is_string($lead->payload) ? json_decode($lead->payload, true) : $lead->payload;
                                $landingPageUrl = $payload[\'meta\'][\'landing_page_url\'] ?? null;
                            }
                        @endphp
                        @if($landingPageUrl)
                            <a href="{{ $landingPageUrl }}" target="_blank" style="color: #3b82f6; text-decoration: none;">{{ parse_url($landingPageUrl, PHP_URL_HOST) ?: $landingPageUrl }}</a>
                            <button class="copy-btn" onclick="copyToClipboard(\'{{ $landingPageUrl }}\', this)" style="background: #10b981; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; margin-left: 8px;">📋</button>
                        @else
                            <span style="color: #6b7280;">Not provided</span>
                        @endif
                    </div>
                </div>';

$content = str_replace($landingPageOld, $landingPageNew, $content);

// Find and replace the TCPA Consent Text section
$tcpaConsentOld = '                <!-- TCPA Consent Text -->
                <div class="info-item" style="grid-column: span 2;">
                    <div class="info-label">
                        TCPA Consent Text
                        @if($lead->tcpa_consent_text)
                            <button class="copy-btn" onclick="copyToClipboard(\'{{ addslashes($lead->tcpa_consent_text) }}\', this)" style="background: #10b981; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; margin-left: 8px; font-size: 12px;">📋 Copy</button>
                        @endif
                    </div>
                    <div class="info-value" style="font-size: 0.875rem; line-height: 1.5; padding: 10px; background: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb;">
                        @if($lead->tcpa_consent_text)
                            {{ $lead->tcpa_consent_text }}
                        @else
                            <span style="color: #6b7280;">Not provided</span>
                        @endif
                    </div>
                </div>';

$tcpaConsentNew = '                <!-- TCPA Consent Text -->
                <div class="info-item" style="grid-column: span 2;">
                    <div class="info-label">
                        TCPA Consent Text
                        @php
                            $tcpaConsentText = $lead->tcpa_consent_text;
                            // Check meta field
                            if (!$tcpaConsentText && $lead->meta) {
                                $meta = is_string($lead->meta) ? json_decode($lead->meta, true) : $lead->meta;
                                $tcpaConsentText = $meta[\'tcpa_consent_text\'] ?? null;
                            }
                            // Check payload
                            if (!$tcpaConsentText && $lead->payload) {
                                $payload = is_string($lead->payload) ? json_decode($lead->payload, true) : $lead->payload;
                                $tcpaConsentText = $payload[\'meta\'][\'tcpa_consent_text\'] ?? null;
                            }
                        @endphp
                        @if($tcpaConsentText)
                            <button class="copy-btn" onclick="copyToClipboard(\'{{ addslashes($tcpaConsentText) }}\', this)" style="background: #10b981; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; margin-left: 8px; font-size: 12px;">📋 Copy</button>
                        @endif
                    </div>
                    <div class="info-value" style="font-size: 0.875rem; line-height: 1.5; padding: 10px; background: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb;">
                        @if($tcpaConsentText)
                            {{ $tcpaConsentText }}
                        @else
                            <span style="color: #6b7280;">Not provided</span>
                        @endif
                    </div>
                </div>';

$content = str_replace($tcpaConsentOld, $tcpaConsentNew, $content);

// Also fix the LeadID Code in the vendor section
$leadIdCodeOld = '                <div class="info-item">
                    <div class="info-label">LeadID Code</div>
                    <div class="info-value">
                        @if($lead->leadid_code)
                            <span style="font-family: monospace; font-size: 12px;">{{ $lead->leadid_code }}</span>
                            <button class="copy-btn" onclick="copyToClipboard(\'{{ $lead->leadid_code }}\', this)" style="margin-left: 10px; padding: 2px 8px; background: #22c55e; color: white; border: none; border-radius: 4px; cursor: pointer;">
                                📋 Copy
                            </button>
                        @else
                            Not provided
                        @endif
                    </div>
                </div>';

$leadIdCodeNew = '                <div class="info-item">
                    <div class="info-label">LeadID Code</div>
                    <div class="info-value">
                        @php
                            $leadIdCode = $lead->leadid_code;
                            // Check meta field
                            if (!$leadIdCode && $lead->meta) {
                                $meta = is_string($lead->meta) ? json_decode($lead->meta, true) : $lead->meta;
                                $leadIdCode = $meta[\'lead_id_code\'] ?? $meta[\'leadid_code\'] ?? null;
                            }
                            // Check payload
                            if (!$leadIdCode && $lead->payload) {
                                $payload = is_string($lead->payload) ? json_decode($lead->payload, true) : $lead->payload;
                                $leadIdCode = $payload[\'meta\'][\'lead_id_code\'] ?? $payload[\'leadid_code\'] ?? null;
                            }
                        @endphp
                        @if($leadIdCode)
                            <span style="font-family: monospace; font-size: 12px;">{{ $leadIdCode }}</span>
                            <button class="copy-btn" onclick="copyToClipboard(\'{{ $leadIdCode }}\', this)" style="margin-left: 10px; padding: 2px 8px; background: #22c55e; color: white; border: none; border-radius: 4px; cursor: pointer;">
                                📋 Copy
                            </button>
                        @else
                            Not provided
                        @endif
                    </div>
                </div>';

$content = str_replace($leadIdCodeOld, $leadIdCodeNew, $content);

// Write back the file
file_put_contents('resources/views/agent/lead-display.blade.php', $content);

echo "✅ Fixed TCPA display fields to check all data sources:\n";
echo "  - IP Address: checks lead->ip_address, meta->ip_address, payload->contact->ip_address\n";
echo "  - TrustedForm: checks lead->trusted_form_cert, meta->trusted_form_cert_url, payload->meta->trusted_form_cert_url\n";
echo "  - Landing Page: checks lead->landing_page_url, meta->landing_page_url, payload->meta->landing_page_url\n";
echo "  - TCPA Consent: checks lead->tcpa_consent_text, meta->tcpa_consent_text, payload->meta->tcpa_consent_text\n";
echo "  - LeadID Code: checks lead->leadid_code, meta->lead_id_code, payload->meta->lead_id_code\n";


// Read the current file
$content = file_get_contents('resources/views/agent/lead-display.blade.php');

// Find and replace the IP Address section
$ipAddressOld = '                <!-- IP Address -->
                <div class="info-item">
                    <div class="info-label">IP Address</div>
                    <div class="info-value">
                        {{ $lead->ip_address ?: \'Not provided\' }}
                        @if($lead->ip_address)
                            <button class="copy-btn" onclick="copyToClipboard(\'{{ $lead->ip_address }}\', this)" style="background: #10b981; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; margin-left: 8px;">📋</button>
                        @endif
                    </div>
                </div>';

$ipAddressNew = '                <!-- IP Address -->
                <div class="info-item">
                    <div class="info-label">IP Address</div>
                    <div class="info-value">
                        @php
                            $ipAddress = $lead->ip_address;
                            // Check meta field
                            if (!$ipAddress && $lead->meta) {
                                $meta = is_string($lead->meta) ? json_decode($lead->meta, true) : $lead->meta;
                                $ipAddress = $meta[\'ip_address\'] ?? null;
                            }
                            // Check payload
                            if (!$ipAddress && $lead->payload) {
                                $payload = is_string($lead->payload) ? json_decode($lead->payload, true) : $lead->payload;
                                $ipAddress = $payload[\'contact\'][\'ip_address\'] ?? $payload[\'meta\'][\'ip_address\'] ?? null;
                            }
                        @endphp
                        {{ $ipAddress ?: \'Not provided\' }}
                        @if($ipAddress)
                            <button class="copy-btn" onclick="copyToClipboard(\'{{ $ipAddress }}\', this)" style="background: #10b981; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; margin-left: 8px;">📋</button>
                        @endif
                    </div>
                </div>';

$content = str_replace($ipAddressOld, $ipAddressNew, $content);

// Find and replace the TrustedForm Certificate section
$trustedFormOld = '                <!-- TrustedForm Certificate -->
                <div class="info-item">
                    <div class="info-label">TrustedForm Certificate</div>
                    <div class="info-value">
                        @if($lead->trusted_form_cert)
                            <span style="color: #28a745;">✓ Certificate available</span>
                            <button class="copy-btn" onclick="copyToClipboard(\'{{ $lead->trusted_form_cert }}\', this)" style="background: #10b981; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; margin-left: 8px;">📋</button>
                        @else
                            <span style="color: #6b7280;">Not provided</span>
                        @endif
                    </div>
                </div>';

$trustedFormNew = '                <!-- TrustedForm Certificate -->
                <div class="info-item">
                    <div class="info-label">TrustedForm Certificate</div>
                    <div class="info-value">
                        @php
                            $trustedFormCert = $lead->trusted_form_cert;
                            // Check meta field
                            if (!$trustedFormCert && $lead->meta) {
                                $meta = is_string($lead->meta) ? json_decode($lead->meta, true) : $lead->meta;
                                $trustedFormCert = $meta[\'trusted_form_cert_url\'] ?? $meta[\'trusted_form_cert\'] ?? null;
                            }
                            // Check payload
                            if (!$trustedFormCert && $lead->payload) {
                                $payload = is_string($lead->payload) ? json_decode($lead->payload, true) : $lead->payload;
                                $trustedFormCert = $payload[\'meta\'][\'trusted_form_cert_url\'] ?? $payload[\'meta\'][\'trusted_form_cert\'] ?? null;
                            }
                        @endphp
                        @if($trustedFormCert)
                            <span style="color: #28a745;">✓ Certificate available</span>
                            <button class="copy-btn" onclick="copyToClipboard(\'{{ $trustedFormCert }}\', this)" style="background: #10b981; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; margin-left: 8px;">📋</button>
                        @else
                            <span style="color: #6b7280;">Not provided</span>
                        @endif
                    </div>
                </div>';

$content = str_replace($trustedFormOld, $trustedFormNew, $content);

// Find and replace the Landing Page URL section
$landingPageOld = '                <!-- Landing Page URL -->
                <div class="info-item">
                    <div class="info-label">Landing Page URL</div>
                    <div class="info-value">
                        @if($lead->landing_page_url)
                            <a href="{{ $lead->landing_page_url }}" target="_blank" style="color: #3b82f6; text-decoration: none;">{{ parse_url($lead->landing_page_url, PHP_URL_HOST) ?: $lead->landing_page_url }}</a>
                            <button class="copy-btn" onclick="copyToClipboard(\'{{ $lead->landing_page_url }}\', this)" style="background: #10b981; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; margin-left: 8px;">📋</button>
                        @else
                            <span style="color: #6b7280;">Not provided</span>
                        @endif
                    </div>
                </div>';

$landingPageNew = '                <!-- Landing Page URL -->
                <div class="info-item">
                    <div class="info-label">Landing Page URL</div>
                    <div class="info-value">
                        @php
                            $landingPageUrl = $lead->landing_page_url;
                            // Check meta field
                            if (!$landingPageUrl && $lead->meta) {
                                $meta = is_string($lead->meta) ? json_decode($lead->meta, true) : $lead->meta;
                                $landingPageUrl = $meta[\'landing_page_url\'] ?? null;
                            }
                            // Check payload
                            if (!$landingPageUrl && $lead->payload) {
                                $payload = is_string($lead->payload) ? json_decode($lead->payload, true) : $lead->payload;
                                $landingPageUrl = $payload[\'meta\'][\'landing_page_url\'] ?? null;
                            }
                        @endphp
                        @if($landingPageUrl)
                            <a href="{{ $landingPageUrl }}" target="_blank" style="color: #3b82f6; text-decoration: none;">{{ parse_url($landingPageUrl, PHP_URL_HOST) ?: $landingPageUrl }}</a>
                            <button class="copy-btn" onclick="copyToClipboard(\'{{ $landingPageUrl }}\', this)" style="background: #10b981; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; margin-left: 8px;">📋</button>
                        @else
                            <span style="color: #6b7280;">Not provided</span>
                        @endif
                    </div>
                </div>';

$content = str_replace($landingPageOld, $landingPageNew, $content);

// Find and replace the TCPA Consent Text section
$tcpaConsentOld = '                <!-- TCPA Consent Text -->
                <div class="info-item" style="grid-column: span 2;">
                    <div class="info-label">
                        TCPA Consent Text
                        @if($lead->tcpa_consent_text)
                            <button class="copy-btn" onclick="copyToClipboard(\'{{ addslashes($lead->tcpa_consent_text) }}\', this)" style="background: #10b981; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; margin-left: 8px; font-size: 12px;">📋 Copy</button>
                        @endif
                    </div>
                    <div class="info-value" style="font-size: 0.875rem; line-height: 1.5; padding: 10px; background: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb;">
                        @if($lead->tcpa_consent_text)
                            {{ $lead->tcpa_consent_text }}
                        @else
                            <span style="color: #6b7280;">Not provided</span>
                        @endif
                    </div>
                </div>';

$tcpaConsentNew = '                <!-- TCPA Consent Text -->
                <div class="info-item" style="grid-column: span 2;">
                    <div class="info-label">
                        TCPA Consent Text
                        @php
                            $tcpaConsentText = $lead->tcpa_consent_text;
                            // Check meta field
                            if (!$tcpaConsentText && $lead->meta) {
                                $meta = is_string($lead->meta) ? json_decode($lead->meta, true) : $lead->meta;
                                $tcpaConsentText = $meta[\'tcpa_consent_text\'] ?? null;
                            }
                            // Check payload
                            if (!$tcpaConsentText && $lead->payload) {
                                $payload = is_string($lead->payload) ? json_decode($lead->payload, true) : $lead->payload;
                                $tcpaConsentText = $payload[\'meta\'][\'tcpa_consent_text\'] ?? null;
                            }
                        @endphp
                        @if($tcpaConsentText)
                            <button class="copy-btn" onclick="copyToClipboard(\'{{ addslashes($tcpaConsentText) }}\', this)" style="background: #10b981; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; margin-left: 8px; font-size: 12px;">📋 Copy</button>
                        @endif
                    </div>
                    <div class="info-value" style="font-size: 0.875rem; line-height: 1.5; padding: 10px; background: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb;">
                        @if($tcpaConsentText)
                            {{ $tcpaConsentText }}
                        @else
                            <span style="color: #6b7280;">Not provided</span>
                        @endif
                    </div>
                </div>';

$content = str_replace($tcpaConsentOld, $tcpaConsentNew, $content);

// Also fix the LeadID Code in the vendor section
$leadIdCodeOld = '                <div class="info-item">
                    <div class="info-label">LeadID Code</div>
                    <div class="info-value">
                        @if($lead->leadid_code)
                            <span style="font-family: monospace; font-size: 12px;">{{ $lead->leadid_code }}</span>
                            <button class="copy-btn" onclick="copyToClipboard(\'{{ $lead->leadid_code }}\', this)" style="margin-left: 10px; padding: 2px 8px; background: #22c55e; color: white; border: none; border-radius: 4px; cursor: pointer;">
                                📋 Copy
                            </button>
                        @else
                            Not provided
                        @endif
                    </div>
                </div>';

$leadIdCodeNew = '                <div class="info-item">
                    <div class="info-label">LeadID Code</div>
                    <div class="info-value">
                        @php
                            $leadIdCode = $lead->leadid_code;
                            // Check meta field
                            if (!$leadIdCode && $lead->meta) {
                                $meta = is_string($lead->meta) ? json_decode($lead->meta, true) : $lead->meta;
                                $leadIdCode = $meta[\'lead_id_code\'] ?? $meta[\'leadid_code\'] ?? null;
                            }
                            // Check payload
                            if (!$leadIdCode && $lead->payload) {
                                $payload = is_string($lead->payload) ? json_decode($lead->payload, true) : $lead->payload;
                                $leadIdCode = $payload[\'meta\'][\'lead_id_code\'] ?? $payload[\'leadid_code\'] ?? null;
                            }
                        @endphp
                        @if($leadIdCode)
                            <span style="font-family: monospace; font-size: 12px;">{{ $leadIdCode }}</span>
                            <button class="copy-btn" onclick="copyToClipboard(\'{{ $leadIdCode }}\', this)" style="margin-left: 10px; padding: 2px 8px; background: #22c55e; color: white; border: none; border-radius: 4px; cursor: pointer;">
                                📋 Copy
                            </button>
                        @else
                            Not provided
                        @endif
                    </div>
                </div>';

$content = str_replace($leadIdCodeOld, $leadIdCodeNew, $content);

// Write back the file
file_put_contents('resources/views/agent/lead-display.blade.php', $content);

echo "✅ Fixed TCPA display fields to check all data sources:\n";
echo "  - IP Address: checks lead->ip_address, meta->ip_address, payload->contact->ip_address\n";
echo "  - TrustedForm: checks lead->trusted_form_cert, meta->trusted_form_cert_url, payload->meta->trusted_form_cert_url\n";
echo "  - Landing Page: checks lead->landing_page_url, meta->landing_page_url, payload->meta->landing_page_url\n";
echo "  - TCPA Consent: checks lead->tcpa_consent_text, meta->tcpa_consent_text, payload->meta->tcpa_consent_text\n";
echo "  - LeadID Code: checks lead->leadid_code, meta->lead_id_code, payload->meta->lead_id_code\n";





