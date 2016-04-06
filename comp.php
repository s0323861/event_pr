<?php
/*
 * スマートフォン向け イベント告知ツール
 * 
 * http://tsukuba42195.top/
 * Copyright(c)2016 Akira Mukai All rights reserved.
 * 
 */

// 変数に代入する
$name = $_POST['name'];
$place = $_POST['place'];
$date1 = $_POST['date1'];
$memo = $_POST['content'];
$password = $_POST['password'];
$capacity = $_POST['capacity'];
$id = $_POST['id'];

// 文字列を置換する
$name = trim($name);
//$memo = str_replace(array("\r\n","\r","\n"), '<br>', $memo);
//$memo = str_replace('\t', '', $memo);
//$memo = trim($memo);

// ファイルの名前
$filename = "./data/" . $id . ".txt";
$file_list = "./data/list.txt";

// ファイルの存在確認
if( !file_exists($filename) ){
  // ファイル作成
  touch( $filename );
}else{
  // すでにファイルが存在する為エラーとする
  exit();
}

$handle = fopen( $filename, 'a' );
fwrite( $handle, $name . "\n" );
fwrite( $handle, $place . "\n" );
fwrite( $handle, $date1 . "\n" );
fwrite( $handle, $capacity . "\n" );
fwrite( $handle, $password . "\n" );
fwrite( $handle, $memo . "\n" );
fclose($handle);

$write_list = $id . "\t" . $name . "\t" . $place . "\t" . $date1 . "\t" . $capacity . "\n";
if( !file_exists($file_list) ){
  touch($file_list);
}

$hdl_list = fopen($file_list, 'a');
fwrite($hdl_list, $write_list);
fclose($hdl_list);



// Content-TypeをJSONに指定する
header('Content-Type: application/json');

// 「200 OK」 で {"data":"24歳、学生です"} のように返す

$uri = (empty($_SERVER["HTTPS"]) ? "http://" : "https://") . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
$url = substr($uri, 0, strrpos($uri, "/")) . "/list/?id=" . $id;

$data1 = "<a href=\"" . $url . "\" class=\"alert-link\" target=\"_blank\">" . $url . "</a>";

$data2 = "<a href=\"" . $url . "\" class=\"btn btn-primary\" target=\"_blank\"><i class=\"fa fa-external-link\"></i> イベントページを表示する</a>";

echo json_encode(compact('data1','data2'));

?>
