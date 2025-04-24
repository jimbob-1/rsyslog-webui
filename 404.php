<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
    <link href="css/dark-mode.css" rel="stylesheet">
    <style>
        .error-page {
            max-width: 500px;
            margin: 100px auto;
            text-align: center;
        }
        .error-code {
            font-size: 72px;
            font-weight: 500;
            color: #6c757d;
            margin-bottom: 0;
        }
        .error-message {
            font-size: 24px;
            margin-bottom: 30px;
            color: #343a40;
        }
        .error-face {
            fill: #d1d1d1;
        }
        .error-eyes {
            fill: #6c757d;
        }
    </style>
</head>
<body class="<?php echo isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark' ? 'theme-dark' : 'theme-light'; ?>">
    <div class="container">
        <div class="error-page">
            <div class="card shadow">
                <div class="card-body p-5">
                    <h1 class="error-code">404</h1>
                    <h2 class="error-message">Page Not Found</h2>
                    <p class="text-muted mb-4">The page you're looking for doesn't exist or has been moved.</p>
                    
                    <svg width="120" height="120" viewBox="0 0 200 200">
                        <circle cx="100" cy="100" r="90" fill="none" stroke="#e0e0e0" stroke-width="10" />
                        <g class="error-face">
                            <!-- Modified face - neutral expression instead of smile -->
                            <circle cx="70" cy="80" r="8" class="error-eyes" />
                            <circle cx="130" cy="80" r="8" class="error-eyes" />
                            <line x1="70" y1="130" x2="130" y2="130" stroke="#6c757d" stroke-width="8" stroke-linecap="round" />
                        </g>
                    </svg>
                    
                    <div class="mt-4">
                        <a href="index.php" class="btn btn-primary">Go to Homepage</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html> 