<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lead Not Found</title>
    <style>
        body{font-family:ui-sans-serif,system-ui,Segoe UI,Roboto,Helvetica,Arial,sans-serif;background:#0f172a;color:#e2e8f0;margin:0}
        .wrap{max-width:900px;margin:0 auto;padding:16px}
        .card{background:#111827;border:1px solid #1f2937;border-radius:10px;padding:16px}
        .row{display:flex;gap:12px;flex-wrap:wrap}
        .col{flex:1 1 260px}
        label{display:block;margin:6px 0 4px;color:#94a3b8;font-size:12px}
        input,textarea{width:100%;padding:10px;border-radius:8px;border:1px solid #334155;background:#0b1220;color:#e2e8f0}
        button{background:#059669;color:#fff;border:none;border-radius:8px;padding:10px 14px;cursor:pointer;font-weight:600}
        button:hover{background:#047857}
        .muted{color:#94a3b8;font-size:13px}
    </style>
    <script>
        async function createLead(e){
            console.log('Create Lead function called');
            e.preventDefault();
            const f = e.target;
            const payload = {
                external_lead_id: Math.floor(Date.now() * 1000).toString(),
                first_name: f.first_name.value.trim(),
                last_name: f.last_name.value.trim(),
                phone: f.phone.value.trim(),
                email: f.email.value.trim(),
                address: f.address.value.trim(),
                city: f.city.value.trim(),
                state: f.state.value.trim(),
                zip_code: f.zip.value.trim(),
                notes: f.notes.value.trim(),
                source: 'vicidial-iframe-capture',
                vici_lead_id: '{{ $leadId }}' // Pass Vici lead ID back to update vendor_lead_code
            };
            console.log('Payload:', payload);
            console.log('Capture URL:', '{{ $captureUrl }}');
            
            try {
                // Use GET request with query parameters to bypass CSRF
                const queryParams = new URLSearchParams(payload).toString();
                const captureUrl = '{{ $captureUrl }}';
                console.log('Full URL being called:', captureUrl + '?' + queryParams);
                const res = await fetch(captureUrl + '?' + queryParams, {
                    method: 'GET',
                    headers: { 'Content-Type': 'application/json' }
                });
                console.log('Response status:', res.status);
                const data = await res.json();
                console.log('Response data:', data);
                if (data && data.redirect) { 
                    console.log('Redirecting to:', data.redirect);
                    window.location.href = data.redirect; 
                    return; 
                }
                alert(data.error || 'Failed to create lead');
            } catch (error) {
                console.error('Error:', error);
                alert('Error creating lead: ' + error.message);
            }
        }
    </script>
    </head>
<body>
    <div class="wrap">
        <div class="card">
            <h2 style="margin:0 0 8px">Lead Not Found</h2>
            <p class="muted">No lead matched ID <strong>{{ $leadId }}</strong>. Create it now with the details from Vici.</p>
            <form onsubmit="createLead(event)">
                <div class="row">
                    <div class="col">
                        <label>First name</label>
                        <input name="first_name" value="{{ $prefill['first_name'] ?? '' }}">
                    </div>
                    <div class="col">
                        <label>Last name</label>
                        <input name="last_name" value="{{ $prefill['last_name'] ?? '' }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <label>Phone</label>
                        <input name="phone" value="{{ $prefill['phone'] ?? '' }}">
                    </div>
                    <div class="col">
                        <label>Email</label>
                        <input name="email" value="{{ $prefill['email'] ?? '' }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <label>Address</label>
                        <input name="address" value="{{ $prefill['address'] ?? '' }}">
                    </div>
                    <div class="col">
                        <label>City</label>
                        <input name="city" value="{{ $prefill['city'] ?? '' }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <label>State</label>
                        <input name="state" value="{{ $prefill['state'] ?? '' }}">
                    </div>
                    <div class="col">
                        <label>ZIP</label>
                        <input name="zip" value="{{ $prefill['zip'] ?? '' }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <label>Notes</label>
                        <textarea name="notes" rows="3">{{ $prefill['notes'] ?? '' }}</textarea>
                    </div>
                </div>
                <div style="margin-top:12px">
                    <button type="submit">Create Lead and Continue</button>
                </div>
                <p class="muted" style="margin-top:10px">This will create a Brain lead with external_lead_id = {{ $leadId }} and open the edit page in iframe mode.</p>
            </form>
        </div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lead Not Found - {{ $leadId }}</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="shortcut icon" href="/favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 14px;
            line-height: 1.4;
            background: #f8f9fa;
            color: #333;
            min-height: 100vh;
            overflow-y: auto;
        }

        .container {
            max-width: 100%;
            min-height: 100vh;
            background: white;
            display: flex;
            flex-direction: column;
        }

        .header {
            overflow-x: hidden;
            width: 100%;
            max-width: 100%;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header h1 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .header .subtitle {
            font-size: 14px;
            opacity: 0.9;
        }

        .content {
            flex: 1;
            padding: 30px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .icon {
            font-size: 64px;
            color: #ffc107;
            margin-bottom: 20px;
        }

        .message {
            font-size: 18px;
            color: #666;
            margin-bottom: 30px;
            max-width: 400px;
        }

        .lead-info {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            min-width: 300px;
        }

        .lead-info h3 {
            color: #495057;
            margin-bottom: 10px;
            font-size: 16px;
        }

        .lead-info p {
            color: #6c757d;
            margin: 5px 0;
        }

        .actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-1px);
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #218838;
            transform: translateY(-1px);
        }

        .notes-section {
            margin-top: 20px;
            text-align: left;
            max-width: 400px;
            width: 100%;
        }

        .notes-section textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: vertical;
            min-height: 100px;
            font-family: inherit;
        }

        .notes-section label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #495057;
        }

        @media (max-width: 768px) {
            .header {
            overflow-x: hidden;
            width: 100%;
            max-width: 100%;
                padding: 15px;
            }
            
            .header h1 {
                font-size: 20px;
            }
            
            .content {
                padding: 20px;
            }
            
            .actions {
                flex-direction: column;
                align-items: center;
            }
            
            .btn {
                width: 100%;
                max-width: 200px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚠️ Lead Not Found</h1>
            <div class="subtitle">No previous data available for this contact</div>
        </div>

        <div class="content">
            <div class="icon">🔍</div>
            
            <div class="message">
                This phone number is not in our system. This could be a new lead, cold call, or the lead data hasn't been synced yet.
            </div>

            <div class="lead-info">
                <h3>Call Information</h3>
                <p><strong>Lead ID:</strong> {{ $leadId }}</p>
                <p><strong>Status:</strong> No previous data found</p>
                <p><strong>Action:</strong> Manual qualification required</p>
            </div>

            <div class="notes-section">
                <label for="agent-notes">Agent Notes (Optional):</label>
                <textarea id="agent-notes" placeholder="Add any notes about this call..."></textarea>
            </div>

            <div class="actions">
                <button class="btn btn-success" onclick="transferToRingba()">
                    🔄 Transfer to Buyer
                </button>
                
                <button class="btn btn-secondary" onclick="addManualLead()">
                    📝 Create Lead Record
                </button>
                
                <button class="btn btn-primary" onclick="refreshData()">
                    🔄 Refresh Data
                </button>
            </div>
        </div>
    </div>

    <script>
        function transferToRingba() {
            const notes = document.getElementById('agent-notes').value;
            
            fetch('{{ $transferUrl }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    lead_id: '{{ $leadId }}',
                    agent_notes: notes,
                    transfer_reason: 'unknown_lead_qualified'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Transfer request sent to Ringba successfully!');
                } else {
                    alert('❌ Transfer failed: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Transfer error:', error);
                alert('❌ Transfer failed: Network error');
            });
        }

        function addManualLead() {
            const notes = document.getElementById('agent-notes').value;
            
            // This would integrate with your lead creation system
            fetch('{{ $apiBase }}/leads/manual', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    lead_id: '{{ $leadId }}',
                    source: 'vici_manual',
                    agent_notes: notes,
                    phone: 'unknown', // Vici would provide this
                    status: 'manual_entry'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Lead record created successfully!');
                    location.reload(); // Refresh to show the new lead data
                } else {
                    alert('❌ Failed to create lead: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Create lead error:', error);
                alert('❌ Failed to create lead: Network error');
            });
        }

        function refreshData() {
            location.reload();
        }

        // Auto-refresh every 30 seconds in case lead data comes in
        setInterval(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html>