<?php
require_once 'Workerman/Autoloader.php';
use Workerman\Worker;

define(TYPE_1, 1); // 普通消息
define(TYPE_2, 2); // 列表消息
define(TYPE_3, 3); // 特殊消息

// 创建一个Worker监听2345端口，使用http协议通讯
$worker = new Worker("websocket://0.0.0.0:2345");

// 启动4个进程对外提供服务
$worker->count = 1;

// 存放用户链接
$worker->uidConnections = [];

// 连接事件
// $worker->onConnection = function ($connection) use ($worker)
// {
    
// };

// 用户离线事件
$worker->onClose = function ($connection) use ($worker) {
    $name = $connection->name;
    unset($worker->uidConnections[$name]);
    echo $name , "下线\n";
    if ($worker->uidConnections) {
        echo "剩余在线ID：" , json_encode(array_keys($worker->uidConnections)) , "\n";
    } else {
        echo "没有ID在线！\n";
    }
    // 用户离线通知其他用户
    foreach ($worker->uidConnections as $key => $otherConnection) {
        $otherConnection->send(json_encode(['type' => TYPE_3, 'message' => 'ID：'.$name.'下线。']));
    }
};

// 接收到数据逻辑处理
$worker->onMessage = function($connection, $data) use ($worker)
{
    $message = json_decode($data, true);
    if ($message) {
        if (isset($message['id'])) {
            if (!array_key_exists($message['id'], $worker->uidConnections)) {
                // 发送当前已注册的用户ID
                if ($worker->uidConnections) {
                    $list['message']['list'] = array_keys($worker->uidConnections);
                    $list['type'] = TYPE_2; //列表信息
                    $connection->send(json_encode($list));
                }
                // 通知其他用户有用户上线
                foreach ($worker->uidConnections as $key => $otherConnection) {
                    $otherConnection->send(json_encode(['type' => TYPE_3, 'message' => 'ID：'.$message['id'].'上线！']));
                }
                // 没有注册时注册发送来的ID
                $connection->name = $message['id'];
                $worker->uidConnections[$message['id']] = $connection;
                echo $connection->name , "上线\n";
            } // 注册时反馈信息
            else if (!isset($message['to'])) {
                $connection->send(json_encode(['type' => TYPE_3, 'message' => 'ID：'.$message['id'].' 被占用！']));
            }
        }

        if (isset($message['to'])) {
            if (isset($worker->uidConnections[$message['to']])) {
                if ($message['to'] != $message['id']) {
                    echo "{$message['id']} 发送消息给 {$message['to']}\n";
                    // 向对方发送消息
                    $reply = [
                        'from'    => $message['id'],
                        'message' => $message['message'],
                        'type'    => TYPE_1 //普通消息
                    ];
                    $replyStr = json_encode($reply);
                    $worker->uidConnections[$message['to']]->send($replyStr);
                }
            } else {
                $connection->send(json_encode(['type' => TYPE_3, 'message' => 'ID：'.$message['to'].' 不存在或已下线！']));
            }
        }
    }
};

Worker::runAll();