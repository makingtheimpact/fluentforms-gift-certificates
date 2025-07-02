=== Fluent Forms Gift Certificates ===
Contributors: makingtheimpact
Tags: fluent-forms, gift-certificates, ecommerce, forms, email, certificates
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Generate and manage gift certificates for Fluent Forms with customizable designs and automatic email delivery.

== Description ==

Fluent Forms Gift Certificates is a comprehensive WordPress plugin that seamlessly integrates with Fluent Forms to provide powerful gift certificate functionality. Perfect for businesses looking to offer gift certificates through their website with professional email delivery and usage tracking.

= Key Features =

**🎁 Gift Certificate Generation**
* Automatically generate unique gift certificate codes
* Store certificates in WordPress database with full tracking
* Prevent reuse and track usage status

**📧 Email Delivery**
* Beautiful, customizable email templates
* Automatic delivery to recipients
* Support for personal messages and custom designs

**🎨 Design System**
* Create multiple certificate designs
* Custom email templates for each design
* Featured images and branding options
* Set amount ranges per design

**📊 Admin Dashboard**
* Comprehensive statistics and overview
* Certificate management interface
* Usage tracking and history
* Quick actions and bulk operations

**🔧 Form Integration**
* Seamless Fluent Forms integration
* Purchase forms for buying certificates
* Application forms for using certificates
* Real-time validation and balance checking

**📱 Frontend Shortcodes**
* `[gift_certificate_balance]` - Check certificate balances
* `[gift_certificate_designs]` - Showcase available designs

**⚙️ Configuration Options**
* Minimum and maximum amounts
* Currency settings
* Expiry date configuration
* Email sender settings
* Form selection and customization

= Perfect For =
* Online stores
* Service businesses
* Restaurants and cafes
* Spas and salons
* Any business offering gift certificates

= Requirements =
* WordPress 5.0 or higher
* PHP 7.4 or higher
* Fluent Forms plugin (free or pro)
* MySQL 5.6 or higher

== Installation ==

1. Upload the `fluentforms-gift-certificates` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Gift Certificates → Settings to configure the plugin
4. Enable the forms you want to use for gift certificates
5. Create your first certificate design

== Configuration ==
1. Enable the Gift Certificate Design and Redemption fields under **Fluent Forms → Global Settings → Elements**.
2. Connect your preferred gateway in **Fluent Forms → Global Settings → Payment Settings**.
3. Build purchase and redemption forms with the required fields.
4. Open **Gift Certificates → Configure Forms**, choose your purchase and redemption forms (hold Ctrl/Cmd for multiple selections), then save.
5. When setting up an Outgoing Webhook, include the API token (found under **Gift Certificates → Settings**) in a `X-FFGC-Token` header or `token` URL parameter.

== Frequently Asked Questions ==

= Does this work with the free version of Fluent Forms? =

Yes! This plugin works with both the free and pro versions of Fluent Forms.

= Can I customize the email templates? =

Absolutely! You can create custom email templates for each design with HTML and CSS. The plugin includes placeholders for dynamic content like recipient name, certificate code, amount, and more.

= How do I set up different designs? =

Go to Gift Certificates → Designs and create new designs. Each design can have its own minimum/maximum amounts, featured image, and custom email template.

= Can customers use partial amounts from a certificate? =

Yes! The plugin tracks usage and allows customers to use part of a certificate's value. The remaining balance is stored and can be used in future purchases.

= How do I track certificate usage? =

The plugin automatically logs all certificate usage in the admin dashboard. You can view usage history, track balances, and see which forms were used.

= Can I set expiry dates for certificates? =

Yes! You can configure expiry dates in the plugin settings. Certificates will automatically be marked as expired after the set date.

= Is the plugin mobile-friendly? =

Yes! All frontend interfaces are fully responsive and work great on mobile devices.

= Can I integrate with my existing forms? =

Yes! Simply enable the forms you want to use in the plugin settings. Forms with amount/price fields will be treated as purchase forms, while others will be application forms.

== Screenshots ==

1. Admin dashboard with statistics and quick actions
2. Certificate management interface
3. Design creation and management
4. Settings configuration panel
5. Frontend balance checker shortcode
6. Purchase form with design selection
7. Email template editor
8. Usage tracking and history

== Changelog ==

= 1.0.3 =
* Replaced `_certificate_status` meta key with `_status`
* Migrated existing posts to the new meta key

= 1.0.2 =
* Moved usage logs from post meta to a custom table
* Migrated existing logs during upgrade

= 1.0.1 =
* Unified certificate meta keys
* Added migration for existing data

= 1.0.0 =
* Initial release
* Basic gift certificate functionality
* Email templates and delivery
* Admin dashboard and management
* Frontend shortcodes
* Fluent Forms integration
* Design system
* Usage tracking
* Settings configuration

== Upgrade Notice ==

= 1.0.3 =
Meta key changed from `_certificate_status` to `_status`. Run the upgrade to copy existing data.

= 1.0.2 =
Database changes for usage logs. Run the upgrade to migrate data.

= 1.0.1 =
Recommended upgrade to unify certificate meta keys.

= 1.0.0 =
Initial release of Fluent Forms Gift Certificates plugin.

== Developer Information ==

For developers, this plugin includes:

* Comprehensive hooks and filters
* Custom post types for certificates and designs
* Database tables for usage tracking
* AJAX endpoints for frontend functionality
* Responsive CSS and JavaScript
* WordPress coding standards compliance

See the full documentation at: [GitHub Repository](https://github.com/makingtheimpact/fluentforms-gift-certificates) 