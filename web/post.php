<?php
// [Webhook URL]欄に表示されているURL
$webhook_url = 'https://outlook.office.com/webhook/1bba23d0-f254-4c3d-b8a2-b8f7a442b1ec@83de2fdd-3b4b-4fb0-b1df-83d3adde868d/IncomingWebhook/45ce0726cdc345c3b1d5e2426c969d62/9d716b8a-4c71-4ad6-bd72-b8d8826c0ca2';

// Slackに投稿するメッセージ
$msg = array(
    'username' => 'MSTeamsテスト', 
    'text' => 'Hello, Teams Incoming WebHooks.'
);
$msg = json_encode($msg);
$msg = 'payload=' . urlencode($msg);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $webhook_url);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $msg);
curl_exec($ch);
curl_close($ch);