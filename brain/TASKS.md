# Tasks Panel (Live)

- Last Updated: 2025-08-08 12:25 EST
- Overall Status: Webhooks OK; Vici push OK; Allstate logs verification pending; Search UX polishing in progress

Keep this file open in Cursor as a status panel. I will update it as work progresses.

## Active
- [ ] Allstate testing logs: ensure entries visible in `allstate_test_logs` and `/admin/allstate-testing`
- [ ] Search module UI/UX: refine layout, grouped filters, persistent per_page, clearer empty state
- [ ] Allstate dashboard bulk process: fix "Network Error"
- [ ] Logo sizing: make logo 3x larger across pages
- [ ] Vici hardening: set UploadAPI creds in Render env; keep query overrides for tests only; verify update endpoint

## Recently Completed (auto-prune after 48h)
- [x] 2025-08-08 12:20 EST — Vici Non-Agent API working (UploadAPI). Pushed Brain lead 1057 → list 101 (Vici `11533805`)
- [x] 2025-08-08 12:18 EST — Implement HTTPS→HTTP fallback and form-encoded requests to Vici
- [x] 2025-08-08 12:16 EST — Expose server egress IP (`/server-egress-ip`) and login probe (`/test/vici-login`)
- [x] 2025-08-08 12:10 EST — Improve lead search: multi-token, case-insensitive, supports full name/id/external id/city/state/zip
- [x] 2025-08-08 11:50 EST — Webhooks stable (`/api-webhook`) with LQF nested payload handling

## Notes
- See `brain/API_CONFIGURATIONS.md` and `brain/PROJECT_MEMORY.md` for integration details.
