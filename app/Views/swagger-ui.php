<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>API Documentation - Transfer System</title>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist@4.5.0/swagger-ui.css" />
    <link rel="icon" type="image/png" href="https://unpkg.com/swagger-ui-dist@4.5.0/favicon-32x32.png" sizes="32x32" />
    <link rel="icon" type="image/png" href="https://unpkg.com/swagger-ui-dist@4.5.0/favicon-16x16.png" sizes="16x16" />
    <style>
        html {
            box-sizing: border-box;
            overflow: -moz-scrollbars-vertical;
            overflow-y: scroll;
        }
        
        *,
        *:before,
        *:after {
            box-sizing: inherit;
        }
        
        body {
            margin: 0;
            background: #fafafa;
        }
        
        .swagger-ui .topbar {
            background-color: #1976d2;
        }
        
        .swagger-ui .info .title small.version-stamp {
            background-color: #1976d2;
        }
        
        .swagger-ui .opblock.opblock-post .opblock-summary-method {
            background: #1976d2;
        }
        
        .swagger-ui .btn.execute {
            background-color: #1976d2;
        }
        
        .swagger-ui .btn.authorize {
            color: #1976d2;
            border-color: #1976d2;
        }
        
        .swagger-ui .btn.authorize svg {
            fill: #1976d2;
        }
    </style>
</head>

<body>
    <div id="swagger-ui"></div>

    <script src="https://unpkg.com/swagger-ui-dist@4.5.0/swagger-ui-bundle.js" charset="UTF-8"></script>
    <script src="https://unpkg.com/swagger-ui-dist@4.5.0/swagger-ui-standalone-preset.js" charset="UTF-8"></script>
    <script>
        window.onload = function() {
            // Begin Swagger UI call region
            const ui = SwaggerUIBundle({
                url: "/openapi.json",
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "StandaloneLayout",
                docExpansion: "list",
                tagSorter: "alpha",
                operationsSorter: "alpha",
                persistAuthorization: true
            });
            // End Swagger UI call region

            window.ui = ui;
        };
    </script>
</body>
</html> 