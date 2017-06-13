<?php
function get($url, array $params = [], array $headers = [])
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url . '?' . http_build_query($params, '', '&'), 
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FAILONERROR => true,
        CURLOPT_HTTPHEADER => $headers,
    ]);
    $response = curl_exec($ch);
    if ($response === false) {
        throw new RuntimeException(curl_error($ch));
    }
    return json_decode($response);
}

function post($url, array $params = [], array $headers = [])
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FAILONERROR => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($params, '', '&'),
        CURLOPT_HTTPHEADER => $headers,
    ]);
    $response = curl_exec($ch);
    if ($response === false) {
        throw new RuntimeException(curl_error($ch));
    }
    return json_decode($response);
}

// アクセスキー
$accessKeys = [
    'GNAVI' => getenv('gnavi_access_key'),
    'LINE' => getenv('LINE_CHANNEL_ACCESS_TOKEN'),
];

// ユーザーからのメッセージ取得
$request = json_decode(file_get_contents('php://input'));

// ぐるなびへのリクエスト実行
$response = get('http://api.gnavi.co.jp/RestSearchAPI/20150630', [
    'format' => 'json',
    'keyid' => $accessKeys['GNAVI'],
    'latitude' => $request->events[0]->message->latitude,
    'longitude' => $request->events[0]->message->longitude,
    'category_s' => 'RSFST08008', // 業態がラーメン屋さんを意味するぐるなびのコード(大業態マスタ取得APIをコールして調査)
    'range' => 2, // 緯度経度は日本測地系で日比谷シャンテのもの。範囲はrange=2で500m以内を指定している。
    'hit_per_page' => 5, 
]);

// LINEへのリクエスト実行
post('https://api.line.me/v2/bot/message/reply', [
    'replyToken' => $request->events[0]->replyToken,
    'messages' => [
        'type' => 'template',
        'altText' => '候補をご案内しています。(Powered by ぐるなび)',
        'template' => [
            'type' => 'carousel',
            'columns' => array_map(function ($rest) {
                return [
                    'title' => $rest->name,
                    'text' => "住所: $rest->address",
                    'actions' => [
                        'type' => 'uri',
                        'label' => 'URL',
                        'uri' => $rest->url,
                    ]
                ];
            }, $response->rest),
        ]
    ],
], [
    'Content-Type: application/json; charser=UTF-8',
    "Authorization: Bearer " . $accessKeys[LINE]
]);