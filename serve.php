<?php
/**
 * Created by PhpStorm.
 * User: 大伟PHPer
 * Date: 2019/2/17
 * Time: 4:21 PM
 */
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
require './vendor/autoload.php';

$func = function ($data) {
    $dbms = 'mysql';     //数据库类型
    $host = 'localhost'; //数据库主机名
    $dbName = 'blog_db';    //使用的数据库
    $user = 'root';      //数据库连接用户名
    $pass = 'root';          //对应的密码
    $dsn = "$dbms:host=$host;dbname=$dbName";
    try {
        $dbh = new PDO($dsn, $user, $pass);
    } catch (Exception $e) {
        echo '错误';
    }
    // 数据分页
    $fetch = $dbh->query('select id from test_user limit 100000')->fetchAll();
    $dataCount = sizeof($fetch) ?? 0;
    if(!$dataCount) return 0;
    $page = 1000;
    $pageTotals = $dataCount % $page ? $dataCount / $page + 1 : $dataCount / $page;
    // 多页同时进行
    $headCode = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N'];
    for($i=0;$i<$pageTotals;$i++){
        //go(function() use($i, $dbh, $page) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            // 导出数据
            $limit = ' limit ' . $i * $page . ',' . $page ;
            $data = $dbh->query('select * from test_user ' . $limit)->fetchAll(PDO::FETCH_ASSOC);
            if($data){
                // 表头
                array_unshift($data, ['用户ID','用户名']);
                // 数据
                foreach($data AS $key=>$val){
                    $key += 1;
                    $cell = 0; // 横行第几个单元格
                    foreach($val AS $rowVal){
                        $callCode = $headCode[$cell] . $key;
                        $sheet->setCellValue($callCode, $rowVal);
                        $cell++;
                    }
                }
                $writer = new Xlsx($spreadsheet);
                $writer->save('xlsx/hello_'.$i.'.xlsx');
                $writer = null;
                $data = null;
                $sheet = null;
            }
            $spreadsheet = null;
        //});
    }
    // 关闭连接
    $dbh = null;

};

$serv = new swoole_server('127.0.0.1', 9526, SWOOLE_BASE);

$serv->set(array(
    'worker_num' => 1,
    'task_worker_num' => 4,
    //'daemonize'=>1,
    //'task_enable_coroutine' => true
));
$serv->on('Start', function ($server) {
    echo '服务已开启' . PHP_EOL;
});

$serv->on('Receive', function (swoole_server $serv, $fd, $from_id, $data) {
    $serv->send($fd, 'ok');
    echo '接收数据，数据长度：' . strlen($data) . PHP_EOL;
    $serv->task($data);
    $serv->task($data);
});

$serv->on('Task', function (swoole_server $serv, $task_id, $from_id, $data) use ($func) {
//$serv->on('Task', function (swoole_server $serv, Swoole\Server\Task $task) use ($func) {
    //$data = $task->data;
    echo "Task($task_id)进程开始工作" . PHP_EOL;
    $func($data);
    //$task->finish($data);
    $serv->finish($data);
});

$serv->on('Finish', function (swoole_server $serv, $task_id, $data) {
    echo 'task任务处理完成' . PHP_EOL;
});

$serv->start();