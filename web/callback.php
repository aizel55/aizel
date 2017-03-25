<?php
// アクセスキー
$gnaviaccesskey = getenv('gnavi_access_key');
$accessToken = getenv('LINE_CHANNEL_ACCESS_TOKEN');

//ユーザーからのメッセージ取得
$json_string = file_get_contents('php://input');
$jsonObj = json_decode($json_string);

$type = $jsonObj->{"events"}[0]->{"message"}->{"type"};
//メッセージ取得
$text = $jsonObj->{"events"}[0]->{"message"}->{"text"};
//ReplyToken取得
$replyToken = $jsonObj->{"events"}[0]->{"replyToken"};

//緯度取得
$latitude = $jsonObj->{"events"}[0]->{"message"}->{"latitude"};
//経度取得
$longitude = $jsonObj->{"events"}[0]->{"message"}->{"longitude"};


//エンドポイントのURIとフォーマットパラメータを変数に入れる
$uri   = "http://api.gnavi.co.jp/RestSearchAPI/20150630/";
//APIアクセスキーを変数に入れる
$acckey= $gnaviaccesskey;
//返却値のフォーマットを変数に入れる
$format= "json";
//緯度・経度、範囲を変数に入れる

// 業態がラーメン屋さんを意味するぐるなびのコード(大業態マスタ取得APIをコールして調査)
$category_s = "RSFST08008";

$hit_per_page = "5";

//緯度経度は日本測地系で日比谷シャンテのもの。範囲はrange=2で500m以内を指定している。
$range = 2;

//URL組み立て
$url  = sprintf("%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s", $uri, "?format=", $format, "&keyid=", $acckey, "&latitude=", $latitude,"&longitude=",$longitude,"&category_s=",$category_s,"&range=",$range,"&hit_per_page=",$hit_per_page);
//API実行
$json = file_get_contents($url);
//取得した結果をオブジェクト化
$obj  = json_decode($json);

$total_hit_count = $obj->{'total_hit_count'};
$result = "";

//デバッグ用画像データ
$image_sample = "";

//結果をパース
//トータルヒット件数、店舗番号、店舗名、最寄の路線、最寄の駅、最寄駅から店までの時間、店舗の小業態を出力
if ($total_hit_count === null) {
    $result .= "近くにラーメン屋さんはありません。";
}else{
    $result .= "近くにあるラーメン屋さんです。\n\n";

    foreach((array)$obj as $key => $val){
      if(strcmp($key, "rest") == 0){
          foreach((array)$val as $restArray){
               $result .= $restArray->{'name'}."\n";
               $result .= $restArray->{'url'}."\n";
               $image_sample = $restArray->{'image_url'}->{'shop_image1'};
              }
     
          }
    }
   // $response_format_text .="Powered by ぐるなび";
}

//返信データ作成
//	"type" => "text",
//	"text" => $result
  $response_format_text = [
    "type" => "template",
    "altText" => "こちらの〇〇はいかがですか？",
    "template" => [
      "type" => "buttons",
      "thumbnailImageUrl" => "https://" . $_SERVER['SERVER_NAME'] . "/img1.jpg",
      "title" => "○○レストラン".$image_sample,
      "text" => "お探しのレストランはこれですね",
      "actions" => [
          [
            "type" => "postback",
            "label" => "予約する",
            "data" => "action=buy&itemid=123"
          ],
          [
            "type" => "postback",
            "label" => "電話する",
            "data" => "action=pcall&itemid=123"
          ],
          [
            "type" => "uri",
            "label" => "詳しく見る",
            "uri" => "http://www.yahoo.co.jp/"
          ],
          [
            "type" => "message",
            "label" => "違うやつ",
            "text" => "違うやつお願い"
          ]
      ]
    ]
  ];

$post_data = [
	"replyToken" => $replyToken,
	"messages" => [$response_format_text]
	];

$ch = curl_init("https://api.line.me/v2/bot/message/reply");
    curl_setopt($ch, CURLOPT_POST,true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json; charser=UTF-8',
      'Authorization: Bearer ' . $accessToken
    ));
    $result = curl_exec($ch);
    curl_close($ch);

