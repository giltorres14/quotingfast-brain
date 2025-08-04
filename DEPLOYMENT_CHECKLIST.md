# DEPLOYMENT CHECKLIST - PREVENT REPEATED MISTAKES

## Before Any Database Issues Investigation:

### 1. Infrastructure Audit (ALWAYS FIRST)
- [ ] List ALL render.yaml files: `find . -name "render.yaml*"`
- [ ] Check which render.yaml is being used by Render
- [ ] Verify current database type: `curl /api/database/status`
- [ ] Check what databases are already provisioned
- [ ] Compare local config vs deployed config

### 2. Database Persistence Issues
- [ ] Is it SQLite? → Container filesystem is ephemeral (will wipe)
- [ ] Is PostgreSQL already available? → Use it instead
- [ ] Don't spend time "fixing" SQLite persistence - switch to PostgreSQL

### 3. Template/Data Issues
- [ ] Check data storage format: `curl /api/lead/X/payload`
- [ ] Verify template conditional logic
- [ ] Test with simple data first, then complex

### 4. Before Declaring "Fixed"
- [ ] Test on clean deployment
- [ ] Verify data persists through deployment cycle
- [ ] Check all related features work together

## Key Learning: Always audit existing infrastructure before building new solutions!