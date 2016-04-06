<?php
/*
 * スマートフォン向け イベント告知ツール
 * 
 * http://tsukuba42195.top/
 * Copyright(c)2016 Akira Mukai All rights reserved.
 * 
 */

// イベントを削除する
$file_list = "./data/list.txt";
if($_POST['cmd'] == "delete" and $_POST['id'] != ""){

  $id = $_POST['id'];

  $filename = "./data/" . $id . ".txt";

  if(file_exists($filename)){
    $readline = explode("\n", file_get_contents($filename));
    $deletename = $readline[0];

    unlink($filename);
  }

  $memberfile = "./data/" . $id . "_member.txt";

  if(file_exists($memberfile)){
    unlink($memberfile);
  }

  if(!file_exists($filename) and !file_exists($memberfile)){
    //$js_delete = "$(window).load(function(){\n";
    $js_delete = "  $('#deleteModal').modal('show');\n";
    //$js_delete .= "});\n";
  }

  // リストからも削除する
  $fp = fopen($file_list, 'r');
  if ($fp){
    if (flock($fp, LOCK_SH)){
      while (!feof($fp)) {
        $buffer = fgets($fp);
        $listline = explode("\t", $buffer);
        $cid = $listline[0];
        $hash[$cid] = $buffer;
      }
      flock($fp, LOCK_UN);
    }
  }
  fclose($fp);

  if(array_key_exists($id, $hash)){
    // 古いキーを削除する
    unset($hash[$id]);
  }

  // リストファイルを上書きする
  $fp = fopen($file_list, 'w');
  if($fp){
    foreach ($hash as $key => $value){
      fputs($fp, $value);
    }
  }
  fclose($fp);

}


$today = date("Y/m/d");

$file_list = "./data/list.txt";
// イベントのリストを作成する
$fp = fopen($file_list, 'r');
if ($fp){
  if (flock($fp, LOCK_SH)){
    $cnt = 0;
    while (!feof($fp)) {
      $buffer = fgets($fp);
      $listline = explode("\t", $buffer);
      $list_id = $listline[0];
      $list_name = $listline[1];
      $list_place = $listline[2];
      $list_date = $listline[3];
      $list_capa = intval($listline[4]);
      if($list_capa == 0){
        $list_capa = "なし";
      }
      if($list_id != ""){
        // 過去のイベントは表示しない
        $target_date = mb_substr($list_date, 0, mb_strpos($list_date, "("));
        if(strtotime($today) < strtotime($target_date)){
          $list_html .= "<tr><td>" . $list_date . "</td><td><a href=\"./list/?id=" . $list_id . "\" target=\"_blank\">" . $list_name . "</a></td><td>" . $list_place . "</td><td>" . $list_capa . "</td></tr>";
          $cnt++;
        }
      }
    }
    flock($fp, LOCK_UN);
  }
}
fclose($fp);
if($cnt == 0){
  $list_html = "<tr><td colspan=\"4\">現在受付中のイベントはありません。</td></tr>";
}


?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="keywords" content="勉強会">
  <meta name="description" content="現在参加者募集中の勉強会一覧です。">
  <title>告知くん - 勉強会の開催＆支援ツール</title>
  <link rel="shortcut icon" href="./favicon.ico">
  <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
  <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
  <style type="text/css">
  body {
    padding-top: 80px;
    background: #efefe9;
  }
  @media ( min-width: 768px ) {
    #banner {
      min-height: 300px;
      border-bottom: none;
    }
    .bs-docs-section {
      margin-top: 8em;
    }
    .bs-component {
      position: relative;
    }
    .bs-component .modal {
      position: relative;
      top: auto;
      right: auto;
      left: auto;
      bottom: auto;
      z-index: 1;
      display: block;
    }
    .bs-component .modal-dialog {
      width: 90%;
    }
    .bs-component .popover {
      position: relative;
      display: inline-block;
      width: 220px;
      margin: 20px;
    }
    .nav-tabs {
      margin-bottom: 15px;
    }
    .progress {
      margin-bottom: 10px;
    }
  }

  @media( max-width : 585px ){
      .board {
      width: 90%;
      height:auto !important;
    }
    span.round-tabs {
      font-size:16px;
      width: 50px;
      height: 50px;
      line-height: 50px;
    }
    .tab-content .head{
      font-size:20px;
    }
    .nav-tabs > li a {
      width: 50px;
      height: 50px;
      line-height:50px;
    }

    li.active:after {
      content: " ";
      position: absolute;
      left: 35%;
    }

    .btn-outline-rounded {
      padding:12px 20px;
    }

  }

  #main {
    height: 100%;
  }
  </style>

  <!--[if lt IE 9]>
    <script src="//oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="//oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->

</head>
<body>

<header>
  <div class="navbar navbar-default navbar-fixed-top">
    <div class="container">
      <div class="navbar-header">
        <a href="./" class="navbar-brand"><i class="glyphicon glyphicon-bullhorn"></i> 告知くん</a>
        <button class="navbar-toggle" type="button" data-toggle="collapse" data-target="#navbar-main">
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
      </div>
      <div class="navbar-collapse collapse" id="navbar-main">
        <ul class="nav navbar-nav navbar-right">
          <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="glyphicon glyphicon-menu-hamburger"></i></a>
          <ul class="dropdown-menu" role="menu">
            <li><a href="./"><i class="glyphicon glyphicon-wrench"></i> 勉強会の作成</a></li>
            <li class="active"><a href="list.php"><i class="glyphicon glyphicon-list"></i> 勉強会リスト</a></li>
          </ul>
          </li>
        </ul>
      </div>
    </div>
  </div>
</header>

<div id="main" class="container">

  <section id="list">
  <article>
  <div class="row">
    <div class="col-md-12">
    <h1><i class="glyphicon glyphicon-education"></i> 勉強会リスト</h1>

      <div class="bs-component">
        <table class="table table-striped table-hover ">
          <thead>
            <tr>
              <th>開催日</th>
              <th>イベント名</th>
              <th>会場</th>
              <th>定員</th>
            </tr>
          </thead>
          <tbody>
            <tr>
            <?php echo $list_html; ?>
            </tr>
          </tbody>
        </table>
      </div>

    </div>
  </div>
  </article>
  </section>

<?php
if($_POST['cmd'] == "delete" and $_POST['id'] != ""){
echo <<<EOM
  <!-- 削除確認のモーダル -->
  <div id="deleteModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <a class="close" data-dismiss="modal">&times;</a>
          <h3>削除完了</h3>
        </div>
        <div class="modal-body">
          <p>イベント「{$deletename}」を削除しました。</p>
        </div>
        <div class="modal-footer">
          <a class="btn btn-default" data-dismiss="modal">Close</a>
        </div>
      </div>
    </div>
  </div>

EOM;
}
?>

  <!-- Footer -->
  <footer>
    <div class="row">
      <div class="col-lg-12">
        <p>
        Copyright (C) 2016 <a href="http://tsukuba42195.top/">Akira Mukai</a><br>
        </p>
      </div>
    </div>
  </footer>

</div>

<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>

<script type="text/javascript">
$(function(){
<?php
if($_POST['cmd'] == "delete" and $_POST['id'] != ""){
  echo $js_delete;
}
?>
});
</script>

</body>
</html>
