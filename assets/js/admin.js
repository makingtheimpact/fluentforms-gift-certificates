/**
 * Admin JavaScript for Fluent Forms Gift Certificates
 */

jQuery(document).ready(function($) {
    
    // Initialize admin functionality
    initAdminFeatures();
    
    function initAdminFeatures() {
        // Add any admin-specific functionality here
        console.log('Fluent Forms Gift Certificates Admin initialized');
    }
    
    // Handle resend email functionality (if not already handled in meta box)
    $(document).on('click', '#ffgc-resend-email', function() {
        var button = $(this);
        var spinner = button.next('.spinner');
        var certificateId = button.data('certificate-id') || getCertificateIdFromUrl();
        
        if (!certificateId) {
            alert('Certificate ID not found');
            return;
        }
        
        button.prop('disabled', true);
        spinner.css('visibility', 'visible');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ffgc_resend_email',
                certificate_id: certificateId,
                nonce: ffgc_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('Email sent successfully!');
                } else {
                    alert('Failed to send email: ' + (response.data || 'Unknown error'));
                }
            },
            error: function() {
                alert('An error occurred while sending the email.');
            },
            complete: function() {
                button.prop('disabled', false);
                spinner.css('visibility', 'hidden');
            }
        });
    });
    
    // Helper function to get certificate ID from URL
    function getCertificateIdFromUrl() {
        var urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('post');
    }
    
    // Handle design preview functionality
    $(document).on('click', '.ffgc-design-preview', function(e) {
        e.preventDefault();
        var designId = $(this).data('design-id');
        
        if (designId) {
            // Open design preview in a modal or new window
            window.open(ajaxurl + '?action=ffgc_preview_design&design_id=' + designId + '&nonce=' + ffgc_ajax.nonce, 'design_preview', 'width=800,height=600');
        }
    });
    
    // Handle bulk actions for certificates
    $(document).on('change', '#bulk-action-selector-top, #bulk-action-selector-bottom', function() {
        var selectedAction = $(this).val();
        var bulkActionButton = $('.button-primary[value="Apply"]');
        
        if (selectedAction === 'resend_email') {
            bulkActionButton.text('Resend Emails');
        } else {
            bulkActionButton.text('Apply');
        }
    });
    
    // Handle certificate status changes
    $(document).on('change', 'select[name="_certificate_status"]', function() {
        var status = $(this).val();
        var row = $(this).closest('tr');
        
        // Update visual status indicator
        row.find('.ffgc-status').removeClass('unused used expired').addClass(status);
        row.find('.ffgc-status').text(status.charAt(0).toUpperCase() + status.slice(1));
    });
    
    // Handle amount validation
    $(document).on('blur', 'input[name="_certificate_amount"]', function() {
        var amount = parseFloat($(this).val());
        var minAmount = parseFloat($(this).data('min-amount') || 0);
        var maxAmount = parseFloat($(this).data('max-amount') || 999999);
        
        if (amount < minAmount) {
            alert('Amount cannot be less than $' + minAmount.toFixed(2));
            $(this).val(minAmount.toFixed(2));
        } else if (amount > maxAmount) {
            alert('Amount cannot be more than $' + maxAmount.toFixed(2));
            $(this).val(maxAmount.toFixed(2));
        }
    });
    
    // Handle design amount range validation
    $(document).on('blur', 'input[name="_min_amount"], input[name="_max_amount"]', function() {
        var minAmount = parseFloat($('input[name="_min_amount"]').val());
        var maxAmount = parseFloat($('input[name="_max_amount"]').val());
        
        if (minAmount && maxAmount && minAmount >= maxAmount) {
            alert('Minimum amount must be less than maximum amount');
            $(this).focus();
        }
    });
    
    // Handle email template preview
    $(document).on('click', '#ffgc-preview-template', function() {
        var template = $('#_email_template').val();
        var designId = $('#_design_id').val();
        
        if (!template) {
            alert('Please enter an email template first');
            return;
        }
        
        // Open template preview in a new window
        var previewWindow = window.open('', 'template_preview', 'width=800,height=600');
        previewWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Email Template Preview</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    .preview-header { background: #f1f1f1; padding: 10px; margin-bottom: 20px; }
                    .preview-content { border: 1px solid #ddd; padding: 20px; }
                </style>
            </head>
            <body>
                <div class="preview-header">
                    <h2>Email Template Preview</h2>
                    <p>This is a preview of how the email will look. Placeholders will be replaced with actual data.</p>
                </div>
                <div class="preview-content">
                    ${template.replace(/\{([^}]+)\}/g, '<span style="background: #ffeb3b; padding: 2px 4px; border-radius: 3px;">{$1}</span>')}
                </div>
            </body>
            </html>
        `);
        previewWindow.document.close();
    });
    
    // Handle form integration settings
    $(document).on('change', 'input[name="ffgc_forms_enabled[]"]', function() {
        var checkedForms = $('input[name="ffgc_forms_enabled[]"]:checked');
        
        if (checkedForms.length > 0) {
            $('#ffgc-forms-notice').remove();
            $('.ffgc-forms-enabled').show();
        } else {
            if ($('#ffgc-forms-notice').length === 0) {
                $('<div id="ffgc-forms-notice" class="notice notice-warning"><p>No forms are currently enabled for gift certificate functionality.</p></div>').insertAfter('.ffgc-form-settings');
            }
            $('.ffgc-forms-enabled').hide();
        }
    });
    
    // Handle certificate code generation
    $(document).on('click', '#ffgc-generate-code', function() {
        var codeField = $('#_certificate_code');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ffgc_generate_code',
                nonce: ffgc_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    codeField.val(response.data);
                } else {
                    alert('Failed to generate code: ' + (response.data || 'Unknown error'));
                }
            },
            error: function() {
                alert('An error occurred while generating the code.');
            }
        });
    });
    
    // Handle usage history display
    $(document).on('click', '.ffgc-view-history', function() {
        var certificateId = $(this).data('certificate-id');
        var modal = $('#ffgc-history-modal');
        
        if (modal.length === 0) {
            // Create modal if it doesn't exist
            $('body').append(`
                <div id="ffgc-history-modal" class="ffgc-modal" style="display: none;">
                    <div class="ffgc-modal-content">
                        <span class="ffgc-modal-close">&times;</span>
                        <h3>Usage History</h3>
                        <div id="ffgc-history-content"></div>
                    </div>
                </div>
            `);
        }
        
        // Load usage history
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ffgc_get_certificate_history',
                certificate_id: certificateId,
                nonce: ffgc_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#ffgc-history-content').html(response.data);
                    $('#ffgc-history-modal').show();
                } else {
                    alert('Failed to load usage history: ' + (response.data || 'Unknown error'));
                }
            },
            error: function() {
                alert('An error occurred while loading usage history.');
            }
        });
    });
    
    // Handle modal close
    $(document).on('click', '.ffgc-modal-close, .ffgc-modal', function(e) {
        if (e.target === this) {
            $(this).hide();
        }
    });
    
    // Handle keyboard shortcuts
    $(document).on('keydown', function(e) {
        // Ctrl/Cmd + Enter to save
        if ((e.ctrlKey || e.metaKey) && e.keyCode === 13) {
            $('#publish').click();
        }
        
        // Escape to close modals
        if (e.keyCode === 27) {
            $('.ffgc-modal').hide();
        }
    });
    
    // Initialize tooltips
    $('[data-tooltip]').each(function() {
        $(this).attr('title', $(this).data('tooltip'));
    });
    
    // Handle responsive design for admin
    function handleResponsiveAdmin() {
        if (window.innerWidth < 768) {
            $('.ffgc-stats-grid').css('grid-template-columns', '1fr');
            $('.ffgc-action-buttons').css('flex-direction', 'column');
        } else {
            $('.ffgc-stats-grid').css('grid-template-columns', 'repeat(auto-fit, minmax(250px, 1fr))');
            $('.ffgc-action-buttons').css('flex-direction', 'row');
        }
    }
    
    // Call on load and resize
    handleResponsiveAdmin();
    $(window).on('resize', handleResponsiveAdmin);
    
    // Handle form validation
    $('form').on('submit', function() {
        var requiredFields = $(this).find('[required]');
        var isValid = true;
        
        requiredFields.each(function() {
            if (!$(this).val()) {
                $(this).addClass('error');
                isValid = false;
            } else {
                $(this).removeClass('error');
            }
        });
        
        if (!isValid) {
            alert('Please fill in all required fields.');
            return false;
        }
        
        return true;
    });
    
    // Remove error class on input
    $(document).on('input', '.error', function() {
        $(this).removeClass('error');
    });
    
}); 