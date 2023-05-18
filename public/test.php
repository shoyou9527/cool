<?php 
$apiKey = "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";
$url = 'https://api.openai.com/v1/chat/completions';

$headers = array(
    "Authorization: Bearer {$apiKey}",
    "OpenAI-Organization: org-UyOiu4QFds9k1zR3vRqq3JSP",
    "Content-Type: application/json"
);

// Define messages
$messages = array();
$messages[] = array("role" => "user", "content" => "你身為一個專業seo工程師 寫出你對於seo的看法，必須達4千字以上的文章");

// Define data
$data = array();
$data["model"] = "gpt-3.5-turbo";
$data["messages"] = $messages;
$data["max_tokens"] = 4000;

// init curl
$curl = curl_init($url);
curl_setopt($curl, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

$result = curl_exec($curl);
if (curl_errno($curl)) {
    echo 'Error:' . curl_error($curl);
} else {
    // echo $result;
}
print_r(json_decode($result));
?>