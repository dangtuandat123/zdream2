<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Lỗi hệ thống | ZDream</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #000000;
            color: white;
            padding: 24px;
            text-align: center;
        }
        body::before {
            content: '';
            position: fixed;
            top: 0; left: 25%;
            width: 300px; height: 300px;
            background: rgba(239, 68, 68, 0.15);
            border-radius: 50%;
            filter: blur(100px);
            z-index: -1;
        }
        body::after {
            content: '';
            position: fixed;
            bottom: 0; right: 0;
            width: 250px; height: 250px;
            background: rgba(249, 115, 22, 0.1);
            border-radius: 50%;
            filter: blur(80px);
            z-index: -1;
        }
        .icon {
            width: 100px; height: 100px;
            background: linear-gradient(135deg, rgba(239,68,68,0.2), rgba(249,115,22,0.2));
            border: 1px solid rgba(239,68,68,0.3);
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
        }
        .icon i { font-size: 40px; color: #ef4444; }
        h1 {
            font-size: 72px;
            font-weight: 800;
            background: linear-gradient(135deg, #ef4444, #f97316);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
        }
        h2 { font-size: 24px; margin-bottom: 8px; }
        p { color: rgba(255,255,255,0.5); margin-bottom: 32px; max-width: 400px; }
        a {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 14px 28px;
            background: linear-gradient(135deg, #a855f7, #ec4899);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            box-shadow: 0 4px 20px rgba(168, 85, 247, 0.3);
            transition: all 0.3s;
        }
        a:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(168, 85, 247, 0.5);
        }
    </style>
</head>
<body>
    <div class="icon">
        <i class="fa-solid fa-triangle-exclamation"></i>
    </div>
    <h1>500</h1>
    <h2>Lỗi hệ thống</h2>
    <p>Đã xảy ra lỗi không mong muốn. Vui lòng thử lại sau hoặc liên hệ hỗ trợ.</p>
    <a href="/">
        <i class="fa-solid fa-house"></i>
        Về trang chủ
    </a>
</body>
</html>
