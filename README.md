# Fluent Forms Gift Certificates

A comprehensive WordPress plugin that integrates with Fluent Forms to provide gift certificate functionality with customizable designs and automatic email delivery.

## 🎯 Features

### Core Functionality
- **Gift Certificate Generation**: Automatically generate unique gift certificate codes when forms are submitted
- **Database Storage**: Store certificate codes, amounts, and usage status in WordPress database
- **Email Delivery**: Send beautifully designed gift certificates to recipients automatically
- **Form Integration**: Seamlessly integrate with Fluent Forms for both purchase and application forms
- **Usage Tracking**: Prevent reuse and track certificate usage status

### Admin Features
- **Dashboard**: Comprehensive admin dashboard with statistics and quick actions
- **Certificate Management**: Create, edit, and manage gift certificates with detailed information
- **Design System**: Create multiple gift certificate designs with different themes and email templates
- **Settings Panel**: Configure minimum/maximum amounts, currency, expiry dates, and email settings
- **Usage Logs**: Track all certificate usage with detailed history

### Frontend Features
- **Balance Checker**: Shortcode to check gift certificate balances
- **Purchase Forms**: Shortcode for purchasing gift certificates
- **Design Showcase**: Display available certificate designs
- **Responsive Design**: Mobile-friendly interface
- **AJAX Integration**: Smooth user experience with real-time validation

## 📋 Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- Fluent Forms plugin (free or pro)
- MySQL 5.6 or higher

## 🚀 Installation

1. **Download the Plugin**
   Download the latest release from https://github.com/makingtheimpact/fluentforms-gift-certificates

2. **Upload to WordPress**
   - Upload the `fluentforms-gift-certificates` folder to `/wp-content/plugins/`
   - Or zip the folder and upload via WordPress admin

3. **Activate the Plugin**
   - Go to WordPress Admin → Plugins
   - Find "Fluent Forms Gift Certificates" and click "Activate"

4. **Configure Settings**
   - Go to Gift Certificates → Settings
   - Configure your preferences and enable forms

## ⚙️ Configuration

### 1. Enable Forms
1. Go to **Gift Certificates → Settings**
2. Select which Fluent Forms should have gift certificate functionality
3. Forms with amount/price fields will be treated as purchase forms
4. Other forms will be treated as application forms

### 2. Create Designs
1. Go to **Gift Certificates → Designs**
2. Click "Add New Design"
3. Set minimum and maximum amounts
4. Upload a featured image (optional)
5. Create custom email template with placeholders
6. Set status to "Active"

### 3. Configure Email Settings
1. Go to **Gift Certificates → Settings**
2. Set email sender name and address
3. Customize email subject line
4. Configure default email template

## 📝 Shortcodes

### Balance Checker
```php
[gift_certificate_balance]
```

**Parameters:**
- `title` - Custom title (default: "Check Gift Certificate Balance")
- `button_text` - Custom button text (default: "Check Balance")
- `placeholder` - Custom placeholder text
- `show_history` - Show usage history (default: "yes")

### Purchase Form
```php
[gift_certificate_purchase]
```

**Parameters:**
- `title` - Custom title (default: "Purchase Gift Certificate")
- `show_designs` - Show design selection (default: "yes")
- `min_amount` - Override minimum amount
- `max_amount` - Override maximum amount

### Designs Showcase
```php
[gift_certificate_designs]
```

**Parameters:**
- `title` - Custom title (default: "Gift Certificate Designs")
- `columns` - Number of columns (default: 3)
- `show_prices` - Show price ranges (default: "yes")

## 🎨 Email Templates

### Available Placeholders
- `{recipient_name}` - Recipient's name
- `{certificate_code}` - Unique certificate code
- `{amount}` - Certificate amount
- `{personal_message}` - Personal message (if provided)
- `{expiry_date}` - Expiry date
- `{site_name}` - Website name
- `{site_url}` - Website URL

### Example Template
```html
<!DOCTYPE html>
<html>
<head>
    <title>Gift Certificate</title>
</head>
<body>
    <h1>🎁 Gift Certificate</h1>
    <p>Dear {recipient_name},</p>
    <p>You have received a gift certificate worth <strong>${amount}</strong>!</p>
    <div class="code">{certificate_code}</div>
    {personal_message}
    <p>Valid until: {expiry_date}</p>
</body>
</html>
```

## 🔧 Development

### File Structure
```
fluentforms-gift-certificates/
├── assets/
│   ├── css/
│   │   ├── admin.css
│   │   └── frontend.css
│   ├── js/
│   │   ├── admin.js
│   │   └── frontend.js
│   └── images/
├── includes/
│   ├── class-ffgc-core.php
│   ├── class-ffgc-post-types.php
│   ├── class-ffgc-settings.php
│   ├── class-ffgc-forms.php
│   ├── class-ffgc-email.php
│   ├── class-ffgc-shortcodes.php
│   └── class-ffgc-installer.php
├── templates/
│   └── admin/
│       ├── main-page.php
│       ├── settings-page.php
│       ├── designs-page.php
│       └── meta-boxes/
├── languages/
├── fluentforms-gift-certificates.php
├── index.php
└── README.md
```

### Hooks and Filters

#### Actions
- `ffgc_certificate_created` - Fired when a certificate is created
- `ffgc_certificate_used` - Fired when a certificate is used
- `ffgc_email_sent` - Fired when an email is sent

#### Filters
- `ffgc_certificate_code_format` - Modify certificate code format
- `ffgc_email_template` - Modify email template
- `ffgc_amount_limits` - Modify amount limits

### Database Tables
- `wp_posts` - Gift certificates and designs (custom post types)
- `wp_postmeta` - Certificate and design metadata
- `wp_ffgc_usage_log` - Usage tracking table

## 🛠️ Customization

### Adding Custom Fields
```php
// Add custom field to certificate meta box
add_action('add_meta_boxes', function() {
    add_meta_box(
        'custom_certificate_field',
        'Custom Field',
        'custom_field_callback',
        'gift_certificate'
    );
});
```

### Custom Email Templates
```php
// Override default email template
add_filter('ffgc_email_template', function($template, $certificate_id) {
    // Your custom template logic
    return $custom_template;
}, 10, 2);
```

### Custom Validation
```php
// Add custom validation rules
add_filter('ffgc_validate_certificate', function($is_valid, $code) {
    // Your custom validation logic
    return $is_valid;
}, 10, 2);
```

## 🐛 Troubleshooting

### Common Issues

1. **Emails Not Sending**
   - Check WordPress email configuration
   - Verify SMTP settings if using SMTP plugin
   - Check spam folder

2. **Forms Not Working**
   - Ensure Fluent Forms is activated
   - Check if forms are enabled in settings
   - Verify form has amount/price fields for purchase forms

3. **Certificates Not Generating**
   - Check database permissions
   - Verify post types are registered
   - Check for JavaScript errors

### Debug Mode
Enable WordPress debug mode to see detailed error messages:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## 📄 License

This plugin is licensed under the GPL v2 or later.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📞 Support

For support, please:
1. Check the documentation
2. Search existing issues
3. Create a new issue with detailed information

## 🔄 Changelog

### Version 1.0.0
- Initial release
- Basic gift certificate functionality
- Email templates
- Admin dashboard
- Shortcodes
- Form integration

## 🙏 Credits

- Built for Fluent Forms
- Uses WordPress coding standards
- Responsive design with modern CSS
- Accessibility compliant

---

**Note**: This plugin requires Fluent Forms to be installed and activated. It is designed to work with both free and pro versions of Fluent Forms. 