<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Buyer Registration - The Brain</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .signup-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
            position: relative;
        }
        
        .signup-header {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            padding: 2rem;
            text-align: center;
            position: relative;
        }
        
        .logo {
            height: 100px;
            margin-bottom: 1rem;
            filter: brightness(1.2);
        }
        
        .signup-header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .signup-header p {
            opacity: 0.9;
            font-size: 1rem;
        }
        
        .signup-form {
            padding: 2rem;
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
            padding: 0.875rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.2s ease;
            background: #f9fafb;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #3b82f6;
            background: white;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .checkbox-group {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            margin-top: 1rem;
        }
        
        .checkbox-input {
            margin-top: 0.25rem;
            transform: scale(1.2);
        }
        
        .checkbox-label {
            font-size: 0.9rem;
            color: #6b7280;
            line-height: 1.5;
        }
        
        .checkbox-label a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 600;
        }
        
        .checkbox-label a:hover {
            text-decoration: underline;
        }
        
        .submit-btn {
            width: 100%;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 1rem;
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.3);
        }
        
        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 0.9rem;
        }
        
        .login-link a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 600;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
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
        }
        
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #e5e7eb;
            border-top: 4px solid #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .features {
            background: #f8fafc;
            padding: 1.5rem;
            margin: -2rem -2rem 2rem -2rem;
        }
        
        .features h3 {
            color: #374151;
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .feature-list {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            font-size: 0.85rem;
            color: #6b7280;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .feature-icon {
            color: #10b981;
            font-weight: bold;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .feature-list {
                grid-template-columns: 1fr;
            }
            
            .signup-container {
                margin: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <div class="signup-header">
            <img src="https://quotingfast.com/whitelogo" alt="QuotingFast" class="logo" onerror="this.style.display='none';">
            <h1>Join The Brain</h1>
            <p>Start receiving high-quality insurance leads today</p>
        </div>
        
        <form class="signup-form" id="signupForm">
            <div class="features">
                <h3>🚀 What You Get:</h3>
                <div class="feature-list">
                    <div class="feature-item">
                        <span class="feature-icon">✓</span>
                        Real-time lead delivery
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">✓</span>
                        Quality guarantee
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">✓</span>
                        24/7 support
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">✓</span>
                        Advanced analytics
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">✓</span>
                        Flexible pricing
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">✓</span>
                        Easy returns
                    </div>
                </div>
            </div>
            
            <div id="alertContainer"></div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">First Name *</label>
                    <input type="text" name="first_name" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Last Name *</label>
                    <input type="text" name="last_name" class="form-input" required>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Company Name</label>
                <input type="text" name="company" class="form-input" placeholder="Optional">
            </div>
            
            <div class="form-group">
                <label class="form-label">Email Address *</label>
                <input type="email" name="email" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Phone Number *</label>
                <input type="tel" name="phone" class="form-input" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Password *</label>
                    <input type="password" name="password" class="form-input" required minlength="8">
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm Password *</label>
                    <input type="password" name="password_confirmation" class="form-input" required>
                </div>
            </div>
            
            <div class="checkbox-group">
                <input type="checkbox" id="terms" name="terms_accepted" class="checkbox-input" required>
                <label for="terms" class="checkbox-label">
                    I agree to the <a href="#" target="_blank">Terms of Service</a> and <a href="#" target="_blank">Privacy Policy</a>. 
                    I understand that leads are non-refundable after 24 hours and that I am responsible for maintaining sufficient account balance.
                </label>
            </div>
            
            <button type="submit" class="submit-btn" id="submitBtn">
                🚀 Create My Account
            </button>
            
            <div class="login-link">
                Already have an account? <a href="/buyer/login">Sign in here</a>
            </div>
        </form>
        
        <div class="loading" id="loadingOverlay">
            <div class="spinner"></div>
            <p>Creating your account...</p>
        </div>
    </div>

    <script>
        document.getElementById('signupForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = document.getElementById('submitBtn');
            const loadingOverlay = document.getElementById('loadingOverlay');
            const alertContainer = document.getElementById('alertContainer');
            
            // Clear previous alerts
            alertContainer.innerHTML = '';
            
            // Show loading
            submitBtn.disabled = true;
            loadingOverlay.style.display = 'block';
            
            try {
                const response = await fetch('/buyer/signup', {
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
                    
                    // Reset form
                    this.reset();
                    
                    // Redirect after 3 seconds
                    setTimeout(() => {
                        window.location.href = '/buyer/login';
                    }, 3000);
                    
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
                        <strong>Error:</strong> Registration failed. Please try again.
                    </div>
                `;
            } finally {
                submitBtn.disabled = false;
                loadingOverlay.style.display = 'none';
            }
        });
        
        // Password confirmation validation
        const password = document.querySelector('input[name="password"]');
        const confirmPassword = document.querySelector('input[name="password_confirmation"]');
        
        confirmPassword.addEventListener('input', function() {
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Passwords do not match');
            } else {
                confirmPassword.setCustomValidity('');
            }
        });
    </script>
</body>
</html>