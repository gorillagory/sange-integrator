# Vite Manifest Login Mismatch Fix - 021

Status: in_progress

## Checklist

- [x] Confirm runtime error source against `public/build/manifest.json`.
- [x] Remove direct page-component Vite entry from `resources/views/app.blade.php`.
- [x] Clear compiled views and framework caches in container.
- [x] Move checklist to `completed`.

## Verification Notes

- Manifest contains only `resources/js/app.js` and `resources/css/app.css` entries.
- Previous Blade directive requested a non-entry page file (`resources/js/Pages/Auth/Login.vue`) causing ViteException.

Status: completed
