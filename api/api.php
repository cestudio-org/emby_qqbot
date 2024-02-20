<?php
// QQ群相关
$qqapi = getenv('QQAPI'); // 不要加后面的斜杠
$accesstoken = base64_decode(getenv('ACCESSTOKEN')); // 不能加密，比较不安全，但是只能这样了
// TG Bot相关
$tg_token = getenv('TGTOKEN');
$tg_api = "https://api.telegram.org/bot";
// WeChat 相关
$wxapi = getenv("WXAPI");

// API安全
if ($_SERVER['REQUEST_METHOD'] != "POST") {
    exit("{\"code\":405,\"msg\":\"不支持的请求方式\"}");
} elseif ($_GET['group_id']) {
    $group_id = $_GET['group_id'];
} else {
    exit("{\"code\":404,\"msg\":\"Group not Found\"}");
}

// 接受WebHook数据并数组化
$data = json_decode(file_get_contents('php://input'),true);
// 判断Event
$method = $data['Event'];
switch ($method) {
    // 发送测试通知
    case 'system.webhooktest': //测试
        $message = "测试成功！！\n相关信息 " . $data['Server']['Name'] . " 上\n它的版本是：" . $data['Server']['Version'];
        break;
    // 服务器
    case 'system.serverrestartrequired': //服务器需要重启
        $message = "警告！服务器需要重启，请管理员及时处理";
        break;
    case 'system.updateavailable': //服务器有可用更新
        $message = "警告！服务器有可用更新，请管理员及时更新";
        break;
    // 媒体库
    case 'library.new': //新媒体已添加
        $message = "警告！服务器添加了新的影视作品,请管理员核实：\n" . $data['Title'] . "\n" . $data['Description'];
        break;
    case 'library.deleted': //删除了一个媒体
        $message = "警告！服务器删除了一个影视作品,请管理员核实：\n" . $data['Title'] . "\n" . $data['Description'];
        break;
    case 'library.subtitlesdownloaded': //已下载字幕
        $message = "服务器一个影视作品下载了字幕：\n" . $data['Title'] . "\n" . $data['Description'];
        break;
    // 播放
    case 'playback.start': // 播放开始
    case 'playback.pause': // 播放暂停
    case 'playback.unpause': // 播放取消暂停
    case 'playback.stop': // 播放停止
        $message = $data['Title'];
        break;
    // 用户
    case 'item.rate': //添加到"最爱" | 从 "最爱" 中移除
    case 'item.markplayed': //标记为已播放
    case 'item.markunplayed': //标记为未播放
        $message = $data['Title'];
        break;
    case 'user.authenticated': //已验证用户身份
        $message = "警告！用户成功登录了，请注意非法登录并及时检查密码安全:\n" . $data['Title'];
        break;
    case 'user.authenticationfailed': //无法验证用户身份
        $message = "警告！服务器出现用户登录失败，谨防DDoS:\n" . $data['Title'];
        break;
    case 'user.lockedout': //用户被锁定
        $message = "警告！服务器某用户被锁定！:\n" . $data['Title'];
        break;
    case 'user.created': //用户已创建
        $message = "警告！服务器创建了一个用户，谨防提权攻击！:\n" . $data['Title'];
        break;
    case 'user.deleted': //用户已删除
        $message = "警告！服务器删除了一个用户，谨防误操作！:\n" . $data['Title'];
        break;
    case 'user.passwordchanged': //密码已更改
        $message = "警告！服务器修改了一个用户的密码，谨防渗透攻击！:\n" . $data['Title'];
        break;        break;
    case 'user.policyupdated': //用户策略已更新
        $message = "警告！服务器某用户的策略已被更新！:\n" . $data['Title'];
        break;
    // 设备
    case 'devices.cameraimageuploaded': //相机图片已上传
        $message = $data['Title'];
        break;
    // 插件
    case 'plugins.plugininstalled': //插件已安装
    case 'plugins.pluginuninstalled': //插件已卸载
    case 'plugins.plugininstallfailed': //插件安装失败
    case 'plugins.pluginupdated': //插件已更新
        $message = $data['Title'];
        break;
    // 电视直播
    case 'livetv.timercreated': //已计划录制
    case 'livetv.timercancelled': //录制已取消
    case 'livetv.seriestimercreated': //已计划电视剧录制
    case 'livetv.seriestimercancelled': //电视剧录制已取消
    case 'livetv.recordingstarted': //录制已开始
    case 'livetv.recordingended': //录制已结束
    case 'livetv.recordingerror': //录制失败
        $message = $data['Title'];
        break;
    // 外部
    case 'external.externalnotification': //通过 Emby Server API 外部通知
        $message = $data['Title'];
        break;
    // 非法请求
    default:
        exit("{\"code\":404,\"msg\":\"Event not Found\"}");
}

if ($_GET['type'] == "gocq") {
    $base = $qqapi . "/send_group_msg?group_id=$group_id&message=";
} elseif ($_GET['type'] == "tg") {
    $base = $tg_api . $tg_token . "/SendMessage?chat_id=$group_id&text=$message";
} elseif ($_GET['type'] == "chronocat") {
    $base = $qqapi . "/v1/message.create";
    $data = array(
        'channel_id' => $group_id,
        'content' => $message
    );
    $context = stream_context_create(array(
        'http' => array(
            'method'  => 'POST',
            'header'  => "Content-Type: application/json\r\n" .
                         "Authorization: Bearer " . $accesstoken,
            'content' => json_encode($data)
        )
    ));
    exit(file_get_contents($base, false, $context));
} elseif ($_GET['type'] == "wx") {
    $base = $wxapi . "/webhook/msg";
    $data = array(
        'to' => $group_id,
        'type' =>"text",
        'content' => $message
    );
    $context = stream_context_create(array(
        'http' => array(
            'method'  => 'POST',
            'header'  => "Content-Type: application/json",
            'content' => json_encode($data)
        )
    ));
    exit(file_get_contents($base, false, $context));
} else{ 
    exit("{\"code\":404,\"msg\":\"Type not Found\"}");
}

$context = stream_context_create(array(
    'http' => array(
        'header'  => "Authorization: Bearer " . $accesstoken
    )
));
exit(file_get_contents($base . urlencode($message), false, $context));
