<!DOCTYPE html>
<html lang="en">
<head>
    {{HEADER_TAGS}}
    <link href="https://fonts.googleapis.com/icon?family=Material+Symbols+Outlined" rel="preload" as="style" onload="this.rel='stylesheet'">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="preload" as="style" onload="this.rel='stylesheet'">
    <link rel="preload" as="style" onload="this.rel='stylesheet'" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* PlayGrid public shell: no legacy fixed sidebar. */
        .sidebar{display:none!important}
        .sidebar + .wrapper{margin-left:0!important}
        .gamemonetize-container{margin-left:0!important;margin-right:0!important}
    </style>
</head>
<body>
    {{HEADER}}
    <div {{PAGE_THEATER_MODE}} class="gamemonetize-page-tree gamemonetize-container" style="{{SIDEBAR_MARGIN}}">
        {{PAGE_CONTENT}}
    </div>
    {{FOOTER_BAR}}
    {{FOOTER}}
</body>
</html>
