<div id="content" class="poki-public-page poki-category-page">
    <div class="poki-page-heading">
        <h1>{{CATEGORY_NAME}}</h1>
        <p class="poki-page-intro">Play {{CATEGORY_NAME}} games online for free. Browse the latest and most played titles in this category.</p>
    </div>

    <section class="poki-section">
        <div class="section-title">
            <h2>{{CATEGORY_NAME}} games</h2><span>{{CATEGORY_PAGE_LABEL}}</span>
            <a href="{{CONFIG_SITE_URL}}/categories">Categories</a>
        </div>
        <div class="game-list-grid-container poki-game-list">
            {{CATEGORY_GAMES_LIST}}
        </div>
        {{CATEGORY_PAGINATION}}
    </section>
</div>

<div class="bgs bottomtext fn-clear">
    {{FOOTER_DESCRIPTION_MODIFIED}}
</div>

<script>
var cat = "{{CATEGORYID}}";
</script>

{{FOOTER_CONTENT}}