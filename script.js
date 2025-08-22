// 销毁确认函数
function confirmDestroy(noteId) {
    if (confirm('确定要销毁这张纸条吗？此操作不可撤销！')) {
        window.location.href = '?id=' + noteId + '&destroy=1';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const noteForm = document.getElementById('note-form');
    const linkContainer = document.getElementById('link-container');
    const noteLinkInput = document.getElementById('note-link');
    const copyBtn = document.getElementById('copy-btn');
    const noteContent = document.getElementById('note-content');
    const charCount = document.getElementById('char-count');
    const createNewBtn = document.getElementById('create-new-btn');
    const passwordInput = document.getElementById('password-protect');
    const togglePasswordBtn = document.querySelector('.toggle-password');
    

    
    // 字符计数器功能
    noteContent.addEventListener('input', function() {
        const count = this.value.length;
        charCount.textContent = count;
        
        // 根据字符数量改变颜色
        if (count > 1000) {
            charCount.style.color = '#e74c3c';
        } else if (count > 500) {
            charCount.style.color = '#f39c12';
        } else {
            charCount.style.color = '#27ae60';
        }
    });
    

    

    
    // 初始化字符计数（仅在元素存在时）
    if (noteContent && charCount) {
        charCount.textContent = noteContent.value.length;
    }
    
    // 密码显示/隐藏功能
    if (togglePasswordBtn && passwordInput) {
        togglePasswordBtn.addEventListener('click', function() {
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
    }
    
    // 表单提交处理（仅在表单存在时）
    if (noteForm) {
        noteForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // 验证表单
        if (noteContent.value.trim() === '') {
            showToast('请输入纸条内容', 'error');
            noteContent.focus();
            return;
        }
        
        // 获取表单数据
        const formData = new FormData(noteForm);
        
        // 显示加载状态
        const submitBtn = noteForm.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 生成中...';
        submitBtn.disabled = true;
        
        // 发送AJAX请求
        fetch('save_note.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            // 检查响应状态
            if (!response.ok) {
                throw new Error(`HTTP错误! 状态: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            // 恢复按钮状态
            submitBtn.innerHTML = originalBtnText;
            submitBtn.disabled = false;
            
            if (data.success) {
                // 显示生成的链接
                noteLinkInput.value = data.url;
                
                // 使用动画显示链接容器
                noteForm.style.opacity = '0';
                setTimeout(() => {
                    noteForm.style.display = 'none';
                    linkContainer.style.display = 'block';
                    setTimeout(() => {
                        linkContainer.style.opacity = '1';
                    }, 50);
                }, 300);
                
                // 滚动到链接区域
                linkContainer.scrollIntoView({ behavior: 'smooth' });
                
                // 显示成功提示
                let successMessage = '纸条创建成功！';
                if (data.has_password) {
                    successMessage += ' (已启用密码保护)';
                }
                showToast(successMessage, 'success');
            } else {
                // 显示错误信息
                showToast('生成纸条失败: ' + data.message, 'error');
            }
        })
        .catch(error => {
            // 恢复按钮状态
            submitBtn.innerHTML = originalBtnText;
            submitBtn.disabled = false;
            
            // 显示更具体的错误信息
            if (error.message.includes('Failed to fetch') || error.message.includes('ERR_CONNECTION_REFUSED')) {
                showToast('无法连接到服务器。请确保PHP服务器已启动。', 'error');
            } else {
                showToast('发生错误: ' + error.message, 'error');
            }
            console.error(error);
        });
    });
    
    // 复制链接功能 - 使用现代Clipboard API
    copyBtn.addEventListener('click', function() {
        // 选择输入框内容
        noteLinkInput.select();
        
        // 尝试使用现代Clipboard API
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(noteLinkInput.value)
                .then(() => {
                    // 显示复制成功提示
                    const originalBtnHTML = copyBtn.innerHTML;
                    copyBtn.innerHTML = '<i class="fas fa-check"></i> 已复制!';
                    
                    setTimeout(() => {
                        copyBtn.innerHTML = originalBtnHTML;
                    }, 2000);
                    
                    showToast('链接已复制到剪贴板', 'success');
                })
                .catch(err => {
                    showToast('复制失败: ' + err, 'error');
                    console.error('复制失败:', err);
                });
        } else {
            // 回退到旧方法
            try {
                const successful = document.execCommand('copy');
                if (successful) {
                    // 显示复制成功提示
                    const originalBtnHTML = copyBtn.innerHTML;
                    copyBtn.innerHTML = '<i class="fas fa-check"></i> 已复制!';
                    
                    setTimeout(() => {
                        copyBtn.innerHTML = originalBtnHTML;
                    }, 2000);
                    
                    showToast('链接已复制到剪贴板', 'success');
                } else {
                    showToast('复制失败，请手动复制', 'error');
                }
            } catch (err) {
                showToast('复制失败: ' + err, 'error');
                console.error('复制失败:', err);
            }
        }
    });
    
    // 创建新纸条按钮
    if (createNewBtn) {
        createNewBtn.addEventListener('click', function() {
            // 重置表单
            noteForm.reset();
            charCount.textContent = '0';
            charCount.style.color = '#27ae60';
            
            // 使用动画切换视图
            linkContainer.style.opacity = '0';
            setTimeout(() => {
                linkContainer.style.display = 'none';
                noteForm.style.display = 'block';
                setTimeout(() => {
                    noteForm.style.opacity = '1';
                }, 50);
            }, 300);
            
            // 聚焦到文本区域
            noteContent.focus();
        });
    }
    
    // 添加自定义提示框功能
    function showToast(message, type = 'info') {
        // 移除现有的提示框
        const existingToasts = document.querySelectorAll('.toast');
        existingToasts.forEach(toast => {
            document.body.removeChild(toast);
        });
        
        // 创建新的提示框
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        
        // 根据类型设置图标
        let icon = 'info-circle';
        if (type === 'success') icon = 'check-circle';
        if (type === 'error') icon = 'exclamation-circle';
        if (type === 'warning') icon = 'exclamation-triangle';
        
        toast.innerHTML = `
            <i class="fas fa-${icon}"></i>
            <span>${message}</span>
        `;
        
        document.body.appendChild(toast);
        
        // 显示提示框
        setTimeout(() => {
            toast.classList.add('show');
        }, 10);
        
        // 自动隐藏提示框
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                if (document.body.contains(toast)) {
                    document.body.removeChild(toast);
                }
            }, 300);
        }, 3000);
    }
    
    // 添加CSS样式
    const style = document.createElement('style');
    style.textContent = `
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
        .toast-info {
            background-color: #3498db;
        }
        .toast-success {
            background-color: #2ecc71;
        }
        .toast-warning {
            background-color: #f39c12;
        }
        .toast-error {
            background-color: #e74c3c;
        }
        .char-counter {
            text-align: right;
            font-size: 14px;
            color: #27ae60;
            margin-top: 5px;
        }
        .security-info {
            background-color: #f8f9fa;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #2c3e50;
            border-left: 3px solid #3498db;
        }
        .security-info i {
            color: #3498db;
            margin-right: 5px;
        }
        .success-icon {
            font-size: 48px;
            color: #2ecc71;
            margin-bottom: 15px;
            animation: bounceIn 0.6s;
        }
        .create-new {
            margin-top: 25px;
        }
        #create-new-btn {
            background: transparent;
            border: 2px solid #3498db;
            color: #3498db;
            padding: 10px 20px;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        #create-new-btn:hover {
            background-color: #3498db;
            color: white;
        }
        .app-description {
            color: #7f8c8d;
            margin-bottom: 30px;
            font-size: 16px;
        }
        footer {
            margin-top: 40px;
            color: #95a5a6;
            font-size: 14px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        @keyframes bounceIn {
            0% { transform: scale(0); opacity: 0; }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); opacity: 1; }
        }
    `;
    document.head.appendChild(style);
    
    // 添加页面过渡效果
    noteForm.style.transition = 'opacity 0.3s ease';
    linkContainer.style.transition = 'opacity 0.3s ease';
    linkContainer.style.opacity = '0';}})
