<!DOCTYPE html>
<html>
<head>
    <block:meta>
        <meta charset="UTF-8">
    </block:meta>
    <title>${documentation.spec.info.title}</title>

    <block:stylesheets>
        <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/gh/zentlix/swagger-php/public/swagger-ui.css" />
        <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/gh/zentlix/swagger-php/public/index.css" />
    </block:stylesheets>
    <link rel="icon" type="image/png" href="https://cdn.jsdelivr.net/gh/zentlix/swagger-php/public/favicon-32x32.png" sizes="32x32" />
    <link rel="icon" type="image/png" href="https://cdn.jsdelivr.net/gh/zentlix/swagger-php/public/favicon-16x16.png" sizes="16x16" />

    <block:swagger_data>
        <script id="swagger-data" type="application/json">{!! json_encode($documentation, 65) !!}</script>
    </block:swagger_data>
</head>
<body>
    <block:swagger_data>
        <div id="swagger-ui"></div>
    </block:swagger_data>

    <block:javascripts>
        <script src="https://cdn.jsdelivr.net/gh/zentlix/swagger-php/public/swagger-ui-bundle.js" charset="UTF-8"> </script>
        <script src="https://cdn.jsdelivr.net/gh/zentlix/swagger-php/public/swagger-ui-standalone-preset.js" charset="UTF-8"> </script>
        <script src="https://cdn.jsdelivr.net/gh/zentlix/swagger-php/public/init-swagger-ui.js" charset="UTF-8"> </script>
    </block:javascripts>

    <block:swagger_initialization>
        <script type="text/javascript">
            (function () {
                var swaggerUI = {!! json_encode($swagger_ui_config, 65) !!};
                window.onload = loadSwaggerUI(swaggerUI);
            })();
        </script>
    </block:swagger_initialization>
</body>
</html>
