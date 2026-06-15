(function ($) {
    'use strict';

    var currentRequest = 0;

    function applyPreset(days) {
        var end = new Date();
        var start = new Date();
        start.setDate(end.getDate() - (days - 1));
        $('#ctc-analytics-end').val(end.toISOString().slice(0, 10));
        $('#ctc-analytics-start').val(start.toISOString().slice(0, 10));
        refreshAll();
    }

    function renderKpis(data) {
        if (!data || !data.total_clicks) {
            return;
        }

        var cards = [
            { key: 'total_clicks', label: 'WhatsApp Clicks' },
            { key: 'unique_clicks', label: 'Unique Clicks' },
            { key: 'high_intent_clicks', label: 'High-Intent Clicks' },
            { key: 'cart_value_clicked', label: 'Cart Value at Click', format: 'currency' },
            { key: 'mobile_share', label: 'Mobile Share', format: 'percent' }
        ];

        var html = '';

        cards.forEach(function (card) {
            var metric = data[card.key];
            var value = card.format === 'currency'
                ? CtcAnalytics.formatCurrency(metric.value)
                : (card.format === 'percent' ? metric.value + '%' : CtcAnalytics.formatNumber(metric.value));

            html += '<div class="ctc-analytics-kpi">';
            html += '<span class="ctc-analytics-kpi__label">' + card.label + '</span>';
            html += '<strong class="ctc-analytics-kpi__value">' + value + '</strong>';
            if (metric.delta_label) {
                html += '<span class="ctc-analytics-kpi__delta">' + metric.delta_label + '</span>';
            }
            html += '</div>';
        });

        if (data.top_placement && data.top_placement.label) {
            html += '<div class="ctc-analytics-kpi">';
            html += '<span class="ctc-analytics-kpi__label">Top Placement</span>';
            html += '<strong class="ctc-analytics-kpi__value">' + data.top_placement.label + '</strong>';
            html += '<span class="ctc-analytics-kpi__delta">' + CtcAnalytics.formatNumber(data.top_placement.count) + ' clicks</span>';
            html += '</div>';
        }

        $('#ctc-analytics-kpis').html(html);
    }

    function renderTrend(payload) {
        var $body = $('#ctc-analytics-trend .ctc-analytics-card__body');
        if (!payload.series || !payload.series.length) {
            $body.html('<p class="ctc-analytics-empty">' + ctc_chat_analytics.i18n.empty + '</p>');
            return;
        }

        var max = 1;
        payload.series.forEach(function (point) {
            max = Math.max(max, point.clicks);
        });

        var html = '<div class="ctc-analytics-bars">';
        payload.series.forEach(function (point) {
            var height = Math.round((point.clicks / max) * 100);
            html += '<div class="ctc-analytics-bar" title="' + point.date + ': ' + point.clicks + '">';
            html += '<span style="height:' + height + '%"></span>';
            html += '<em>' + point.clicks + '</em>';
            html += '</div>';
        });
        html += '</div>';
        $body.html(html);
    }

    function renderFunnel(payload) {
        var $body = $('#ctc-analytics-funnel .ctc-analytics-card__body');
        if (!payload.stages || !payload.stages.length) {
            $body.html('<p class="ctc-analytics-empty">' + ctc_chat_analytics.i18n.empty + '</p>');
            return;
        }

        var html = '<div class="ctc-analytics-funnel">';
        payload.stages.forEach(function (stage, index) {
            html += '<div class="ctc-analytics-funnel__stage">';
            html += '<span class="ctc-analytics-funnel__label">' + stage.label + '</span>';
            html += '<strong>' + CtcAnalytics.formatNumber(stage.count) + '</strong>';
            html += '</div>';
            if (index < payload.stages.length - 1) {
                html += '<span class="ctc-analytics-funnel__arrow" aria-hidden="true">→</span>';
            }
        });
        html += '</div>';
        $body.html(html);
    }

    function renderTopTable($el, items, columns) {
        if (!items || !items.length) {
            $el.html('<p class="ctc-analytics-empty">' + ctc_chat_analytics.i18n.empty + '</p>');
            return;
        }

        var html = '<table class="widefat striped ctc-analytics-table"><thead><tr>';
        columns.forEach(function (col) {
            html += '<th>' + col.label + '</th>';
        });
        html += '</tr></thead><tbody>';

        items.forEach(function (item) {
            html += '<tr>';
            columns.forEach(function (col) {
                var cell = col.render(item);
                html += '<td>' + cell + '</td>';
            });
            html += '</tr>';
        });

        html += '</tbody></table>';
        $el.html(html);
    }

    function refreshAll() {
        if ($('[data-ctc-analytics="dashboard"]').data('enabled') !== 1) {
            return;
        }

        var range = CtcAnalytics.getRange();
        var requestId = ++currentRequest;

        CtcAnalytics.setLoading($('#ctc-analytics-kpis'));
        CtcAnalytics.setLoading($('#ctc-analytics-trend .ctc-analytics-card__body'));
        CtcAnalytics.setLoading($('#ctc-analytics-funnel .ctc-analytics-card__body'));
        CtcAnalytics.setLoading($('#ctc-analytics-top-products .ctc-analytics-card__body'));
        CtcAnalytics.setLoading($('#ctc-analytics-top-numbers .ctc-analytics-card__body'));

        CtcAnalytics.post('ctc_analytics_kpis', range).done(function (result) {
            if (requestId !== currentRequest) {
                return;
            }
            if (!result.response.success) {
                CtcAnalytics.setError($('#ctc-analytics-kpis'));
                return;
            }
            renderKpis(result.response.data);
        });

        CtcAnalytics.post('ctc_analytics_trend', $.extend({ grouping: 'day' }, range)).done(function (result) {
            if (requestId !== currentRequest) {
                return;
            }
            renderTrend(result.response.success ? result.response.data : { series: [] });
        });

        CtcAnalytics.post('ctc_analytics_funnel', range).done(function (result) {
            if (requestId !== currentRequest) {
                return;
            }
            renderFunnel(result.response.success ? result.response.data : { stages: [] });
        });

        CtcAnalytics.post('ctc_analytics_top_products', range).done(function (result) {
            if (requestId !== currentRequest) {
                return;
            }
            renderTopTable(
                $('#ctc-analytics-top-products .ctc-analytics-card__body'),
                result.response.success ? result.response.data.items : [],
                [
                    { label: 'Product', render: function (item) { return $('<span/>').text(item.name).html(); } },
                    { label: 'Clicks', render: function (item) { return CtcAnalytics.formatNumber(item.clicks); } },
                    { label: 'Unique', render: function (item) { return CtcAnalytics.formatNumber(item.unique_clicks); } }
                ]
            );
        });

        CtcAnalytics.post('ctc_analytics_top_numbers', range).done(function (result) {
            if (requestId !== currentRequest) {
                return;
            }
            renderTopTable(
                $('#ctc-analytics-top-numbers .ctc-analytics-card__body'),
                result.response.success ? result.response.data.items : [],
                [
                    { label: 'Number', render: function (item) { return $('<span/>').text(item.masked_number || item.label).html(); } },
                    { label: 'Clicks', render: function (item) { return CtcAnalytics.formatNumber(item.clicks); } },
                    { label: 'High-intent', render: function (item) { return CtcAnalytics.formatNumber(item.high_intent_clicks); } }
                ]
            );
        });
    }

    $(function () {
        if (!$('[data-ctc-analytics="dashboard"]').length) {
            return;
        }

        $('[data-range-preset]').on('click', function () {
            applyPreset(parseInt($(this).data('range-preset'), 10));
        });

        $('#ctc-analytics-apply').on('click', refreshAll);
        refreshAll();
    });
})(jQuery);
