<?php
/**
 * Created by PhpStorm.
 * User: 大伟PHPer
 * Date: 2019/2/17
 * Time: 4:21 PM
 */


require './do.php';

$serv = new Swoole\WebSocket\Server("0.0.0.0", 9501);

$serv->on('open', function (Swoole\WebSocket\Server $server, $request) {
    echo "new connection fd{$request->fd}\n";
});

/**
 * $data : ['table' => 'test_user']
 */
$serv->on('message', function (Swoole\WebSocket\Server $server, $frame) {
    global $pdoObj;
    echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
    $pData = $frame->data;
    $server->push($frame->fd, "ok");
    $dbh = $pdoObj();
    // 数据分页
    $fetch = $dbh->query('select id from test_user')->fetchAll();
    $dataCount = sizeof($fetch) ?? 0;
    if(!$dataCount) return 0;
    $page = 1000;
    $pageTotals = $dataCount % $page ? $dataCount / $page + 1 : $dataCount / $page; //总页数(前端平分元素)
    $pData = json_decode($pData,true);
    for($i=0;$i<$pageTotals;$i++) {
        $start = $i * $page;
        $data = array_merge($pData, ['offset' => $start, 'limit' => $page, 'pageTotals' => $pageTotals]);
        $server->task($data);
    }
    $dbh = null;
});

$serv->on('close', function ($ser, $fd) {
    echo "client {$fd} closed\n";
});

$serv->set(array(
    'worker_num' => 1,
    'task_worker_num' => 4,
    //'daemonize'=>1,
    //'task_enable_coroutine' => true
));
$serv->on('Start', function ($server) {
    echo '服务已开启' . PHP_EOL;
});

$serv->on('Task', function (swoole_server $serv, $task_id,$src_worker_id, $data) use ($taskWork) {
    global $pdoObj;
    echo "Task($task_id)进程开始工作" . PHP_EOL;
    $taskWork($data);
    $serv->finish($data);
});

$serv->on('Finish', function (swoole_server $server, $task_id, $data) {
    global $serv;
    foreach ($serv->connections as $fd) {
        // 判断是否是websocket连接
        if ($serv->isEstablished($fd)) {
            $server->push($fd, json_encode($data));
        }
    }
    echo 'task'.$task_id.'任务处理完成' . PHP_EOL;
});

$serv->start();
