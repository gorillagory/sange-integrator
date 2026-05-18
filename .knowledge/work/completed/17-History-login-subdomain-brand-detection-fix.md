# Login Subdomain Brand Detection Fix - 017

Status: in_progress

## Checklist

- [x] Reproduce issue from screenshot (`bt.bayam.test:8000/login` rendering system login identity).
- [x] Patch host/subdomain detection in shared brand context to handle host headers with port.
- [x] Harden tenant subdomain lookup to tolerate case/whitespace drift.
- [x] Run syntax/verification checks.
- [x] Move checklist to `completed`.

## Verification Notes

- Expected outcome: tenant login should render tenant identity (name/logo/theme/subdomain) on subdomain host.
- `php -l app/Http/Middleware/HandleInertiaRequests.php` passed.

Status: completed
