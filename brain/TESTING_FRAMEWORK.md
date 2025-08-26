# Brain Application Testing Framework

## Overview
This document outlines the testing framework to prevent regressions when making changes to the Brain application.

## Pre-Deployment Testing Checklist

### 1. Core Functionality Tests
- [ ] **Health endpoint**: `curl https://quotingfast-brain-ohio.onrender.com/health` returns 200
- [ ] **Lead edit page**: `curl https://quotingfast-brain-ohio.onrender.com/agent/lead/1` returns 200
- [ ] **Lead not found**: `curl https://quotingfast-brain-ohio.onrender.com/agent/lead/999999?iframe=1` returns 200 (not 500)

### 2. JavaScript Functionality Tests
- [ ] **Conditional questions work**:
  - Question 1: "Are you currently insured?" = "Yes" → Shows provider and duration questions
  - Question 4: "DUI or SR22?" = "DUI Only" or "Both" → Shows timeframe question
- [ ] **Save button works**: Changes persist after clicking save
- [ ] **Enrichment buttons work**: No JavaScript errors, shows toast notifications
- [ ] **Toast notifications work**: Non-blocking, auto-dismissing

### 3. Database Tests
- [ ] **Lead creation**: New leads can be created
- [ ] **Lead updates**: Existing leads can be updated
- [ ] **Duplicate detection**: Duplicate queue system works

### 4. API Tests
- [ ] **Webhook endpoints**: `/api-webhook` accepts leads
- [ ] **Contact save**: `/api/lead/{id}/contact-save` works
- [ ] **Qualification save**: `/agent/lead/{id}/qualify` works

## Regression Prevention Rules

### 1. Small, Focused Changes
- Make one change at a time
- Test each change before moving to the next
- Use descriptive commit messages

### 2. Git Workflow
- Create feature branches for major changes
- Test on feature branch before merging
- Use `git revert` for quick rollbacks

### 3. JavaScript Changes
- Never modify working functions without testing
- Add error handling without changing core logic
- Test in multiple browsers

### 4. Emergency Rollback Procedure

If a deployment breaks functionality:

1. **Immediate rollback**:
   ```bash
   git log --oneline -5  # Find last working commit
   git reset --hard <last_working_commit>
   git push --force-with-lease origin main
   ```

2. **Verify rollback**:
   ```bash
   sleep 60
   curl -s -o /dev/null -w "%{http_code}" https://quotingfast-brain-ohio.onrender.com/health
   ```

## Success Metrics

- **Zero 500 errors** in production
- **All JavaScript functions** working
- **Save functionality** persisting changes
- **Enrichment buttons** showing toast notifications
- **Conditional questions** showing/hiding properly

## Last Updated
August 26, 2025 - Created after fixing regression issues
