<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; img-src 'self' data:; font-src 'self' https://cdnjs.cloudflare.com https://use.fontawesome.com data:;">
    <title>查看纸条 - 安全分享信息</title>
    <link rel="icon" href="./images/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="./images/favicon.svg">
    <meta name="theme-color" content="#3498db">
    <meta name="description" content="查看加密的临时纸条，安全地接收敏感信息">
    <?php
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    // 启动会话，用于密码保护功能
    session_start();
    ?>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/icon.css">
    <link rel="stylesheet" href="css/animations.css">
    <!-- 移除本地fontawesome引用，只使用CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        :root {
            --bg-color: #f5f7fa;
            --text-color: #333;
            --card-bg: #fff;
            --primary-color: #3498db;
            --danger-color: #e74c3c;
        }
        
        body.dark-mode {
            --bg-color: #1a1a2e;
            --text-color: #e1e1e1;
            --card-bg: #222831;
            --primary-color: #4a9ff5;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            line-height: 1.6;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        
        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background-color: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 30px;
            font-weight: 600;
        }
        
        .note-view {
            background-color: #fff9c4;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin: 25px 0;
            position: relative;
            transform: rotate(var(--rotation, 0deg));
            border: 1px solid rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }
        
        .note-view:hover {
            transform: rotate(0deg) scale(1.01);
        }
        
        .note-content {
            font-size: 18px;
            line-height: 1.8;
            white-space: pre-wrap;
            word-wrap: break-word;
            color: #333;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 5px;
            text-shadow: 0 0 1px rgba(0,0,0,0.05);
            letter-spacing: 0.2px;
        }
        
        .note-meta {
            margin-top: 25px;
            font-size: 14px;
            color: #777;
            text-align: right;
            padding-top: 15px;
            border-top: 1px dashed rgba(0, 0, 0, 0.1);
            font-style: italic;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .note-meta div {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 5px;
        }
        
        .note-meta i {
            font-size: 12px;
            opacity: 0.7;
        }
        
        
        
        .app-description {
            text-align: center;
            color: #7f8c8d;
            margin-bottom: 30px;
            font-size: 16px;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 25px;
            color: #3498db;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            padding-left: 25px;
        }
        
        .back-link:before {
            content: "←";
            position: absolute;
            left: 0;
            transition: transform 0.3s ease;
        }
        
        .back-link:hover {
            color: #2980b9;
        }
        
        .back-link:hover:before {
            transform: translateX(-5px);
        }
        
        .copy-button {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(255, 255, 255, 0.8);
            border: none;
            border-radius: 50px;
            padding: 8px 12px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            color: #3498db;
        }
        
        .copy-button i {
            margin-right: 5px;
        }
        
        .copy-button:hover {
            background: rgba(255, 255, 255, 1);
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
        }
        
        .note-actions {
            margin-top: 30px;
            text-align: center;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
            position: relative;
        }
        
        .destroy-button {
            background: linear-gradient(to right, #e74c3c, #c0392b);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(231, 76, 60, 0.3);
            letter-spacing: 0.5px;
        }
        
        /* 分享按钮样式 */
        .share-button {
            background: linear-gradient(to right, #3498db, #2980b9);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(52, 152, 219, 0.3);
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .destroy-button:hover {
            background: linear-gradient(to right, #c0392b, #e74c3c);
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(231, 76, 60, 0.4);
        }
        
        .share-button:hover {
            background: linear-gradient(to right, #2980b9, #3498db);
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(52, 152, 219, 0.4);
        }
        
        .share-button:active {
            transform: translateY(0);
            box-shadow: 0 2px 5px rgba(52, 152, 219, 0.4);
        }
        
        .share-button i {
            margin-right: 8px;
            font-size: 16px;
        }
        
        .destroy-button:active {
            transform: translateY(0);
            box-shadow: 0 2px 5px rgba(231, 76, 60, 0.4);
        }
        
        .success-message {
            background-color: #eafaf1;
            color: #27ae60;
            padding: 25px;
            margin: 25px 0;
            border-radius: 8px;
            text-align: center;
            font-weight: 600;
            font-size: 18px;
            box-shadow: 0 5px 15px rgba(39, 174, 96, 0.1);
            border-left: 5px solid #27ae60;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-20px) rotate(var(--rotation, -1deg)); }
            to { opacity: 1; transform: translateX(0) rotate(var(--rotation, -1deg)); }
        }
        
        footer {
            margin-top: 40px;
            text-align: center;
            color: #95a5a6;
            font-size: 14px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            display: flex;
            align-items: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            transform: translateY(-20px);
            opacity: 0;
            transition: all 0.3s ease;
            z-index: 1000;
            max-width: 350px;
        }
        
        .toast.show {
            transform: translateY(0);
            opacity: 1;
        }
        
        .toast i {
            margin-right: 10px;
            font-size: 18px;
        }
        
        .toast-success {
            background-color: #2ecc71;
        }
        
        .toast-error {
            background-color: #e74c3c;
        }
        
        .toast-info {
            background-color: #3498db;
        }
        
        .toast-warning {
            background-color: #f39c12;
        }
        
        
        
        
        
        /* 改进成功消息样式 */
        .success-message-container {
            text-align: center;
            color: #2ecc71;
            font-size: 20px;
            padding: 30px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.5s ease;
        }
        
        .success-message-container i {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        /* 深色模式切换按钮 */
        .theme-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background-color: var(--card-bg);
            box-shadow: 0 3px 10px var(--shadow-color);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .theme-toggle:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px var(--shadow-color);
        }
        
        .theme-toggle i {
            font-size: 20px;
            color: var(--primary-color);
            transition: all 0.3s ease;
        }
        
        body.dark-mode .theme-toggle i {
            color: #f1c40f;
        }
        
        /* 纸条装饰元素 */
        .note-view::before {
            content: "";
            position: absolute;
            top: -5px;
            left: 50%;
            transform: translateX(-50%);
            width: 40px;
            height: 10px;
            background-color: rgba(0,0,0,0.1);
            border-radius: 0 0 5px 5px;
        }
        
        .paper-texture {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('data:image/png;base64,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');
            opacity: 0.05;
            pointer-events: none;
            z-index: 0;
        }
        
        /* 分享菜单样式 */
        .share-menu {
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
            padding: 10px 0;
            min-width: 220px;
            z-index: 100;
            display: none;
            margin-top: 10px;
            animation: fadeIn 0.3s ease;
            border: 1px solid rgba(0,0,0,0.1);
        }
        
        .share-menu.show {
            display: block;
            animation: fadeInUp 0.3s ease;
        }
        
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .share-menu-item {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: #333;
            text-decoration: none;
            transition: all 0.2s ease;
            font-size: 14px;
        }
        
        .share-menu-item:hover {
            background-color: #f5f7fa;
            color: #3498db;
        }
        
        .share-menu-item i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
            font-size: 16px;
        }
        
        .share-menu-item.facebook i {
            color: #3b5998;
        }
        
        .share-menu-item.twitter i {
            color: #1da1f2;
        }
        
        .share-menu-item.whatsapp i {
            color: #25d366;
        }
        
        .share-menu-item.email i {
            color: #ea4335;
        }
        
        .share-menu-item.linkedin i {
            color: #0077b5;
        }
        
        .share-menu-item.telegram i {
            color: #0088cc;
        }
        
        .share-menu-item.copy i {
            color: #7f8c8d;
        }
        
        .share-menu-item.copy {
            cursor: pointer;
            border-top: 1px solid rgba(0,0,0,0.05);
            margin-top: 5px;
        }
        
        /* 二维码容器 */
        .qr-code-container {
            text-align: center;
            margin-top: 20px;
            padding: 15px;
            background-color: white;
            border-radius: 8px;
            display: none;
        }
        
        .qr-code-container.show {
            display: block;
            animation: fadeIn 0.5s ease;
        }
        
        .qr-code-container img {
            max-width: 200px;
            height: auto;
        }
        
        /* 倒计时样式 */
        .countdown {
            display: inline-flex;
            align-items: center;
            background-color: rgba(0,0,0,0.05);
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 13px;
            color: #e74c3c;
            margin-top: 10px;
            font-weight: 600;
        }
        
        .countdown i {
            margin-right: 5px;
            color: #e74c3c;
        }
        
        /* 打印按钮 */
        .print-button {
            background: linear-gradient(to right, #9b59b6, #8e44ad);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(155, 89, 182, 0.3);
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .print-button i {
            margin-right: 8px;
        }
        
        .print-button:hover {
            background: linear-gradient(to right, #8e44ad, #9b59b6);
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(155, 89, 182, 0.4);
        }
        
        @media (max-width: 768px) {
            .container {
                margin: 15px;
                padding: 20px;
            }
            
            .note-view {
                padding: 25px;
                margin: 20px 0;
            }
            
            .note-content {
                font-size: 16px;
            }
            
            h1 {
                font-size: 24px;
            }
            
            .app-description {
                font-size: 14px;
            }
            
            .destroy-button, .share-button, .print-button {
                padding: 10px 20px;
                font-size: 14px;
            }
            
            .note-actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .theme-toggle {
                top: 10px;
                right: 10px;
                width: 40px;
                height: 40px;
            }
        }
        
        .image-upload-container {
            margin: 20px 0;
            border-radius: 8px;
            overflow: hidden;
        }
        
        /* 密码表单样式 */
        .password-form-container {
            background-color: var(--card-bg);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin: 25px 0;
            text-align: center;
            animation: fadeIn 0.5s ease;
        }
        
        .password-form-container h2 {
            color: var(--primary-color);
            margin-bottom: 15px;
            font-size: 24px;
        }
        
        .password-form-container p {
            margin-bottom: 20px;
            color: var(--text-color);
        }
        
        .password-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
            max-width: 300px;
            margin: 0 auto;
        }
        
        .password-form input {
            padding: 12px 15px;
            border-radius: 8px;
            border: 1px solid rgba(0,0,0,0.1);
            font-size: 16px;
            width: 100%;
            background-color: var(--bg-color);
            color: var(--text-color);
        }
        
        .submit-password {
            background: linear-gradient(to right, #3498db, #2980b9);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(52, 152, 219, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .submit-password:hover {
            background: linear-gradient(to right, #2980b9, #3498db);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(52, 152, 219, 0.4);
        }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #3498db;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .back-link:hover {
            color: #2980b9;
            text-decoration: underline;
        }
        
        .image-upload-area.has-image {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            margin: 15px 0;
            border: 1px solid rgba(0,0,0,0.05);
        }
        
        .note-image {
            width: 100%;
            height: auto;
            display: block;
            border-radius: 6px;
            transition: opacity 0.3s ease;
        }
        
        .image-caption {
            background-color: rgba(0,0,0,0.05);
            padding: 8px 12px;
            font-size: 14px;
            color: #555;
            text-align: center;
            border-top: 1px solid rgba(0,0,0,0.05);
        }
        
        .no-image-text, .image-error {
            padding: 15px;
            background-color: rgba(0,0,0,0.05);
            border-radius: 8px;
            text-align: center;
            color: var(--text-color);
            font-size: 14px;
            margin: 10px 0;
            border: 1px dashed rgba(0,0,0,0.1);
        }
        
        .image-error {
            padding: 30px;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 500;
            color: #e74c3c;
        }
        
        /* 打印样式 */
        @media print {
            body {
                background: white;
                font-size: 12pt;
            }
            
            .container {
                box-shadow: none;
                margin: 0;
                padding: 0;
                max-width: 100%;
            }
            
            .note-view {
                box-shadow: none;
                transform: none !important;
            }
            
            .note-actions, .back-link, .copy-button, .theme-toggle, footer {
                display: none !important;
            }
            
            .note-content {
                font-size: 14pt;
            }
        }
    </style>
</head>
<body>
    <div class="decoration-dot dot-1"></div>
    <div class="decoration-dot dot-2"></div>
    <div class="decoration-dot dot-3"></div>
    
    <div class="container slide-in-up">
        <h1 class="fade-in" style="animation-delay: 0.2s"><i class="fas fa-sticky-note"></i> 纸条内容</h1>
        <p class="app-description fade-in" style="animation-delay: 0.4s">这是一个加密的临时纸条，查看后可选择立即销毁</p>
        
        <?php
        // 获取纸条ID
        $noteId = isset($_GET['id']) ? $_GET['id'] : '';
        
        // 验证ID格式
        if (empty($noteId) || !preg_match('/^[a-f0-9]{16}$/', $noteId)) {
            echo '<div class="error-message">
                    <p>无效的纸条ID</p>
                  </div>';
        } else {
            // 尝试读取纸条文件
            $filePath = "notes/$noteId.json";
            
            if (file_exists($filePath)) {
                $noteData = json_decode(file_get_contents($filePath), true);
                
                // 检查是否过期
                $isExpired = false;
                if ($noteData['expiry'] > 0 && time() > $noteData['expiry']) {
                    $isExpired = true;
                    echo '<div class="error-message">
                            <p><i class="fas fa-exclamation-circle"></i> 此纸条已过期</p>
                          </div>';
                } elseif (isset($_GET['destroy']) && $_GET['destroy'] == 1) {
                    // 销毁纸条
                    unlink($filePath);
                    echo '<div class="success-message-container">
                            <i class="fas fa-check-circle"></i>
                            <p>纸条已成功销毁！</p>
                          </div>';
                } else {
                    // 检查是否需要密码
                    if ($noteData['has_password'] && !isset($_SESSION['note_auth_'.$noteId])) {
                        // 如果提交了密码，验证密码
                        if (isset($_POST['note_password'])) {
                            if (password_verify($_POST['note_password'], $noteData['password_hash'])) {
                                // 密码正确，设置会话
                                $_SESSION['note_auth_'.$noteId] = true;
                            } else {
                                // 密码错误，显示错误信息
                                echo '<div class="error-message">
                                        <p><i class="fas fa-exclamation-circle"></i> 密码错误，请重试</p>
                                      </div>';
                                // 显示密码输入表单
                                echo '<div class="password-form-container">
                                        <h2><i class="fas fa-lock"></i> 此纸条受密码保护</h2>
                                        <p>请输入密码查看内容</p>
                                        <form method="post" class="password-form">
                                            <div class="form-group">
                                                <input type="password" name="note_password" placeholder="输入密码" required>
                                            </div>
                                            <button type="submit" class="submit-password">
                                                <i class="fas fa-unlock"></i> 验证密码
                                            </button>
                                        </form>
                                      </div>';
                                echo '<a href="index.php" class="back-link">返回创建新纸条</a>';
                                exit;
                            }
                        } else {
                            // 显示密码输入表单
                            echo '<div class="password-form-container">
                                    <h2><i class="fas fa-lock"></i> 此纸条受密码保护</h2>
                                    <p>请输入密码查看内容</p>
                                    <form method="post" class="password-form">
                                        <div class="form-group">
                                            <input type="password" name="note_password" placeholder="输入密码" required>
                                        </div>
                                        <button type="submit" class="submit-password">
                                            <i class="fas fa-unlock"></i> 验证密码
                                        </button>
                                    </form>
                                  </div>';
                            echo '<a href="index.php" class="back-link">返回创建新纸条</a>';
                            exit;
                        }
                    }
                    
                    // 显示纸条内容
                    $rotation = rand(-2, 2);
                    echo '<div class="note-view" style="--rotation: ' . $rotation . 'deg">
                        <div class="paper-texture"></div>';
                    
                    // 复制按钮
                    echo '<button class="copy-button" id="copy-content">
                            <i class="fas fa-copy"></i> 复制
                          </button>';
                    
                    // 纸条内容
                    echo '<div class="note-content" id="note-content">' . htmlspecialchars($noteData['content']) . '</div>';
                    
                    // 图片显示
                    $imageUrl = !empty($noteData['image']) && isset($noteData['image_path']) ? $noteData['image_path'] : '';
                    error_log("Loading image from: " . $imageUrl);
                    
                    if ($imageUrl) {
                        echo '<div class="image-upload-container">
                            <div class="image-upload-area has-image">
                              <img src="' . htmlspecialchars($imageUrl) . '" class="note-image lazy-load" 
                                   onload="this.classList.add(\'loaded\')" 
                                   onerror="this.style.display=\'none\'; console.error(\'图片加载失败\', this.src)" 
                                   alt="纸条图片" crossorigin="anonymous">
                              <div class="image-caption"><i class="fas fa-paperclip"></i> 附件图片</div>
                            </div>
                           </div>';
                    }
                    // 删除空白图片容器
                    
                    // 纸条元数据
                    echo '<div class="note-meta">';
                    
                    // 创建时间
                    $createdDate = date('Y-m-d H:i', $noteData['created']);
                    echo '<div><i class="far fa-calendar-plus"></i> 创建于: ' . $createdDate . '</div>';
                    
                    // 过期时间
                    if ($noteData['expiry'] > 0) {
                        $expiryDate = date('Y-m-d H:i', $noteData['expiry']);
                        echo '<div><i class="far fa-calendar-times"></i> 过期于: ' . $expiryDate . '</div>';
                    } else {
                        echo '<div><i class="far fa-clock"></i> 永不过期</div>';
                    }
                    
                    // 移除倒计时显示
                    
                    echo '</div>'; // 结束 note-meta
                    echo '</div>'; // 结束 note-view
                    
                    // 操作按钮
                    echo '<div class="note-actions">';
                    
                    // 销毁按钮
                    echo '<button class="destroy-button" onclick="confirmDestroy(\'' . $noteId . '\')">
                            <i class="fas fa-trash-alt"></i> 立即销毁
                          </button>';
                    
                    // 分享按钮
                    echo '<button class="share-button" id="share-button">
                            <i class="fas fa-share-alt"></i> 分享纸条
                          </button>';
                    
                    // 打印按钮
                    echo '<button class="print-button" id="print-button">
                            <i class="fas fa-print"></i> 打印
                          </button>';
                    
                    // 分享菜单
                    echo '<div class="share-menu" id="share-menu">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=' . urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) . '" target="_blank" class="share-menu-item facebook">
                                <i class="fab fa-facebook-f"></i> Facebook
                            </a>
                            <a href="https://twitter.com/intent/tweet?url=' . urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) . '&text=' . urlencode('查看这个加密的临时纸条') . '" target="_blank" class="share-menu-item twitter">
                                <i class="fab fa-twitter"></i> Twitter
                            </a>
                            <a href="https://api.whatsapp.com/send?text=' . urlencode('查看这个加密的临时纸条: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) . '" target="_blank" class="share-menu-item whatsapp">
                                <i class="fab fa-whatsapp"></i> WhatsApp
                            </a>
                            <a href="https://www.linkedin.com/sharing/share-offsite/?url=' . urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) . '" target="_blank" class="share-menu-item linkedin">
                                <i class="fab fa-linkedin-in"></i> LinkedIn
                            </a>
                            <a href="https://t.me/share/url?url=' . urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) . '&text=' . urlencode('查看这个加密的临时纸条') . '" target="_blank" class="share-menu-item telegram">
                                <i class="fab fa-telegram-plane"></i> Telegram
                            </a>
                            <a href="mailto:?subject=' . urlencode('查看这个加密的临时纸条') . '&body=' . urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) . '" class="share-menu-item email">
                                <i class="fas fa-envelope"></i> 电子邮件
                            </a>
                            <div class="share-menu-item copy" id="copy-link">
                                <i class="fas fa-link"></i> 复制链接
                            </div>
                          </div>';
                    
                    echo '</div>'; // 结束 note-actions
                }
            } else {
                echo '<div class="error-message">
                        <p><i class="fas fa-exclamation-triangle"></i> 纸条不存在或已被销毁</p>
                      </div>';
            }
        }
        ?>

        <a href="index.php" class="back-link">返回创建新纸条</a>

        <footer>
            <p>临时纸条 &copy; <?php echo date('Y'); ?> | 安全、简单、高效的临时信息分享工具 | <a href="https://github.com/0x101008/sticker" target="_blank" rel="noopener noreferrer"><i class="fab fa-github"></i> 开源地址</a></p>
        </footer>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // 调试函数
        function debugElement(id, name) {
            const element = document.getElementById(id);
            if (element) {
                console.log(`${name} 已找到:`, element);
            } else {
                console.error(`${name} 未找到!`);
            }
        }
        
        // 调试关键元素
        debugElement('share-button', '分享按钮');
        debugElement('share-menu', '分享菜单');
        
        // 复制纸条内容
        const copyContentBtn = document.getElementById('copy-content');
        if (copyContentBtn) {
            copyContentBtn.addEventListener('click', function() {
                const content = document.getElementById('note-content').innerText;
                navigator.clipboard.writeText(content).then(function() {
                    showToast('内容已复制到剪贴板', 'success');
                }, function() {
                    showToast('复制失败，请手动复制', 'error');
                });
            });
        }
        
        // 分享菜单
        const shareBtn = document.getElementById('share-button');
        const shareMenu = document.getElementById('share-menu');
        if (shareBtn && shareMenu) {
            // 设置分享菜单的位置
            function positionShareMenu() {
                // 使用CSS中的居中定位，不需要额外的JavaScript定位逻辑
                // 但确保菜单不会超出屏幕边界
                const menuRect = shareMenu.getBoundingClientRect();
                const viewportWidth = window.innerWidth;
                
                // 如果菜单超出屏幕右侧
                if (menuRect.right > viewportWidth) {
                    const overflow = menuRect.right - viewportWidth;
                    shareMenu.style.transform = `translateX(calc(-50% - ${overflow + 10}px))`;
                }
                // 如果菜单超出屏幕左侧
                else if (menuRect.left < 0) {
                    const overflow = Math.abs(menuRect.left);
                    shareMenu.style.transform = `translateX(calc(-50% + ${overflow + 10}px))`;
                }
            }
            
            shareBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // 先添加show类，然后再调整位置
                shareMenu.classList.toggle('show');
                
                // 确保菜单可见后再调整位置
                if (shareMenu.classList.contains('show')) {
                    // 使用setTimeout确保DOM已更新
                    setTimeout(() => {
                        positionShareMenu();
                    }, 10);
                }
                
                console.log('分享按钮被点击，菜单状态:', shareMenu.classList.contains('show'));
            });
            
            // 点击其他区域关闭菜单
            document.addEventListener('click', function(e) {
                if (shareMenu.classList.contains('show') && !shareMenu.contains(e.target) && e.target !== shareBtn) {
                    shareMenu.classList.remove('show');
                }
            });
            
            // 复制链接
            const copyLinkBtn = document.getElementById('copy-link');
            if (copyLinkBtn) {
                copyLinkBtn.addEventListener('click', function() {
                    navigator.clipboard.writeText(window.location.href).then(function() {
                        showToast('链接已复制到剪贴板', 'success');
                        shareMenu.classList.remove('show');
                    }, function() {
                        // 回退方案：创建临时输入框
                        const tempInput = document.createElement('input');
                        tempInput.value = window.location.href;
                        document.body.appendChild(tempInput);
                        tempInput.select();
                        document.execCommand('copy');
                        document.body.removeChild(tempInput);
                        showToast('链接已复制到剪贴板', 'success');
                        shareMenu.classList.remove('show');
                    });
                });
            }
            
            // 添加原生分享API支持
            if (navigator.share) {
                const nativeShareBtn = document.createElement('div');
                nativeShareBtn.className = 'share-menu-item native-share';
                nativeShareBtn.innerHTML = '<i class="fas fa-share-alt"></i> 使用系统分享';
                shareMenu.insertBefore(nativeShareBtn, shareMenu.firstChild);
                
                nativeShareBtn.addEventListener('click', function() {
                    navigator.share({
                        title: '查看这个加密的临时纸条',
                        url: window.location.href
                    }).then(() => {
                        shareMenu.classList.remove('show');
                    }).catch(err => {
                        console.log('分享失败:', err);
                    });
                });
            }
        }
        
        // 自定义提示框函数
        function showToast(message, type = "info") {
            // 移除现有的提示框
            const existingToasts = document.querySelectorAll(".toast");
            existingToasts.forEach(toast => {
                document.body.removeChild(toast);
            });
            
            // 创建新的提示框
            const toast = document.createElement("div");
            toast.className = `toast toast-${type}`;
            
            // 根据类型设置图标
            let icon = "info-circle";
            if (type === "success") icon = "check-circle";
            if (type === "error") icon = "exclamation-circle";
            if (type === "warning") icon = "exclamation-triangle";
            
            toast.innerHTML = `
                <i class="fas fa-${icon}"></i>
                <span>${message}</span>
            `;
            
            document.body.appendChild(toast);
            
            // 显示提示框
            setTimeout(() => {
                toast.classList.add("show");
            }, 10);
            
            // 自动隐藏提示框
            setTimeout(() => {
                toast.classList.remove("show");
                setTimeout(() => {
                    if (document.body.contains(toast)) {
                        document.body.removeChild(toast);
                    }
                }, 300);
            }, 3000);
        }
    });
        // 深色模式切换
        const themeToggle = document.getElementById('theme-toggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', function() {
                document.body.classList.toggle('dark-mode');
                localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
                
                // 切换图标
                const icon = themeToggle.querySelector('i');
                if (document.body.classList.contains('dark-mode')) {
                    icon.classList.replace('fa-moon', 'fa-sun');
                } else {
                    icon.classList.replace('fa-sun', 'fa-moon');
                }
            });
            
            // 检查本地存储中的设置
            if (localStorage.getItem('darkMode') === 'true') {
                document.body.classList.add('dark-mode');
                const icon = themeToggle.querySelector('i');
                icon.classList.replace('fa-moon', 'fa-sun');
            }
        }
        
        // 倒计时功能
        const countdownElement = document.getElementById('countdown-timer');
        if (countdownElement) {
            const expiryTime = <?php echo $noteData['expiry'] ?? 0; ?>;
            const currentTime = Math.floor(Date.now() / 1000);
            
            if (expiryTime > currentTime) {
                updateCountdown();
                const countdownInterval = setInterval(updateCountdown, 1000);
                
                function updateCountdown() {
                    const now = Math.floor(Date.now() / 1000);
                    const remaining = expiryTime - now;
                    
                    if (remaining <= 0) {
                        clearInterval(countdownInterval);
                        countdownElement.textContent = '已过期';
                        return;
                    }
                    
                    const days = Math.floor(remaining / (3600 * 24));
                    const hours = Math.floor((remaining % (3600 * 24)) / 3600);
                    const minutes = Math.floor((remaining % 3600) / 60);
                    const seconds = remaining % 60;
                    
                    let countdownText = '';
                    if (days > 0) countdownText += `${days}天 `;
                    if (hours > 0 || days > 0) countdownText += `${hours}小时 `;
                    if (minutes > 0 || hours > 0 || days > 0) countdownText += `${minutes}分钟 `;
                    countdownText += `${seconds}秒`;
                    
                    countdownElement.textContent = countdownText;
                }
            }
        }
        

        // 打印功能
        const printButton = document.getElementById('print-button');
        if (printButton) {
            printButton.addEventListener('click', function() {
                window.print();
            });
        }
    </script>
    <script>
    // 临时添加confirmDestroy函数
    function confirmDestroy(noteId) {
        if (confirm('确定要销毁这张纸条吗？此操作不可撤销！')) {
            window.location.href = '?id=' + noteId + '&destroy=1';
        }
    }
    
    // 密码输入表单功能
    document.addEventListener('DOMContentLoaded', function() {
        const passwordInput = document.querySelector('.password-form input[type="password"]');
        const passwordForm = document.querySelector('.password-form');
        
        if (passwordForm) {
            // 添加密码显示/隐藏按钮
            const toggleBtn = document.createElement('button');
            toggleBtn.type = 'button';
            toggleBtn.className = 'toggle-password';
            toggleBtn.innerHTML = '<i class="fas fa-eye"></i>';
            toggleBtn.tabIndex = -1;
            
            if (passwordInput) {
                // 将按钮添加到密码输入框旁边
                passwordInput.parentNode.style.position = 'relative';
                passwordInput.style.paddingRight = '40px';
                passwordInput.parentNode.appendChild(toggleBtn);
                
                // 添加切换密码可见性的功能
                toggleBtn.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    
                    // 切换图标
                    const icon = this.querySelector('i');
                    if (type === 'password') {
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    } else {
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    }
                });
                
                // 自动聚焦密码输入框
                passwordInput.focus();
            }
        }
    });
    </script>
    <script src="script.js"></script>
    <script src="js/fontawesome-fix.js"></script>
</body>
</html>