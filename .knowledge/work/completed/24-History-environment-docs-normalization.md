# Environment Docs Normalization - 024

Status: in_progress
Owner: Codex
Created: 2026-05-14

## Checklist

- [x] Confirm current environment/config inconsistencies from repo docs and defaults.
- [x] Remove conflicting port defaults from `.env.example`.
- [x] Refresh setup documentation to match current Sail, hostnames, DB roles, and Vite port behavior.
- [x] Replace default root README with concise project-specific architecture and local setup guidance.
- [x] Record verification notes and move checklist to `completed`.

## Verification Notes

- Verified `.env.example` now has one `APP_PORT`, adds `VITE_PORT=5174`, and aligns `APP_URL`, `DB_CONNECTION`, and `DB_DATABASE` with the active local runtime baseline.
- Verified `README.md` and `.knowledge/setup-process.md` now agree on local hosts, control DB usage, and app/Vite/Postgres ports.
- Verified `.devcontainer/devcontainer.json` port values align with the documented local environment.
- Note: plain `JSON.parse` rejects `devcontainer.json` because the file uses JSON-with-comments syntax accepted by VS Code/devcontainer tooling.

## Blockers

- None.

Status: completed
