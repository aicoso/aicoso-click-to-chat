(function ($) {
    'use strict';

    function getReportType() {
        return $('[data-ctc-analytics="reports"]').data('report') || 'clicks';
    }

    function getFilters() {
        var $root = $('[data-ctc-analytics="reports"]');
        return {
            start_date: $('#ctc-reports-start').val(),
            end_date: $('#ctc-reports-end').val(),
            page: 1,
            per_page: 20,
            search: $('#ctc-reports-search').val() || '',
            product_id: $root.data('product-id') || 0,
            number_id: $root.data('number-id') || 0
        };
    }

    function renderClickLog(data) {
        if (!data.rows || !data.rows.length) {
            return '<p class="ctc-analytics-empty">' + ctc_chat_analytics.i18n.empty + '</p>';
        }

        var html = '<table class="widefat striped ctc-analytics-table"><thead><tr>';
        html += '<th>Date</th><th>Placement</th><th>Product</th><th>Order</th><th>Cart total</th><th>Number</th><th>Device</th><th>Page</th>';
        html += '</tr></thead><tbody>';

        data.rows.forEach(function (row) {
            html += '<tr>';
            html += '<td>' + row.clicked_at + '</td>';
            html += '<td>' + row.button_label + '</td>';
            html += '<td>' + (row.product_name || '—') + '</td>';
            html += '<td>' + (row.order_number || '—') + '</td>';
            html += '<td>' + (row.cart_total_formatted || '—') + '</td>';
            html += '<td>' + row.number_label + '</td>';
            html += '<td>' + row.device_type + '</td>';
            html += '<td><span class="ctc-analytics-truncate" title="' + (row.page_url || '') + '">' + (row.page_url || '—') + '</span></td>';
            html += '</tr>';
        });

        html += '</tbody></table>';
        html += '<p class="ctc-analytics-pagination-info">' + data.total + ' total rows</p>';
        return html;
    }

    function renderAggregate(report, data) {
        if (!data.rows || !data.rows.length) {
            return '<p class="ctc-analytics-empty">' + ctc_chat_analytics.i18n.empty + '</p>';
        }

        var html = '<table class="widefat striped ctc-analytics-table"><thead><tr>';
        if (report === 'placements') {
            html += '<th>Placement</th>';
        } else if (report === 'products') {
            html += '<th>Product</th>';
        } else if (report === 'numbers') {
            html += '<th>Number</th>';
        } else {
            html += '<th>Page path</th>';
        }
        html += '<th>Clicks</th><th>Unique</th><th>Share</th><th>High-intent</th></tr></thead><tbody>';

        data.rows.forEach(function (row) {
            html += '<tr>';
            if (report === 'placements') {
                html += '<td>' + row.placement + '</td>';
            } else if (report === 'products') {
                html += '<td>' + row.product + '</td>';
            } else if (report === 'numbers') {
                html += '<td>' + row.number + '</td>';
            } else {
                html += '<td>' + row.page_path + '</td>';
            }
            html += '<td>' + row.clicks + '</td>';
            html += '<td>' + row.unique_clicks + '</td>';
            html += '<td>' + row.share_pct + '%</td>';
            html += '<td>' + row.high_intent + '</td>';
            html += '</tr>';
        });

        html += '</tbody></table>';
        return html;
    }

    function loadReport() {
        var report = getReportType();
        var filters = getFilters();
        var $table = $('#ctc-reports-table .ctc-analytics-card__body');
        CtcAnalytics.setLoading($table);

        if (report === 'clicks') {
            CtcAnalytics.post('ctc_analytics_report_clicks', filters).done(function (result) {
                if (!result.response.success) {
                    CtcAnalytics.setError($table);
                    return;
                }
                $table.html(renderClickLog(result.response.data));
            });
            return;
        }

        CtcAnalytics.post('ctc_analytics_report_aggregate', $.extend({ report: report }, filters)).done(function (result) {
            if (!result.response.success) {
                CtcAnalytics.setError($table);
                return;
            }
            $table.html(renderAggregate(report, result.response.data));
        });
    }

    function exportCsv() {
        var filters = getFilters();
        var params = $.param({
            action: 'ctc_analytics_export_csv',
            nonce: ctc_chat_analytics.nonce,
            start_date: filters.start_date,
            end_date: filters.end_date,
            search: filters.search,
            product_id: filters.product_id,
            number_id: filters.number_id
        });
        window.location = ctc_chat_analytics.ajaxurl + '?' + params;
    }

    $(function () {
        if (!$('[data-ctc-analytics="reports"]').length) {
            return;
        }

        $('#ctc-reports-apply').on('click', loadReport);
        $('#ctc-reports-export').on('click', exportCsv);
        loadReport();
    });
})(jQuery);
