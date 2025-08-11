<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Buyer Login - The Brain</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
            position: relative;
        }
        
        .login-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 2.5rem 2rem;
            text-align: center;
            position: relative;
        }
        
        .logo {
            height: 100px;
            margin-bottom: 1rem;
            filter: brightness(1.2);
        }
        
        .login-header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .login-header p {
            opacity: 0.9;
            font-size: 1rem;
        }
        
        .login-form {
            padding: 2.5rem 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .form-input {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.2s ease;
            background: #f9fafb;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            font-size: 0.9rem;
        }
        
        .remember-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .checkbox-input {
            transform: scale(1.1);
        }
        
        .forgot-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        
        .forgot-link:hover {
            text-decoration: underline;
        }
        
        .submit-btn {
            width: 100%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-bottom: 1.5rem;
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }
        
        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .signup-link {
            text-align: center;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 0.9rem;
        }
        
        .signup-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .signup-link a:hover {
            text-decoration: underline;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
        
        .alert-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
        }
        
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
        }
        
        .alert-info {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1d4ed8;
        }
        
        .loading {
            display: none;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            z-index: 10;
        }
        
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #e5e7eb;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .features-preview {
            background: #f8fafc;
            padding: 1.5rem;
            margin: -2.5rem -2rem 2rem -2rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .features-preview h3 {
            color: #374151;
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .feature-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            text-align: center;
        }
        
        .stat-item {
            padding: 0.75rem;
            background: white;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }
        
        .stat-number {
            font-size: 1.2rem;
            font-weight: 700;
            color: #667eea;
            display: block;
        }
        
        .stat-label {
            font-size: 0.75rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        @media (max-width: 768px) {
            .login-container {
                margin: 1rem;
            }
            
            .remember-forgot {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
            
            .feature-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <img src="https://quotingfast.com/whitelogo" alt="QuotingFast" class="logo" onerror="this.style.display='none';">
            <h1>Welcome Back</h1>
            <p>Access your lead management dashboard</p>
        </div>
        
        <form class="login-form" id="loginForm">
            <div class="features-preview">
                <h3>📊 Your Dashboard Awaits</h3>
                <div class="feature-stats">
                    <div class="stat-item">
                        <span class="stat-number">24/7</span>
                        <span class="stat-label">Lead Delivery</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">Real-time</span>
                        <span class="stat-label">Analytics</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">Quality</span>
                        <span class="stat-label">Guarantee</span>
                    </div>
                </div>
            </div>
            
            <div id="alertContainer"></div>
            
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-input" required autofocus>
            </div>
            
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-input" required>
            </div>
            
            <div class="remember-forgot">
                <div class="remember-group">
                    <input type="checkbox" id="remember" name="remember" class="checkbox-input">
                    <label for="remember">Keep me signed in</label>
                </div>
                <a href="#" class="forgot-link">Forgot password?</a>
            </div>
            
            <button type="submit" class="submit-btn" id="submitBtn">
                🚀 Sign In to Dashboard
            </button>
            
            <div class="signup-link">
                Don't have an account? <a href="/buyer/signup">Create one here</a>
            </div>
        </form>
        
        <div class="loading" id="loadingOverlay">
            <div class="spinner"></div>
            <p>Signing you in...</p>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = document.getElementById('submitBtn');
            const loadingOverlay = document.getElementById('loadingOverlay');
            const alertContainer = document.getElementById('alertContainer');
            
            // Clear previous alerts
            alertContainer.innerHTML = '';
            
            // Show loading
            submitBtn.disabled = true;
            submitBtn.textContent = '⏳ Signing in...';
            loadingOverlay.style.display = 'block';
            
            try {
                const response = await fetch('/buyer/login', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alertContainer.innerHTML = `
                        <div class="alert alert-success">
                            <strong>Success!</strong> ${result.message}
                        </div>
                    `;
                    
                    // Redirect to dashboard
                    setTimeout(() => {
                        window.location.href = result.redirect || '/buyer/dashboard';
                    }, 1000);
                    
                } else {
                    alertContainer.innerHTML = `
                        <div class="alert alert-error">
                            <strong>Error:</strong> ${result.message}
                        </div>
                    `;
                }
                
            } catch (error) {
                alertContainer.innerHTML = `
                    <div class="alert alert-error">
                        <strong>Error:</strong> Login failed. Please try again.
                    </div>
                `;
            } finally {
                if (!document.querySelector('.alert-success')) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = '🚀 Sign In to Dashboard';
                    loadingOverlay.style.display = 'none';
                }
            }
        });
        
        // Check for success message from registration
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('registered') === 'true') {
            document.getElementById('alertContainer').innerHTML = `
                <div class="alert alert-info">
                    <strong>Registration Complete!</strong> Please sign in with your new account.
                </div>
            `;
        }
    </script>
</body>
</html>