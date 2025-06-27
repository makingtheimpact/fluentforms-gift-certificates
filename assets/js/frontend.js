/**
 * Frontend JavaScript for Fluent Forms Gift Certificates
 */

jQuery(document).ready(function($) {
    
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
            resultDiv.html('<div class="ffgc-error">Please enter a gift certificate code.</div>').show();
            return;
        }
        
        resultDiv.html('<div class="ffgc-loading">Checking balance...</div>').show();
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
                resultDiv.html('<div class="ffgc-error">An error occurred. Please try again.</div>');
            }
        });
    });
    
    // Handle purchase form submission
    $(document).on('submit', '#ffgc-purchase-form', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        var resultDiv = $('#ffgc-purchase-result');
        
        resultDiv.html('<div class="ffgc-loading">Processing...</div>').show();
        
        $.ajax({
            url: ffgc_ajax.ajax_url,
            type: 'POST',
            data: formData + '&action=ffgc_purchase_certificate&nonce=' + ffgc_ajax.nonce,
            success: function(response) {
                if (response.success) {
                    resultDiv.html('<div class="ffgc-success">' + response.data + '</div>');
                    $('#ffgc-purchase-form')[0].reset();
                } else {
                    resultDiv.html('<div class="ffgc-error">' + response.data + '</div>');
                }
            },
            error: function() {
                resultDiv.html('<div class="ffgc-error">An error occurred. Please try again.</div>');
            }
        });
    });
    
    // Handle check balance button click
    $(document).on('click', '#ffgc_check_balance', function() {
        var code = $('#ffgc_certificate_code').val();
        var resultDiv = $('#ffgc_balance_result');
        
        if (!code) {
            resultDiv.html('<div class="ffgc-error">Please enter a gift certificate code.</div>').show();
            return;
        }
        
        resultDiv.html('<div class="ffgc-loading">Checking balance...</div>').show();
        
        $.ajax({
            url: ffgc_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ffgc_validate_certificate',
                code: code,
                nonce: ffgc_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    var html = '<div class="ffgc-success">';
                    html += '<h4>Certificate Valid!</h4>';
                    html += '<p><strong>Balance:</strong> $' + response.data.balance + '</p>';
                    html += '<p><strong>Total Value:</strong> $' + response.data.total + '</p>';
                    html += '<p><strong>Used Amount:</strong> $' + response.data.used + '</p>';
                    html += '<p><strong>Expires:</strong> ' + response.data.expiry_date + '</p>';
                    html += '</div>';
                    
                    resultDiv.html(html);
                } else {
                    resultDiv.html('<div class="ffgc-error">' + response.data + '</div>');
                }
            },
            error: function() {
                resultDiv.html('<div class="ffgc-error">An error occurred. Please try again.</div>');
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
    
    // Handle design selection in purchase form
    $(document).on('change', '#ffgc-design-id', function() {
        var designId = $(this).val();
        var amountField = $('#ffgc-purchase-amount');
        
        if (designId) {
            // Get design details and update amount limits
            $.ajax({
                url: ffgc_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'ffgc_get_design_details',
                    design_id: designId,
                    nonce: ffgc_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        amountField.attr('min', response.data.min_amount);
                        amountField.attr('max', response.data.max_amount);
                        amountField.next('small').text('Minimum: $' + response.data.min_amount + ', Maximum: $' + response.data.max_amount);
                    }
                }
            });
        }
    });
    
    // Handle amount validation in purchase form
    $(document).on('blur', '#ffgc-purchase-amount', function() {
        var amount = parseFloat($(this).val());
        var minAmount = parseFloat($(this).attr('min') || 0);
        var maxAmount = parseFloat($(this).attr('max') || 999999);
        
        if (amount < minAmount) {
            alert('Amount cannot be less than $' + minAmount.toFixed(2));
            $(this).val(minAmount.toFixed(2));
        } else if (amount > maxAmount) {
            alert('Amount cannot be more than $' + maxAmount.toFixed(2));
            $(this).val(maxAmount.toFixed(2));
        }
    });
    
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
        
        // Enter key on purchase form
        if (e.keyCode === 13 && $('#ffgc-purchase-form').length) {
            $('#ffgc-purchase-form').submit();
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
                button.text('Copied!');
                setTimeout(function() {
                    button.text('Copy Code');
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
            
            button.text('Copied!');
            setTimeout(function() {
                button.text('Copy Code');
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