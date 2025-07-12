<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Online Ordering System</title>
  <style>
    :root {
      --primary: #F7B801;
      --text: #333333;
      --background: #ffffff;
      --light-gray: #f2f2f2;
      --border: #e0e0e0;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: var(--light-gray);
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .container {
      background-color: var(--background);
      padding: 40px 20px;
      border-radius: 12px;
      width: 90%;
      max-width: 400px;
      text-align: center;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    }

    h1 {
      color: var(--text);
      font-size: 24px;
      margin-bottom: 24px;
    }

    .btn {
      display: block;
      width: 100%;
      margin: 10px 0;
      padding: 14px;
      font-size: 16px;
      background-color: var(--primary);
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      transition: background-color 0.3s ease;
      text-decoration: none;
    }

    .btn:hover {
      background-color: #e0a700;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Kakaw & Capital Chicken</h1>
    <a href="user/login.php" class="btn">Log In</a>
    <a href="user/register.php" class="btn">Sign Up</a>
  </div>
</body>
</html>
