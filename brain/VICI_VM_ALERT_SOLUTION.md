# Vici VM Alert Implementation for List 103
**Created:** January 15, 2025

## The Solution: URL Parameter Approach

### 1. Configure Vici to Pass List ID in Iframe URL

In Vici's campaign settings, modify the Agent Screen URL to include the list ID:

**Current URL (probably):**
```
https://quotingfast-brain-ohio.onrender.com/agent/lead/[vendor_lead_code]
```

**Updated URL:**
```
https://quotingfast-brain-ohio.onrender.com/agent/lead/[vendor_lead_code]?list_id=[list_id]&campaign=[campaign]&agent=[user]
```

### 2. Modify The Brain's Lead Display Page

Update `resources/views/agent/lead-display.blade.php` to detect and display VM alert:

```php
@php
    $listId = request()->get('list_id');
    $isVMList = in_array($listId, ['103', '105']); // VM lists
@endphp

@if($isVMList)
    <div id="vmAlert" style="
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        background: linear-gradient(135deg, #ff6b6b, #ee5a24);
        color: white;
        padding: 20px;
        text-align: center;
        font-size: 1.3rem;
        font-weight: bold;
        z-index: 9999;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        animation: pulse 2s infinite;
    ">
        ⚠️ VOICEMAIL LIST - LEAVE MESSAGE ONLY ⚠️
        <div style="font-size: 1rem; margin-top: 10px;">
            This lead is in List {{ $listId }} - Please leave a voicemail and set status to LVM
        </div>
        <button onclick="document.getElementById('vmAlert').style.display='none'" 
                style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%); 
                       background: white; color: #ee5a24; border: none; padding: 5px 15px; 
                       border-radius: 20px; cursor: pointer;">
            Got it ✓
        </button>
    </div>
    
    <style>
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }
    </style>
@endif
```

### 3. Add Audio Alert (Optional)

```javascript
@if($isVMList)
<script>
    // Play alert sound when page loads
    window.addEventListener('DOMContentLoaded', function() {
        // Create audio alert
        const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBjiS2Oy9diMFl2+z9N17yubCvgxTmq');
        audio.play().catch(e => console.log('Audio blocked:', e));
        
        // Flash the background
        let flashes = 0;
        const flashInterval = setInterval(() => {
            document.body.style.background = flashes % 2 === 0 ? '#ffebee' : 'white';
            flashes++;
            if (flashes > 6) {
                clearInterval(flashInterval);
                document.body.style.background = 'white';
            }
        }, 300);
    });
</script>
@endif
```

### 4. Database Tracking (Already Have This)

The lead already has `vici_list_id` in the database, so we can also check that:

```php
// In lead-display.blade.php
@php
    $listId = request()->get('list_id') ?? $lead->vici_list_id;
    $isVMList = in_array($listId, [103, 105]);
    
    $listDescriptions = [
        103 => 'First Voicemail Attempt',
        105 => 'Second Voicemail Attempt'
    ];
@endphp
```

## Implementation Steps:

### Step 1: Update Vici Campaign Settings
1. Login to Vici Admin
2. Go to Campaigns → [Your Campaign] → Detail View
3. Find "Agent Screen URL" or "Script URL"
4. Update to include parameters:
   ```
   https://quotingfast-brain-ohio.onrender.com/agent/lead/[vendor_lead_code]?list_id=[list_id]
   ```

### Step 2: Update The Brain's Lead Display
Add the VM alert code to the top of the body in `agent/lead-display.blade.php`

### Step 3: Test Flow
1. Move a test lead to List 103
2. Have agent receive the call
3. Verify the alert appears in the iframe
4. Confirm agent can dismiss alert
5. Check that status LVM triggers move to List 104

## Alternative Approaches (If URL Params Don't Work):

### Option A: API Check on Page Load
```javascript
// Check with Brain's API what list this lead is in
fetch('/api/lead/' + leadId + '/vici-status')
    .then(response => response.json())
    .then(data => {
        if (data.list_id === 103 || data.list_id === 105) {
            showVMAlert();
        }
    });
```

### Option B: WebSocket Real-time Updates
- Set up WebSocket connection between Brain and Vici
- Real-time notifications when lead enters VM list
- More complex but provides instant updates

### Option C: Vici Custom Fields
- Add custom field `is_vm_list` to vicidial_list
- Set to 'Y' when moving to Lists 103/105
- Brain checks this field on page load

## Benefits of URL Parameter Approach:
✅ **Simple** - Just update Vici URL configuration
✅ **Instant** - No API calls needed
✅ **Reliable** - Works even if database is slow
✅ **Visible** - Agents can't miss the alert
✅ **No Popup Blocking** - Alert is part of the page, not a popup

## Visual Alert Features:
- **Red gradient banner** at top of page
- **Pulsing animation** to catch attention
- **Clear instructions** for the agent
- **Dismissible** but prominent
- **Audio alert** (optional)
- **Background flash** on load

## Testing Checklist:
- [ ] Vici passes list_id in URL
- [ ] Brain receives and reads parameter
- [ ] Alert shows for List 103
- [ ] Alert shows for List 105
- [ ] No alert for other lists
- [ ] Agent can dismiss alert
- [ ] Status LVM triggers movement
- [ ] Works in iframe context
**Created:** January 15, 2025

## The Solution: URL Parameter Approach

### 1. Configure Vici to Pass List ID in Iframe URL

In Vici's campaign settings, modify the Agent Screen URL to include the list ID:

**Current URL (probably):**
```
https://quotingfast-brain-ohio.onrender.com/agent/lead/[vendor_lead_code]
```

**Updated URL:**
```
https://quotingfast-brain-ohio.onrender.com/agent/lead/[vendor_lead_code]?list_id=[list_id]&campaign=[campaign]&agent=[user]
```

### 2. Modify The Brain's Lead Display Page

Update `resources/views/agent/lead-display.blade.php` to detect and display VM alert:

```php
@php
    $listId = request()->get('list_id');
    $isVMList = in_array($listId, ['103', '105']); // VM lists
@endphp

@if($isVMList)
    <div id="vmAlert" style="
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        background: linear-gradient(135deg, #ff6b6b, #ee5a24);
        color: white;
        padding: 20px;
        text-align: center;
        font-size: 1.3rem;
        font-weight: bold;
        z-index: 9999;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        animation: pulse 2s infinite;
    ">
        ⚠️ VOICEMAIL LIST - LEAVE MESSAGE ONLY ⚠️
        <div style="font-size: 1rem; margin-top: 10px;">
            This lead is in List {{ $listId }} - Please leave a voicemail and set status to LVM
        </div>
        <button onclick="document.getElementById('vmAlert').style.display='none'" 
                style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%); 
                       background: white; color: #ee5a24; border: none; padding: 5px 15px; 
                       border-radius: 20px; cursor: pointer;">
            Got it ✓
        </button>
    </div>
    
    <style>
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }
    </style>
@endif
```

### 3. Add Audio Alert (Optional)

```javascript
@if($isVMList)
<script>
    // Play alert sound when page loads
    window.addEventListener('DOMContentLoaded', function() {
        // Create audio alert
        const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBjiS2Oy9diMFl2+z9N17yubCvgxTmq');
        audio.play().catch(e => console.log('Audio blocked:', e));
        
        // Flash the background
        let flashes = 0;
        const flashInterval = setInterval(() => {
            document.body.style.background = flashes % 2 === 0 ? '#ffebee' : 'white';
            flashes++;
            if (flashes > 6) {
                clearInterval(flashInterval);
                document.body.style.background = 'white';
            }
        }, 300);
    });
</script>
@endif
```

### 4. Database Tracking (Already Have This)

The lead already has `vici_list_id` in the database, so we can also check that:

```php
// In lead-display.blade.php
@php
    $listId = request()->get('list_id') ?? $lead->vici_list_id;
    $isVMList = in_array($listId, [103, 105]);
    
    $listDescriptions = [
        103 => 'First Voicemail Attempt',
        105 => 'Second Voicemail Attempt'
    ];
@endphp
```

## Implementation Steps:

### Step 1: Update Vici Campaign Settings
1. Login to Vici Admin
2. Go to Campaigns → [Your Campaign] → Detail View
3. Find "Agent Screen URL" or "Script URL"
4. Update to include parameters:
   ```
   https://quotingfast-brain-ohio.onrender.com/agent/lead/[vendor_lead_code]?list_id=[list_id]
   ```

### Step 2: Update The Brain's Lead Display
Add the VM alert code to the top of the body in `agent/lead-display.blade.php`

### Step 3: Test Flow
1. Move a test lead to List 103
2. Have agent receive the call
3. Verify the alert appears in the iframe
4. Confirm agent can dismiss alert
5. Check that status LVM triggers move to List 104

## Alternative Approaches (If URL Params Don't Work):

### Option A: API Check on Page Load
```javascript
// Check with Brain's API what list this lead is in
fetch('/api/lead/' + leadId + '/vici-status')
    .then(response => response.json())
    .then(data => {
        if (data.list_id === 103 || data.list_id === 105) {
            showVMAlert();
        }
    });
```

### Option B: WebSocket Real-time Updates
- Set up WebSocket connection between Brain and Vici
- Real-time notifications when lead enters VM list
- More complex but provides instant updates

### Option C: Vici Custom Fields
- Add custom field `is_vm_list` to vicidial_list
- Set to 'Y' when moving to Lists 103/105
- Brain checks this field on page load

## Benefits of URL Parameter Approach:
✅ **Simple** - Just update Vici URL configuration
✅ **Instant** - No API calls needed
✅ **Reliable** - Works even if database is slow
✅ **Visible** - Agents can't miss the alert
✅ **No Popup Blocking** - Alert is part of the page, not a popup

## Visual Alert Features:
- **Red gradient banner** at top of page
- **Pulsing animation** to catch attention
- **Clear instructions** for the agent
- **Dismissible** but prominent
- **Audio alert** (optional)
- **Background flash** on load

## Testing Checklist:
- [ ] Vici passes list_id in URL
- [ ] Brain receives and reads parameter
- [ ] Alert shows for List 103
- [ ] Alert shows for List 105
- [ ] No alert for other lists
- [ ] Agent can dismiss alert
- [ ] Status LVM triggers movement
- [ ] Works in iframe context




