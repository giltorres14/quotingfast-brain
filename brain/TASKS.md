# Tasks Panel (Live)

Keep this file open in Cursor as a status panel. I will update it as work progresses.

## Active
- [ ] Allstate testing logs: ensure entries visible in `allstate_test_logs` and `/admin/allstate-testing`
- [ ] Search module UI/UX: refine layout, grouped filters, persistent per_page, clearer empty state
- [ ] Allstate dashboard bulk process: fix "Network Error"
- [ ] Logo sizing: make logo 3x larger across pages
- [ ] Vici hardening: set UploadAPI creds in Render env; keep query overrides for tests only; verify update endpoint

## Recently Completed
- [x] Vici Non-Agent API working (UploadAPI). Pushed Brain lead 1057 → list 101 (Vici `11533805`)
- [x] Implement HTTPS→HTTP fallback and form-encoded requests to Vici
- [x] Expose server egress IP (`/server-egress-ip`) and login probe (`/test/vici-login`)
- [x] Improve lead search: multi-token, case-insensitive, supports full name/id/external id/city/state/zip
- [x] Webhooks stable (`/api-webhook`) with LQF nested payload handling

## Notes
- See `brain/API_CONFIGURATIONS.md` and `brain/PROJECT_MEMORY.md` for integration details.
