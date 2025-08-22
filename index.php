<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>纸条生成器 - 安全分享信息</title>
    <link rel="icon" href="./images/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="./images/favicon.svg">
    <meta name="theme-color" content="#3498db">
    <meta name="description" content="创建一个加密的临时纸条，安全地分享敏感信息">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/icon.css">
    <link rel="stylesheet" href="css/animations.css">
    <link rel="stylesheet" href="css/fontawesome.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="decoration-dot dot-1"></div>
    <div class="decoration-dot dot-2"></div>
    <div class="decoration-dot dot-3"></div>
    
    <div class="container slide-in-up">
        <h1 class="fade-in" style="animation-delay: 0.2s"><i class="fas fa-sticky-note"></i> 纸条生成器</h1>
        <p class="app-description fade-in" style="animation-delay: 0.4s">创建一个加密的临时纸条，安全地分享敏感信息</p>
        
        <div class="note-generator">
            <?php session_start(); 
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); ?>
            <form id="note-form">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="form-group">
                    <label for="note-content"><i class="fas fa-pen"></i> 纸条内容：</label>
                    <textarea id="note-content" name="content" rows="5" placeholder="在此输入您想要分享的内容..."></textarea>
                    <div class="char-counter">
                        <span id="char-count">0</span> 个字符
                    </div>
                </div>
                

                <div class="form-group">
                    <label for="expiry-time"><i class="fas fa-clock"></i> 过期时间：</label>
                    <select id="expiry-time" name="expiry">
                        <option value="0.5">30分钟</option>
                        <option value="1">1小时</option>
                        <option value="24" selected>1天</option>
                        <option value="72">3天</option>
                        <option value="168">7天</option>
                        <option value="720">30天</option>
                        <option value="0">永不过期</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="password-protect"><i class="fas fa-lock"></i> 密码保护：</label>
                    <div class="password-input-container">
                        <input type="password" id="password-protect" name="password" placeholder="设置访问密码（可选）" autocomplete="new-password">
                        <button type="button" class="toggle-password" tabindex="-1">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-hint">设置密码后，查看纸条时需要输入密码</div>
                </div>
                
                <div class="security-info">
                    <i class="fas fa-shield-alt"></i> 安全提示：纸条在被查看后将可以被销毁
                </div>
                
                <button type="submit" id="generate-btn">
                    <i class="fas fa-magic"></i> 生成纸条链接
                </button>
            </form>
        </div>
        
        <div id="link-container" class="link-container" style="display: none;">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h3>您的纸条链接已生成</h3>
            <div class="link-box">
                <input type="text" id="note-link" readonly>
                <button id="copy-btn"><i class="fas fa-copy"></i> 复制</button>
            </div>
            <p class="note-tip"><i class="fas fa-info-circle"></i> 此链接在设定的时间后将过期，请及时分享。</p>
            <div class="create-new">
                <button id="create-new-btn"><i class="fas fa-plus"></i> 创建新纸条</button>
            </div>
        </div>
        
        <footer>
            <p>临时纸条 &copy; <?php echo date('Y'); ?> | 安全、简单、高效的临时信息分享工具 | <a href="https://github.com/0x101008/sticker" target="_blank" rel="noopener noreferrer"><i class="fab fa-github"></i> 开源地址</a></p>
        </footer>
    </div>

    <script>
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
    </script>
    <script src="script.js"></script>
    <script>
        // 密码强度检测功能
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password-protect');
            const passwordHint = document.querySelector('.password-hint');
            
            if (passwordInput && passwordHint) {
                passwordInput.addEventListener('input', function() {
                    const password = this.value;
                    
                    if (password.length === 0) {
                        passwordHint.innerHTML = '设置密码后，查看纸条时需要输入密码';
                        passwordHint.style.color = '#777';
                        return;
                    }
                    
                    // 检查密码强度
                    let strength = 0;
                    let feedback = '';
                    
                    // 长度检查
                    if (password.length >= 8) {
                        strength += 1;
                    }
                    
                    // 包含数字
                    if (/\d/.test(password)) {
                        strength += 1;
                    }
                    
                    // 包含小写字母
                    if (/[a-z]/.test(password)) {
                        strength += 1;
                    }
                    
                    // 包含大写字母
                    if (/[A-Z]/.test(password)) {
                        strength += 1;
                    }
                    
                    // 包含特殊字符
                    if (/[^A-Za-z0-9]/.test(password)) {
                        strength += 1;
                    }
                    
                    // 根据强度显示不同的提示
                    if (strength <= 2) {
                        feedback = '<i class="fas fa-exclamation-circle"></i> 密码强度：弱';
                        passwordHint.style.color = '#e74c3c';
                    } else if (strength <= 4) {
                        feedback = '<i class="fas fa-check-circle"></i> 密码强度：中';
                        passwordHint.style.color = '#f39c12';
                    } else {
                        feedback = '<i class="fas fa-shield-alt"></i> 密码强度：强';
                        passwordHint.style.color = '#27ae60';
                    }
                    
                    passwordHint.innerHTML = feedback;
                });
            }
        });
    </script>
</body>
</html>