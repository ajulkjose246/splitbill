<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SplitBill - Smart Bill Splitting Made Easy</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('/css/home.css') }}" rel="stylesheet">
   
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <nav class="nav">
                <a href="#" class="logo">
                    <i class="fas fa-receipt"></i> SplitBill
                </a>
                <ul class="nav-links">
                    <li><a href="#features">Features</a></li>
                    <li><a href="#calculator">Calculator</a></li>
                    <li><a href="#about">About</a></li>
                    <li><a href="#contact">Contact</a></li>
                    
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Split Bills Like a Pro</h1>
            <p>Never worry about splitting expenses again. Our smart app makes bill splitting effortless, fair, and fun!</p>
            <div class="cta-buttons">
                
                <a href="{{ route('login') }}" class="btn btn-secondary">
                    <i class="fas fa-sign-in-alt"></i> Get Started
                </a>
            </div>
        </div>
    </section>

    
    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <h2>Why Choose SplitBill?</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h3>Lightning Fast</h3>
                    <p>Split bills instantly with our smart calculator. No more manual calculations or awkward moments.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Group Management</h3>
                    <p>Create groups, add members, and track shared expenses. Perfect for roommates, trips, and events.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <h3>Smart Analytics</h3>
                    <p>Get insights into your spending patterns and see who owes what with beautiful visualizations.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3>Mobile Friendly</h3>
                    <p>Access your bills anywhere, anytime. Works perfectly on all devices and screen sizes.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Secure & Private</h3>
                    <p>Your financial data is protected with bank-level security. Your privacy is our priority.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-sync"></i>
                    </div>
                    <h3>Real-time Sync</h3>
                    <p>All changes sync instantly across all devices. Never lose track of your shared expenses.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Payment Section with Famous Quotes -->
    

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 SplitBill. Made with <i class="fas fa-heart" style="color: #ef4444;"></i> for easy bill splitting.</p>
        </div>
    </footer>

</body>
</html>
