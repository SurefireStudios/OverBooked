# Security Policy

OverBooked runs inside WordPress, takes bookings from unauthenticated visitors, and exposes a
public REST endpoint. Security reports are taken seriously.

## Supported versions

| Version | Status |
| --- | --- |
| `2.5.2` | ✅ Supported |
| `< 2.5.2` | ❌ Superseded — upgrade to `2.5.2` |

Fixes land on the latest release.

## Reporting a vulnerability

**Please do not open a public issue for an unpatched vulnerability.**

Use either channel:

1. **GitHub Security Advisories** — the
   [Report a vulnerability](https://github.com/SurefireStudios/OverBooked/security/advisories/new)
   form on this repository. This is the preferred route.
2. **Email** — contact [Surefire Studios](https://www.surefirestudios.io) via the details on
   our site, with `OverBooked security` in the subject line.

Please include, where you can:

- The plugin version and your WordPress and PHP versions
- The file, endpoint or shortcode involved
- Steps to reproduce, ideally with a minimal proof of concept
- The privilege level required (unauthenticated visitor, customer, agent, administrator)
- Your assessment of the impact

### What to expect

- We aim to acknowledge a report within **7 days**.
- We will confirm the issue and share a rough remediation timeline.
- Once a fix ships, we will credit you in the release notes unless you prefer otherwise.

## Scope

**In scope** — everything in this repository: the plugin PHP, the AJAX handlers, the REST
endpoints, the bundled add-ons, and the front-end templates.

Areas we are particularly interested in:

- **The booking flow**, which processes input from unauthenticated visitors: injection,
  stored XSS in appointment/custom-field data, or booking on behalf of another user
- **The public REST endpoint** `GET /wp-json/booked/v1/availability`, which is intentionally
  unauthenticated (`permission_callback` returns true) so visitors can see open slots — any
  data disclosure beyond availability, or a way to enumerate private data through it, is a
  finding
- **The authenticated endpoint** `GET /wp-json/booked/v1/appointments` — capability-check
  bypasses
- **CSV export** — formula injection or exposure of other users' appointments
- Missing nonce or capability checks on any AJAX action

**Out of scope:**

- Vulnerabilities in WordPress core, WooCommerce, or other plugins/themes
- Issues requiring an already-compromised administrator account
- Missing security headers at the web-server level

## Notes for reviewers

- The plugin inherits the `booked_` prefix and `booked.php` main file from its upstream origin
  ("Booked"). The public text domain is `overbooked`.
- Version 2.5.0 added input sanitisation, nonce verification, safer session handling, and
  `wp_safe_redirect()` across the plugin.
