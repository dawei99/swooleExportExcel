<?php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
require './vendor/autoload.php';

/**
 * 数据库连接对象
 */
$pdoObj = function(){
    echo '使用了' . PHP_EOL;
    try {
        $dbms = 'mysql';     //数据库类型
        $host = 'localhost'; //数据库主机名
        $dbName = 'blog_db';    //使用的数据库
        $user = 'root';      //数据库连接用户名
        $pass = 'root';          //对应的密码
        $dsn = "$dbms:host=$host;dbname=$dbName";
        $dbh = new PDO($dsn, $user, $pass);
        return $dbh;
    } catch (Exception $e) {
        echo 'pdo连接发生错误';
    }

};

/**
 * 数据库连接对象
 */
$redisObj = function(){
    echo '使用了' . PHP_EOL;
//    try {
//        $obj = new Redis();
//        $obj->connect('127.0.0.1', '6379');
//        return $obj;
//    } catch (Exception $e) {
//        echo 'redis连接失败';
//    }
};

/**
 * task任务工作
 * @param $data
 */
$taskWork = function ($data) use($pdoObj) {

    // 多页同时进行
    $headCode = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N'];
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $dbh = $pdoObj();
    // 导出数据
    $limit = ' limit ' . $data['offset'] . ',' . $data['limit'] ;
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
        $writer->save('xlsx/hello_'.rand().'.xlsx');
        $writer = null;
        $data = null;
        $sheet = null;
    }
    $spreadsheet = null;
    // 关闭连接
    $dbh = null;

};
