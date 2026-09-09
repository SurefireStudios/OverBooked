# Contributing

Thanks for your interest in OverBooked. Issues and pull requests are welcome.

Found a **security vulnerability**? Do not open a public issue — follow [SECURITY.md](SECURITY.md).

## Good first contributions

- **Remove the leftover upstream support calls.** `includes/functions.php` still fetches
  knowledge-base articles from the original vendor's Ticksy API (`api.ticksy.com/v1/boxystudio/…`)
  with a hard-coded key, and links to `boxystudio.ticksy.com`. These should point at Surefire
  Studios' own support, or be removed.
- **Testing reports** against current WordPress releases — `readme.txt` says *Tested up to 6.8*.
- **Automated tests.** There are none yet; the REST endpoints and the availability logic are
  the obvious first targets.
- **Translations** — the template is in `languages/`.

## Setting up

OverBooked is a standard WordPress plugin. There's no build step for a normal checkout — the
Composer `vendor/` and built `dist/` assets are committed, so a clone activates as-is.

```bash
git clone https://github.com/SurefireStudios/OverBooked.git \
  wp-content/plugins/overbooked
```

Activate **OverBooked** under **Plugins**, then work on the files in place. A local WordPress
with `WP_DEBUG` on is recommended:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

If you change Composer dependencies, run `composer install` and commit the updated `vendor/`,
since it ships in the repository.

## How it fits together

| Path | Responsibility |
| --- | --- |
| `booked.php` | Plugin header, constants, bootstrap, REST route registration |
| `includes/` | Core logic and helpers |
| `includes/ajax/` | Admin and front-end AJAX handlers |
| `includes/add-ons/` | WooCommerce payments, calendar feeds, front-end agents |
| `includes/email-templates/` | Notification email HTML |
| `post-types/` | Custom post types for calendars and appointments |
| `templates/` | Front-end output |
| `assets/` · `dist/` | Source and built CSS/JS |

The internal function and option prefix is `booked_` and the main file is `booked.php` — both
inherited from the upstream plugin. The public text domain is `overbooked`.

## Conventions

Follow WordPress plugin conventions, which the modernised code already uses:

- **Escape on output**: `esc_html()`, `esc_attr()`, `esc_url()`
- **Sanitize on input**: `sanitize_text_field()` and friends; `wp_unslash()` before sanitising
- **Guard writes**: capability checks plus nonce verification on AJAX and forms
- **Redirect safely**: `wp_safe_redirect()`
- **Target PHP 8.3** — the plugin declares `Requires PHP: 8.3`; no dynamic properties (declare
  them), and prefer modern PHP where it's already in use
- **Keep strings translatable** in the `overbooked` text domain

## Before opening a pull request

Every PHP file must parse. CI runs this on PHP 8.3 and 8.4:

```bash
git ls-files '*.php' | xargs -n1 php -l
composer validate --no-check-publish --no-check-lock
```

Then test in a real WordPress install: the plugin activates without notices, a calendar renders
from `[booked-calendar]`, a booking completes, and the confirmation email sends.

Describe what you changed and the WordPress and PHP versions you tested against.

## Reporting a bug

[Open an issue](https://github.com/SurefireStudios/OverBooked/issues) with the plugin version,
your WordPress and PHP versions, the shortcode or screen involved, steps to reproduce, and any
PHP error or debug-log output.

## License

By contributing, you agree that your work is released under the
[GNU General Public License v2.0 or later](LICENSE), the same terms as the rest of the project.
