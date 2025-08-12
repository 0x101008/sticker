// Font Awesome 图标修复脚本
document.addEventListener('DOMContentLoaded', function() {
    // 检查Font Awesome是否正确加载
    const checkFontAwesome = () => {
        const span = document.createElement('span');
        span.className = 'fa';
        span.style.display = 'none';
        document.body.insertBefore(span, document.body.firstChild);
        
        const beforeStyle = window.getComputedStyle(span, ':before');
        const hasFA = beforeStyle.getPropertyValue('font-family').includes('Font Awesome');
        
        if (!hasFA) {
            console.warn('Font Awesome 未正确加载，尝试重新加载...');
            reloadFontAwesome();
        } else {
            console.log('Font Awesome 已正确加载');
        }
        
        document.body.removeChild(span);
    };
    
    // 重新加载Font Awesome
    const reloadFontAwesome = () => {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css';
        link.integrity = 'sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==';
        link.crossOrigin = 'anonymous';
        link.referrerPolicy = 'no-referrer';
        
        document.head.appendChild(link);
    };
    
    // 执行检查
    setTimeout(checkFontAwesome, 500);
});