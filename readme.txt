=== OverBooked ===
Contributors: surefirestudios
Tags: appointment, booking, calendar, scheduling, business, appointments, bookings, reservation
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 8.3
Stable tag: 2.5.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Powerful appointment booking made simple. Lightweight WordPress appointment booking plugin designed for modern sites. Built with PHP 8+ and the latest WordPress standards.

== Description ==

**OverBooked** is a modern WordPress appointment booking plugin that has been completely updated for PHP 8.3 and WordPress 6.8.2 compatibility. This version features enhanced security, improved performance, and a streamlined codebase that makes scheduling effortless for both site owners and clients.

### 🚀 **What's New in Version 2.5.1**

**✨ Complete Modernization**
* **PHP 8.3 Compatible** - Fully updated for PHP 8.3 with improved session handling
* **WordPress 6.8.2 Ready** - Tested and optimized for the latest WordPress version
* **Enhanced REST API** - New REST API endpoints for better performance and integration
* **Security Hardened** - Complete security audit with improved input sanitization and nonce verification

**🛡️ Security Enhancements**
* **Input Sanitization** - All user inputs properly sanitized using WordPress functions
* **Nonce Verification** - Enhanced security for all AJAX requests and form submissions
* **Session Security** - Improved session management with secure cookie settings for PHP 8.3
* **Permission Validation** - Better user permission checks and access control
* **Safe Redirects** - All redirects use wp_safe_redirect() for enhanced security

**⚡ Performance Optimizations**
* **Database Query Optimization** - Reduced query limits to prevent timeouts (500+ → 100 max)
* **REST API Endpoints** - New efficient API endpoints for calendar and appointment data
* **Session Management** - Optimized session handling that properly closes connections
* **Memory Usage** - Reduced memory footprint through efficient query handling
* **Timeout Prevention** - Query limits capped to prevent REST API timeouts
 
### 🎯 **Perfect For**
* **Small Businesses** - Professional appointment scheduling
* **Service Providers** - Client booking management
* **Consultants** - Meeting coordination
* **Healthcare Providers** - Patient appointment scheduling
* **Salons & Spas** - Beauty service bookings
* **Any WordPress Site** - That needs reliable appointment booking

### 🔧 **Core Features**

**📅 Advanced Calendar System**
* Multiple calendar support with individual settings
* Custom time slot creation and management
* Flexible availability configuration
* Intuitive admin calendar interface
* Mobile-responsive calendar views

**👥 User Management**
* Guest booking support (optional registration)
* User registration integration
* Role-based permissions (Administrator, Booking Agent)
* Customer profile management
* Automated user account creation

**📧 Smart Email Notifications**
* Automated email notifications for all actions
* Customizable email templates with tokens
* Appointment reminders (configurable timing)
* Approval/cancellation notifications
* Custom email signatures and branding

**💼 Business Tools**
* CSV export functionality for reporting
* Admin dashboard with upcoming appointments
* Custom fields for additional information
* Multi-language support (translation ready)
* Calendar feed integration

### 🚀 **Getting Started**
1. **Install & Activate** - Upload and activate the plugin
2. **Configure Settings** - Set up your calendars and time slots
3. **Create Appointment Pages** - Use [booked-calendar] shortcode
4. **Set Up Notifications** - Configure email templates
5. **Start Booking** - Your appointment system is ready!

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/booked/` or install through WordPress admin
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'Appointments' in your WordPress admin to configure settings
4. Create your first calendar and set up time slots
5. Add the [booked-calendar] shortcode to any page or post
6. Configure email notifications and other preferences
7. Start accepting appointments!

== Frequently Asked Questions ==

= Is this plugin secure and updated? =
Yes! Version 2.5.1 includes a complete security audit with proper input sanitization, nonce verification, and session management. The plugin is fully updated for PHP 8.3 and WordPress 6.8.2.

= Does it work with PHP 8.3? =
Absolutely! This version has been specifically updated and tested for PHP 8.3 compatibility with improved session handling and modern PHP practices.

= Is it mobile responsive? =
Yes! The plugin includes a fully responsive design that works perfectly on mobile devices, tablets, and desktops.

= Can I customize the appearance? =
Yes! The plugin includes color customization options and works with any WordPress theme. You can also add custom CSS for further styling.

= Does it support multiple calendars? =
Yes! You can create multiple calendars for different services, staff members, or locations, each with their own settings.

= Can customers book without registering? =
Yes! The plugin supports both guest booking (no registration required) and registered user booking.

= Is there payment integration? =
Yes! The plugin includes WooCommerce integration for accepting payments with bookings.

= Can I export appointment data? =
Yes! The plugin includes CSV export functionality for appointments with various filtering options.

= Does it send email notifications? =
Yes! Automated email notifications are sent for new bookings, approvals, cancellations, and reminders. All templates are customizable.

= Does it work with any WordPress theme? =
Yes! The plugin is designed to work with any properly coded WordPress theme and follows WordPress standards.

== Changelog ==

= 2.5.2 =
* ✅ **Cleanup** - Removed leftover upstream code that called a third-party support API (Ticksy) with a hard-coded key on the admin welcome screen. The function was unused dead code inherited from the original plugin; removing it drops an external network call and the bundled key.

= 2.5.1 =
* ✅ **PHP 8.2 Fix** - Fixed deprecation notice for dynamic properties in the main plugin class file

= 2.5.0 =
* ✅ **MAJOR UPDATE** - Complete modernization for PHP 8.3 and WordPress 6.8.2
* ✅ **PHP 8.3 Compatible** - Updated session handling and deprecated function replacements
* ✅ **Security Enhanced** - Complete security audit with proper input sanitization
* ✅ **Performance Optimized** - Database query optimization and REST API isolation to prevent timeouts
* ✅ **REST API Added** - New REST API endpoints for better performance and integration
* ✅ **Session Security** - Improved session management with secure settings
* ✅ **AJAX Security** - Enhanced AJAX request security and validation
* ✅ **Code Modernization** - Updated to modern PHP and WordPress practices
* ✅ **Author Updated** - Now maintained by Surefire Studios
* ✅ **Query Optimization** - Reduced query limits from 500-5000 to 100 maximum
* ✅ **REST API Isolation** - Plugin components completely bypassed during REST API requests
* ✅ **Error Handling** - Improved error handling and user feedback

== Upgrade Notice ==

= 2.5.0 =
**MAJOR UPDATE REQUIRED** - This version brings PHP 8.3 compatibility, enhanced security, performance optimizations, and WordPress 6.8.2 support. This update fixes REST API timeout issues and provides significant security improvements. Backup your site before updating and test in staging environment first.

== Support ==

For support, documentation, and updates, visit:
* **GitHub Repository**: https://github.com/SurefireStudios/OverBooked
* **Website**: https://www.surefirestudios.io
