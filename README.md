# Fluent Forms Gift Certificates

A comprehensive WordPress plugin that integrates with Fluent Forms Pro to provide gift certificate functionality. Create, manage, and redeem gift certificates seamlessly through Fluent Forms.

## Features

### 🎨 **Visual Design Selection**
- Create multiple gift certificate designs with custom images and themes
- Visual grid layout for design selection in forms
- Configurable columns and display options
- Design-specific minimum/maximum amounts

### 💳 **Fluent Forms Pro Integration**
- **Custom Field Types**: Gift Certificate Design and Redemption fields
- **Purchase Forms**: Complete gift certificate purchasing workflow
- **Redemption Forms**: Easy certificate application and validation
- **Payment Integration**: Works with Fluent Forms payment gateways
- **Form Calculations**: Automatic discount application

### 🔧 **Admin Management**
- Comprehensive admin dashboard with statistics
- Certificate management and tracking
- Design creation and management
- Form configuration interface
- Usage history and reporting

### 📧 **Email System**
- Automated gift certificate delivery
- Customizable email templates
- Design-specific email content
- Recipient information handling

### 🎯 **User Experience**
- Real-time balance checking
- AJAX-powered interactions
- Responsive design
- Accessibility features
- Mobile-friendly interface

## Installation

1. Upload the plugin files to `/wp-content/plugins/fluentforms-gift-certificates/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Ensure Fluent Forms Pro is installed and activated
4. Configure the plugin through the admin dashboard

## Configuration

### 1. Create Gift Certificate Designs

1. Go to **Gift Certificates > Designs**
2. Click "Add New Design"
3. Upload a design image
4. Set minimum/maximum amounts
5. Configure email template
6. Set as active

### 2. Configure Fluent Forms

#### Purchase Form Setup
1. **Enable Fields** - In *Fluent Forms → Global Settings → Elements* turn on the Gift Certificate Design and Redemption fields.
2. **Connect Payment Gateway** - Configure your preferred gateway under *Fluent Forms → Global Settings → Payment Settings*.
3. Create a new Fluent Form
4. Add the following field types:
   - **Gift Certificate Design** - Visual design selection
   - **Text Input** (name: `recipient_name`) - Recipient's name
   - **Email Input** (name: `recipient_email`) - Recipient's email
   - **Textarea** (name: `personal_message`) - Personal message
   - **Custom Payment Amount** - Gift certificate amount
   - **Payment Method** - Payment processing
5. Configure payment settings
6. Save and add to Purchase Forms list

#### Redemption Form Setup
1. Create a new Fluent Form
2. Add the following field types:
   - **Gift Certificate Redemption** - Code entry and validation
   - **Product/Service Selection** - Items to purchase
   - **Payment Fields** (if needed) - Additional payment
3. Configure form calculations
4. Save and add to Redemption Forms list

### 3. Configure Forms Integration

1. Go to **Gift Certificates > Configure Forms**
2. Choose the purchase and redemption forms you created earlier. Hold Ctrl/Cmd to pick multiple forms and click **Save**.
3. Configure field display settings
4. Set up auto-apply options

## Field Types

### Gift Certificate Design Field
- **Display Type**: Grid or Dropdown
- **Columns**: Number of columns in grid (1-6)
- **Show Design Info**: Display price range and description

### Gift Certificate Redemption Field
- **Show Balance Check**: Display "Check Balance" button
- **Auto Apply**: Automatically apply valid certificates

## Shortcodes

### Balance Checker
```
[gift_certificate_balance]
```
Displays a form to check gift certificate balance.

=======

### Designs Showcase
```
[gift_certificate_designs]
```
Displays available gift certificate designs.

## API Integration

### AJAX Endpoints

#### Validate Certificate
```javascript
$.ajax({
    url: ffgc_ajax.ajax_url,
    type: 'POST',
    data: {
        action: 'ffgc_validate_certificate',
        nonce: ffgc_ajax.nonce,
        code: 'CERTIFICATE_CODE'
    },
    success: function(response) {
        if (response.success) {
            console.log('Balance:', response.data.balance);
        }
    }
});
```

#### Get Designs
```javascript
$.ajax({
    url: ffgc_ajax.ajax_url,
    type: 'POST',
    data: {
        action: 'ffgc_get_designs',
        nonce: ffgc_ajax.nonce
    },
    success: function(response) {
        if (response.success) {
            console.log('Designs:', response.data);
        }
    }
});
```

### Purchase Webhook
To trigger certificate generation via the REST API, add an **Outgoing Webhook** action to your purchase form:

1. In the form's **Marketing & CRM Integrations** tab choose **Outgoing Webhook**.
2. Set the request URL to `https://your-site.com/wp-json/ffgc/v1/purchase`.
3. Use the `POST` method and send data as `JSON` or form fields.
4. Map your form fields:
   - `design_id` → the field containing the design choice (select, radio, or Gift Certificate Design field).
   - `recipient_name` → text input for the recipient's name.
   - `recipient_email` → email input for the recipient.
   - `amount` → payment or number field with the purchase amount.
   - `personal_message` → optional textarea for a message.
5. Save the integration and test the form.

## Hooks and Filters

### Actions
- `ffgc_certificate_created` - Fired when a certificate is created
- `ffgc_certificate_applied` - Fired when a certificate is applied
- `ffgc_certificate_expired` - Fired when a certificate expires

### Filters
- `ffgc_certificate_amount` - Modify certificate amount
- `ffgc_certificate_code` - Modify certificate code generation
- `ffgc_email_content` - Modify email content

## Development

### File Structure
```
fluentforms-gift-certificates/
├── assets/
│   ├── css/
│   │   ├── admin.css
│   │   └── frontend.css
│   └── js/
│       ├── admin.js
│       └── frontend.js
├── includes/
│   ├── class-ffgc-core.php
│   ├── class-ffgc-forms.php
│   ├── class-ffgc-settings.php
│   ├── class-ffgc-post-types.php
│   ├── class-ffgc-email.php
│   ├── class-ffgc-shortcodes.php
│   └── class-ffgc-installer.php
├── templates/
│   └── admin/
│       ├── main-page.php
│       ├── settings-page.php
│       ├── forms-page.php
│       └── designs-page.php
└── fluentforms-gift-certificates.php
```

### Adding Custom Field Types
```php
add_filter('fluentform_editor_components', function($components) {
    $components['my_custom_field'] = array(
        'element' => 'my_custom_field',
        'attributes' => array(
            'name' => 'my_custom_field',
            'label' => 'My Custom Field'
        ),
        'settings' => array(
            'custom_setting' => 'value'
        )
    );
    return $components;
});
```

## Support

For support, feature requests, or bug reports, please visit our [GitHub repository](https://github.com/your-repo/fluentforms-gift-certificates).

## Changelog

### Version 1.0.3
- Replaced `_certificate_status` meta key with `_status`
- Migrated existing posts to the new meta key

### Version 1.0.2
- Moved certificate usage logs to a dedicated database table
- Added migration of legacy log meta

### Version 1.0.1
- Unified certificate meta keys
- Added migration for existing data

### Version 1.0.0
- Initial release
- Fluent Forms Pro integration
- Custom field types
- Visual design selection
- Certificate management
- Email system
- Admin dashboard

## License

This plugin is licensed under the GPL v2 or later.

## Credits

Built with ❤️ for the WordPress community. 