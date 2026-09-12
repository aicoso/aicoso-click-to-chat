(function ($) {
    'use strict';

    window.CtcAnalytics = {
        seq: 0,

        getRange: function () {
            return {
                start_date: $('#ctc-analytics-start, #ctc-reports-start').first().val(),
                end_date: $('#ctc-analytics-end, #ctc-reports-end').first().val(),
                compare: $('#ctc-analytics-compare').length ? $('#ctc-analytics-compare').is(':checked') : true
            };
        },

        post: function (action, data) {
            var requestId = ++this.seq;
            var payload = $.extend({
                action: action,
                nonce: ctc_chat_analytics.nonce
            }, data || {});

            return $.post(ctc_chat_analytics.ajaxurl, payload).then(function (response) {
                return {
                    requestId: requestId,
                    response: response
                };
            });
        },

        formatNumber: function (value) {
            return Number(value || 0).toLocaleString();
        },

        formatCurrency: function (value) {
            var amount = Number(value || 0);
            return ctc_chat_analytics.currency + amount.toLocaleString(undefined, {
                minimumFractionDigits: 0,
                maximumFractionDigits: 2
            });
        },

        setLoading: function ($el) {
            $el.html('<div class="ctc-analytics-loading">' + ctc_chat_analytics.i18n.loading + '</div>');
        },

        setError: function ($el) {
            $el.html('<div class="ctc-analytics-error">' + ctc_chat_analytics.i18n.error + '</div>');
        }
    };
})(jQuery);
