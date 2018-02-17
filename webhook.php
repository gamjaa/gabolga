<?php
require 'twitteroauth/autoload.php';
use Abraham\TwitterOAuth\TwitterOAuth;

if (isset($_GET['crc_token'])) {
    // CRC request
    $response_token = base64_encode(hash_hmac('sha256', $_GET['crc_token'], getenv('GABOLGA_BOT_CONSUMER_SECRET'), true));
    
    header('Content-type: application/json');
    echo json_encode([
        'response_token' => "sha256=$response_token"
    ]);

} else if (isset(apache_request_headers()['X-Twitter-Webhooks-Signature'])) {
    // Validating the Signature Header
    $header = apache_request_headers()['X-Twitter-Webhooks-Signature'];
    $body = file_get_contents("php://input");
    $hash = 'sha256='.base64_encode(hash_hmac('sha256', $body, getenv('GABOLGA_BOT_CONSUMER_SECRET'), true));
    
    if (hash_equals($hash, $header)) {
        $mysqli = new mysqli('localhost', getenv('DB_ID'), getenv('DB_PW'), 'gabolga');
        $json = json_decode($body);
        
        // https://developer.twitter.com/en/docs/accounts-and-users/subscribe-account-activity/guides/account-activity-data-objects
        if (isset($json->direct_message_events) && $json->direct_message_events[0]->message_create->sender_id !== '903176813517479936') {
            // DM            
            $connection = new TwitterOAuth(getenv('GABOLGA_BOT_CONSUMER_KEY'), getenv('GABOLGA_BOT_CONSUMER_SECRET'), getenv('GABOLGA_BOT_ACCESS_TOKEN'), getenv('GABOLGA_BOT_ACCESS_TOKEN_SECRET'));

            if (isset($json->direct_message_events[0]->message_create->message_data->entities->urls[0])) {
                // 링크 있음
                $link = parse_url($json->direct_message_events[0]->message_create->message_data->entities->urls[0]->expanded_url);
                $path = explode('/', $link['path']);
    
                if ($link['host'] === 'twitter.com' && $path[2] === 'status') {
                    // 트윗 지도에 등록
                    $tweet = $mysqli->query("SELECT name FROM ".getenv('GABOLGA_DB_PREFIX')."tweet WHERE tweet_id={$path[3]}");

                    if ($tweet->num_rows > 0) {
                        // 등록된 트윗
                        $text = "{$json->users->{$json->direct_message_events[0]->message_create->sender_id}->name} 님의 지도에 '{$tweet->fetch_assoc()['name']}'이(가) 등록되었습니다. 확인해보세요!\nhttps://gabolga.gamjaa.com/my-map.php?tweet_id={$path[3]}";

                    } else {
                        // 미등록된 트윗
                        $text = "아직 가볼가에 등록되지 않은 트윗이에요. 직접 등록해주시면 {$json->users->{$json->direct_message_events[0]->message_create->sender_id}->name} 님의 지도에 장소가 기록된답니다!\nhttps://gabolga.gamjaa.com/tweet.php?tweet_id={$path[3]}&not=yet";

                    }
                    $tweet->free();
                    $mysqli->query("INSERT INTO ".getenv('GABOLGA_DB_PREFIX')."my_map (user_id, tweet_id) VALUES ({$json->direct_message_events[0]->message_create->sender_id}, {$path[3]})");

                    $connection->post('direct_messages/events/new', [
                        'event' => [
                            'type' => 'message_create',
                            'message_create' => [
                                'target' => [
                                    'recipient_id' => $json->direct_message_events[0]->message_create->sender_id
                                ],
                                'message_data' => [
                                    'text' => $text
                                ]
                            ]
                        ]
                    ], true);
                }

            } else {
                // 링크 없음
                $connection->post('direct_messages/events/new', [
                    'event' => [
                        'type' => 'message_create',
                        'message_create' => [
                            'target' => [
                                'recipient_id' => $json->direct_message_events[0]->message_create->sender_id
                            ],
                            'message_data' => [
                                'text' => '궁금한 점이나 건의할 사항이 있으시다면 멘션이나 @_gamjaa으로 DM 보내주세요! 감사합니다.'
                            ]
                        ]
                    ]
                ], true);
            }

        } else if (isset($json->favorite_events)) {
            // Favorite

        } else if (isset($json->follow_events)) {
            // Follow
            $connection = new TwitterOAuth(getenv('GABOLGA_BOT_CONSUMER_KEY'), getenv('GABOLGA_BOT_CONSUMER_SECRET'), getenv('GABOLGA_BOT_ACCESS_TOKEN'), getenv('GABOLGA_BOT_ACCESS_TOKEN_SECRET'));

                $connection->post('direct_messages/events/new', [
                    'event' => [
                        'type' => 'message_create',
                        'message_create' => [
                            'target' => [
                                'recipient_id' => $json->follow_events[0]->source->id
                            ],
                            'message_data' => [
                                'text' => "반갑습니다, {$json->follow_events[0]->source->name} 님! 팔로우 해주셔서 감사합니다.\n가볼까 싶은 장소가 적힌 트윗을 DM으로 공유해주세요! {$json->follow_events[0]->source->name} 님의 지도에 기록해드릴게요."
                            ]
                        ]
                    ]
                ], true);

        }
        
    } else {    // 해시값 불일치
        http_response_code(403);
    }
} else {
    http_response_code(403);    
}
?>