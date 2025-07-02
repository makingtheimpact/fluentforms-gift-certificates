/**
 * Frontend JavaScript for Fluent Forms Gift Certificates
 */

jQuery(document).ready(function($) {
    'use strict';

    // Initialize gift certificate functionality
    FFGC.init();

    // Global FFGC object
    window.FFGC = {
        init: function() {
            this.initDesignSelection();
            this.initRedemptionFields();
            this.initBalanceCheck();
        },

        // Initialize design selection grid
        initDesignSelection: function() {
            $(document).on('click', '.ffgc-design-option', function() {
                var $option = $(this);
                var $field = $option.closest('.ffgc-design-field');
                var $input = $field.find('input[type="hidden"]');
                var designId = $option.data('design-id');

                // Remove selected class from all options
                $field.find('.ffgc-design-option').removeClass('selected');
                
                // Add selected class to clicked option
                $option.addClass('selected');
                
                // Update hidden input value
                $input.val(designId).trigger('change');

                // Trigger custom event for form calculations
                $input.trigger('ffgc_design_selected', [designId]);
            });
        },

        // Initialize redemption fields
        initRedemptionFields: function() {
            $(document).on('input', '.ffgc-certificate-code', function() {
                var $input = $(this);
                var $field = $input.closest('.ffgc-redemption-field');
                var $result = $field.find('.ffgc-redemption-result');
                var autoApply = $input.data('auto-apply') === 'true';
                var code = $input.val().trim();

                // Clear previous results
                $result.hide().removeClass('success error');

                if (code.length >= 8 && autoApply) {
                    // Auto-validate and apply
                    FFGC.validateAndApplyCertificate($input, code);
                }
            });

            // Handle balance check button clicks
            $(document).on('click', '.ffgc-check-balance-btn', function() {
                var $btn = $(this);
                var $field = $btn.closest('.ffgc-redemption-field');
                var $input = $field.find('.ffgc-certificate-code');
                var code = $input.val().trim();

                if (!code) {
                    FFGC.showBalanceResult($field, 'error', ffgc_strings.enter_code);
                    return;
                }

                FFGC.checkBalance($field, code);
            });
        },

        // Initialize balance check functionality
        initBalanceCheck: function() {
            // Legacy balance check (for backward compatibility)
            $(document).on('click', '#ffgc_check_balance', function() {
                var code = $('#ffgc_certificate_code').val().trim();
                if (!code) {
                    FFGC.showBalanceResult($('#ffgc_balance_result'), 'error', ffgc_strings.enter_code);
                    return;
                }
                FFGC.checkBalanceLegacy(code);
            });
        },

        // Check certificate balance
        checkBalance: function($field, code) {
            var $btn = $field.find('.ffgc-check-balance-btn');
            var $result = $field.find('.ffgc-balance-result');

            // Show loading state
            $btn.prop('disabled', true).text(ffgc_strings.checking);
            $result.hide();

            $.ajax({
                url: ffgc_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'ffgc_validate_certificate',
                    nonce: ffgc_ajax.nonce,
                    code: code
                },
                success: function(response) {
                    if (response.success) {
                        FFGC.showBalanceResult($field, 'success', response.data.message);
                    } else {
                        FFGC.showBalanceResult($field, 'error', response.data);
                    }
                },
                error: function() {
                    FFGC.showBalanceResult($field, 'error', ffgc_strings.checking_error);
                },
                complete: function() {
                    $btn.prop('disabled', false).text(ffgc_strings.check_balance);
                }
            });
        },

        // Legacy balance check
        checkBalanceLegacy: function(code) {
            var $btn = $('#ffgc_check_balance');
            var $result = $('#ffgc_balance_result');

            $btn.prop('disabled', true).text(ffgc_strings.checking);
            $result.hide();

            $.ajax({
                url: ffgc_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'ffgc_validate_certificate',
                    nonce: ffgc_ajax.nonce,
                    code: code
                },
                success: function(response) {
                    if (response.success) {
                        FFGC.showBalanceResult($result, 'success', response.data.message);
                    } else {
                        FFGC.showBalanceResult($result, 'error', response.data);
                    }
                },
                error: function() {
                    FFGC.showBalanceResult($result, 'error', ffgc_strings.checking_error);
                },
                complete: function() {
                    $btn.prop('disabled', false).text(ffgc_strings.check_balance);
                }
            });
        },

        // Validate and apply certificate
        validateAndApplyCertificate: function($input, code) {
            var $field = $input.closest('.ffgc-redemption-field');
            var $result = $field.find('.ffgc-redemption-result');

            $.ajax({
                url: ffgc_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'ffgc_validate_certificate',
                    nonce: ffgc_ajax.nonce,
                    code: code
                },
                success: function(response) {
                    if (response.success) {
                        FFGC.showRedemptionResult($field, 'success', 'Certificate applied successfully! Balance: ' + response.data.balance);
                        
                        // Trigger form recalculation if available
                        if (typeof window.FluentForm !== 'undefined') {
                            $input.trigger('ffgc_certificate_applied', [response.data.balance]);
                        }
                    } else {
                        FFGC.showRedemptionResult($field, 'error', response.data);
                    }
                },
                error: function() {
                    FFGC.showRedemptionResult($field, 'error', ffgc_strings.apply_error);
                }
            });
        },

        // Show balance result
        showBalanceResult: function($element, type, message) {
            $element
                .removeClass('success error')
                .addClass(type)
                .html(message)
                .show();
        },

        // Show redemption result
        showRedemptionResult: function($field, type, message) {
            var $result = $field.find('.ffgc-redemption-result');
            $result
                .removeClass('success error')
                .addClass(type)
                .html(message)
                .show();
        },

        // Format currency
        formatCurrency: function(amount) {
            if (typeof wc_price !== 'undefined') {
                return wc_price(amount);
            }
            var symbol = (typeof ffgc_ajax !== 'undefined' && ffgc_ajax.currency_symbol) ? ffgc_ajax.currency_symbol : '$';
            return symbol + parseFloat(amount).toFixed(2);
        }
    };

    // Fluent Forms integration
    if (typeof window.FluentForm !== 'undefined') {
        // Add custom field types to Fluent Forms
        FluentForm.addFieldType('gift_certificate_design', {
            template: function(field) {
                return '<div class="ffgc-design-field" data-field-id="' + field.attributes.name + '">' +
                       '<input type="hidden" name="' + field.attributes.name + '" value="" />' +
                       '<div class="ffgc-design-grid"></div>' +
                       '</div>';
            },
            getValue: function(field) {
                return field.$el.find('input[type="hidden"]').val();
            },
            setValue: function(field, value) {
                field.$el.find('input[type="hidden"]').val(value);
                field.$el.find('.ffgc-design-option').removeClass('selected');
                field.$el.find('[data-design-id="' + value + '"]').addClass('selected');
            }
        });

        FluentForm.addFieldType('gift_certificate_redemption', {
            template: function(field) {
                return '<div class="ffgc-redemption-field" data-field-id="' + field.attributes.name + '">' +
                       '<div class="ffgc-code-input-group">' +
                       '<input type="text" name="' + field.attributes.name + '" class="ffgc-certificate-code" placeholder="' + ffgc_strings.enter_gift_code + '" />' +
                       '<button type="button" class="ffgc-check-balance-btn">' + ffgc_strings.check_balance + '</button>' +
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
            }
        });

        // Handle form calculations
        $(document).on('ffgc_certificate_applied', function(e, balance) {
            var $form = $(e.target).closest('form');
            if ($form.length && typeof $form[0].FluentForm !== 'undefined') {
                // Trigger form recalculation
                $form[0].FluentForm.recalculateTotal();
            }
        });

        $(document).on('ffgc_design_selected', function(e, designId) {
            var $form = $(e.target).closest('form');
            if ($form.length && typeof $form[0].FluentForm !== 'undefined') {
                // Trigger form recalculation when design is selected
                $form[0].FluentForm.recalculateTotal();
            }
        });
    }

    // WooCommerce integration (if available)
    if (typeof wc_price !== 'undefined') {
        // Override currency formatting to use WooCommerce
        FFGC.formatCurrency = function(amount) {
            return wc_price(amount);
        };
    }

    // Initialize frontend functionality
    initFrontendFeatures();
    
    function initFrontendFeatures() {
        // Add any frontend-specific functionality here
        console.log('Fluent Forms Gift Certificates Frontend initialized');
    }
    
    // Handle balance checker form submission
    $(document).on('submit', '#ffgc-balance-form', function(e) {
        e.preventDefault();
        
        var code = $('#ffgc-balance-code').val();
        var resultDiv = $('#ffgc-balance-result');
        var historyDiv = $('#ffgc-usage-history');
        
        if (!code) {
            resultDiv.html('<div class="ffgc-error">' + ffgc_strings.enter_code + '.</div>').show();
            return;
        }
        
        resultDiv.html('<div class="ffgc-loading">' + ffgc_strings.checking_balance + '</div>').show();
        historyDiv.hide();
        
        $.ajax({
            url: ffgc_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ffgc_check_balance',
                code: code,
                nonce: ffgc_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    var html = '<div class="ffgc-success">';
                    html += '<h4>Certificate Found!</h4>';
                    html += '<p><strong>Balance:</strong> $' + response.data.balance + '</p>';
                    html += '<p><strong>Total Value:</strong> $' + response.data.total + '</p>';
                    html += '<p><strong>Used Amount:</strong> $' + response.data.used + '</p>';
                    html += '</div>';
                    
                    resultDiv.html(html);
                    
                    // Show usage history if enabled
                    if ($('#ffgc-usage-history').length > 0) {
                        loadUsageHistory(code);
                    }
                } else {
                    resultDiv.html('<div class="ffgc-error">' + response.data + '</div>');
                }
            },
            error: function() {
                resultDiv.html('<div class="ffgc-error">' + ffgc_strings.error_occurred + '</div>');
            }
        });
    });
    
    
    // Load usage history
    function loadUsageHistory(code) {
        $.ajax({
            url: ffgc_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ffgc_get_usage_history',
                code: code,
                nonce: ffgc_ajax.nonce
            },
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    var html = '<table class="ffgc-history-table">';
                    html += '<thead><tr><th>Date</th><th>Amount Used</th><th>Form</th></tr></thead>';
                    html += '<tbody>';
                    
                    response.data.forEach(function(usage) {
                        html += '<tr>';
                        html += '<td>' + usage.date + '</td>';
                        html += '<td>$' + usage.amount + '</td>';
                        html += '<td>' + usage.form_title + '</td>';
                        html += '</tr>';
                    });
                    
                    html += '</tbody></table>';
                    $('#ffgc-history-content').html(html);
                    $('#ffgc-usage-history').show();
                }
            }
        });
    }
    
    
    // Handle form validation
    $('.ffgc-form').on('submit', function() {
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
    
    // Handle responsive design
    function handleResponsiveDesign() {
        if (window.innerWidth < 768) {
            $('.ffgc-check-balance-btn').css({
                'margin-left': '0',
                'margin-top': '10px',
                'width': '100%'
            });
            
            $('.ffgc-designs-grid').css('grid-template-columns', '1fr');
        } else {
            $('.ffgc-check-balance-btn').css({
                'margin-left': '10px',
                'margin-top': '0',
                'width': 'auto'
            });
        }
    }
    
    // Call on load and resize
    handleResponsiveDesign();
    $(window).on('resize', handleResponsiveDesign);
    
    // Handle accessibility
    $(document).on('keydown', function(e) {
        // Enter key on balance checker form
        if (e.keyCode === 13 && $('#ffgc-balance-form').length) {
            $('#ffgc-balance-form').submit();
        }
        
    });
    
    // Handle form field focus for better UX
    $('.ffgc-field-group input, .ffgc-field-group textarea, .ffgc-field-group select').on('focus', function() {
        $(this).closest('.ffgc-field-group').addClass('focused');
    }).on('blur', function() {
        $(this).closest('.ffgc-field-group').removeClass('focused');
    });
    
    // Handle loading states
    function showLoading(element) {
        element.addClass('loading');
        element.prop('disabled', true);
    }
    
    function hideLoading(element) {
        element.removeClass('loading');
        element.prop('disabled', false);
    }
    
    // Handle copy to clipboard functionality
    $(document).on('click', '.ffgc-copy-code', function() {
        var code = $(this).data('code');
        var button = $(this);
        
        if (navigator.clipboard) {
            navigator.clipboard.writeText(code).then(function() {
                button.text(ffgc_strings.copied);
                setTimeout(function() {
                    button.text(ffgc_strings.copy_code);
                }, 2000);
            });
        } else {
            // Fallback for older browsers
            var textArea = document.createElement('textarea');
            textArea.value = code;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            
            button.text(ffgc_strings.copied);
            setTimeout(function() {
                button.text(ffgc_strings.copy_code);
            }, 2000);
        }
    });
    
    // Handle design preview
    $(document).on('click', '.ffgc-design-preview', function(e) {
        e.preventDefault();
        var designId = $(this).data('design-id');
        
        if (designId) {
            // Open design preview in a modal
            var modal = $('<div class="ffgc-modal">' +
                '<div class="ffgc-modal-content">' +
                '<span class="ffgc-modal-close">&times;</span>' +
                '<div class="ffgc-modal-body"></div>' +
                '</div>' +
                '</div>');
            
            $('body').append(modal);
            
            // Load design preview
            $.ajax({
                url: ffgc_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'ffgc_preview_design',
                    design_id: designId,
                    nonce: ffgc_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        modal.find('.ffgc-modal-body').html(response.data);
                        modal.show();
                    }
                }
            });
        }
    });
    
    // Handle modal close
    $(document).on('click', '.ffgc-modal-close, .ffgc-modal', function(e) {
        if (e.target === this) {
            $(this).remove();
        }
    });
    
    // Handle keyboard navigation for modals
    $(document).on('keydown', function(e) {
        if (e.keyCode === 27 && $('.ffgc-modal').length) {
            $('.ffgc-modal').remove();
        }
    });
    
    // Add CSS for focused state
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .ffgc-field-group.focused label {
                color: #0073aa;
            }
            .ffgc-field-group.focused input,
            .ffgc-field-group.focused textarea,
            .ffgc-field-group.focused select {
                border-color: #0073aa;
                box-shadow: 0 0 0 3px rgba(0, 115, 170, 0.1);
            }
            .ffgc-modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 9999;
                display: none;
            }
            .ffgc-modal-content {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: white;
                padding: 20px;
                border-radius: 8px;
                max-width: 90%;
                max-height: 90%;
                overflow: auto;
            }
            .ffgc-modal-close {
                position: absolute;
                top: 10px;
                right: 15px;
                font-size: 24px;
                cursor: pointer;
                color: #666;
            }
            .ffgc-modal-close:hover {
                color: #333;
            }
        `)
        .appendTo('head');
    
}); 