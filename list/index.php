<?php
/*
 * スマートフォン向け イベント告知ツール
 * 
 * http://tsukuba42195.top/
 * Copyright(c)2016 Akira Mukai All rights reserved.
 * 
 */


if($_SERVER["REQUEST_METHOD"] != "POST"){
  $id= $_GET['id'];
}else{
  $cmd = $_POST['cmd'];
  $id = $_POST['id'];
}

$fl = "../data/" . $id . ".txt";
$fl_mem = "../data/" . $id . "_member.txt";

// 現在の申込者数
if( !file_exists($fl_mem) ){
  $applicants = 0;
}else{
  $app_arry = explode("\n", file_get_contents($fl_mem));
  $cnt = 0;
  for($cnt_tmp = 0; $cnt_tmp < count($app_arry); $cnt_tmp++){
    $line_tmp = explode("\t", $app_arry[$cnt_tmp]);
    if($line_tmp[0] != "" and $line_tmp[0] != "\n"){
      $cnt++;
    }
  }
  $applicants = $cnt;
}

if($cmd != ""){

  // 対象のファイル内のデータを取り出す
  if (file_exists($fl)) {

    // 改行で区切って配列へ入れる
    $line = explode("\n", file_get_contents($fl));

    // ファイルの中身を変数へ入れる
    $title = $line[0];
    $place = $line[1];
    $date = $line[2];
    $capacity = $line[3];
    $password = $line[4];
    $memo = $line[5];

    $link = "http://kokuchi.tsukuba42195.top/list/?id=" . $id;

    if($capacity == ""){
      $capa_disp = "なし";
    }else{
      $capa_disp = $capacity . "名";
    }

    // 参加申し込み
    if($cmd == "join"){

      if($capacity == "" or intval($capacity) > $applicants){

        $email = $_POST['email'];
        $name = $_POST['name'];

        $add = "\n" . $name . "\t" . $email . "\n";

        if($email != "" and $name != ""){

          // ファイルの存在確認
          if( !file_exists($fl_mem) ){
            // ファイル作成
            touch( $fl_mem );
          }

          // 一回しか登録させない
          $list = explode("\n", file_get_contents($fl_mem));
          $add_compare = preg_replace("/( |　)/", "", $add);
          for ($i = 0; $i < count($list); $i++) {
            $list[$i]  = preg_replace("/( |　)/", "", $list[$i]);
            if($add_compare == $list[$i]){
              $alert_msg = "既に登録されています。";
              break;
            }
          }

          if($error == ""){
            // ファイルに書き込む(新規追加)
            file_put_contents($fl_mem, $add, FILE_APPEND);
            $success_msg = "ありがとうございます！お申し込みを受け付けました。";
          }

        }else{
          $alert_msg = "お名前かメールアドレスが未入力です。";
        }

      }else{
        $alert_msg = "大変申し訳ありません。申込の定員に達したようです。";
      }

    // 参加キャンセル
    }elseif($cmd == "cancel"){

      $email = $_POST['email'];
      $name = $_POST['name'];

      $add = $name . "\t" . $email;

      if($email != "" and $name != ""){

        // ファイルが存在しない場合
        if( !file_exists($fl_mem) ){

          $alert_msg = "まだ申し込みされていません。";

        }else{

          $list = explode("\n", file_get_contents($fl_mem));
          //$add_compare = preg_replace("/( |　)/", "", $add);
          for ($i = 0; $i < count($list); $i++) {
            //$list[$i]  = preg_replace("/( |　)/", "", $list[$i]);
            if($add == $list[$i]){
              // 削除
              unset($list[$i]);
              $new_list = array_merge($list);
              $success_msg = "お申し込みをキャンセルしました。";
              break;
            }
          }

          if($success_msg != ""){
            // ファイルに書き込む(キャンセル分は削除)
            $fp = fopen($fl_mem, 'w');
            if($fp){
              if (flock($fp, LOCK_SH)){
                foreach ($new_list as $key => $value){
                  fputs($fp, $value);
                }
                flock($fp, LOCK_UN);
              }
            }
            fclose($fp);
          }else{
            $alert_msg = "お名前かメールアドレスが一致しませんでした。";
          }

        }

      }else{
        $alert_msg = "お名前かメールアドレスが未入力です。";
      }

    }else{
      $error = "パラメータが不正です。";
    }

  }else{
    $error = "パラメータが不正です。";
  }

}else{

  // ファイルの存在チェック
  if (file_exists($fl)) {

    // 改行で区切って配列へ入れる
    $line = explode("\n", file_get_contents($fl));

    // ファイルの中身を取り出す
    $title = $line[0];
    $place = $line[1];
    $date = $line[2];
    $capacity = $line[3];
    $password = $line[4];
    $memo = $line[5];

    $link = "./?id=" . $id;

    if($capacity == ""){
      $capa_disp = "なし";
    }else{
      $capa_disp = $capacity . "名";
    }

  }else{
    $error = "URLが間違っています。";
  }

}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo $title; ?> - 告知くん</title>
  <link rel="shortcut icon" href="../favicon.ico">
  <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
  <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="../css/social-buttons.css">
  <style type="text/css">
  body { padding-top: 80px; }
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
    .btn-outline-rounded{
      padding: 10px 40px;
      margin: 20px 0;
      border: 2px solid transparent;
      border-radius: 25px;
    }

    .btn.green{
      background-color:#5cb85c;
      /*border: 2px solid #5cb85c;*/
      color: #ffffff;
    }
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
        <a href="../" class="navbar-brand"><i class="glyphicon glyphicon-bullhorn"></i> 告知くん</a>
        <button class="navbar-toggle" type="button" data-toggle="collapse" data-target="#navbar-main">
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
      </div>
      <div class="navbar-collapse collapse" id="navbar-main">
        <ul class="nav navbar-nav navbar-right">
          <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"> メニュー <span class="caret"></span></a>
          <ul class="dropdown-menu" role="menu">
            <li><a href="" data-toggle="modal" data-target="#cancel"><i class="fa fa-user"></i> 参加キャンセル</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="" data-toggle="modal" data-target="#admin"><i class="fa fa-user-secret"></i> 管理画面</a></li>
          </ul>
          </li>
        </ul>
      </div>
    </div>
  </div>
</header>

<div class="container">

<?php
if(empty($error)){

  if($success_msg != ""){

    $msg = '<div class="bs-component">';
    $msg .= '<div class="alert alert-dismissible alert-success">';
    $msg .= '<button type="button" class="close" data-dismiss="alert">&times;</button>';
    $msg .= '<h4>Thank you!</h4><p>' . $success_msg . "</p>";
    $msg .= '</div>';
    $msg .= '</div>';

  }elseif($alert_msg != ""){

    $msg = '<div class="bs-component">';
    $msg .= '<div class="alert alert-dismissible alert-warning">';
    $msg .= '<button type="button" class="close" data-dismiss="alert">&times;</button>';
    $msg .= '<h4>Warning!</h4><p>' . $alert_msg . "</p>";
    $msg .= '</div>';
    $msg .= '</div>';

  }

  // 定員に達しているかどうかの判定
  if($capacity == ""){
    $capa = "";
    $capa_disc = "";
  }elseif(intval($capacity) <= $applicants){
    $capa = " disabled";
    $capa_disc = '<p class="text-danger text-center">申し訳ありません。定員に達しました。</p>';
  }

echo <<<EOM
  <div class="row">
    <div class="col-lg-12">
      <h1><i class="glyphicon glyphicon-education"></i> {$title}</h1>
    </div>
  </div>

  <hr>

  <div class="row">
    <div class="col-lg-12">

{$msg}

      <div class="bs-component">

        <div class="panel panel-primary">
          <div class="panel-heading">
            <h3 class="panel-title"><span class="glyphicon glyphicon-comment"></span> 概 要</h3>
          </div>
          <div class="panel-body">
            {$memo}
          </div>
        </div>

      </div>

    </div>
  </div>

  <div class="row">
    <div class="col-md-4">

      <div class="bs-component">

        <div class="panel panel-primary">
          <div class="panel-heading">
            <h3 class="panel-title"><span class="glyphicon glyphicon-calendar"></span> 日 付</h3>
          </div>
          <div class="panel-body">
            {$date}
          </div>
        </div>

      </div>
    </div>

    <div class="col-md-4">

      <div class="bs-component">

        <div class="panel panel-primary">
          <div class="panel-heading">
            <h3 class="panel-title"><span class="glyphicon glyphicon-user"></span> 定 員</h3>
          </div>
          <div class="panel-body">
            {$capa_disp}
          </div>
        </div>

      </div>
    </div>

    <div class="col-md-4">

      <div class="bs-component">

        <div class="panel panel-primary">
          <div class="panel-heading">
            <h3 class="panel-title"><span class="glyphicon glyphicon-map-marker"></span> 都道府県</h3>
          </div>
          <div class="panel-body">
            {$place}
          </div>
        </div>

      </div>

    </div>
  </div>

  <div class="row">
    <div class="col-lg-12">

      <p class="text-center">
      <button type="button" class="btn btn-success btn-outline-rounded green{$capa}" data-toggle="modal" data-target="#join"> 参加する <span style="margin-left:10px;" class="glyphicon glyphicon-send"></span></button>
      </p>
      {$capa_disc}


      <!-- 参加申込画面のモーダルstart -->
      <div id="join" class="modal fade" role="dialog">
        <div class="modal-dialog">

          <!-- Modal content -->
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal">&times;</button>
              <h4 class="modal-title">参加する</h4>
            </div>
            <div class="modal-body">

              <div class="well bs-component">
              <form class="form-horizontal" action="./?id={$id}" method="post" data-toggle="validator">
              <input type="hidden" name="id" value="{$id}">
              <input type="hidden" name="cmd" value="join">
                <fieldset>
                  <div class="form-group has-feedback">
                    <label for="inputName" class="control-label">お名前</label>
                    <input type="text" name="name" class="form-control" id="inputName" placeholder="鏡音リン" required>
                  </div>
                  <div class="form-group">
                    <label for="inputEmail" class="control-label">Email</label>
                    <input type="email" class="form-control" name="email" id="inputEmail" placeholder="rin@vocaloid.com" data-error="Bruh, that email address is invalid" required>
                    <div class="help-block with-errors"></div>
                  </div>
                  <div class="form-group">
                    <button type="submit" class="btn btn-primary">申し込む</button>
                  </div>
                </fieldset>
              </form>
              </div>

            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
          </div>

        </div>
      </div>
      <!-- 参加申込画面のモーダルend -->

      <!-- 参加キャンセルのモーダルstart -->
      <div id="cancel" class="modal fade" role="dialog">
        <div class="modal-dialog">

          <!-- Modal content -->
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal">&times;</button>
              <h4 class="modal-title">キャンセル</h4>
            </div>
            <div class="modal-body">

              <div class="well bs-component">
              <form class="form-horizontal" action="./?id={$id}" method="post" data-toggle="validator">
              <input type="hidden" name="id" value="{$id}">
              <input type="hidden" name="cmd" value="cancel">
                <fieldset>
                  <div class="form-group has-feedback">
                    <label for="inputName" class="control-label">お名前</label>
                    <input type="text" name="name" class="form-control" id="inputName" placeholder="鏡音リン" required>
                  </div>
                  <div class="form-group">
                    <label for="inputEmail" class="control-label">Email</label>
                    <input type="email" name="email" class="form-control" id="inputEmail" placeholder="Email" data-error="Bruh, that email address is invalid" required>
                    <div class="help-block with-errors"></div>
                  </div>
                  <div class="form-group">
                    <button type="submit" class="btn btn-primary">参加を取り消す</button>
                  </div>
                </fieldset>
              </form>
              </div>

            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
          </div>

        </div>
      </div>
      <!-- 参加キャンセルのモーダルend -->

      <!-- 管理者パスワード入力画面のモーダルstart -->
      <div id="admin" class="modal fade" role="dialog">
        <div class="modal-dialog">
          <div class="modal-content">
            <form class="form-horizontal" action="../admin/" method="post" data-toggle="validator">
            <input type="hidden" name="id" value="{$id}">
            <input type="hidden" name="cmd" value="admin">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
              <h4 class="modal-title">認 証</h4>
            </div>
            <div class="modal-body">
              <div class="well bs-component">
                <div class="form-group">
                  <input type="password" pattern="^[_A-z0-9]{1,}$" maxlength="15" data-minlength="4" class="form-control" name="password" placeholder="パスワード" required>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-primary">ログイン</button>
              <button type="button" class="btn btn-default" data-dismiss="modal">閉じる</button>
            </div>
            </form>
          </div>
        </div>
      </div>
      <!-- 管理者パスワード入力画面のモーダルend -->





    </div>
  </div>

EOM;
}else{

  $err_msg = '<div class="bs-component">';
  $err_msg .= '<div class="alert alert-dismissible alert-danger">';
  $err_msg .= '<button type="button" class="close" data-dismiss="alert">&times;</button>';
  $err_msg .= '<strong>Oh snap!</strong> <a href="#" class="alert-link">' . $error . '</a> Try submitting again.';
  $err_msg .= '</div>';
  $err_msg .= '</div>';

  echo $err_msg;

}
?>

  <hr>

  <footer class="footer">

<?php
if(empty($error)){
echo <<<EOM
  <p><a class="btn btn-social-icon btn-twitter" href="http://twitter.com/share?url={$link}&text={$title}" target="_blank"><span class="fa fa-twitter"></span></a> <a class="btn btn-default" href="http://tsukuba42195.top/pocket/add.php?url={$link}" target="_blank"><i class="fa fa-get-pocket" style="color:#ff3366;"></i></a></p>
EOM;
}
?>

  <p>
  Copyright (C) 2016 <a href="http://tsukuba42195.top/">Akira Mukai</a><br>
  </p>

  </footer><!-- /footer -->

</div> <!-- /container -->


<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
<script src="../js/validator.js"></script>

<script>
$(function(){

  $('.input-group-btn [data-toggle="tooltip"]').tooltip();

});
</script>
</body>
</html>
