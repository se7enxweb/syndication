{* The progress of a background run: parameter $job_id (empty: nothing to show). The page polls syndication/job/<id>. *}
{if $job_id}
<section class="syn-card" id="syn-job" data-url="{concat( 'syndication/job/', $job_id )|ezurl( 'no' )}" aria-live="polite">
    <h2>{'Background run'|i18n( 'extension/syndication' )} <span class="syn-pill is-info" id="syn-job-status">{'starting'|i18n( 'extension/syndication' )}</span></h2>
    <p class="syn-muted" id="syn-job-result"></p>
    <pre class="syn-log" id="syn-job-log"></pre>
</section>
<script>
(function () {ldelim}
    var box = document.getElementById('syn-job');
    var labels = {ldelim} starting: '{'starting'|i18n( 'extension/syndication' )|wash( 'javascript' )}', running: '{'running'|i18n( 'extension/syndication' )|wash( 'javascript' )}',
                          done: '{'finished'|i18n( 'extension/syndication' )|wash( 'javascript' )}', failed: '{'failed'|i18n( 'extension/syndication' )|wash( 'javascript' )}', unknown: '{'unknown'|i18n( 'extension/syndication' )|wash( 'javascript' )}' {rdelim};
    var classes = {ldelim} starting: 'is-info', running: 'is-warn', done: 'is-ok', failed: 'is-bad', unknown: 'is-muted' {rdelim};
    function poll() {ldelim}
        fetch(box.getAttribute('data-url'), {ldelim} credentials: 'same-origin', headers: {ldelim} 'Accept': 'application/json' {rdelim} {rdelim})
            .then(function (r) {ldelim} return r.json(); {rdelim})
            .then(function (job) {ldelim}
                var status = document.getElementById('syn-job-status');
                status.textContent = labels[job.status] || job.status;
                status.className = 'syn-pill ' + (classes[job.status] || 'is-muted');
                document.getElementById('syn-job-log').textContent = (job.log || []).join('\n');
                var result = job.result || {ldelim}{rdelim};
                var parts = [];
                ['feeds', 'imports', 'sources', 'checked', 'generated', 'new', 'imported', 'failed'].forEach(function (key) {ldelim}
                    if (typeof result[key] === 'number') {ldelim} parts.push(key + ': ' + result[key]); {rdelim}
                {rdelim});
                if (result.error) {ldelim} parts.push(result.error); {rdelim}
                document.getElementById('syn-job-result').textContent = parts.join(' · ');
                if (job.status === 'starting' || job.status === 'running') {ldelim} window.setTimeout(poll, 2000); {rdelim}
            {rdelim})
            .catch(function () {ldelim} window.setTimeout(poll, 5000); {rdelim});
    {rdelim}
    poll();
{rdelim})();
</script>
{/if}
