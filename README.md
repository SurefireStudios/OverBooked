<div align="center">

<img src="welcome-banner.png" alt="OverBooked — Powerful appointment booking made simple" width="100%">

# OverBooked

**A lightweight WordPress appointment-booking plugin, modernised for PHP 8.3 and current WordPress — calendars, time slots, guest or registered booking, email notifications and WooCommerce payments.**

[![License: GPL v2+](https://img.shields.io/badge/license-GPL--2.0--or--later-blue)](LICENSE)
[![PHP Lint](https://github.com/SurefireStudios/OverBooked/actions/workflows/php-lint.yml/badge.svg)](https://github.com/SurefireStudios/OverBooked/actions/workflows/php-lint.yml)
[![Version 2.5.1](https://img.shields.io/badge/version-2.5.1-0d9488)](booked.php)
[![WordPress 5.0+](https://img.shields.io/badge/WordPress-5.0%2B-21759b?logo=wordpress&logoColor=white)](https://wordpress.org)
[![PHP 8.3+](https://img.shields.io/badge/PHP-8.3%2B-777bb4?logo=php&logoColor=white)](https://www.php.net)
[![Stars](https://img.shields.io/github/stars/SurefireStudios/OverBooked?style=flat)](https://github.com/SurefireStudios/OverBooked/stargazers)

Built by **[Surefire Studios](https://www.surefirestudios.io)**

</div>

---

## What it does

Add a booking calendar to any WordPress page with a shortcode. Visitors pick an open time
slot and book it — as a guest or a registered user — and both sides get email confirmations.
Site owners manage everything from an **Appointments** admin area: multiple calendars,
per-calendar availability, custom fields, approvals, CSV export and optional WooCommerce
payments.

OverBooked is a modernised fork of the GPL "Booked" plugin, updated for **PHP 8.3** and current
WordPress, with a security pass and a small REST API added.

---

## ✨ Features

- **Multiple calendars** — one per service, staff member or location, each with its own availability and settings
- **Flexible time slots** — custom slot creation, per-day availability, and a mobile-responsive calendar
- **Guest or registered booking** — customers book without an account, or with automatic account creation
- **Roles** — an *Administrator* and a *Booking Agent* role, with front-end agent tools
- **Email notifications** — templated confirmations, approvals, cancellations and reminders, with tokens and custom signatures
- **Custom fields** — collect extra information on the booking form
- **WooCommerce payments** — take payment for a booking through WooCommerce *(bundled add-on)*
- **Calendar feeds** — publish calendars as feeds *(bundled add-on)*
- **CSV export** — export appointments for reporting
- **REST API** — read endpoints for appointments and availability under `booked/v1`
- **Translation-ready** — `overbooked` text domain with bundled `.pot`, plus WPML config

---

## 📦 Requirements

- WordPress **5.0** or higher (tested up to 6.8)
- PHP **8.3** or higher
- *Optional:* [WooCommerce](https://wordpress.org/plugins/woocommerce/) for paid bookings

---

## 🚀 Installation

### From this repository

1. Download the repository as a ZIP (**Code → Download ZIP**), or clone it:
   ```bash
   git clone https://github.com/SurefireStudios/OverBooked.git
   ```
2. Put the folder in `wp-content/plugins/`. The plugin is identified by the header in
   `booked.php`, so the folder name doesn't matter — WordPress will name a GitHub ZIP
   `OverBooked-main`, which works fine.
3. Activate **OverBooked** under **Plugins** in the WordPress admin.
4. An **Appointments** menu appears in the sidebar.

> [!NOTE]
> This repository already includes its Composer `vendor/` directory and built `dist/` assets,
> so a clone is ready to activate — no build step required. If you prefer to install
> dependencies yourself, run `composer install`.

---

## 📖 Usage

1. Go to **Appointments** in the admin and create a **calendar**.
2. Set its available days and time slots.
3. Put the calendar on a page with the shortcode:
   ```
   [booked-calendar]
   ```
4. Configure email notifications under the plugin's settings.
5. Approve or manage incoming bookings from the admin, or export them to CSV.

### Shortcodes

| Shortcode | Purpose |
| --- | --- |
| `[booked-calendar]` | The main booking calendar |
| `[booked-calendar-switcher]` | Switch between multiple calendars |
| `[booked-appointments]` | A user's upcoming appointments |
| `[booked-profile]` | Customer profile management |
| `[booked-login]` | Front-end login form |
| `[booked-fea-appointments]` | Front-end agent appointment view |

### REST API

Two read endpoints are registered under the `booked/v1` namespace:

| Endpoint | Method | Auth |
| --- | --- | --- |
| `/wp-json/booked/v1/appointments` | GET | Requires a permission check |
| `/wp-json/booked/v1/availability` | GET | Public |

They were added to move calendar and availability reads off the admin-ajax path and avoid
REST-request timeouts.

---

## 🧩 Bundled add-ons

These live under `includes/add-ons/` and load automatically:

- **WooCommerce Payments** — charge for a booking via WooCommerce
- **Calendar Feeds** — expose calendars as subscribable feeds
- **Front-end Agents** — let booking agents manage appointments from the front end

---

## 🧰 Tech stack

| Layer | Used |
| --- | --- |
| Platform | WordPress plugin (PHP) |
| Language | PHP 8.3+, JavaScript (jQuery), CSS |
| Dependencies | Composer — [`fortawesome/wordpress-fontawesome`](https://github.com/FortAwesome/wordpress-fontawesome) |
| Bundled UI libs | Chosen, Tooltipster |
| Payments | WooCommerce *(optional)* |
| i18n | `overbooked` text domain, `.pot` included, WPML config |

---

## 🗂 Project structure

```
OverBooked/
├── booked.php                 # Plugin header and bootstrap
├── includes/                  # Core logic, AJAX handlers, add-ons, email templates
│   ├── ajax/                  # Admin and front-end AJAX endpoints
│   ├── add-ons/               # WooCommerce payments, calendar feeds, front-end agents
│   └── export-csv.php         # CSV export
├── post-types/                # Custom post types (calendars, appointments)
├── templates/                 # Front-end templates
├── assets/ · dist/            # Source and built CSS/JS
├── languages/                 # Translations (.pot)
├── vendor/                    # Composer dependencies (committed)
├── composer.json
├── wpml-config.xml
└── readme.txt                 # WordPress.org-style readme
```

---

## 📝 Changelog

Full history is in [`readme.txt`](readme.txt). In brief:

- **2.5.1** — Fixed a PHP 8.2 dynamic-property deprecation in the main plugin class
- **2.5.0** — Modernisation for PHP 8.3 and current WordPress: security pass (input sanitisation, nonce verification, safer session handling, `wp_safe_redirect`), REST endpoints for calendar/availability data, and query-limit tuning to prevent REST timeouts

---

## 🤝 Contributing

Issues and pull requests are welcome. See **[CONTRIBUTING.md](CONTRIBUTING.md)** for setup and
conventions.

> [!CAUTION]
> Found a security vulnerability? **Don't open a public issue** — follow [SECURITY.md](SECURITY.md) to report it privately.

---

## 📄 License

Released under the **GNU General Public License v2.0 or later** — see [LICENSE](LICENSE).

OverBooked is derived from the GPL-licensed "Booked" plugin and is distributed under the same
terms. Bundled libraries (Font Awesome, Chosen, Tooltipster) retain their own licenses.

---

## 🔗 Links

- 🌐 **Surefire Studios** — <https://www.surefirestudios.io>
- 🐛 **Issues** — <https://github.com/SurefireStudios/OverBooked/issues>

<div align="center">
<sub>Built by <a href="https://www.surefirestudios.io">Surefire Studios</a>.</sub>
</div>
