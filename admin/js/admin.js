/**
 * Click to Chat - Admin JavaScript
 *
 * This file contains all the JavaScript for the admin-facing aspects of the plugin.
 */

(function($) {
    'use strict';

    /**
     * Initialize the admin functionality
     */
    function initAdmin() {
        // Initialize tabs
        initTabs();

        // Initialize color pickers
        initColorPickers();

        // Initialize WhatsApp number management
        initNumbersManagement();

        // Initialize message template previews
        initMessageTemplates();

        // Initialize shortcode generator
        initShortcodeGenerator();

        // Initialize select2 for multiselect dropdowns
        initSelect2();

        // Initialize position field toggles
        initPositionToggles();

        // Initialize catalog mode and advanced options
        initAdvancedOptions();
    }

    /**
     * Initialize tab navigation
     */
    function initTabs() {
        // Settings page tab navigation (button style)
        $('.ctc-settings-nav-item').on('click', function(e) {
            e.preventDefault();

            // Get the target tab
            const targetTab = $(this).data('tab');

            // Update active tab
            $('.ctc-settings-nav-item').removeClass('active');
            $(this).addClass('active');

            // Show the target tab content
            $('.ctc-settings-panel').removeClass('active').hide();
            $('#ctc-settings-' + targetTab).addClass('active').show();

            // Update the tab parameter in the URL
            const url = new URL(window.location);
            url.searchParams.set('tab', targetTab);
            window.history.pushState({}, '', url);
        });

        // New settings page tabs (alternate style)
        $('.ctc-tab-nav-item').on('click', function(e) {
            e.preventDefault();

            // Get the target tab
            const targetTab = $(this).data('tab');

            // Update active tab
            $('.ctc-tab-nav-item').removeClass('active');
            $(this).addClass('active');

            // Show the target tab content
            $('.ctc-settings-tab-content').removeClass('active').hide();
            $('#' + targetTab).addClass('active').show();

            // Update the tab parameter in the URL
            const url = new URL(window.location);
            url.searchParams.set('tab', targetTab);
            window.history.pushState({}, '', url);
        });

        // Legacy tab support (for backwards compatibility)
        $('.ctc-admin-tabs .nav-tab').on('click', function(e) {
            e.preventDefault();

            // Get the target tab
            const targetTab = $(this).data('tab');

            // Update active tab
            $('.ctc-admin-tabs .nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');

            // Show the target tab content
            $('.ctc-admin-tab-content').hide();
            $('#' + targetTab).show();

            // Update the tab parameter in the URL
            const url = new URL(window.location);
            url.searchParams.set('tab', targetTab);
            window.history.pushState({}, '', url);
        });

        // Show the active tab on page load
        const urlParams = new URLSearchParams(window.location.search);
        const activeTab = urlParams.get('tab');

        if (activeTab) {
            // Try new tabs first
            if ($('.ctc-tab-nav-item[data-tab="' + activeTab + '"]').length) {
                $('.ctc-tab-nav-item[data-tab="' + activeTab + '"]').trigger('click');
            }
            // Fall back to legacy tabs
            else if ($('.ctc-admin-tabs .nav-tab[data-tab="' + activeTab + '"]').length) {
                $('.ctc-admin-tabs .nav-tab[data-tab="' + activeTab + '"]').trigger('click');
            }
        } else {
            // Default to the first tab
            if ($('.ctc-tab-nav-item:first').length) {
                $('.ctc-tab-nav-item:first').trigger('click');
            } else if ($('.ctc-admin-tabs .nav-tab:first').length) {
                $('.ctc-admin-tabs .nav-tab:first').trigger('click');
            }
        }
    }

    /**
     * Initialize color pickers
     */
    function initColorPickers() {
        $('.ctc-color-field').wpColorPicker();
    }
    
    /**
     * Initialize WhatsApp number management
     */
    function initNumbersManagement() {
        // Handle expand/collapse functionality - updated for new classes
        $(document).on('click', '.ctc-number-toggle', function() {
            const $card = $(this).closest('.ctc-number-item, .ctc-number-card');
            const $content = $card.find('.ctc-number-content, .ctc-number-body');
            const $icon = $(this).find('.dashicons');

            $content.slideToggle(200, function() {
                // Update icon based on visibility after animation completes
                if ($content.is(':visible')) {
                    $icon.removeClass('dashicons-arrow-down-alt2').addClass('dashicons-arrow-up-alt2');
                } else {
                    $icon.removeClass('dashicons-arrow-up-alt2').addClass('dashicons-arrow-down-alt2');
                }
            });
        });

        // Handle default checkbox - only one can be selected
        $(document).on('change', '.ctc-default-checkbox', function() {
            if ($(this).is(':checked')) {
                // Uncheck all other default checkboxes
                $('.ctc-default-checkbox').not(this).prop('checked', false);
            }
        });
        
        // Handle number deletion - updated for new classes
        $(document).on('click', '.ctc-number-delete', function() {
            const $item = $(this).closest('.ctc-number-item, .ctc-number-card');
            const numberName = $item.find('.ctc-number-title').text().trim();
            const numberId = $item.data('id');
            
            if (confirm(ctc_admin.delete_number_confirm)) {
                // Add the ID to a hidden input to track deleted numbers
                if (!$('#ctc-deleted-numbers').length) {
                    $('form').append('<input type="hidden" id="ctc-deleted-numbers" name="ctc_deleted_numbers" value="" />');
                }
                
                let deletedNumbers = $('#ctc-deleted-numbers').val();
                deletedNumbers = deletedNumbers ? deletedNumbers.split(',') : [];
                deletedNumbers.push(numberId);
                $('#ctc-deleted-numbers').val(deletedNumbers.join(','));
                
                // Remove the item from the UI
                $item.slideUp(300, function() {
                    $(this).remove();
                });
            }
        });
        
        // Add new WhatsApp number
        $('#ctc-add-number').on('click', function() {
            // Get the template
            const template = wp.template('ctc-number-template');
            
            // Get the next number ID and index
            const nextId = parseInt($('#ctc-next-number-id').val());
            const nextIndex = $('.ctc-number-item, .ctc-number-card').length;
            
            // Render the template
            const html = template({
                id: nextId,
                index: nextIndex
            });
            
            // Append to container
            $('#ctc-numbers-container').append(html);
            
            // Initialize select2 for the new selects
            initSelect2();
            
            // Update the next ID
            $('#ctc-next-number-id').val(nextId + 1);
            
            // Scroll to the new number - updated for new classes
            $('html, body').animate({
                scrollTop: $('.ctc-number-item:last, .ctc-number-card:last').offset().top - 50
            }, 500);
            
            return false;
        });
        
        // Validate number name on change to prevent duplicates
        $(document).on('change', '.ctc-number-name', function() {
            const $input = $(this);
            const name = $input.val().trim();
            
            if (name === '') {
                return;
            }
            
            // Check for duplicate names
            let isDuplicate = false;
            $('.ctc-number-name').not($input).each(function() {
                if ($(this).val().trim() === name) {
                    isDuplicate = true;
                    return false; // Break the loop
                }
            });
            
            if (isDuplicate) {
                alert(ctc_admin.duplicate_name_error);
                $input.val('').focus();
            }
        });
        
        // Ensure the first number is expanded by default - updated for new classes
        $('.ctc-number-item:first .ctc-number-content, .ctc-number-card:first .ctc-number-body').show();
        
        // Initialize select2 for existing selects
        initSelect2();
        
        // Helper function to update indices for all numbers - updated for new classes
        function updateNumberIndices() {
            $('.ctc-number-item, .ctc-number-card').each(function(index) {
                const id = $(this).data('id');
                
                // Update all name attributes in this number item
                $(this).find('[name^="ctc_numbers["]').each(function() {
                    const name = $(this).attr('name');
                    const newName = name.replace(/ctc_numbers\[\d+\]/, 'ctc_numbers[' + index + ']');
                    $(this).attr('name', newName);
                });
            });
        }
    }

    /**
     * Initialize Select2 for various select inputs
     */
    function initSelect2() {
        // Page select
        $('.ctc-page-select').select2({
            ajax: {
                url: ctc_admin.ajaxurl,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        action: 'ctc_search_pages',
                        term: params.term || '',
                        nonce: ctc_admin.nonce
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.success ? data.data.results : []
                    };
                },
                cache: true
            },
            minimumInputLength: 0,
            placeholder: ctc_admin.select_pages_text || 'Select pages...',
            allowClear: true
        });
        
        // Post select
        $('.ctc-post-select').select2({
            ajax: {
                url: ctc_admin.ajaxurl,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        action: 'ctc_search_posts',
                        term: params.term || '',
                        nonce: ctc_admin.nonce
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.success ? data.data.results : []
                    };
                },
                cache: true
            },
            minimumInputLength: 0,
            placeholder: 'Select posts...',
            allowClear: true
        });

        // Category select (for exclusions, NOT for advanced options)
        $('.ctc-category-select').select2({
            ajax: {
                url: ctc_admin.ajaxurl,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        action: 'ctc_search_categories',
                        term: params.term || '',
                        nonce: ctc_admin.nonce
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.success ? data.data.results : []
                    };
                },
                cache: true
            },
            minimumInputLength: 0,
            placeholder: ctc_admin.select_categories_text || 'Select categories...',
            allowClear: true
        });

        // Tag select
        $('.ctc-tag-select').select2({
            ajax: {
                url: ctc_admin.ajaxurl,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        action: 'ctc_search_tags',
                        term: params.term || '',
                        nonce: ctc_admin.nonce
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.success ? data.data.results : []
                    };
                },
                cache: true
            },
            minimumInputLength: 0,
            placeholder: 'Select tags...',
            allowClear: true
        });

        // Product select
        $('.ctc-product-select').select2({
            ajax: {
                url: ctc_admin.ajaxurl,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        action: 'ctc_search_products',
                        term: params.term || '',
                        nonce: ctc_admin.nonce
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.success ? data.data.results : []
                    };
                },
                cache: true
            },
            minimumInputLength: 0,
            placeholder: ctc_admin.select_products_text || 'Select products...',
            allowClear: true
        });
    }

    /**
     * Initialize message template previews
     */
    function initMessageTemplates() {
        // Preview template - updated to work with both old and new class names
        $('.ctc-template-preview-button, .ctc-preview-btn').on('click', function() {
            const $button = $(this);
            const $container = $button.closest('.ctc-template-item, .ctc-template-card');
            const templateType = $button.data('template-type'); // Get from button, not container
            const templateContent = $container.find('.ctc-template-textarea').val();
            const $previewContainer = $container.find('.ctc-template-preview');


            // Show loading state
            $button.prop('disabled', true).text(ctc_admin.loading_text || 'Loading...');
            
            // Send AJAX request
            $.ajax({
                url: ctc_admin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'ctc_preview_message',
                    template_type: templateType,
                    template_content: templateContent,
                    nonce: ctc_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Create a text node to properly display the preview without HTML interpretation
                        var previewText = response.data.preview;
                        // Convert line breaks to HTML for display
                        var formattedPreview = previewText.split('\n').map(function(line) {
                            return $('<div>').text(line).html();
                        }).join('<br>');
                        $previewContainer.html(formattedPreview);
                        $previewContainer.removeClass('ctc-hidden').addClass('ctc-visible');
                    } else {
                        $previewContainer.html('<p class="error">' + response.data.message + '</p>');
                        $previewContainer.removeClass('ctc-hidden').addClass('ctc-visible');
                    }
                },
                error: function(xhr, status, error) {
                    let errorMessage = 'Error generating preview.';
                    if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                        errorMessage = xhr.responseJSON.data.message;
                    } else if (error) {
                        errorMessage = 'Error: ' + error;
                    }
                    $previewContainer.html('<p class="error">' + errorMessage + '</p>');
                    $previewContainer.removeClass('ctc-hidden').addClass('ctc-visible');
                },
                complete: function() {
                    $button.prop('disabled', false).text(ctc_admin.preview_text || 'Preview');
                }
            });
            
            return false;
        });
        
        // Insert placeholder into textarea - updated to work with both old and new class names
        $(document).on('click', '.ctc-placeholder-tag', function() {
            const placeholder = $(this).text();
            const $textarea = $(this).closest('.ctc-template-item, .ctc-template-card').find('.ctc-template-textarea');
            
            insertAtCursor($textarea[0], placeholder);
            
            return false;
        });
    }

    /**
     * Initialize shortcode generator
     */
    function initShortcodeGenerator() {
        // New improved shortcode builder

        // Handle button type selection
        $('input[name="button_type"]').on('change', function() {
            const type = $(this).val();
            if (type === 'product') {
                $('.ctc-product-options').removeClass('ctc-hidden').addClass('ctc-visible');
            } else {
                $('.ctc-product-options').removeClass('ctc-visible').addClass('ctc-hidden');
            }
            generateImprovedShortcode();
            updateImprovedPreview();
        });

        // Handle product source selection
        $('input[name="product_source"]').on('change', function() {
            if ($(this).val() === 'specific') {
                $('.ctc-product-id-field').removeClass('ctc-hidden').addClass('ctc-visible');
            } else {
                $('.ctc-product-id-field').removeClass('ctc-visible').addClass('ctc-hidden');
            }
            generateImprovedShortcode();
        });

        // Handle all parameter changes
        $('.ctc-shortcode-param').on('change input', function() {
            generateImprovedShortcode();
            updateImprovedPreview();
        });

        // Handle copy button
        $('#ctc-copy-shortcode').on('click', function() {
            const shortcode = $('#ctc-generated-shortcode').text();
            copyToClipboard(shortcode);

            // Show success message
            $('.ctc-copy-success').removeClass('ctc-hidden').addClass('ctc-visible');
            setTimeout(function() {
                $('.ctc-copy-success').removeClass('ctc-visible').addClass('ctc-hidden');
            }, 2000);

            // Change button text temporarily
            const $btnText = $(this).find('.ctc-copy-text');
            const originalText = $btnText.text();
            $btnText.text('Copied!');
            setTimeout(() => {
                $btnText.text(originalText);
            }, 2000);

            return false;
        });

        // Handle advanced section collapse
        $('.ctc-collapsible').on('click', function() {
            $(this).toggleClass('active');
            $('.ctc-advanced-content').slideToggle();
        });

        // Initialize on load
        generateImprovedShortcode();
        updateImprovedPreview();

        // Legacy support - keep old functionality
        $('.ctc-shortcode-form select, .ctc-shortcode-form input').on('change', function() {
            generateShortcode();
        });

        $(document).on('click', '.ctc-copy-shortcode', function() {
            const shortcode = $('.ctc-shortcode-code').text();
            copyToClipboard(shortcode);
            return false;
        });
    }

    /**
     * Generate improved shortcode
     */
    function generateImprovedShortcode() {
        let shortcode = '[ctc_button';

        // Get all parameters
        const type = $('input[name="button_type"]:checked').val() || 'product';
        const productSource = $('input[name="product_source"]:checked').val();
        const productId = $('#ctc_product_id').val();
        const text = $('#ctc_button_text').val();
        const bgColor = $('#ctc_bg_color').val();
        const textColor = $('#ctc_text_color').val();
        const showIcon = $('input[data-param="icon"]').is(':checked');
        const size = $('select[data-param="size"]').val();
        const align = $('select[data-param="align"]').val();
        const showNumber = $('select[data-param="show_number"]').val();
        const message = $('textarea[data-param="message"]').val();
        const cssClass = $('input[data-param="css_class"]').val();

        // Add type
        shortcode += ' type="' + type + '"';

        // Product specific
        if (type === 'product') {
            if (productSource === 'current') {
                shortcode += ' current="yes"';
            } else {
                shortcode += ' current="no"';
                if (productId) {
                    shortcode += ' product_id="' + productId + '"';
                }
            }
        }

        // Appearance
        if (text) {
            shortcode += ' text="' + text.replace(/"/g, '&quot;') + '"';
        }

        if (bgColor && bgColor !== '#25D366') {
            shortcode += ' bg_color="' + bgColor + '"';
        }

        if (textColor && textColor !== '#ffffff') {
            shortcode += ' text_color="' + textColor + '"';
        }

        shortcode += ' icon="' + (showIcon ? 'yes' : 'no') + '"';

        if (size && size !== 'normal') {
            shortcode += ' size="' + size + '"';
        }

        if (align && align !== 'center') {
            shortcode += ' align="' + align + '"';
        }

        // Advanced
        if (showNumber) {
            shortcode += ' show_number="' + showNumber + '"';
        }

        if (message) {
            shortcode += ' message="' + message.replace(/"/g, '&quot;') + '"';
        }

        if (cssClass) {
            shortcode += ' css_class="' + cssClass + '"';
        }

        shortcode += ']';

        $('#ctc-generated-shortcode').text(shortcode);
    }

    /**
     * Update improved preview
     */
    function updateImprovedPreview() {
        const text = $('#ctc_button_text').val() || ctc_admin.default_button_text || 'Order via WhatsApp';
        const bgColor = $('#ctc_bg_color').val() || '#25D366';
        const textColor = $('#ctc_text_color').val() || '#ffffff';
        const showIcon = $('input[data-param="icon"]').is(':checked');
        const size = $('select[data-param="size"]').val() || 'normal';
        const align = $('select[data-param="align"]').val() || 'center';

        let previewHtml = '<div class="ctc-align-' + align + '">';
        previewHtml += '<a href="#" class="ctc-whatsapp-button ctc-button-size-' + size + '" ';
        previewHtml += 'style="background-color: ' + bgColor + '; color: ' + textColor + ';" onclick="return false;">';

        if (showIcon) {
            previewHtml += '<span class="ctc-whatsapp-icon">';
            previewHtml += '<svg viewBox="0 0 24 24" width="24" height="24">';
            previewHtml += '<path fill="currentColor" d="M17.498 14.382c-.301-.15-1.767-.867-2.04-.966-.273-.101-.473-.15-.673.15-.197.295-.771.964-.944 1.162-.175.195-.349.21-.646.075-.3-.15-1.263-.465-2.403-1.485-.888-.795-1.484-1.77-1.66-2.07-.174-.3-.019-.465.13-.615.136-.135.301-.345.451-.523.146-.181.194-.301.297-.496.1-.21.049-.375-.025-.524-.075-.15-.672-1.62-.922-2.206-.24-.584-.487-.51-.672-.51-.172-.015-.371-.015-.571-.015-.2 0-.523.074-.797.359-.273.3-1.045 1.02-1.045 2.475s1.07 2.865 1.219 3.075c.149.195 2.105 3.195 5.1 4.485.714.3 1.27.48 1.704.629.714.227 1.365.195 1.88.121.574-.091 1.767-.721 2.016-1.426.255-.705.255-1.29.18-1.425-.074-.135-.27-.21-.57-.345m-5.446 7.443h-.016c-1.77 0-3.524-.48-5.055-1.38l-.36-.214-3.75.975 1.005-3.645-.239-.375c-.99-1.576-1.516-3.391-1.516-5.26 0-5.445 4.455-9.885 9.942-9.885 2.654 0 5.145 1.035 7.021 2.91 1.875 1.859 2.909 4.35 2.909 6.99-.004 5.444-4.46 9.885-9.935 9.885M20.52 3.449C18.24 1.245 15.24 0 12.045 0 5.463 0 .104 5.334.101 11.893c0 2.096.549 4.14 1.595 5.945L0 24l6.335-1.652c1.746.943 3.71 1.444 5.71 1.447h.006c6.585 0 11.946-5.336 11.949-11.896 0-3.176-1.24-6.165-3.495-8.411"/>';
            previewHtml += '</svg>';
            previewHtml += '</span>';
        }

        previewHtml += '<span class="ctc-button-text">' + escapeHtml(text) + '</span>';
        previewHtml += '</a>';
        previewHtml += '</div>';

        $('#ctc-button-preview').html(previewHtml);
    }

    /**
     * Escape HTML
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Generate shortcode based on form values (Legacy)
     */
    function generateShortcode() {
        const $form = $('.ctc-shortcode-form');
        const productId = $form.find('#shortcode_product_id').val();
        const current = $form.find('#shortcode_current').is(':checked') ? 'yes' : 'no';
        const text = $form.find('#shortcode_text').val();
        const bgColor = $form.find('#shortcode_bg_color').val();
        const textColor = $form.find('#shortcode_text_color').val();
        const icon = $form.find('#shortcode_icon').is(':checked') ? 'yes' : 'no';
        const showNumber = $form.find('#shortcode_show_number').val();
        const size = $form.find('#shortcode_size').val();
        const align = $form.find('#shortcode_align').val();
        const type = $form.find('#shortcode_type').val();
        const cssClass = $form.find('#shortcode_css_class').val();
        
        // Build the shortcode
        let shortcode = '[whatsapp_button';
        
        if (productId) {
            shortcode += ' product_id="' + productId + '"';
        }
        
        shortcode += ' current="' + current + '"';
        
        if (text) {
            shortcode += ' text="' + text + '"';
        }
        
        if (bgColor) {
            shortcode += ' bg_color="' + bgColor + '"';
        }
        
        if (textColor) {
            shortcode += ' text_color="' + textColor + '"';
        }
        
        shortcode += ' icon="' + icon + '"';
        
        if (showNumber) {
            shortcode += ' show_number="' + showNumber + '"';
        }
        
        shortcode += ' size="' + size + '"';
        shortcode += ' align="' + align + '"';
        shortcode += ' type="' + type + '"';
        
        if (cssClass) {
            shortcode += ' css_class="' + cssClass + '"';
        }
        
        shortcode += ']';
        
        // Update the shortcode display
        $('.ctc-shortcode-code').text(shortcode);
        
        // Update the preview
        updateShortcodePreview();
    }

    /**
     * Update the shortcode preview
     */
    function updateShortcodePreview() {
        const $form = $('.ctc-shortcode-form');
        const text = $form.find('#shortcode_text').val() || ctc_admin.default_button_text;
        const bgColor = $form.find('#shortcode_bg_color').val() || '#25D366';
        const textColor = $form.find('#shortcode_text_color').val() || '#ffffff';
        const icon = $form.find('#shortcode_icon').is(':checked');
        const size = $form.find('#shortcode_size').val();
        const align = $form.find('#shortcode_align').val();
        
        // Create button HTML
        let buttonHtml = '<div class="ctc-shortcode-container ctc-align-' + align + '">';
        buttonHtml += '<a href="#" class="ctc-whatsapp-button ctc-button-size-' + size;
        
        if (icon) {
            buttonHtml += ' ctc-button-with-icon';
        }
        
        buttonHtml += '" style="background-color: ' + bgColor + '; color: ' + textColor + ';">';
        
        if (icon) {
            buttonHtml += '<span class="ctc-whatsapp-icon">';
            buttonHtml += '<svg viewBox="0 0 24 24" width="24" height="24">';
            buttonHtml += '<path fill="currentColor" d="M17.498 14.382c-.301-.15-1.767-.867-2.04-.966-.273-.101-.473-.15-.673.15-.197.295-.771.964-.944 1.162-.175.195-.349.21-.646.075-.3-.15-1.263-.465-2.403-1.485-.888-.795-1.484-1.77-1.66-2.07-.174-.3-.019-.465.13-.615.136-.135.301-.345.451-.523.146-.181.194-.301.297-.496.1-.21.049-.375-.025-.524-.075-.15-.672-1.62-.922-2.206-.24-.584-.487-.51-.672-.51-.172-.015-.371-.015-.571-.015-.2 0-.523.074-.797.359-.273.3-1.045 1.02-1.045 2.475s1.07 2.865 1.219 3.075c.149.195 2.105 3.195 5.1 4.485.714.3 1.27.48 1.704.629.714.227 1.365.195 1.88.121.574-.091 1.767-.721 2.016-1.426.255-.705.255-1.29.18-1.425-.074-.135-.27-.21-.57-.345m-5.446 7.443h-.016c-1.77 0-3.524-.48-5.055-1.38l-.36-.214-3.75.975 1.005-3.645-.239-.375c-.99-1.576-1.516-3.391-1.516-5.26 0-5.445 4.455-9.885 9.942-9.885 2.654 0 5.145 1.035 7.021 2.91 1.875 1.859 2.909 4.35 2.909 6.99-.004 5.444-4.46 9.885-9.935 9.885M20.52 3.449C18.24 1.245 15.24 0 12.045 0 5.463 0 .104 5.334.101 11.893c0 2.096.549 4.14 1.595 5.945L0 24l6.335-1.652c1.746.943 3.71 1.444 5.71 1.447h.006c6.585 0 11.946-5.336 11.949-11.896 0-3.176-1.24-6.165-3.495-8.411"/>';
            buttonHtml += '</svg>';
            buttonHtml += '</span>';
        }
        
        buttonHtml += '<span class="ctc-button-text">' + text + '</span>';
        buttonHtml += '</a>';
        buttonHtml += '</div>';
        
        // Update the preview
        $('.ctc-shortcode-preview-container').html(buttonHtml);
    }

    /**
     * Helper function to insert text at cursor position in textarea
     */
    function insertAtCursor(field, text) {
        // IE support
        if (document.selection) {
            field.focus();
            const sel = document.selection.createRange();
            sel.text = text;
            field.focus();
        }
        // Modern browsers
        else if (field.selectionStart || field.selectionStart === 0) {
            const startPos = field.selectionStart;
            const endPos = field.selectionEnd;
            const scrollTop = field.scrollTop;
            
            field.value = field.value.substring(0, startPos) + text + field.value.substring(endPos, field.value.length);
            field.focus();
            field.selectionStart = startPos + text.length;
            field.selectionEnd = startPos + text.length;
            field.scrollTop = scrollTop;
        } else {
            field.value += text;
            field.focus();
        }
    }

    /**
     * Initialize position field toggles
     */
    function initPositionToggles() {
        // Toggle cart position field
        $('#ctc_cart_page_enabled').on('change', function() {
            if ($(this).is(':checked')) {
                $('.ctc-cart-position-row').removeClass('ctc-hidden');
            } else {
                $('.ctc-cart-position-row').addClass('ctc-hidden');
            }
        });

        // Toggle checkout position field
        $('#ctc_checkout_page_enabled').on('change', function() {
            if ($(this).is(':checked')) {
                $('.ctc-checkout-position-row').removeClass('ctc-hidden');
            } else {
                $('.ctc-checkout-position-row').addClass('ctc-hidden');
            }
        });

        // Also handle single product and shop page position fields if they exist
        $('#ctc_single_product_enabled').on('change', function() {
            if ($(this).is(':checked')) {
                $('.ctc-single-position-row').removeClass('ctc-hidden');
            } else {
                $('.ctc-single-position-row').addClass('ctc-hidden');
            }
        });

        $('#ctc_shop_page_enabled').on('change', function() {
            if ($(this).is(':checked')) {
                $('.ctc-shop-position-row').removeClass('ctc-hidden');
            } else {
                $('.ctc-shop-position-row').addClass('ctc-hidden');
            }
        });
    }

    /**
     * Initialize advanced options functionality
     */
    function initAdvancedOptions() {
        // Handle catalog mode toggle
        $('#ctc_catalog_mode').on('change', function() {
            if ($(this).is(':checked')) {
                // Check all hide button options
                $('#ctc_hide_add_to_cart').prop('checked', true);
                $('#ctc_hide_proceed_checkout').prop('checked', true);
                $('#ctc_hide_place_order').prop('checked', true);

                // Disable individual checkboxes when catalog mode is on
                $('#ctc_hide_add_to_cart').prop('disabled', true);
                $('#ctc_hide_proceed_checkout').prop('disabled', true);
                $('#ctc_hide_place_order').prop('disabled', true);
            } else {
                // Enable individual checkboxes when catalog mode is off
                $('#ctc_hide_add_to_cart').prop('disabled', false);
                $('#ctc_hide_proceed_checkout').prop('disabled', false);
                $('#ctc_hide_place_order').prop('disabled', false);
            }
        });

        // Check catalog mode state on page load
        if ($('#ctc_catalog_mode').is(':checked')) {
            $('#ctc_hide_add_to_cart').prop('disabled', true);
            $('#ctc_hide_proceed_checkout').prop('disabled', true);
            $('#ctc_hide_place_order').prop('disabled', true);
        }
    }

    /**
     * Helper function to copy text to clipboard
     */
    function copyToClipboard(text) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.setAttribute('readonly', '');
        textarea.style.position = 'absolute';
        textarea.style.left = '-9999px';
        document.body.appendChild(textarea);

        textarea.select();
        const success = document.execCommand('copy');
        document.body.removeChild(textarea);

        if (success) {
            alert(ctc_admin.copy_success);
        } else {
            alert(ctc_admin.copy_error);
        }

        return success;
    }

    // Initialize when document is ready
    $(document).ready(function() {
        initAdmin();
    });

})(jQuery);