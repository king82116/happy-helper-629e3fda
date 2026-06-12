<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <link rel="icon" type="image/svg+xml" href="https://joshgame.online/logo.png">
    <title>Prime Tech with Rohit | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #FFD700;
            /* Gold */
            --secondary: #FFA500;
            /* Orange */
            --accent: #FF8C00;
            /* Dark orange */
            --text: #333;
            --text-light: #666;
            --white: #fff;
            --glass: rgba(255, 255, 255, 0.25);
            --glass-border: rgba(255, 255, 255, 0.4);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body.login-page {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            min-height: 100vh;
            min-height: -webkit-fill-available;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1rem;
            padding-bottom: calc(1rem + env(safe-area-inset-bottom));
            position: relative;
            overflow-x: hidden;
            overflow-y: auto;
        }

        body::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            top: -50%;
            left: -50%;
            background:
                radial-gradient(circle, transparent 20%, var(--glass) 20%, transparent 30%),
                linear-gradient(45deg, transparent 30%, var(--glass) 30%, transparent 70%);
            background-size: 60px 60px;
            opacity: 0.3;
            animation: animate 45s linear infinite;
            z-index: 0;
        }

        @keyframes animate {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .login-container {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 380px;
            border: 1px solid var(--glass-border);
            position: relative;
            z-index: 1;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .brand-wrapper {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .brand-wrapper h2 {
            color: var(--text);
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .brand-wrapper p {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 0.85rem 1rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 0.75rem;
            font-size: 16px;
            background: rgba(255, 255, 255, 0.7);
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 2px rgba(255, 140, 0, 0.2);
            background: var(--white);
        }

        .btn-login {
            width: 100%;
            padding: 0.9rem;
            border: none;
            border-radius: 0.75rem;
            background: linear-gradient(to right, var(--primary), var(--accent));
            color: var(--text);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 165, 0, 0.3);
        }

        .support-section {
            margin-top: 1.5rem;
            text-align: center;
            padding-top: 1rem;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }

        .support-text {
            color: var(--text-light);
            font-size: 0.8rem;
            margin-bottom: 1rem;
        }

        .support-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #25D366, #128C7E);
            color: white;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .support-link:hover {
            transform: scale(1.1);
        }

        .footer {
            position: relative;
            width: 100%;
            text-align: center;
            padding: 1rem;
            color: var(--white);
            font-size: 0.8rem;
            z-index: 2;
            margin-top: auto;
        }

        .footer a {
            color: inherit;
            text-decoration: none;
            font-weight: 600;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 1.5rem;
            }

            .brand-wrapper h2 {
                font-size: 1.3rem;
            }

            .form-control {
                padding: 0.75rem;
            }

            .btn-login {
                padding: 0.8rem;
            }
        }
    </style>
    <link rel="stylesheet" href="css/mobile-responsive.css">
</head>

<body class="login-page">
    <div class="login-container">
        <div class="brand-wrapper">
            <h2>Login</h2>
            <p>Prime Tech with Rohit Admin Panel</p>
        </div>

        <form action="maulyikarisalu.php" method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" id="username" name="username" required
                    placeholder="Enter username" value="admin">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" required
                    placeholder="Enter password" value="admin">
            </div>

            <button type="submit" class="btn-login">
                <span>Login</span>
                <i class="fas fa-arrow-right"></i>
            </button>
        </form>

        <div class="support-section">
            <div class="support-text">Need help? Contact us</div>
            <a href="https://t.me/primetechwithrohityt" target="_blank" class="support-link">
                <i class="fab fa-telegram"></i>
            </a>
        </div>
    </div>

    <div class="footer">
        &copy; <span id="year"></span> <a href="#">Prime Tech with Rohit</a> | All Rights Reserved
    </div>

    <script>
        document.getElementById('year').textContent = new Date().getFullYear();
    </script>
</body>

</html>