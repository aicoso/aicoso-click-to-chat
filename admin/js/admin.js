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

        // Initialize back-in-stock alerts live customizer and swatches
        initStockAlertCustomizer();

        // Initialize coupon engine live customizer and swatches
        initCouponEngineCustomizer();

        // Initialize catalog mode and advanced options
        initAdvancedOptions();

        // Initialize Live Visual Customizer & Preview
        initLiveVisualCustomizer();

        // Lean tab progress feedback (matches affiliate admin pattern)
        initTabLoadingProgress();

        // Initialize plugin-native dismissible banners.
        initAdminBanners();
    }

    /**
     * Dismiss plugin-native admin banners.
     */
    function initAdminBanners() {
        $(document).on('click', '.ctc-admin-banner__dismiss', function () {
            $(this).closest('.ctc-admin-banner').remove();
        });
    }

    /**
     * Show a slim progress bar while settings tabs navigate.
     */
    function initTabLoadingProgress() {
        let tabProgressTimer = null;

        function startTabProgress($container) {
            let progress = 8;
            let step = 0;

            if (!$container.length) {
                return;
            }

            window.clearInterval(tabProgressTimer);
            $container.addClass('ctc-tab-loading').css('--ctc-tab-progress-scale', progress / 100);

            tabProgressTimer = window.setInterval(function() {
                const remaining = 96 - progress;

                step += 1;
                progress += Math.max(0.12, remaining * (step < 8 ? 0.16 : 0.055));

                if (progress > 96) {
                    progress = 96;
                }

                $container.css('--ctc-tab-progress-scale', (progress / 100).toFixed(4));
            }, 140);
        }

        function handleTabNavigationClick(event) {
            const href = this.getAttribute('href') || '';

            if (!href || '#' === href || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                return;
            }

            const $container = $(this).closest('.ctc_tablinks.subsubsub, .ctc-settings-split__nav');

            startTabProgress($container);
        }

        $(document).on('click', '.ctc_tablinks.subsubsub a', handleTabNavigationClick);
        $(document).on('click', '.ctc-settings-split-nav a', handleTabNavigationClick);
    }

    /**
     * Initialize color pickers
     */
    function initColorPickers() {
        $('.ctc-chat-color-field').each(function() {
            const $input = $(this);
            $input.wpColorPicker({
                change: function(event, ui) {
                    // Update the input field value and trigger change
                    if (ui.color) {
                        const val = ui.color.toString();
                        $(event.target).val(val);
                        $(event.target).trigger('change');

                        if ($(event.target).attr('id') === 'ctc_chat_stock_bg_color') {
                            $('#ctc-chat-stock-preview-btn').css('background-color', val);
                        } else if ($(event.target).attr('id') === 'ctc_chat_stock_text_color') {
                            $('#ctc-chat-stock-preview-btn').css('color', val);
                        } else if ($(event.target).attr('id') === 'ctc_chat_coupon_badge_bg') {
                            $('#ctc-chat-coupon-preview-chip').css('background-color', val);
                        } else if ($(event.target).attr('id') === 'ctc_chat_coupon_badge_color') {
                            $('#ctc-chat-coupon-preview-chip').css('color', val);
                        } else if ($(event.target).attr('id') === 'ctc_chat_button_bg_color') {
                            $('.ctc-preview-button-target').css('background-color', val);
                        } else if ($(event.target).attr('id') === 'ctc_chat_button_text_color') {
                            $('.ctc-preview-button-target').css('color', val);
                        }
                    }
                },
                clear: function(event) {
                    // Clear the input field value and trigger change
                    $(event.target).val('');
                    $(event.target).trigger('change');

                    if ($(event.target).attr('id') === 'ctc_chat_stock_bg_color') {
                        $('#ctc-chat-stock-preview-btn').css('background-color', '#ff9800');
                    } else if ($(event.target).attr('id') === 'ctc_chat_stock_text_color') {
                        $('#ctc-chat-stock-preview-btn').css('color', '#ffffff');
                    } else if ($(event.target).attr('id') === 'ctc_chat_coupon_badge_bg') {
                        $('#ctc-chat-coupon-preview-chip').css('background-color', '#e11d48');
                    } else if ($(event.target).attr('id') === 'ctc_chat_coupon_badge_color') {
                        $('#ctc-chat-coupon-preview-chip').css('color', '#ffffff');
                    } else if ($(event.target).attr('id') === 'ctc_chat_button_bg_color') {
                        $('.ctc-preview-button-target').css('background-color', '#25D366');
                    } else if ($(event.target).attr('id') === 'ctc_chat_button_text_color') {
                        $('.ctc-preview-button-target').css('color', '#ffffff');
                    }
                }
            });
        });
    }

    /**
     * Initialize Back-in-Stock alerts customizer swatches and live preview
     */
    function initStockAlertCustomizer() {
        const $previewBtn = $('#ctc-chat-stock-preview-btn');
        if (!$previewBtn.length) {
            return;
        }

        // Live update preview text
        $('#ctc_chat_stock_button_text').on('input change', function() {
            const txt = $(this).val() || 'Notify Me on WhatsApp 🔔';
            $previewBtn.find('.ctc-chat-button-text').text(txt);
        });

        // Quick swatch click
        $(document).on('click', '.ctc-stock-swatch', function(e) {
            e.preventDefault();
            const bg = $(this).data('bg');
            const text = $(this).data('text');

            if (bg) {
                const $bgInput = $('#ctc_chat_stock_bg_color');
                $bgInput.val(bg);
                try {
                    $bgInput.wpColorPicker('color', bg);
                } catch (err) {}
                $previewBtn.css('background-color', bg);
            }

            if (text) {
                const $textInput = $('#ctc_chat_stock_text_color');
                $textInput.val(text);
                try {
                    $textInput.wpColorPicker('color', text);
                } catch (err) {}
                $previewBtn.css('color', text);
            }
        });
    }

    /**
     * Initialize Coupon Engine customizer swatches, coupon select, and live preview
     */
    function initCouponEngineCustomizer() {
        const $previewChip = $('#ctc-chat-coupon-preview-chip');
        if (!$previewChip.length) {
            return;
        }

        // Live update preview text
        $('#ctc_chat_coupon_badge_text').on('input change', function() {
            const txt = $(this).val() || '🎁 Chat to get 10% OFF!';
            $('#ctc-chat-coupon-preview-text').text(txt);
        });

        // Quick swatch click
        $(document).on('click', '.ctc-coupon-swatch', function(e) {
            e.preventDefault();
            const bg = $(this).data('bg');
            const text = $(this).data('text');

            if (bg) {
                const $bgInput = $('#ctc_chat_coupon_badge_bg');
                $bgInput.val(bg);
                try {
                    $bgInput.wpColorPicker('color', bg);
                } catch (err) {}
                $previewChip.css('background-color', bg);
            }

            if (text) {
                const $textInput = $('#ctc_chat_coupon_badge_color');
                $textInput.val(text);
                try {
                    $textInput.wpColorPicker('color', text);
                } catch (err) {}
                $previewChip.css('color', text);
            }
        });

        // When a WooCommerce coupon is selected, optionally suggest discount label
        $('#ctc_chat_coupon_select').on('change', function() {
            const selectedText = $(this).find('option:selected').text();
            const match = selectedText.match(/\(([^)]+)\)/);
            if (match && match[1] && !$('#ctc_chat_coupon_custom_discount').val()) {
                $('#ctc_chat_coupon_custom_discount').val(match[1]);
            }
        });
    }

    /**
     * Initialize color pickers specifically for the shortcode generator
     */
    function initShortcodeColorPickers() {
        // Initialize color pickers for shortcode generator fields with real-time preview
        $('#ctc_chat_bg_color, #ctc_chat_text_color').wpColorPicker({
            change: function(event, ui) {
                // Update the input field value first
                if (ui.color) {
                    $(event.target).val(ui.color.toString());
                }
                // Immediately update the preview when color changes
                generateImprovedShortcode();
                updateImprovedPreview();
            },
            clear: function(event, ui) {
                // Clear the input field value
                $(event.target).val('');
                // Update the preview when color is cleared
                generateImprovedShortcode();
                updateImprovedPreview();
            }
        });
    }

    /**
     * Initialize WhatsApp number management
     */
    function initNumbersManagement() {
        // Handle expand/collapse functionality - updated for new classes
        $(document).on('click', '.ctc-chat-number-toggle', function() {
            const $card = $(this).closest('.ctc-chat-number-item, .ctc-chat-number-card');
            const $content = $card.find('.ctc-chat-number-content, .ctc-chat-number-body');
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
        $(document).on('change', '.ctc-chat-default-checkbox', function() {
            if ($(this).is(':checked')) {
                // Uncheck all other default checkboxes
                $('.ctc-chat-default-checkbox').not(this).prop('checked', false);
            }
        });
        
        // Handle number deletion - updated for new classes
        $(document).on('click', '.ctc-chat-number-delete', function() {
            const $item = $(this).closest('.ctc-chat-number-item, .ctc-chat-number-card');
            const numberName = $item.find('.ctc-chat-number-title').text().trim();
            const numberId = $item.data('id');
            
            if (confirm(ctc_chat_admin.delete_number_confirm)) {
                // Add the ID to a hidden input to track deleted numbers
                if (!$('#ctc-chat-deleted-numbers').length) {
                    $('form').append('<input type="hidden" id="ctc-chat-deleted-numbers" name="ctc_chat_deleted_numbers" value="" />');
                }
                
                let deletedNumbers = $('#ctc-chat-deleted-numbers').val();
                deletedNumbers = deletedNumbers ? deletedNumbers.split(',') : [];
                deletedNumbers.push(numberId);
                $('#ctc-chat-deleted-numbers').val(deletedNumbers.join(','));
                
                // Remove the item from the UI
                $item.slideUp(300, function() {
                    $(this).remove();
                });
            }
        });
        
        // Add new WhatsApp number
        $('#ctc-chat-add-number').on('click', function() {
            // Get the template
            const template = wp.template('ctc-chat-number-template');
            
            // Get the next number ID and index
            const nextId = parseInt($('#ctc-chat-next-number-id').val());
            const nextIndex = $('.ctc-chat-number-item, .ctc-chat-number-card').length;
            
            // Render the template
            const html = template({
                id: nextId,
                index: nextIndex
            });
            
            // Append to container
            $('#ctc-chat-numbers-container').append(html);
            
            // Initialize select2 for the new selects
            initSelect2();
            
            // Update the next ID
            $('#ctc-chat-next-number-id').val(nextId + 1);
            
            // Scroll to the new number - updated for new classes
            $('html, body').animate({
                scrollTop: $('.ctc-chat-number-item:last, .ctc-chat-number-card:last').offset().top - 50
            }, 500);
            
            return false;
        });
        
        // Validate number name on change to prevent duplicates
        $(document).on('change', '.ctc-chat-number-name', function() {
            const $input = $(this);
            const name = $input.val().trim();
            
            if (name === '') {
                return;
            }
            
            // Check for duplicate names
            let isDuplicate = false;
            $('.ctc-chat-number-name').not($input).each(function() {
                if ($(this).val().trim() === name) {
                    isDuplicate = true;
                    return false; // Break the loop
                }
            });
            
            if (isDuplicate) {
                alert(ctc_chat_admin.duplicate_name_error);
                $input.val('').focus();
            }
        });
        
        // Ensure the first number is expanded by default - updated for new classes
        $('.ctc-chat-number-item:first .ctc-chat-number-content, .ctc-chat-number-card:first .ctc-chat-number-body').show();
        
        // Initialize select2 for existing selects
        initSelect2();
        
        // Helper function to update indices for all numbers - updated for new classes
        function updateNumberIndices() {
            $('.ctc-chat-number-item, .ctc-chat-number-card').each(function(index) {
                const id = $(this).data('id');
                
                // Update all name attributes in this number item
                $(this).find('[name^="ctc_chat_numbers["]').each(function() {
                    const name = $(this).attr('name');
                    const newName = name.replace(/ctc_chat_numbers\[\d+\]/, 'ctc_chat_numbers[' + index + ']');
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
        $('.ctc-chat-page-select').select2({
            ajax: {
                url: ctc_chat_admin.ajaxurl,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        action: 'ctc_chat_search_pages',
                        term: params.term || '',
                        nonce: ctc_chat_admin.nonce
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
            placeholder: ctc_chat_admin.select_pages_text || 'Select pages...',
            allowClear: true
        });
        
        // Post select
        $('.ctc-chat-post-select').select2({
            ajax: {
                url: ctc_chat_admin.ajaxurl,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        action: 'ctc_chat_search_posts',
                        term: params.term || '',
                        nonce: ctc_chat_admin.nonce
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
        $('.ctc-chat-category-select').select2({
            ajax: {
                url: ctc_chat_admin.ajaxurl,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        action: 'ctc_chat_search_categories',
                        term: params.term || '',
                        nonce: ctc_chat_admin.nonce
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
            placeholder: ctc_chat_admin.select_categories_text || 'Select categories...',
            allowClear: true
        });

        // Tag select
        $('.ctc-chat-tag-select').select2({
            ajax: {
                url: ctc_chat_admin.ajaxurl,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        action: 'ctc_chat_search_tags',
                        term: params.term || '',
                        nonce: ctc_chat_admin.nonce
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
        $('.ctc-chat-product-select').select2({
            ajax: {
                url: ctc_chat_admin.ajaxurl,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        action: 'ctc_chat_search_products',
                        term: params.term || '',
                        nonce: ctc_chat_admin.nonce
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
            placeholder: ctc_chat_admin.select_products_text || 'Select products...',
            allowClear: true
        });
    }

    /**
     * Initialize message template previews
     */
    function initMessageTemplates() {
        // Preview template - updated to work with both old and new class names
        $('.ctc-chat-template-preview-button, .ctc-chat-preview-btn').on('click', function() {
            const $button = $(this);
            const $container = $button.closest('.ctc-chat-template-item, .ctc-chat-template-card');
            const templateType = $button.data('template-type'); // Get from button, not container
            const templateContent = $container.find('.ctc-chat-template-textarea').val();
            const $previewContainer = $container.find('.ctc-chat-template-preview');


            // Show loading state
            $button.prop('disabled', true).text(ctc_chat_admin.loading_text || 'Loading...');
            
            // Send AJAX request
            $.ajax({
                url: ctc_chat_admin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'ctc_chat_preview_message',
                    template_type: templateType,
                    template_content: templateContent,
                    nonce: ctc_chat_admin.nonce
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
                        $previewContainer.removeClass('ctc-chat-hidden').addClass('ctc-chat-visible');
                    } else {
                        $previewContainer.html('<p class="error">' + response.data.message + '</p>');
                        $previewContainer.removeClass('ctc-chat-hidden').addClass('ctc-chat-visible');
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
                    $previewContainer.removeClass('ctc-chat-hidden').addClass('ctc-chat-visible');
                },
                complete: function() {
                    $button.prop('disabled', false).text(ctc_chat_admin.preview_text || 'Preview');
                }
            });
            
            return false;
        });
        
        // Insert placeholder into textarea - updated to work with both old and new class names
        $(document).on('click', '.ctc-chat-placeholder-tag', function() {
            const placeholder = $(this).text();
            const $textarea = $(this).closest('.ctc-chat-template-item, .ctc-chat-template-card').find('.ctc-chat-template-textarea');
            
            insertAtCursor($textarea[0], placeholder);
            
            return false;
        });
    }

    /**
     * Initialize shortcode generator
     */
    function initShortcodeGenerator() {
        // New improved shortcode builder

        // Initialize color pickers for shortcode generator
        initShortcodeColorPickers();

        // Handle button type selection
        $('input[name="button_type"]').on('change', function() {
            const type = $(this).val();
            if (type === 'product') {
                $('.ctc-chat-product-options').removeClass('ctc-chat-hidden').addClass('ctc-chat-visible');
            } else {
                $('.ctc-chat-product-options').removeClass('ctc-chat-visible').addClass('ctc-chat-hidden');
            }
            generateImprovedShortcode();
            updateImprovedPreview();
        });

        // Handle product source selection
        $('input[name="product_source"]').on('change', function() {
            if ($(this).val() === 'specific') {
                $('.ctc-chat-product-id-field').removeClass('ctc-chat-hidden').addClass('ctc-chat-visible');
            } else {
                $('.ctc-chat-product-id-field').removeClass('ctc-chat-visible').addClass('ctc-chat-hidden');
            }
            generateImprovedShortcode();
        });

        // Handle all parameter changes
        $('.ctc-chat-shortcode-param').on('change input', function() {
            generateImprovedShortcode();
            updateImprovedPreview();
        });

        // Initialize on page load
        generateImprovedShortcode();
        updateImprovedPreview();

        // Handle copy button
        $('#ctc-chat-copy-shortcode').on('click', function() {
            const shortcode = $('#ctc-chat-generated-shortcode').text();
            copyToClipboard(shortcode);

            // Show success message
            $('.ctc-chat-copy-success').removeClass('ctc-chat-hidden').addClass('ctc-chat-visible');
            setTimeout(function() {
                $('.ctc-chat-copy-success').removeClass('ctc-chat-visible').addClass('ctc-chat-hidden');
            }, 2000);

            // Change button text temporarily
            const $btnText = $(this).find('.ctc-chat-copy-text');
            const originalText = $btnText.text();
            $btnText.text('Copied!');
            setTimeout(() => {
                $btnText.text(originalText);
            }, 2000);

            return false;
        });

        // Handle advanced section collapse
        $('.ctc-chat-collapsible').on('click', function() {
            $(this).toggleClass('active');
            $('.ctc-chat-advanced-content').slideToggle();
        });

        // Initialize on load
        generateImprovedShortcode();
        updateImprovedPreview();

        // Legacy support - keep old functionality
        $('.ctc-chat-shortcode-form select, .ctc-chat-shortcode-form input').on('change', function() {
            generateShortcode();
        });

        $(document).on('click', '.ctc-chat-copy-shortcode', function() {
            const shortcode = $('.ctc-chat-shortcode-code').text();
            copyToClipboard(shortcode);
            return false;
        });
    }

    /**
     * Generate improved shortcode
     */
    function generateImprovedShortcode() {
        let shortcode = '[ctc_chat_button';

        // Get all parameters
        const type = $('input[name="button_type"]:checked').val() || 'product';
        const productSource = $('input[name="product_source"]:checked').val();
        const productId = $('#ctc_chat_product_id').val();
        const text = $('#ctc_chat_button_text').val();
        const bgColor = $('#ctc_chat_bg_color').val();
        const textColor = $('#ctc_chat_text_color').val();
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

        $('#ctc-chat-generated-shortcode').text(shortcode);
    }

    /**
     * Update improved preview
     */
    function updateImprovedPreview() {
        const text = $('#ctc_chat_button_text').val() || ctc_chat_admin.default_button_text || 'Order via WhatsApp';
        const bgColor = $('#ctc_chat_bg_color').val() || '#25D366';
        const textColor = $('#ctc_chat_text_color').val() || '#ffffff';
        const showIcon = $('input[data-param="icon"]').is(':checked');
        const size = $('select[data-param="size"]').val() || 'normal';
        const align = $('select[data-param="align"]').val() || 'center';

        let previewHtml = '<div class="ctc-chat-align-' + align + '">';
        previewHtml += '<a href="#" class="ctc-chat-whatsapp-button ctc-chat-button-size-' + size + '" ';
        previewHtml += 'style="background-color: ' + bgColor + '; color: ' + textColor + ';" onclick="return false;">';

        if (showIcon) {
            previewHtml += '<span class="ctc-chat-whatsapp-icon">';
            previewHtml += '<svg viewBox="0 0 24 24" width="24" height="24">';
            previewHtml += '<path fill="currentColor" d="M17.498 14.382c-.301-.15-1.767-.867-2.04-.966-.273-.101-.473-.15-.673.15-.197.295-.771.964-.944 1.162-.175.195-.349.21-.646.075-.3-.15-1.263-.465-2.403-1.485-.888-.795-1.484-1.77-1.66-2.07-.174-.3-.019-.465.13-.615.136-.135.301-.345.451-.523.146-.181.194-.301.297-.496.1-.21.049-.375-.025-.524-.075-.15-.672-1.62-.922-2.206-.24-.584-.487-.51-.672-.51-.172-.015-.371-.015-.571-.015-.2 0-.523.074-.797.359-.273.3-1.045 1.02-1.045 2.475s1.07 2.865 1.219 3.075c.149.195 2.105 3.195 5.1 4.485.714.3 1.27.48 1.704.629.714.227 1.365.195 1.88.121.574-.091 1.767-.721 2.016-1.426.255-.705.255-1.29.18-1.425-.074-.135-.27-.21-.57-.345m-5.446 7.443h-.016c-1.77 0-3.524-.48-5.055-1.38l-.36-.214-3.75.975 1.005-3.645-.239-.375c-.99-1.576-1.516-3.391-1.516-5.26 0-5.445 4.455-9.885 9.942-9.885 2.654 0 5.145 1.035 7.021 2.91 1.875 1.859 2.909 4.35 2.909 6.99-.004 5.444-4.46 9.885-9.935 9.885M20.52 3.449C18.24 1.245 15.24 0 12.045 0 5.463 0 .104 5.334.101 11.893c0 2.096.549 4.14 1.595 5.945L0 24l6.335-1.652c1.746.943 3.71 1.444 5.71 1.447h.006c6.585 0 11.946-5.336 11.949-11.896 0-3.176-1.24-6.165-3.495-8.411"/>';
            previewHtml += '</svg>';
            previewHtml += '</span>';
        }

        previewHtml += '<span class="ctc-chat-button-text">' + escapeHtml(text) + '</span>';
        previewHtml += '</a>';
        previewHtml += '</div>';

        $('#ctc-chat-button-preview').html(previewHtml);
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
        const $form = $('.ctc-chat-shortcode-form');
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
        $('.ctc-chat-shortcode-code').text(shortcode);
        
        // Update the preview
        updateShortcodePreview();
    }

    /**
     * Update the shortcode preview
     */
    function updateShortcodePreview() {
        const $form = $('.ctc-chat-shortcode-form');
        const text = $form.find('#shortcode_text').val() || ctc_chat_admin.default_button_text;
        const bgColor = $form.find('#shortcode_bg_color').val() || '#25D366';
        const textColor = $form.find('#shortcode_text_color').val() || '#ffffff';
        const icon = $form.find('#shortcode_icon').is(':checked');
        const size = $form.find('#shortcode_size').val();
        const align = $form.find('#shortcode_align').val();
        
        // Create button HTML
        let buttonHtml = '<div class="ctc-shortcode-container">';
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
        $('.ctc-chat-shortcode-preview-container').html(buttonHtml);
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
        $('#ctc_chat_cart_page_enabled').on('change', function() {
            if ($(this).is(':checked')) {
                $('.ctc-chat-cart-position-row').removeClass('ctc-chat-hidden');
            } else {
                $('.ctc-chat-cart-position-row').addClass('ctc-chat-hidden');
            }
        });

        // Toggle checkout position field
        $('#ctc_chat_checkout_page_enabled').on('change', function() {
            if ($(this).is(':checked')) {
                $('.ctc-chat-checkout-position-row').removeClass('ctc-chat-hidden');
            } else {
                $('.ctc-chat-checkout-position-row').addClass('ctc-chat-hidden');
            }
        });

        // Also handle single product and shop page position fields if they exist
        $('#ctc_chat_single_product_enabled').on('change', function() {
            if ($(this).is(':checked')) {
                $('.ctc-chat-single-position-row').removeClass('ctc-chat-hidden');
            } else {
                $('.ctc-chat-single-position-row').addClass('ctc-chat-hidden');
            }
        });

        $('#ctc_chat_shop_page_enabled').on('change', function() {
            if ($(this).is(':checked')) {
                $('.ctc-chat-shop-position-row').removeClass('ctc-chat-hidden');
            } else {
                $('.ctc-chat-shop-position-row').addClass('ctc-chat-hidden');
            }
        });

        // Toggle cart/checkout nudge settings
        $('#ctc_chat_nudge_enabled').on('change', function() {
            if ($(this).is(':checked')) {
                $('.ctc-chat-nudge-row').removeClass('ctc-chat-hidden');
            } else {
                $('.ctc-chat-nudge-row').addClass('ctc-chat-hidden');
            }
        });

        // Toggle back in stock alert settings
        $('#ctc_chat_stock_enabled').on('change', function() {
            if ($(this).is(':checked')) {
                $('.ctc-chat-stock-row').removeClass('ctc-chat-hidden');
            } else {
                $('.ctc-chat-stock-row').addClass('ctc-chat-hidden');
            }
        });

        // Toggle coupon engine settings
        $('#ctc_chat_coupon_enabled').on('change', function() {
            if ($(this).is(':checked')) {
                $('.ctc-chat-coupon-row').removeClass('ctc-chat-hidden');
            } else {
                $('.ctc-chat-coupon-row').addClass('ctc-chat-hidden');
            }
        });

        // Toggle desktop QR modal settings
        $('#ctc_chat_qr_modal_enabled').on('change', function() {
            if ($(this).is(':checked')) {
                $('.ctc-chat-qr-modal-row').removeClass('ctc-chat-hidden');
            } else {
                $('.ctc-chat-qr-modal-row').addClass('ctc-chat-hidden');
            }
        });

        // Toggle GDPR & Privacy compliance settings
        $('#ctc_chat_privacy_enabled').on('change', function() {
            if ($(this).is(':checked')) {
                $('.ctc-chat-privacy-row').removeClass('ctc-chat-hidden');
            } else {
                $('.ctc-chat-privacy-row').addClass('ctc-chat-hidden');
            }
        });

        // Initialize position toggles on page load
        $('#ctc_chat_cart_page_enabled').trigger('change');
        $('#ctc_chat_checkout_page_enabled').trigger('change');
        $('#ctc_chat_single_product_enabled').trigger('change');
        $('#ctc_chat_shop_page_enabled').trigger('change');
        $('#ctc_chat_nudge_enabled').trigger('change');
        $('#ctc_chat_stock_enabled').trigger('change');
        $('#ctc_chat_coupon_enabled').trigger('change');
        $('#ctc_chat_qr_modal_enabled').trigger('change');
        $('#ctc_chat_privacy_enabled').trigger('change');
    }

    /**
     * Initialize advanced options functionality
     */
    function initAdvancedOptions() {
        // Handle catalog mode toggle
        $('#ctc_chat_catalog_mode').on('change', function() {
            if ($(this).is(':checked')) {
                // Check all hide button options
                $('#ctc_chat_hide_add_to_cart').prop('checked', true);
                $('#ctc_chat_hide_proceed_checkout').prop('checked', true);
                $('#ctc_chat_hide_place_order').prop('checked', true);

                // Disable individual checkboxes when catalog mode is on
                $('#ctc_chat_hide_add_to_cart').prop('disabled', true);
                $('#ctc_chat_hide_proceed_checkout').prop('disabled', true);
                $('#ctc_chat_hide_place_order').prop('disabled', true);
            } else {
                // Enable individual checkboxes when catalog mode is off
                $('#ctc_chat_hide_add_to_cart').prop('disabled', false);
                $('#ctc_chat_hide_proceed_checkout').prop('disabled', false);
                $('#ctc_chat_hide_place_order').prop('disabled', false);
            }
        });

        // Check catalog mode state on page load
        if ($('#ctc_chat_catalog_mode').is(':checked')) {
            $('#ctc_chat_hide_add_to_cart').prop('disabled', true);
            $('#ctc_chat_hide_proceed_checkout').prop('disabled', true);
            $('#ctc_chat_hide_place_order').prop('disabled', true);
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
            alert(ctc_chat_admin.copy_success);
        } else {
            alert(ctc_chat_admin.copy_error);
        }

        return success;
    }

    /**
     * Initialize Live Visual Customizer with real-time sync and device switcher
     */
    function initLiveVisualCustomizer() {
        const $customizer = $('.ctc-live-customizer');
        if (!$customizer.length) {
            return;
        }

        // Device Switcher (Desktop vs Mobile)
        $(document).on('click', '.ctc-device-btn', function(e) {
            e.preventDefault();
            const device = $(this).data('device');
            $('.ctc-device-btn').removeClass('active');
            $(this).addClass('active');

            $('#ctc-preview-container').attr('data-active-device', device);
            if (device === 'mobile') {
                $('.ctc-mockup-desktop').hide();
                $('.ctc-mockup-mobile').fadeIn(200);
            } else {
                $('.ctc-mockup-mobile').hide();
                $('.ctc-mockup-desktop').fadeIn(200);
            }
        });

        // Live text sync
        $('#ctc_chat_button_text').on('input change', function() {
            const val = $(this).val() || 'Order via WhatsApp';
            $('.ctc-preview-button-text').text(val);
        });

        // Live icon sync
        $('#ctc_chat_button_icon').on('change', function() {
            if ($(this).is(':checked')) {
                $('.ctc-preview-button-target .ctc-chat-whatsapp-icon').show();
            } else {
                $('.ctc-preview-button-target .ctc-chat-whatsapp-icon').hide();
            }
        });

        // Direct input sync for color fields
        $('#ctc_chat_button_bg_color').on('input change', function() {
            const val = $(this).val();
            if (val) {
                $('.ctc-preview-button-target').css('background-color', val);
            }
        });

        $('#ctc_chat_button_text_color').on('input change', function() {
            const val = $(this).val();
            if (val) {
                $('.ctc-preview-button-target').css('color', val);
            }
        });
    }

    // Initialize when document is ready
    $(document).ready(function() {
        initAdmin();
    });

})(jQuery);
