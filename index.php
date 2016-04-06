<?php
/*
 * スマートフォン向け イベント告知ツール
 * 
 * http://tsukuba42195.top/
 * Copyright(c)2016 Akira Mukai All rights reserved.
 * 
 */

// 10桁のランダムな文字列を生成する
$newid = makeRandStr(10);

/**
 * ランダム文字列生成 (英数字)
 * $length: 生成する文字数
 */
function makeRandStr($length) {
    static $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJLKMNOPQRSTUVWXYZ0123456789';
    $str = '';
    for ($i = 0; $i < $length; ++$i) {
        $str .= $chars[mt_rand(0, 61)];
    }
    return $str;
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="keywords" content="イベント,告知">
  <meta name="description" content="勉強会の告知と運営・管理を行なうフリーのツールです。">
  <title>告知くん - 勉強会の開催＆支援ツール</title>
  <link rel="shortcut icon" href="./favicon.ico">
  <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
  <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/css/bootstrap-datetimepicker.min.css">
  <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/summernote/0.8.1/summernote.css">
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



  .board{
    width: 75%;
    margin: 60px auto;
    height: 600px;
    background: #fff;
  }
  .board .nav-tabs {
    position: relative;
    /* border-bottom: 0; */
    /* width: 80%; */
    margin: 40px auto;
    margin-bottom: 0;
    box-sizing: border-box;
  }

  .board > div.board-inner{
    background: #fafafa;
    background-image: url('./geometry2.png');
    background-size: 30%;
  }

  p.narrow{
    width: 80%;
    margin: 10px auto;
  }

  .liner{
    height: 2px;
    background: #ddd;
    position: absolute;
    width: 80%;
    margin: 0 auto;
    left: 0;
    right: 0;
    top: 50%;
    z-index: 1;
  }

  .nav-tabs > li.active > a, .nav-tabs > li.active > a:hover, .nav-tabs > li.active > a:focus {
    color: #555555;
    cursor: default;
    /* background-color: #ffffff; */
    border: 0;
    border-bottom-color: transparent;
  }

  span.round-tabs{
    width: 70px;
    height: 70px;
    line-height: 70px;
    display: inline-block;
    border-radius: 100px;
    background: white;
    z-index: 2;
    position: absolute;
    left: 0;
    text-align: center;
    font-size: 25px;
  }

  span.round-tabs.one{
    color: rgb(34, 194, 34);
    border: 2px solid rgb(34, 194, 34);
  }

  li.active span.round-tabs.one{
    background: #fff !important;
    border: 2px solid #ddd;
    color: rgb(34, 194, 34);
  }

  span.round-tabs.two{
    color: #febe29;
    border: 2px solid #febe29;
  }

  li.active span.round-tabs.two{
    background: #fff !important;
    border: 2px solid #ddd;
    color: #febe29;
  }

  span.round-tabs.three{
    color: #3e5e9a;
    border: 2px solid #3e5e9a;
  }

  li.active span.round-tabs.three{
    background: #fff !important;
    border: 2px solid #ddd;
    color: #3e5e9a;
  }

  span.round-tabs.four{
    color: #f1685e;
    border: 2px solid #f1685e;
  }

  li.active span.round-tabs.four{
    background: #fff !important;
    border: 2px solid #ddd;
    color: #f1685e;
  }

  span.round-tabs.five{
    color: #999;
    border: 2px solid #999;
  }

  li.active span.round-tabs.five{
    background: #fff !important;
    border: 2px solid #ddd;
    color: #999;
  }

  .nav-tabs > li.active > a span.round-tabs{
    background: #fafafa;
  }
  .nav-tabs > li {
    width: 20%;
  }
  /*li.active:before {
    content: " ";
    position: absolute;
    left: 45%;
    opacity:0;
    margin: 0 auto;
    bottom: -2px;
    border: 10px solid transparent;
    border-bottom-color: #fff;
    z-index: 1;
    transition:0.2s ease-in-out;
  }*/
  li:after {
    content: " ";
    position: absolute;
    left: 45%;
    opacity:0;
    margin: 0 auto;
    bottom: 0px;
    border: 5px solid transparent;
    border-bottom-color: #ddd;
    transition:0.1s ease-in-out;
  }
  li.active:after {
    content: " ";
    position: absolute;
    left: 45%;
    opacity:1;
    margin: 0 auto;
    bottom: 0px;
    border: 10px solid transparent;
    border-bottom-color: #ddd;
  }
  .nav-tabs > li a{
    width: 70px;
    height: 70px;
    margin: 20px auto;
    border-radius: 100%;
    padding: 0;
  }

  .nav-tabs > li a:hover{
    background: transparent;
  }

  .tab-content{
  }
  .tab-pane{
    position: relative;
    padding-top: 50px;
  }
  .tab-content .head{
    font-family: 'Roboto Condensed', sans-serif;
    font-size: 25px;
    text-transform: uppercase;
    padding-bottom: 10px;
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
          <li><a href="list.php"><i class="glyphicon glyphicon-list"></i> 勉強会一覧</a></li>
        </ul>
      </div>
    </div>
  </div>
</header>

<div id="main" class="container">

  <section id="make">
  <div class="row">

    <div class="board">
      <div class="board-inner">
        <ul class="nav nav-tabs">
        <div class="liner">
        </div>
        <li class="active">
        <a href="#home" data-toggle="tab" title="ようこそ！">
        <span class="round-tabs one">
        <i class="glyphicon glyphicon-home"></i>
        </span>
        </a>
        </li>

        <li class="disabled">
        <a href="#profile" data-toggle="tab" title="イベントの詳細">
        <span class="round-tabs two">
        <i class="glyphicon glyphicon-comment"></i>
        </span>
        </a>
        </li>

        <li class="disabled">
        <a href="#messages" data-toggle="tab" title="イベント日時など">
        <span class="round-tabs three">
        <i class="fa fa-calendar-check-o"></i>
        </span>
        </a>
        </li>

        <li class="disabled">
        <a href="#settings" data-toggle="tab" title="管理者用パスワード">
        <span class="round-tabs four">
        <i class="fa fa-key"></i>
        </span> 
        </a>
        </li>

        <li class="disabled">
        <a href="#doner" data-toggle="tab" title="完成">
        <span class="round-tabs five">
        <i class="glyphicon glyphicon-ok"></i>
        </span>
        </a>
        </li>

        </ul>
      </div>

      <div class="tab-content">
        <div class="tab-pane fade in active" id="home">

          <h3 class="head text-center">「告知くん」にようこそ <span style="color:#f48260;"><i class="glyphicon glyphicon-heart"></i></h3>
          <p class="narrow text-center">
          「告知くん」は勉強会などのイベント告知＆運営・管理を行うツールです。<br>無料・登録不要・使い捨て型のWebサービスです！
          </p>

          <p class="text-center">
          <button type="button" class="btn btn-success btn-outline-rounded green next-step"> 始める <span style="margin-left:10px;" class="glyphicon glyphicon-send"></span></button>
          </p>

        </div>

        <div class="tab-pane fade" id="profile">
          <h3 class="head text-center"><span class="label label-danger">STEP 1</span></h3>

          <!-- STEP1の内容 start -->
          <form data-toggle="validator" role="form" class="form-horizontal">

          <div class="col-sm-10 col-sm-offset-1">

            <div class="form-group">
              <label for="inputName" class="control-label">イベントの名前</label>
              <input type="text" maxlength="50" class="form-control" id="inputName" name="name" placeholder="50文字以内で入力してください(必須)" required>
            </div>

            <div class="form-group">
              <label for="inputPlace" class="control-label">会場</label>
              <select class="form-control" name="place" id="inputPlace">
                <option value="北海道" selected>北海道</option>
                <option value="青森県">青森県</option>
                <option value="岩手県">岩手県</option>
                <option value="宮城県">宮城県</option>
                <option value="秋田県">秋田県</option>
                <option value="山形県">山形県</option>
                <option value="福島県">福島県</option>
                <option value="東京都">東京都</option>
                <option value="神奈川県">神奈川県</option>
                <option value="埼玉県">埼玉県</option>
                <option value="千葉県">千葉県</option>
                <option value="茨城県">茨城県</option>
                <option value="栃木県">栃木県</option>
                <option value="群馬県">群馬県</option>
                <option value="山梨県">山梨県</option>
                <option value="新潟県">新潟県</option>
                <option value="長野県">長野県</option>
                <option value="富山県">富山県</option>
                <option value="石川県">石川県</option>
                <option value="福井県">福井県</option>
                <option value="愛知県">愛知県</option>
                <option value="岐阜県">岐阜県</option>
                <option value="静岡県">静岡県</option>
                <option value="三重県">三重県</option>
                <option value="大阪府">大阪府</option>
                <option value="兵庫県">兵庫県</option>
                <option value="京都府">京都府</option>
                <option value="滋賀県">滋賀県</option>
                <option value="奈良県">奈良県</option>
                <option value="和歌山県">和歌山県</option>
                <option value="鳥取県">鳥取県</option>
                <option value="島根県">島根県</option>
                <option value="岡山県">岡山県</option>
                <option value="広島県">広島県</option>
                <option value="山口県">山口県</option>
                <option value="徳島県">徳島県</option>
                <option value="香川県">香川県</option>
                <option value="愛媛県">愛媛県</option>
                <option value="高知県">高知県</option>
                <option value="福岡県">福岡県</option>
                <option value="佐賀県">佐賀県</option>
                <option value="長崎県">長崎県</option>
                <option value="熊本県">熊本県</option>
                <option value="大分県">大分県</option>
                <option value="宮崎県">宮崎県</option>
                <option value="鹿児島県">鹿児島県</option>
                <option value="沖縄県">沖縄県</option>
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
                <input type="number" class="form-control" id="inputCapacity" name="capacity" placeholder="定員がない場合は未入力でOK(任意)">
                <span class="input-group-addon">名</span>
              </div>
            </div>

            <ul class="list-inline text-center">
              <li><button type="button" class="btn btn-default prev-step"><span class="glyphicon glyphicon-chevron-left"></span> 前へ</button></li>
              <li><button type="button" class="btn btn-primary btn-info-full next-step" id="stp1btn">次へ <span class="glyphicon glyphicon-chevron-right"></span></button></li>
            </ul>

          </div>
          <!-- STEP1の内容 end -->

        </div>

        <div class="tab-pane fade" id="messages">
          <h3 class="head text-center"><span class="label label-danger">STEP 2</span></h3>

          <!-- STEP2の内容 start -->
          <div class="col-sm-10 col-sm-offset-1">
            <div class="form-group">
              <div id="summernote">概要</div>
            </div>
            <ul class="list-inline text-center">
            <li><button type="button" class="btn btn-default prev-step"><span class="glyphicon glyphicon-chevron-left"></span> 前へ</button></li>
            <li><button type="button" class="btn btn-primary btn-info-full next-step" id="stp2btn">次へ <span class="glyphicon glyphicon-chevron-right"></span></button></li>
            </ul>
          </div>
          <!-- STEP2の内容 end -->

        </div>

        <div class="tab-pane fade" id="settings">
          <h3 class="head text-center"><span class="label label-danger">STEP 3</span></h3>

          <!-- STEP3の内容 start -->
          <div class="col-sm-10 col-sm-offset-1">
            <div class="form-group has-feedback">
              <input type="password" pattern="^[_A-z0-9]{1,}$" maxlength="15" data-minlength="4" class="form-control" id="inputPassword" name="password" placeholder="パスワード" required>
              <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
              <div class="help-block">4文字以上15文字以内</div>
            </div>
            <ul class="list-inline text-center">
            <li><button type="button" class="btn btn-default prev-step"><span class="glyphicon glyphicon-chevron-left"></span> 前へ</button></li>
            <li><button type="button" class="btn btn-primary btn-info-full next-step" id="stp3btn">次へ <span class="glyphicon glyphicon-chevron-right"></span></button></li>
            </ul>
          </div>
          <input type="hidden" name="id" value="<?php echo $newid; ?>" id="eventid">
          </form>
          <!-- STEP3の内容 end -->

        </div>

        <div class="tab-pane fade" id="doner">
          <div class="text-center">
            <i class="img-intro icon-checkmark-circle"></i>
          </div>
          <h3 class="head text-center">完成 <span style="color:#f48260;"><i class="glyphicon glyphicon-heart"></i></span></h3>
          <p class="narrow text-center">
          下記のURLから参加者の募集が行えます。<br>
          以後このURLページにて参加者の管理を行なってください。

          <div class="alert alert-info text-center" role="alert">
            <div id="result1"></div>
          </div>

          <ul class="list-inline text-center">
          <li><div id="result2"></div></li>
          </ul>

          </p>

        </div>

        <div class="clearfix"></div>

      </div>

    </div>

  </div>
  </section>


  <hr>

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
<script src="//cdnjs.cloudflare.com/ajax/libs/summernote/0.8.1/summernote.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/summernote/0.8.1/lang/summernote-ja-JP.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.12.0/moment.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.12.0/moment-with-locales.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/js/bootstrap-datetimepicker.min.js"></script>
<script src="./js/validator.js"></script>

<script type="text/javascript">
$(function(){
  $('a[title]').tooltip();

  $('#datetimepicker1').datetimepicker({
    locale: 'ja',
    format : 'YYYY/M/D(dd)'
  });

  $('#summernote').summernote({
    height: 200,
    lang: 'ja-JP'
  });

  //Wizard
  $('a[data-toggle="tab"]').on('show.bs.tab', function (e) {

    var $target = $(e.target);

    if ($target.parent().hasClass('disabled')) {
      return false;
    }
  });

  $(".next-step").click(function (e) {
    var $active = $('.board .nav-tabs li.active');
    $active.next().removeClass('disabled');
    nextTab($active);
  });

  $(".prev-step").click(function (e) {
    var $active = $('.board .nav-tabs li.active');
    prevTab($active);
  });

  // 入力チェック（ボタンを押させない）
  if ($('#inputName').val().length == 0) {
    $('#stp1btn').prop('disabled', true);
  }
  $('#inputName').on('keydown keyup keypress change', function() {
  if ($(this).val().length > 0) {
    $('#stp1btn').prop('disabled', false);
  } else {
    $('#stp1btn').prop('disabled', true);
  }
  });

  // 入力チェック（ボタンを押させない）
  if ($('#summernote').summernote('isEmpty')) {
    $('#stp2btn').prop('disabled', true);
  }else{
    $('#stp2btn').prop('disabled', false);
  }

  // 入力チェック（ボタンを押させない）
  if ($('#inputPassword').val().length == 0) {
    $('#stp3btn').prop('disabled', true);
  }
  $('#inputPassword').on('keydown keyup keypress change', function() {
    if ($(this).val().length > 0) {
      $('#stp3btn').prop('disabled', false);
    } else {
      $('#stp3btn').prop('disabled', true);
    }
  });

  // 非同期通信を行ない結果を表示する
  $('#stp3btn').click(function () {
    $.ajax({
      type: "POST",
      url: "comp.php", //PHPを呼び出す
      dataType: 'json',
      data: {
        name: $('#inputName').val(), 
        place: $('#inputPlace').val(), 
        date1: $('.date-1').val(), 
        capacity: $('#inputCapacity').val(), 
        password: $('#inputPassword').val(), 
        content: $('#summernote').summernote('code'), 
        id: $('#eventid').val()
      }
    })
    .done(function (response) {
      $('#result1').html(response.data1);
      $('#result2').html(response.data2);
    })
    .fail(function () {
      // jqXHR, textStatus, errorThrown と書くのは長いので、argumentsでまとめて渡す
      // (PHPのfunc_get_args関数の返り値のようなもの)
      $('#result1').val('失敗');
      $('#result2').val(errorHandler(arguments));
    });

    var $active = $('.board .nav-tabs li.active');
    $active.next().removeClass('disabled');
    nextTab($active);

  });

});

function nextTab(elem) {
  $(elem).next().find('a[data-toggle="tab"]').click();
};
function prevTab(elem) {
  $(elem).prev().find('a[data-toggle="tab"]').click();
};

</script>

</body>
</html>
