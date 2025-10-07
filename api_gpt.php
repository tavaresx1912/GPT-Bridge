<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $API_KEY_OPENAI = $_ENV['OPENAI_KEY'];

    $prompt = $_POST['mensagem'];

    $curl = curl_init("https://api.openai.com/v1/chat/completions");

    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,  // Disable SSL verification for local development
        CURLOPT_SSL_VERIFYHOST => false,  // Disable SSL host verification
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Authorization: Bearer $API_KEY_OPENAI"
        ],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode([
            "model" => "gpt-4o-mini",
            "messages" => [
                ["role" => "user", "content" => $prompt]
            ]
        ])
    ]);

    $response = curl_exec($curl);
    
    // Check for cURL errors
    if (curl_errno($curl)) {
        $error = curl_error($curl);
        curl_close($curl);
        echo "<h2>Resposta da OpenAI:</h2>";
        echo "<p>Erro de conexão: " . htmlspecialchars($error) . "</p>";
        exit;
    }
    
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    $result = json_decode($response, true);

    echo "<h2>Resposta da OpenAI:</h2>";

    // Check if the response is valid and has the expected structure
    if ($result && isset($result["choices"][0]["message"]["content"])) {
        echo "<p>" . $result["choices"][0]["message"]["content"] . "</p>";
    } else {
        // Display error information
        echo "<p>Erro ao processar a resposta da API.</p>";
        echo "<p>HTTP Code: " . $httpCode . "</p>";
        if ($result && isset($result["error"])) {
            echo "<p>Detalhes: " . htmlspecialchars($result["error"]["message"]) . "</p>";
        } else {
            echo "<p>Resposta recebida: " . htmlspecialchars($response) . "</p>";
        }
    }

}
?>