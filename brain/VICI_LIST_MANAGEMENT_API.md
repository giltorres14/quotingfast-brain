# 🔄 VICI LIST MANAGEMENT - Brain API Capabilities

## ✅ **YES! The Brain CAN Move Leads Between Vici Lists**

The Brain system has **FULL CONTROL** over Vici list assignments through the Non-Agent API.

---

## 🎯 **How It Works**

### **1. API Method**
The Brain uses Vici's Non-Agent API with the `update_lead` function:

```php
$params = [
    'source' => 'brain',
    'user' => 'apiuser',
    'pass' => 'UZPATJ59GJAVKG8ES6',
    'function' => 'update_lead',
    'vendor_lead_code' => "BRAIN_123", // Our tracking ID
    'list_id_field' => 102,            // New list to move to
    'reset_called_count' => 'N'        // Keep call history
];
```

### **2. Available Functions**

#### **Move Single Lead**
```php
$viciService->moveLeadToList($lead, 102, 'No answer - retry list');
```

#### **Auto-Assign Based on Status**
```php
$viciService->autoAssignLeadToList($lead);
// Automatically determines list based on lead status
```

#### **Bulk Migration**
```php
$moves = [
    ['lead_id' => 1, 'new_list_id' => 102],
    ['lead_id' => 2, 'new_list_id' => 103],
];
$viciService->bulkMoveLeadsToLists($moves);
```

---

## 📋 **List Assignment Logic**

### **Automatic List Assignment Rules:**

| Lead Status | Assigned List | Description |
|------------|---------------|-------------|
| `new`, `fresh` | 101 | Fresh leads, never called |
| `no_answer`, `busy` | 102 | Retry list, 3-5 attempts |
| `callback`, `scheduled` | 103 | Callback list |
| `qualified` | 104 | Qualified leads |
| `hot_lead` | 105 | Hot leads, high interest |
| `stale` | 106 | 6+ attempts or 30+ days old |
| `dnc`, `bad_number` | 199 | Do not call list |

---

## 🛠️ **Migration Command**

### **Analyze and Migrate Existing Leads:**

```bash
# Preview migration (dry run)
php artisan leads:migrate-to-lists --dry-run

# Actually migrate leads
php artisan leads:migrate-to-lists

# Migrate and update in Vici
php artisan leads:migrate-to-lists --update-vici

# Process specific date range
php artisan leads:migrate-to-lists --start-date=2024-01-01 --end-date=2024-12-31
```

---

## 🔄 **Real-Time List Management**

### **Webhook Triggers**
The Brain can automatically move leads based on events:

1. **After Call Disposition**
   - No Answer → List 102 (Retry)
   - Qualified → List 104 (Qualified)
   - DNC → List 199 (Do Not Call)

2. **Based on Call Attempts**
   - 3 attempts, no contact → List 102
   - 6+ attempts → List 106 (Stale)

3. **Time-Based Rules**
   - 30+ days old → List 106 (Stale)
   - Scheduled callback → List 103

---

## 📊 **Tracking & History**

Every list move is tracked:

```json
{
  "list_moves": [
    {
      "from": 101,
      "to": 102,
      "reason": "No answer after 3 attempts",
      "timestamp": "2024-12-20T15:30:00Z"
    },
    {
      "from": 102,
      "to": 104,
      "reason": "Qualified by agent",
      "timestamp": "2024-12-20T16:45:00Z"
    }
  ]
}
```

---

## 🚀 **Implementation Examples**

### **1. Move Lead After Call**
```php
// In ViciCallWebhookController
public function handleDisposition(Request $request)
{
    $lead = Lead::where('external_lead_id', $request->vendor_lead_code)->first();
    
    if ($request->status == 'NA') { // No Answer
        $viciService->moveLeadToList($lead, 102, 'No answer disposition');
    }
}
```

### **2. Scheduled Job for Stale Leads**
```php
// Daily job to move stale leads
$staleLeads = Lead::where('created_at', '<', now()->subDays(30))
                  ->where('vici_list_id', '!=', 106)
                  ->get();

foreach ($staleLeads as $lead) {
    $viciService->moveLeadToList($lead, 106, 'Lead over 30 days old');
}
```

### **3. Manual Admin Action**
```php
// Admin panel button to move lead
Route::post('/admin/lead/{id}/move-list', function ($id, Request $request) {
    $lead = Lead::find($id);
    $viciService = app(ViciDialerService::class);
    
    $result = $viciService->moveLeadToList(
        $lead, 
        $request->new_list_id,
        $request->reason
    );
    
    return response()->json($result);
});
```

---

## ⚡ **Benefits of Brain-Controlled Lists**

1. **Intelligent Routing** - Leads automatically flow to appropriate lists
2. **No Manual Work** - Agents don't need to move leads manually
3. **Better Performance** - Fresh leads stay fresh, callbacks get called back
4. **Complete History** - Track every move and why it happened
5. **Bulk Operations** - Migrate thousands of leads at once

---

## 🔑 **Key Points**

- ✅ Brain has FULL control over Vici list assignments
- ✅ Works through Vici's official Non-Agent API
- ✅ Maintains call history when moving leads
- ✅ Tracks all moves in database
- ✅ Can be automated or manual
- ✅ Supports single, bulk, and auto-assignment

**The Brain is the intelligent layer that makes Vici lists work smarter!**
