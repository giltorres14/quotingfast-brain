<!DOCTYPE html>
<html>
<head>
    <title>Brain Lead Display</title>
    <style>
        body { margin: 0; padding: 0; font-family: Arial, sans-serif; }
        .iframe-container { width: 100%; height: 100vh; }
        iframe { width: 100%; height: 100%; border: none; }
    </style>
</head>
<body>
    <div class="iframe-container">
        <iframe id="brain-iframe" src=""></iframe>
    </div>

    <script>
        function openBrainIframe() {
            var leadId = '{{ request()->get("lead_id") }}';
            var vendorCode = '{{ request()->get("vendor_lead_code") }}';
            var phoneNumber = '{{ request()->get("phone") }}';
            
            // Step 1: Try vendor_lead_code (Brain external_lead_id)
            var url;
            if (vendorCode && vendorCode.trim() !== '') {
                url = 'https://quotingfast-brain-ohio.onrender.com/agent/lead/' + vendorCode + '?iframe=1';
            } else if (phoneNumber && phoneNumber.trim() !== '') {
                // Use phone lookup route with Vici lead ID for precise matching
                var cleanPhone = phoneNumber.replace(/[^0-9]/g, '');
                url = 'https://quotingfast-brain-ohio.onrender.com/agent/lead-by-phone/' + cleanPhone + (leadId && leadId.trim() !== '' ? '?vici_lead_id=' + leadId : '');
            } else {
                // Final fallback - show error or capture form
                url = 'https://quotingfast-brain-ohio.onrender.com/agent/lead/capture';
            }
            
            document.getElementById('brain-iframe').src = url;
            console.log('Brain iframe opened: ' + url);
        }

        // Auto-open when page loads
        window.onload = function() {
            openBrainIframe();
        };
    </script>
</body>
</html>
