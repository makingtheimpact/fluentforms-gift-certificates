/**
 * Admin JavaScript for Fluent Forms Gift Certificates
 */

jQuery(document).ready(function($) {
    'use strict';

    // Global FFGC Admin object
    window.FFGC_Admin = {
        init: function() {
            this.initFluentFormsIntegration();
            this.initDesignManagement();
            this.initCertificateManagement();
            this.initApiTokenField();
        },

        // Initialize Fluent Forms integration
        initFluentFormsIntegration: function() {
            // Check if we're in Fluent Forms editor
            if (typeof window.FluentFormEditor !== 'undefined') {
                this.registerCustomFields();
            }
        },

        // Register custom field types with Fluent Forms
        registerCustomFields: function() {
            // Register Gift Certificate Design field
            if (typeof window.FluentFormEditor !== 'undefined' && window.FluentFormEditor.addFieldType) {
                window.FluentFormEditor.addFieldType('gift_certificate_design', {
                    title: 'Gift Certificate Design',
                    icon: 'el-icon-picture',
                    category: 'Advanced Fields',
                    template: function(field) {
                        return '<div class="ffgc-design-field" data-field-id="' + field.attributes.name + '">' +
                               '<input type="hidden" name="' + field.attributes.name + '" value="' + (field.attributes.value || '') + '" ' + (field.attributes.required ? 'required' : '') + ' />' +
                               '<div class="ffgc-design-grid" style="grid-template-columns: repeat(' + (field.settings.columns || 3) + ', 1fr);">' +
                               '<div class="ffgc-loading-designs">' + ffgc_strings.loading_designs + '</div>' +
                               '</div>' +
                               '</div>';
                    },
                    getValue: function(field) {
                        return field.$el.find('input[type="hidden"]').val();
                    },
                    setValue: function(field, value) {
                        field.$el.find('input[type="hidden"]').val(value);
                        field.$el.find('.ffgc-design-option').removeClass('selected');
                        field.$el.find('[data-design-id="' + value + '"]').addClass('selected');
                    },
                    getSettings: function(field) {
                        return {
                            display_type: field.settings.display_type || 'grid',
                            columns: field.settings.columns || 3,
                            show_design_info: field.settings.show_design_info !== false
                        };
                    },
                    setSettings: function(field, settings) {
                        field.settings = $.extend({}, field.settings, settings);
                        if (settings.columns) {
                            field.$el.find('.ffgc-design-grid').css('grid-template-columns', 'repeat(' + settings.columns + ', 1fr)');
                        }
                    }
                });

                // Register Gift Certificate Redemption field
                window.FluentFormEditor.addFieldType('gift_certificate_redemption', {
                    title: 'Gift Certificate Redemption',
                    icon: 'el-icon-ticket',
                    category: 'Advanced Fields',
                    template: function(field) {
                        return '<div class="ffgc-redemption-field" data-field-id="' + field.attributes.name + '">' +
                               '<div class="ffgc-code-input-group">' +
                               '<input type="text" name="' + field.attributes.name + '" value="' + (field.attributes.value || '') + '" ' +
                               'placeholder="' + (field.attributes.placeholder || ffgc_strings.enter_gift_code) + '" ' +
                               (field.attributes.required ? 'required' : '') + ' class="ffgc-certificate-code" ' +
                               'data-auto-apply="' + (field.settings.auto_apply ? 'true' : 'false') + '" />' +
                               (field.settings.show_balance_check !== false ? '<button type="button" class="ffgc-check-balance-btn">' + ffgc_strings.check_balance + '</button>' : '') +
                               '</div>' +
                               '<div class="ffgc-balance-result" style="display: none;"></div>' +
                               '<div class="ffgc-redemption-result" style="display: none;"></div>' +
                               '</div>';
                    },
                    getValue: function(field) {
                        return field.$el.find('.ffgc-certificate-code').val();
                    },
                    setValue: function(field, value) {
                        field.$el.find('.ffgc-certificate-code').val(value);
                    },
                    getSettings: function(field) {
                        return {
                            show_balance_check: field.settings.show_balance_check !== false,
                            auto_apply: field.settings.auto_apply || false
                        };
                    },
                    setSettings: function(field, settings) {
                        field.settings = $.extend({}, field.settings, settings);
                    }
                });

                // Load designs for design fields
                this.loadDesignsForFields();
            }
        },

        // Load designs for design fields
        loadDesignsForFields: function() {
            $.ajax({
                url: ffgc_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'ffgc_get_designs',
                    nonce: ffgc_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        FFGC_Admin.populateDesignFields(response.data);
                    }
                }
            });
        },

        // Populate design fields with available designs
        populateDesignFields: function(designs) {
            $('.ffgc-design-field').each(function() {
                var $field = $(this);
                var $grid = $field.find('.ffgc-design-grid');
                
                if ($grid.find('.ffgc-loading-designs').length) {
                    $grid.empty();
                    
                    designs.forEach(function(design) {
                        var designHtml = '<div class="ffgc-design-option" data-design-id="' + design.id + '">';
                        if (design.image_url) {
                            designHtml += '<div class="ffgc-design-image"><img src="' + design.image_url + '" alt="' + design.title + '" /></div>';
                        }
                        designHtml += '<div class="ffgc-design-info">';
                        designHtml += '<h4>' + design.title + '</h4>';
                        if (design.min_amount || design.max_amount) {
                            designHtml += '<p class="ffgc-design-range">';
                            if (design.min_amount && design.max_amount) {
                                designHtml += 'Range: $' + design.min_amount + ' - $' + design.max_amount;
                            } else if (design.min_amount) {
                                designHtml += 'Minimum: $' + design.min_amount;
                            } else if (design.max_amount) {
                                designHtml += 'Maximum: $' + design.max_amount;
                            }
                            designHtml += '</p>';
                        }
                        designHtml += '</div></div>';
                        
                        $grid.append(designHtml);
                    });
                }
            });
        },

        // Initialize design management
        initDesignManagement: function() {
            // Handle design image upload
            $(document).on('click', '.ffgc-upload-image', function(e) {
                e.preventDefault();
                var $button = $(this);
                var $input = $button.siblings('input[type="hidden"]');
                var $preview = $button.siblings('.ffgc-image-preview');

                var frame = wp.media({
                    title: ffgc_strings.select_design_image,
                    button: {
                        text: ffgc_strings.use_this_image
                    },
                    multiple: false
                });

                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    $input.val(attachment.id);
                    $preview.html('<img src="' + attachment.sizes.medium.url + '" alt="Design Image" />');
                });

                frame.open();
            });

            // Handle design status toggle
            $(document).on('change', '.ffgc-design-status', function() {
                var $checkbox = $(this);
                var designId = $checkbox.data('design-id');
                var isActive = $checkbox.is(':checked');

                $.ajax({
                    url: ffgc_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'ffgc_toggle_design_status',
                        nonce: ffgc_ajax.nonce,
                        design_id: designId,
                        is_active: isActive
                    },
                    success: function(response) {
                        if (response.success) {
                            // Show success message
                            FFGC_Admin.showMessage(ffgc_strings.design_status_updated, 'success');
                        } else {
                            // Revert checkbox state
                            $checkbox.prop('checked', !isActive);
                            FFGC_Admin.showMessage(ffgc_strings.failed_update_design, 'error');
                        }
                    },
                    error: function() {
                        // Revert checkbox state
                        $checkbox.prop('checked', !isActive);
                        FFGC_Admin.showMessage(ffgc_strings.error_occurred, 'error');
                    }
                });
            });
        },

        // Initialize certificate management
        initCertificateManagement: function() {
            // Handle certificate status changes
            $(document).on('change', '.ffgc-certificate-status', function() {
                var $select = $(this);
                var certificateId = $select.data('certificate-id');
                var newStatus = $select.val();

                $.ajax({
                    url: ffgc_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'ffgc_update_certificate_status',
                        nonce: ffgc_ajax.nonce,
                        certificate_id: certificateId,
                        status: newStatus
                    },
                    success: function(response) {
                        if (response.success) {
                            FFGC_Admin.showMessage(ffgc_strings.certificate_status_updated, 'success');
                        } else {
                            FFGC_Admin.showMessage(ffgc_strings.failed_update_certificate, 'error');
                        }
                    },
                    error: function() {
                        FFGC_Admin.showMessage(ffgc_strings.error_occurred, 'error');
                    }
                });
            });

            // Handle bulk actions
            $(document).on('click', '.ffgc-bulk-action', function(e) {
                e.preventDefault();
                var $button = $(this);
                var action = $button.data('action');
                var selectedCertificates = $('.ffgc-certificate-checkbox:checked').map(function() {
                    return $(this).val();
                }).get();

                if (selectedCertificates.length === 0) {
                    FFGC_Admin.showMessage(ffgc_strings.select_certificates, 'warning');
                    return;
                }

                if (confirm(ffgc_strings.confirm_bulk_action.replace('%d', selectedCertificates.length))) {
                    $.ajax({
                        url: ffgc_ajax.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'ffgc_bulk_action',
                            nonce: ffgc_ajax.nonce,
                            bulk_action: action,
                            certificate_ids: selectedCertificates
                        },
                        success: function(response) {
                            if (response.success) {
                                FFGC_Admin.showMessage(ffgc_strings.bulk_action_completed, 'success');
                                location.reload();
                            } else {
                                FFGC_Admin.showMessage(ffgc_strings.failed_bulk_action, 'error');
                            }
                        },
                        error: function() {
                            FFGC_Admin.showMessage(ffgc_strings.error_occurred, 'error');
                        }
                    });
                }
            });
        },

        initApiTokenField: function() {
            $(document).on('click', '#ffgc_copy_token', function(e) {
                e.preventDefault();
                var $input = $('#ffgc_api_token');
                $input[0].select();
                document.execCommand('copy');
                FFGC_Admin.showMessage(ffgc_strings.copied, 'success');
            });

            $(document).on('click', '#ffgc_regenerate_token', function(e) {
                e.preventDefault();
                if (confirm(ffgc_strings.confirm_regenerate_token)) {
                    var token = Math.random().toString(36).substring(2) + Math.random().toString(36).substring(2);
                    $('#ffgc_api_token').val(token);
                }
            });
        },

        // Show message
        showMessage: function(message, type) {
            var $message = $('<div class="ffgc-message ffgc-message-' + type + '">' + message + '</div>');
            $('body').append($message);
            
            setTimeout(function() {
                $message.fadeOut(function() {
                    $(this).remove();
                });
            }, 3000);
        }
    };

    // Initialize admin functionality after object definition
    FFGC_Admin.init();

    // Initialize frontend functionality for admin preview
    if (typeof window.FFGC !== 'undefined') {
        window.FFGC.init();
    }
}); 