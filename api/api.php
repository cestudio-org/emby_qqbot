<?php
$qqapi=getenv('QQAPI'); //不要加后面的斜杠
// API安全
if ($_SERVER['REQUEST_METHOD']!="POST"){
    exit("{\"code\":405,\"msg\":\"不支持的请求方式\"}");
}elseif ($_GET['group_id']){
    $group_id = $_GET['group_id'];
}else{
    exit("{\"code\":404,\"msg\":\"Group not Found\"}");
}

// 判断Event
$data = json_decode($_POST['data'],true);
var_dump($_POST);
$method = $data['Event'];
switch ($method){
    case 'system.webhooktest':
        $message = "测试成功力！！\n我工作在 ".$data['Server']['Name']." 上\n它的版本是：".$data['Server']['Version'];
        break;
    case 'system.serverrestartrequired':
        $message = "服务器需要重启力";
        break;

    case 'system.updateavailable':
        $message = "服务器有可用更新力";
        break;

    case 'library.new':
        $message = "服务器添加了新的影视作品：\n" . $data['Description'];
        break;

    case 'user.authenticated':
        $message = "有人成功登录了:\n" . $data['Title'];
        break;

    case 'user.authenticationfailed':
        $message = "有人登录失败了:\n" . $data['Title'];
        break;

    case 'playback.pause':
    case 'playback.unpause':
    case 'playback.stop':
    case 'playback.start':
        $message = $data['Title'];
        break;

    default:
        exit("{\"code\":404,\"msg\":\"Event not Found\"}");
}

$base = $qqapi . "/send_group_msg?group_id=$group_id&message=";
exit(file_get_contents($base . urlencode($message)));

