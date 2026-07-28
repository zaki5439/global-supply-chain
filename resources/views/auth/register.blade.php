<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Risk Intelligence</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { 
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo i {
            font-size: 48px;
            background: linear-gradient(135deg, #818cf8, #c084fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .logo h2 {
            color: #fff;
            font-weight: 800;
            margin-top: 10px;
            letter-spacing: -0.5px;
        }
        
        .logo p {
            color: #94a3b8;
            font-size: 14px;
        }
        
        .form-label {
            color: #cbd5e1;
            font-weight: 500;
            font-size: 14px;
            margin-bottom: 8px;
        }
        
        .form-control {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
            padding: 12px 16px;
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            background: rgba(15, 23, 42, 0.8);
            border-color: #818cf8;
            box-shadow: 0 0 0 4px rgba(129, 140, 248, 0.15);
            color: #fff;
        }
        
        /* Fix webkit autofill styling */
        input:-webkit-autofill,
        input:-webkit-autofill:hover, 
        input:-webkit-autofill:focus, 
        input:-webkit-autofill:active{
            -webkit-box-shadow: 0 0 0 30px #0f172a inset !important;
            -webkit-text-fill-color: white !important;
            transition: background-color 5000s ease-in-out 0s;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border: none;
            color: white;
            padding: 14px;
            border-radius: 12px;
            font-weight: 600;
            width: 100%;
            margin-top: 16px;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -10px rgba(99, 102, 241, 0.5);
        }
        
        .demo-accounts {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .demo-accounts h6 {
            color: #94a3b8;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 16px;
            text-align: center;
        }
        
        .demo-badge {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 12px;
            color: #cbd5e1;
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .demo-badge:hover {
            background: rgba(255,255,255,0.1);
        }
        
        .text-danger {
            color: #f87171 !important;
            font-size: 13px;
            margin-top: 5px;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="glass-card">
            <div class="logo">
                <i class="bi bi-shield-lock-fill"></i>
                <h2>Risk Intel</h2>
                <p>Global Supply Chain Command Center</p>
            </div>
            
            <form method="POST" action="{{ route('register') }}">
                @csrf
                
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <div class="position-relative">
                        <i class="bi bi-person position-absolute top-50 translate-middle-y" style="left: 16px; color: #94a3b8; font-size: 1.1rem; z-index: 4;"></i>
                        <input type="text" class="form-control position-relative" name="name" value="{{ old('name') }}" required autofocus placeholder="John Doe" style="padding-left: 44px; z-index: 2;">
                    </div>
                    @error('name')
                        <div class="text-danger"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <div class="position-relative">
                        <i class="bi bi-envelope position-absolute top-50 translate-middle-y" style="left: 16px; color: #94a3b8; font-size: 1.1rem; z-index: 4;"></i>
                        <input type="email" class="form-control position-relative" name="email" value="{{ old('email') }}" required placeholder="user@riskintel.com" style="padding-left: 44px; z-index: 2;">
                    </div>
                    @error('email')
                        <div class="text-danger"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <div class="position-relative">
                        <i class="bi bi-key position-absolute top-50 translate-middle-y" style="left: 16px; color: #94a3b8; font-size: 1.1rem; z-index: 4;"></i>
                        <input type="password" class="form-control position-relative" name="password" required placeholder="••••••••" style="padding-left: 44px; z-index: 2;">
                    </div>
                    @error('password')
                        <div class="text-danger"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-4">
                    <label class="form-label">Confirm Password</label>
                    <div class="position-relative">
                        <i class="bi bi-check2-circle position-absolute top-50 translate-middle-y" style="left: 16px; color: #94a3b8; font-size: 1.1rem; z-index: 4;"></i>
                        <input type="password" class="form-control position-relative" name="password_confirmation" required placeholder="••••••••" style="padding-left: 44px; z-index: 2;">
                    </div>
                </div>
                
                <button type="submit" class="btn-login mb-4">
                    Create Account <i class="bi bi-person-plus ms-1"></i>
                </button>

                <div class="text-center">
                    <span class="text-secondary">Already have an account?</span> 
                    <a href="{{ route('login') }}" class="text-primary text-decoration-none fw-bold">Sign In</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
