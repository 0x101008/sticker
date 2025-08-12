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
function sendSuccess($message) {
    echo json_encode([
        'success' => true,
        'message' => $message
    ]);
    exit;
}

// 获取参数
$noteId = isset($_GET['id']) ? $_GET['id'] : '';
$token = isset($_GET['token']) ? $_GET['token'] : '';

// 验证ID格式
if (empty($noteId) || !preg_match('/^[a-f0-9]{16}$/', $noteId)) {
    sendError('无效的纸条ID');
}

// 检查文件是否存在
$filePath = "notes/$noteId.json";
if (!file_exists($filePath)) {
    sendError('纸条不存在或已被销毁', 404);
}

// 读取纸条数据
try {
    $noteContent = file_get_contents($filePath);
    if ($noteContent === false) {
        sendError('无法读取纸条数据', 500);
    }
    
    $noteData = json_decode($noteContent, true);
    if (!$noteData) {
        sendError('纸条数据格式无效', 500);
    }
} catch (Exception $e) {
    sendError('读取纸条时发生错误: ' . $e->getMessage(), 500);
}

// 验证令牌（安全检查）
$expectedToken = md5($noteId . $noteData['created']);
if ($token !== $expectedToken) {
    sendError('安全令牌无效', 403);
}

// 删除文件
try {
    if (unlink($filePath)) {
        sendSuccess('纸条已成功销毁');
    } else {
        sendError('销毁纸条失败', 500);
    }
} catch (Exception $e) {
    sendError('销毁纸条时发生错误: ' . $e->getMessage(), 500);
}
?>