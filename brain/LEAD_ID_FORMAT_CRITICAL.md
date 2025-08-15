# 🚨 CRITICAL: LEAD ID FORMAT - 13 DIGITS 🚨

## THE FORMAT IS 13 DIGITS - NOT 9 DIGITS

### Format Structure:
```
TTTTTTTTTTXXX
│         │
│         └─── 3-digit sequence (000-999)
└───────────── 10-digit Unix timestamp
```

### Example IDs:
- ✅ CORRECT: `1755041041000` (13 digits)
- ✅ CORRECT: `1755041041001` (13 digits)
- ✅ CORRECT: `1755041041002` (13 digits)
- ❌ WRONG: `10000001` (9 digits - DO NOT USE)
- ❌ WRONG: `100507445` (9 digits - DO NOT USE)

### Key Points:
1. **ALWAYS 13 DIGITS** - No exceptions
2. **Timestamp-based** - First 10 digits are Unix timestamp
3. **Sequential** - Last 3 digits increment for same-second creates
4. **Numeric only** - No letters or special characters

### Code Location:
- Generation: `app/Models/Lead.php` -> `generateExternalLeadId()`
- Validation: Built into the generation method

### Sample Lead IDs from Database:
- Kenneth Takett: `1755041041000`
- BRITANNI EGOLINSKY: `1755041041001`
- Tiffany Franks: `1755041041002`

### DO NOT CHANGE THIS
This format has been confirmed multiple times. The system is built around 13-digit IDs.
Any attempt to change to 9 digits will break the system.

### Testing:
To verify the format in production:
```php
$lead = Lead::find(1979);
echo strlen($lead->external_lead_id); // Should output: 13
```

---
**Last Updated:** August 2025
**Confirmed By:** User (multiple times)
**Status:** PERMANENT - DO NOT MODIFY



