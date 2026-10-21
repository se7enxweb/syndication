{* The progress of a background run: parameter $job_id (empty: nothing to show). The page polls syndication/job/<id>
   (syndication_admin.js; the status names in data-labels). *}
{if $job_id}
<section class="syn-card" id="syn-job" data-url="{concat( 'syndication/job/', $job_id )|ezurl( 'no' )}" data-labels="{json_encode( hash( 'starting', 'starting'|i18n( 'extension/syndication' ), 'running', 'running'|i18n( 'extension/syndication' ), 'done', 'finished'|i18n( 'extension/syndication' ), 'failed', 'failed'|i18n( 'extension/syndication' ), 'unknown', 'unknown'|i18n( 'extension/syndication' ) ) )|wash}" aria-live="polite">
    <h2>{'Background run'|i18n( 'extension/syndication' )} <span class="a4-badge is-info" id="syn-job-status">{'starting'|i18n( 'extension/syndication' )}</span></h2>
    <p class="syn-muted" id="syn-job-result"></p>
    <pre class="syn-log" id="syn-job-log"></pre>
</section>
{ezscript( array( 'syndication_admin.js' ) )}
{/if}
