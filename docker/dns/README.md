# Split DNS for `bayam.test`

This directory contains a free CoreDNS setup for Tailscale split DNS.

## What it does

- Answers `sys.bayam.test`
- Answers tenant hosts such as `bt.bayam.test`
- Wildcards any other `*.bayam.test` subdomain to the current Tailscale node

## Runtime

The DNS server runs through Docker Compose as `split-dns` and binds:

- `53/tcp`
- `53/udp`

## Tailscale admin setup

1. Open the Tailscale admin console DNS page.
2. Keep MagicDNS enabled.
3. Add a custom nameserver:
   - `100.73.219.44`
4. Restrict that nameserver to:
   - `bayam.test`
5. Leave `Override DNS servers` off.

This creates split DNS only for `*.bayam.test`, while all other domains continue using normal DNS.

## Validation

From another Tailscale client:

```bash
getent hosts sys.bayam.test
getent hosts bt.bayam.test
curl -I http://sys.bayam.test:8080/login
curl -I http://bt.bayam.test:8080/login
```

## If the Tailscale IP changes

Update `db.bayam.test` and restart:

```bash
docker compose up -d split-dns
```
