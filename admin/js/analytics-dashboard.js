(function ($) {
    'use strict';

    var currentRequest = 0;

    function setActivePreset($active) {
        var $presets = $('[data-range-preset]');
        $presets.removeClass('button-primary').attr('aria-pressed', 'false');

        if ($active && $active.length) {
            $active.addClass('button-primary').attr('aria-pressed', 'true');
        }
    }

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

        function formatMetric(value, format) {
            if (format === 'currency') {
                return CtcAnalytics.formatCurrency(value);
            }
            if (format === 'percent') {
                return CtcAnalytics.formatNumber(value) + '%';
            }
            return CtcAnalytics.formatNumber(value);
        }

        function renderComparison(metric, format) {
            if (!data.comparison_enabled || metric.compare_value === null || typeof metric.compare_value === 'undefined') {
                return '';
            }

            if (metric.delta_pct === null || typeof metric.delta_pct === 'undefined') {
                return '<span class="ctc-analytics-kpi__delta ctc-analytics-kpi__delta--neutral">'
                    + 'Prior period: ' + formatMetric(metric.compare_value, format)
                    + '</span>';
            }

            var deltaClass = 'ctc-analytics-kpi__delta--neutral';
            if (metric.delta_pct > 0) {
                deltaClass = 'ctc-analytics-kpi__delta--positive';
            } else if (metric.delta_pct < 0) {
                deltaClass = 'ctc-analytics-kpi__delta--negative';
            }

            return '<span class="ctc-analytics-kpi__delta ' + deltaClass + '">'
                + '<strong>' + (metric.delta_label || '\u2014') + '</strong>'
                + ' vs prior ' + formatMetric(metric.compare_value, format)
                + '</span>';
        }

        var html = '';

        cards.forEach(function (card) {
            var metric = data[card.key];
            var value = formatMetric(metric.value, card.format);

            html += '<div class="ctc-analytics-kpi">';
            html += '<span class="ctc-analytics-kpi__label">' + card.label + '</span>';
            html += '<strong class="ctc-analytics-kpi__value">' + value + '</strong>';
            html += renderComparison(metric, card.format);
            html += '</div>';
        });

        if (data.top_placement && data.top_placement.label) {
            html += '<div class="ctc-analytics-kpi">';
            html += '<span class="ctc-analytics-kpi__label">Top Placement</span>';
            html += '<strong class="ctc-analytics-kpi__value">' + data.top_placement.label + '</strong>';
            html += '<span class="ctc-analytics-kpi__delta">' + CtcAnalytics.formatNumber(data.top_placement.count) + ' clicks</span>';
            if (data.comparison_enabled && data.top_placement.compare_value !== null) {
                html += '<span class="ctc-analytics-kpi__delta ctc-analytics-kpi__delta--neutral">'
                    + 'Prior: ' + (data.top_placement.compare_label || '\u2014')
                    + ' \u00b7 ' + CtcAnalytics.formatNumber(data.top_placement.compare_count) + ' clicks'
                    + '</span>';
            }
            html += '</div>';
        }

        if (data.comparison_enabled && data.range) {
            html += '<p class="ctc-analytics-kpis__comparison-period">Compared with '
                + data.range.compare_start + ' to ' + data.range.compare_end + '</p>';
        }

        $('#ctc-analytics-kpis').html(html);
    }

    function renderTrend(payload) {
        var $body = $('#ctc-analytics-trend .ctc-analytics-card__body');
        if (!payload.series || !payload.series.length) {
            $body.html('<p class="ctc-analytics-empty">' + ctc_chat_analytics.i18n.empty + '</p>');
            return;
        }

        var max = 0;
        payload.series.forEach(function (point) {
            max = Math.max(max, parseInt(point.clicks, 10) || 0);
        });

        if (max < 1) {
            $body.html('<p class="ctc-analytics-empty">' + ctc_chat_analytics.i18n.empty + '</p>');
            return;
        }

        function getAxisMaximum(value, steps) {
            var roughStep = value / steps;
            var magnitude = Math.pow(10, Math.floor(Math.log(roughStep) / Math.LN10));
            var normalized = roughStep / magnitude;
            var rounded = normalized <= 1 ? 1
                : (normalized <= 2 ? 2 : (normalized <= 2.5 ? 2.5 : (normalized <= 5 ? 5 : 10)));
            return Math.max(steps, rounded * magnitude * steps);
        }

        function formatDateLabel(date, grouping) {
            if (grouping === 'week') {
                return date.slice(5);
            }
            if (grouping === 'month') {
                return date;
            }
            return date.slice(5);
        }

        var tickCount = 4;
        var axisMax = getAxisMaximum(max, tickCount);
        var labelStep = Math.max(1, Math.ceil(payload.series.length / 7));
        var html = '<div class="ctc-analytics-chart" role="figure" aria-label="WhatsApp clicks over time">';
        html += '<div class="ctc-analytics-chart__y-axis" aria-hidden="true">';

        for (var tick = tickCount; tick >= 0; tick--) {
            html += '<span>' + Math.round((axisMax * tick) / tickCount) + '</span>';
        }

        html += '</div>';
        html += '<div class="ctc-analytics-chart__scroll">';
        html += '<div class="ctc-analytics-chart__canvas">';
        html += '<div class="ctc-analytics-chart__plot">';

        for (var grid = 0; grid <= tickCount; grid++) {
            html += '<span class="ctc-analytics-chart__gridline" style="bottom:' + ((grid / tickCount) * 100) + '%"></span>';
        }

        html += '<div class="ctc-analytics-chart__bars">';
        payload.series.forEach(function (point) {
            var clicks = Math.max(0, parseInt(point.clicks, 10) || 0);
            var height = (clicks / axisMax) * 100;
            var date = $('<span/>').text(point.date).html();
            var tooltip = date + ': ' + clicks + (clicks === 1 ? ' click' : ' clicks');
            html += '<div class="ctc-analytics-chart__column">';
            html += '<span class="ctc-analytics-chart__bar" style="height:' + height + '%"'
                + (clicks > 0 ? ' tabindex="0"' : '')
                + ' title="' + tooltip + '" aria-label="' + tooltip + '"></span>';
            html += '</div>';
        });
        html += '</div></div>';
        html += '<div class="ctc-analytics-chart__x-axis" aria-hidden="true">';
        payload.series.forEach(function (point, index) {
            var showLabel = index % labelStep === 0 || index === payload.series.length - 1;
            var label = showLabel ? formatDateLabel(point.date, payload.grouping) : '';
            html += '<span>' + $('<span/>').text(label).html() + '</span>';
        });
        html += '</div></div></div></div>';
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
            setActivePreset($(this));
            applyPreset(parseInt($(this).data('range-preset'), 10));
        });

        $('#ctc-analytics-start, #ctc-analytics-end').on('change', function () {
            setActivePreset(null);
        });

        $('#ctc-analytics-apply').on('click', refreshAll);
        refreshAll();
    });
})(jQuery);
