<?php
// 设置安全头部
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// 错误处理函数
function sendError($message, $code = 400) {
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
    exit;
}

// 成功响应函数
function sendSuccess($data) {
    echo json_encode(array_merge(
        ['success' => true],
        $data
    ));
    exit;
}

// 检查是否是POST请求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('只接受POST请求', 405);
}

// 验证CSRF令牌
session_start();
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    sendError('无效的CSRF令牌', 403);
}

// 获取POST数据
$content = isset($_POST['content']) ? trim($_POST['content']) : '';
$expiry = isset($_POST['expiry']) ? floatval($_POST['expiry']) : 24; // 默认24小时
$password = isset($_POST['password']) ? trim($_POST['password']) : ''; // 获取密码


// 验证数据
if (empty($content)) {
    sendError('纸条内容不能为空');
}

// 限制内容长度（10000个字符）
$maxLength = 10000;
if (strlen($content) > $maxLength) {
    sendError('纸条内容不能超过' . $maxLength . '个字符');
}

// 验证过期时间
$validExpiryOptions = [1, 6, 12, 24, 48, 72, 168, 0]; // 0表示永不过期
if (!in_array($expiry, $validExpiryOptions)) {
    $expiry = 24; // 如果不是有效选项，默认为24小时
}

// 创建存储目录（如果不存在）
$storageDir = 'notes';
if (!file_exists($storageDir)) {
    if (!mkdir($storageDir, 0755, true)) {
        sendError('无法创建存储目录', 500);
    }
}

try {
    // 生成唯一ID
    $noteId = bin2hex(random_bytes(8)); // 16个字符的随机ID
    
    // 计算过期时间戳
    $expiryTimestamp = ($expiry > 0) ? time() + ($expiry * 3600) : 0; // 0表示永不过期
    
    // 初始化变量
    $imagePath = '';
    if (!empty($imageData)) {
        // 创建图片存储目录
        $imageDir = 'images';
        if (!file_exists($imageDir)) {
            if (!mkdir($imageDir, 0755, true)) {
                sendError('无法创建图片存储目录', 500);
            }
        }
        
        // 验证图片数据
        if (strpos($imageData, 'data:image/') !== 0) {
            sendError('无效的图片数据');
        }
        
        // 从Base64数据中提取图片内容
        $parts = explode(',', $imageData);
        if (count($parts) !== 2) {
            sendError('无效的图片格式');
        }
        
        // 获取图片类型
        $matches = [];
        if (!preg_match('/data:image\/([a-zA-Z]+);base64/', $parts[0], $matches)) {
            sendError('无法确定图片类型');
        }
        $imageType = $matches[1];
        
        // 解码Base64数据
        $imageContent = base64_decode($parts[1]);
        if ($imageContent === false) {
            sendError('无法解码图片数据');
        }
        
        // 生成唯一的图片文件名
        $imageFileName = $noteId . '_' . time() . '.' . $imageType;
        $relativeImagePath = "$imageDir/$imageFileName";
        
        // 保存图片文件
        if (!file_put_contents($relativeImagePath, $imageContent)) {
            sendError('保存图片失败', 500);
        }
        
        // 设置文件权限
        chmod($relativeImagePath, 0644);
        
        // 生成完整的图片URL路径
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $baseUrl = $protocol . '://' . $host . dirname($_SERVER['PHP_SELF']);
        $imagePath = $baseUrl . '/' . $relativeImagePath;
    }
    
    // 创建纸条数据
    $noteData = [
        'id' => $noteId,
        'content' => $content,
        'created' => time(),
        'expiry' => $expiryTimestamp,
        'has_image' => !empty($imagePath),
        'image_path' => $imagePath,
        'has_password' => !empty($password),
        'password_hash' => !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : ''
    ];
    
    // 保存到文件
    $filePath = "$storageDir/$noteId.json";
    if (!file_put_contents($filePath, json_encode($noteData))) {
        sendError('保存纸条失败，请稍后再试', 500);
    }
    
    // 设置文件权限
    chmod($filePath, 0644);
    
    // 生成访问链接
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $baseUrl = $protocol . '://' . $host . dirname($_SERVER['PHP_SELF']);
    $viewUrl = $baseUrl . '/view.php?id=' . $noteId;
    
    // 生成销毁令牌
    $destroyToken = md5($noteId . $noteData['created']);
    
    sendSuccess([
        'message' => '纸条创建成功',
        'id' => $noteId,
        'url' => $viewUrl,
        'expiry' => $expiry > 0 ? date('Y-m-d H:i:s', $expiryTimestamp) : '永不过期',
        'has_password' => !empty($password)
    ]);
    
} catch (Exception $e) {
    sendError('创建纸条时发生错误: ' . $e->getMessage(), 500);
}
?>