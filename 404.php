<?php
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/header.php'; ?>
    <title>Page Not Found - MediCare+</title>
    <style>
        .error-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        }
        .error-icon {
            font-size: 8rem;
            color: #2A7B8E;
            margin-bottom: 2rem;
        }
        .error-illustration {
            max-width: 400px;
            margin: 0 auto 2rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="error-page">
        <div class="container py-6">
            <div class="row justify-content-center text-center">
                <div class="col-md-8 col-lg-6">
                    <!-- Medical-themed SVG illustration -->
                    <svg class="error-illustration" viewBox="0 0 400 300" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M200 50L250 150H150L200 50Z" fill="#2A7B8E"/>
                        <circle cx="200" cy="170" r="30" fill="#83C5BE"/>
                        <path d="M100 250H300" stroke="#2A7B8E" stroke-width="4" stroke-linecap="round"/>
                        <path d="M180 220L220 220M200 200V240" stroke="#E29578" stroke-width="4"/>
                    </svg>

                    <h1 class="display-1 fw-bold text-primary">404</h1>
                    <h2 class="mb-4">Page Not Found</h2>
                    <p class="lead text-muted mb-4">
                        Oops! The page you're looking for seems to have taken a sick day.
                        Let's get you back to healthy navigation.
                    </p>
                    
                    <div class="d-flex justify-content-center gap-3">
                        <a href="index.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-home me-2"></i>Return Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>