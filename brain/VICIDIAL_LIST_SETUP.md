# VICIDIAL LIST CONFIGURATION SUMMARY
Generated: 2025-08-20 13:34:13 UTC

## TEST A LISTS (48-Call Strategy with 3-Day Rest)
| List | Priority | Days | Calls | Description |
|------|----------|------|-------|-------------|
| 101  | 10       | 1-2  | 0-5   | Fresh leads, highest priority |
| 102  | 20       | 3-4  | 5-8   | Early stage continuation |
| 103  | 30       | 5-6  | 8-12  | Mid stage persistence |
| 104  | 40       | 7-8  | 12-16 | Week 1 complete |
| 106  | 50       | 9-11 | 16-24 | Extended calling |
| 107  | 60       | 12-13| 24-30 | Two weeks |
| 108  | 70       | 14-16| REST  | **3-DAY REST PERIOD** |
| 109  | 80       | 17-30| 30-40 | Final push after rest |
| 111  | 90       | 31-90| 40-48 | Long-term nurture |

## TEST B LISTS (12-18 Call Optimized Strategy)
| List | Priority | Calls | Description |
|------|----------|-------|-------------|
| 150  | 15       | 0-4   | Fresh optimal leads |
| 151  | 25       | 5-8   | Mid stage |
| 152  | 35       | 9-12  | Extended |
| 153  | 45       | 13-18 | Final attempts |

## SPECIAL LISTS
| List | Status   | Description |
|------|----------|-------------|
| 998  | Inactive | Transferred leads (XFER/XFERA) |
| 999  | Inactive | DNC/DNQ - Never call |

## KEY CONFIGURATION POINTS
- **Reset Time:** BLANK (managed by cron scripts)
- **Campaign:** AUTODIAL for all active lists
- **List Order:** Controls priority (lower number = higher priority)
- **Active Status:** Y for calling lists, N for terminal lists
- **Script Override:** INSURANCE (consistent across all)

## LEAD FLOW LOGIC
1. Fresh leads start in List 101 (Test A) or 150 (Test B)
2. Movement based on call count AND time in list
3. Test A includes 3-day rest period in List 108
4. Test B has no rest period (rapid 12-18 call strategy)
5. Terminal dispositions (XFER, DNC, etc.) move to 998/999
