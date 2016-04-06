<?php
/*
 * スマートフォン向け イベント告知ツール
 * 
 * http://tsukuba42195.top/
 * Copyright(c)2016 Akira Mukai All rights reserved.
 * 
 */

// 都道府県の配列
$prefs = array('北海道','青森県','岩手県','宮城県','秋田県','山形県','福島県','茨城県','栃木県','群馬県','埼玉県','千葉県','東京都','神奈川県','山梨県','新潟県','富山県','石川県','福井県','長野県','岐阜県','静岡県','愛知県','三重県','滋賀県','京都府','大阪府','兵庫県','奈良県','和歌山県','鳥取県','島根県','岡山県','広島県','山口県','徳島県','香川県','愛媛県','高知県','福岡県','佐賀県','長崎県','熊本県','大分県','宮崎県','鹿児島県','沖縄県');

// idの取り出し
$id = $_POST['id'];
if(empty($id)){
  $id = $_GET['id'];
}

// 現時点の参加者数を調べる
$j = 0;
$memfl = "../data/" . $id . "_member.txt";
if(file_exists($memfl)) {

  $mem_arry = explode("\n", file_get_contents($memfl));

  $j = 0;

  for($i = 0; $i < count($mem_arry); $i++){
    $tmp = explode("\t", $mem_arry[$i]);
    $mem_name = $tmp[0];
    $mem_mail = $tmp[1];
    if($mem_name != "" and $mem_mail != ""){
      $j++;
      $list_html .= "<tr><td>" . $j . "</td><td>" . $mem_name . "</td><td>" . $mem_mail . "</td></tr>";
      $maillist[] = $mem_mail;
    }
  }

  if($j == 0){
    $list_html .= "<tr><td col=\"3\">まだ申込者はいません。</td></tr>";
  }

}else{
  $list_html .= "<tr><td col=\"3\">まだ申込者はいません。</td></tr>";
}


// パスワードの取り出し
if($_POST['password']){
  $pw = $_POST['password'];
  setcookie('kokuchi_pw', $pw, time() + 60 * 60 * 24 * 7);
}elseif(isset($_COOKIE['kokuchi_pw'])){
  $pw = $_COOKIE['kokuchi_pw'];

// パスワードが設定されていない場合
}else{
  $error = "もう一度ログイン認証してください。";
}


if($error == "" and $id != ""){

  $fl = "../data/" . $id . ".txt";
  $fl_list = "../data/list.txt";

  // 対象のファイル内のデータを取り出す
  if(file_exists($fl)) {

    // 改行で区切って配列へ入れる
    $line = explode("\n", file_get_contents($fl));

    // ファイルの中身を変数へ入れる
    $title = $line[0];
    $place = $line[1];
    $date = $line[2];
    $capacity = $line[3];
    $password = $line[4];
    $memo = $line[5];

    // パスワードが一致しているかどうかのチェック
    if($pw == $password){

      $link = "../list/?id=" . $id;

      if($capacity == ""){
        $capa_disp = "なし";
      }else{
        $capa_disp = $capacity . "名";
      }

      $cmd = $_GET['cmd'];
      if(empty($cmd)){
        $cmd = $_POST['cmd'];
      }

      // イベントの内容を編集する場合
      if($cmd == "edit"){

        // 都道府県の選択
        foreach($prefs as $pref){
          if($place == $pref){
            $option_tag .= '<option value="' . $pref . '" selected>' . $pref . '</option>';
          }else{
            $option_tag .= '<option value="' . $pref . '">' . $pref . '</option>';
          }
        }

      // メールアドレスの一覧表示をする場合
      }elseif($cmd == "mail"){

        $html_mail = implode(", ", $maillist);

      // イベントを削除する場合
      }elseif($cmd == "delete"){

        // TOP画面で処理

      // イベントを編集した場合
      }elseif($cmd == "change"){

        // 変数に代入する
        $name = $_POST['name'];
        $title = $name;
        $place = $_POST['place'];
        $date1 = $_POST['date1'];
        $memo = $_POST['memo'];
        $capacity = $_POST['capacity'];

        if($capacity != "" and $j > intval($capacity)){

          $alert_msg = "参加者数が既に定員を上回っています。";

        }else{

          $handle = fopen( $fl, 'w' );
          fwrite( $handle, $name . "\n" );
          fwrite( $handle, $place . "\n" );
          fwrite( $handle, $date1 . "\n" );
          fwrite( $handle, $capacity . "\n" );
          fwrite( $handle, $pw . "\n" );
          fwrite( $handle, $memo . "\n" );
          fclose($handle);

          // リストファイルを読んで連想配列にセットする
          $fp = fopen($fl_list, 'r');
          if ($fp){
            if (flock($fp, LOCK_SH)){
              while (!feof($fp)) {
                $buffer = fgets($fp);
                $listline = explode("\t", $buffer);
                $cid = $listline[0];
                if(strlen($cid) > 5){
                  $hash[$cid] = $buffer;
                }
              }
              flock($fp, LOCK_UN);
            }
          }
          fclose($fp);

          if(array_key_exists($id, $hash)){
            // 古いキーを削除する
            unset($hash[$id]);
            $hash[$id] = $id . "\t" . $name . "\t" . $place . "\t" . $date1 . "\t" . $capacity . "\n";

            // リストファイルを上書きする
            $fp = fopen($fl_list, 'w');
            if($fp){
              foreach ($hash as $key => $value){
                fputs($fp, $value);
              }
            }
            fclose($fp);

            $success_msg = "変更しました。";

          }else{
            $error = "変更できませんでした。";
          }

        }

        // 都道府県の選択
        foreach($prefs as $pref){
          if($place == $pref){
            $option_tag .= '<option value="' . $pref . '" selected>' . $pref . '</option>';
          }else{
            $option_tag .= '<option value="' . $pref . '">' . $pref . '</option>';
          }
        }

        $cmd = "edit";

      // 名簿を表示する場合
      }else{

        $cmd = "list";



      }










    }else{
      $error = "パスワードが一致しません。";
    }

  }else{
    $error = "パラメータが不正です。";
  }

}else{

  $error = "パラメータが不正です。";

}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>告知くん</title>
  <link rel="shortcut icon" href="../favicon.ico">
  <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
  <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/css/bootstrap-datetimepicker.min.css">
  <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/summernote/0.8.1/summernote.css">
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
<?php
if($error == ""){
echo <<<EOM
          <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"> メニュー <span class="caret"></span></a>
          <ul class="dropdown-menu" role="menu">
            <li><a href="../list/?id={$id}"><i class="glyphicon glyphicon-education"></i> イベントTOP</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="./?cmd=list&id={$id}"><i class="fa fa-users"></i> 申込者リスト</a></li>
            <li><a href="./?cmd=mail&id={$id}"><i class="glyphicon glyphicon-envelope"></i> E-Mailリスト</a></li>
            <li><a href="./?cmd=edit&id={$id}"><i class="glyphicon glyphicon-pencil"></i> イベントの編集</a></li>
            <li><a href="" data-toggle="modal" data-target="#delete"><i class="glyphicon glyphicon-trash"></i> イベントの削除</a></li>
          </ul>
          </li>
EOM;
}
?>
        </ul>
      </div>
    </div>
  </div>
</header>

<div class="container">

<?php
if(empty($error)){

echo <<<EOM
  <div class="row">
    <div class="col-lg-12">
      <h1><i class="glyphicon glyphicon-education"></i> {$title}</h1>
    </div>
  </div>

  <hr>

  <div class="row">
    <div class="col-lg-12">

EOM;

  if($success_msg != ""){

    $msg = '<div class="bs-component">';
    $msg .= '<div class="alert alert-dismissible alert-success">';
    $msg .= '<button type="button" class="close" data-dismiss="alert">&times;</button>';
    $msg .= '<strong>Well done!</strong> ' . $success_msg;
    $msg .= '</div>';
    $msg .= '</div>';

    echo $msg;

  }

  if($alert_msg != ""){

    $msg = '<div class="bs-component">';
    $msg .= '<div class="alert alert-dismissible alert-info">';
    $msg .= '<button type="button" class="close" data-dismiss="alert">&times;</button>';
    $msg .= '<h4>Heads up!</h4><p>' . $alert_msg . '</p>';
    $msg .= '</div>';
    $msg .= '</div>';

    echo $msg;

  }


  if($cmd == "list"){

echo <<<EOM
      <h4>参加者リスト</h4>
      <div class="bs-component">
        <table class="table table-striped table-hover ">
          <thead>
            <tr>
              <th>#</th>
              <th>お名前</th>
              <th>メールアドレス</th>
            </tr>
          </thead>
          <tbody>
            <tr>
            {$list_html}
            </tr>
          </tbody>
        </table>
      </div>
    </div>
EOM;

  }elseif($cmd == "mail"){

echo <<<EOM
      <div class="bs-component">
        <form class="form-horizontal">
        <fieldset>

        <div class="form-group">
          <label for="textArea" class="control-label">参加者E-Mailアドレス</label>
            <textarea class="form-control" rows="5" id="mail_textarea">{$html_mail}</textarea>
        </div>

        <div class="form-group">
          <button type="button" class="btn btn-primary" id="all_select">全て選択</button>
        </div>

        </fieldset>
        </form>
      </div>
    </div>
EOM;

  }elseif($cmd == "edit"){

echo <<<EOM
      <form id="postForm" enctype="multipart/form-data" data-toggle="validator" role="form" class="form-horizontal" action="./" method="post" onsubmit="return postForm()">

      <fieldset>

      <input type="hidden" name="id" value="{$id}">
      <input type="hidden" name="cmd" value="change">

      <div class="form-group">
        <label for="inputName" class="control-label">イベントの名前</label>
        <input type="text" maxlength="50" class="form-control" id="inputName" name="name" placeholder="50文字以内で入力してください(必須)" value="{$title}" required>
      </div>

      <div class="form-group">
        <label for="inputPlace" class="control-label">会場</label>
        <select class="form-control" name="place" id="inputPlace">
        {$option_tag}
        </select>
      </div>

      <div class="form-group">
        <label for="datetimepicker1" class="control-label">開催日</label>
        <div class="input-group date" id="datetimepicker1">
          <input type="text" class="form-control date-1" name="date1" placeholder="右のアイコンを押して選択してください" required>
          <span class="input-group-addon">
          <span class="glyphicon glyphicon-calendar"></span>
          </span>
        </div>
      </div>

      <div class="form-group">
        <label for="inputCapacity" class="control-label">定員</label>
        <div class="input-group">
          <input type="number" class="form-control" id="inputCapacity" name="capacity" value="{$capacity}" placeholder="定員がない場合は未入力でOK(任意)">
          <span class="input-group-addon">名</span>
        </div>
      </div>

      <div class="form-group">
        <label for="summernote" class="control-label">概要</label>
        <textarea class="input-block-level" id="summernote" name="memo">{$memo}</textarea>
      </div>

      <div class="form-group">
        <button type="submit" class="btn btn-primary">変更</button>
        <button type="reset" class="btn btn-default">キャンセル</button>
      </div>
      </fieldset>

      </form>

    </div>
EOM;

  }


echo <<<EOM

  </div>


  <!-- 削除画面のモーダルstart -->
  <div id="delete" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <form class="form-horizontal" action="../list.php" method="post">
        <input type="hidden" name="id" value="{$id}">
        <input type="hidden" name="cmd" value="delete">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        </div>
        <div class="modal-body">
          <div class="well bs-component">
            <p>
            イベント「{$title}」を削除してもよろしいですか？
            </p>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">OK</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">キャンセル</button>
        </div>
        </form>
      </div>
    </div>
  </div>
  <!-- 削除画面のモーダルend -->


EOM;
}else{
echo <<<EOM
  <div class="row">
    <div class="col-lg-12">
      <h1 class="text-danger">Oh snap!</h1>
    </div>
  </div>

  <hr>

  <div class="row">
    <div class="col-lg-12">
EOM;

  $err_msg = '<div class="bs-component">';
  $err_msg .= '<div class="alert alert-dismissible alert-danger">';
  $err_msg .= '<button type="button" class="close" data-dismiss="alert">&times;</button>';
  $err_msg .= '<a href="../" class="alert-link">' . $error . '</a> Try submitting again.';
  $err_msg .= '</div>';
  $err_msg .= '</div>';

  echo $err_msg;

echo <<<EOM
    </div>
  </div>
EOM;
}
?>

  <hr>

  <footer class="footer">

  <p>
  Copyright (C) 2016 <a href="http://tsukuba42195.top/">Akira Mukai</a><br>
  </p>

  </footer><!-- /footer -->

</div> <!-- /container -->


<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/summernote/0.8.1/summernote.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/summernote/0.8.1/lang/summernote-ja-JP.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.12.0/moment.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.12.0/moment-with-locales.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/js/bootstrap-datetimepicker.min.js"></script>
<script src="../js/validator.js"></script>

<script type="text/javascript">
$(function(){

  $('.input-group-btn [data-toggle="tooltip"]').tooltip();

  $('#datetimepicker1').datetimepicker({
    locale: 'ja',
    format : 'YYYY/M/D(dd)',
    defaultDate : <?php echo "'" . $date . "'\n"; ?>
  });

  $('#summernote').summernote({
    height: 400,
    lang: 'ja-JP'
  });

  var postForm = function() {
    var memo = $('textarea[name="memo"]').html($('#summernote').code());
  }

  $('#all_select').click(function() {
    $('#mail_textarea').select();
  });

});
</script>
</body>
</html>
