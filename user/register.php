<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up - Kakaw & Capital Chicken</title>
  <style>
    :root {
      --primary: #F7B801;
      --text: #333333;
      --background: #ffffff;
      --light-gray: #f2f2f2;
      --border: #e0e0e0;
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: var(--light-gray);
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .form-container {
      background-color: var(--background);
      padding: 30px;
      border-radius: 10px;
      width: 90%;
      max-width: 400px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    h2 {
      color: var(--text);
      text-align: center;
      margin-bottom: 20px;
    }

    input[type="text"],
    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding: 12px;
      margin-bottom: 14px;
      border: 1px solid var(--border);
      border-radius: 6px;
      font-size: 14px;
    }

    button {
      width: 100%;
      padding: 12px;
      background-color: var(--primary);
      color: white;
      border: none;
      border-radius: 6px;
      font-size: 16px;
      cursor: pointer;
    }

    button:hover {
      background-color: #e0a700;
    }

    .link {
      text-align: center;
      margin-top: 12px;
      font-size: 14px;
    }

    .link a {
      color: var(--primary);
      text-decoration: none;
    }
  </style>
</head>
<body>
  <div class="form-container">
    <h2>Create Your Account</h2>
      <form method="POST" action="register_process.php">
        <input type="text" name="full_name" placeholder="Full Name" required />
        <input type="text" name="mobile" placeholder="Mobile Number" required />
        <input type="text" name="address" placeholder="Delivery Address" required />
        <input type="email" name="email" placeholder="Email" required />
        <input type="password" name="password" placeholder="Password" required />
        <button type="submit">Sign Up</button>
      </form>
    <div class="link">
      Already have an account? <a href="login.php">Log In</a>
    </div>
  </div>
</body>
</html>
