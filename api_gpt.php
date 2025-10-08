<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $API_KEY_OPENAI = "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";

    $prompt = $_POST['mensagem'];
    $opcao = $_POST['opcao'];


    function get_response($prompt, $API_KEY_OPENAI, $mensagem) {
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
                    ["role" => "system", "content" => $mensagem],
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


    switch ($opcao) {
        case 'chat':
            get_response($prompt, $API_KEY_OPENAI, "Você é um assistente virtual que responde perguntas sobre o mundo da tecnologia. Responda com uma resposta em português, caso não seja um pergunta fale para o usuário");
            break;
        case 'resumo':
            get_response($prompt, $API_KEY_OPENAI, "Você é um assistente virtual que responde perguntas sobre o mundo da tecnologia. Responda com um resumo em português.");
            break;
        case 'termos':
            get_response($prompt, $API_KEY_OPENAI, "Você é um assistente virtual que responde perguntas sobre o mundo da tecnologia. Responda explicando termos técnicos em português.");
            break;
        case 'corretor':
            get_response($prompt, $API_KEY_OPENAI, "Você é um assistente virtual que responde perguntas sobre o mundo da tecnologia. Responda com uma correção de texto em português.");
            break;
        case 'ideias':
            get_response($prompt, $API_KEY_OPENAI, "Você é um assistente virtual que responde perguntas sobre o mundo da tecnologia. Responda com ideias criativas em português.");
            break;
        default:
            echo "<p>Opção inválida.</p>";
            break;
    }
}
?>