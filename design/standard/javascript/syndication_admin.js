/*
 * The syndication administration pages without inline code (they work under a Content Security Policy without
 * 'unsafe-inline'). Printed by parts/syndication/script.tpl, job.tpl and syndication/pending_edit.tpl; sets itself up
 * once however often it is printed.
 *  - a form with data-syn-confirm asks before it is sent; a button with data-syn-confirm-button asks for itself;
 *  - input[data-syn-check-all] checks or unchecks every named checkbox of its table;
 *  - #syn-job (parts/syndication/job.tpl) polls data-url for the background run and shows its status, result and
 *    log; the status names come from data-labels (JSON);
 *  - #syn-set-pending (pending_edit.tpl) sets every select[data-syn-changeable] to the option with value 1.
 */
(function () {
    'use strict';

    function startJob() {
        var box = document.getElementById('syn-job');
        if (!box || box.hasAttribute('data-syn-polling')) {
            return;
        }
        box.setAttribute('data-syn-polling', '1');
        var labels = {};
        try {
            labels = JSON.parse(box.getAttribute('data-labels') || '{}');
        } catch (e) {
            labels = {};
        }
        var classes = { starting: 'is-info', running: 'is-warn', done: 'is-ok', failed: 'is-bad', unknown: '' };

        function poll() {
            fetch(box.getAttribute('data-url'), { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (job) {
                    var status = document.getElementById('syn-job-status');
                    status.textContent = labels[job.status] || job.status;
                    status.className = ('a4-badge ' + (classes[job.status] || '')).trim();
                    document.getElementById('syn-job-log').textContent = (job.log || []).join('\n');
                    var result = job.result || {};
                    var parts = [];
                    ['feeds', 'imports', 'sources', 'checked', 'generated', 'new', 'imported', 'failed'].forEach(function (key) {
                        if (typeof result[key] === 'number') { parts.push(key + ': ' + result[key]); }
                    });
                    if (result.error) { parts.push(result.error); }
                    document.getElementById('syn-job-result').textContent = parts.join(' · ');
                    if (job.status === 'starting' || job.status === 'running') { window.setTimeout(poll, 2000); }
                })
                .catch(function () { window.setTimeout(poll, 5000); });
        }
        poll();
    }

    if (window.SyndicationAdmin) {
        // printed again (the job box may only be in the page now)
        startJob();
        return;
    }
    window.SyndicationAdmin = { startJob: startJob };

    // A form with data-syn-confirm asks before it is sent; a button with data-syn-confirm-button asks for itself.
    document.addEventListener('submit', function (event) {
        var form = event.target;
        var submitter = event.submitter;
        var text = submitter && submitter.getAttribute('data-syn-confirm-button');
        if (!text && form.getAttribute) { text = form.getAttribute('data-syn-confirm'); }
        if (text && !window.confirm(text)) { event.preventDefault(); }
    });

    // Select all checkboxes of a table.
    document.addEventListener('change', function (event) {
        var box = event.target;
        if (!box || !box.matches || !box.matches('input[data-syn-check-all]')) {
            return;
        }
        var table = box.closest('table');
        if (table) {
            table.querySelectorAll('input[type=checkbox][name]').forEach(function (c) { c.checked = box.checked; });
        }
    });

    // The pending items page: set all to pending.
    document.addEventListener('click', function (event) {
        var button = event.target && event.target.closest ? event.target.closest('#syn-set-pending') : null;
        if (!button) {
            return;
        }
        document.querySelectorAll('select[data-syn-changeable]').forEach(function (select) {
            for (var i = 0; i < select.options.length; i++) {
                if (select.options[i].value === '1') { select.selectedIndex = i; }
            }
        });
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', startJob);
    } else {
        startJob();
    }
})();
