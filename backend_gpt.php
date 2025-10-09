<?php

header('Content-Type: application/json, charset=utf-8');

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $API_KEY_OPENAI = "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";

    $prompt = $_POST['mensagem'];
    $opcao = $_POST['opcao'];


    function call_openai($mensagem, $API_KEY_OPENAI) {
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
                "messages" => $mensagem
            ])
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        $result = json_decode($response, true);
        return $result["choices"][0]["message"]["content"];
    }
    
    $validate_system = "se o prompt fizer sentindo retorne true e se não retorne false";
    $validate_user = "Opção: $opcao
    Prompt: $prompt";
    
    $validate_messages = [
        ["role" => "system", "content" => $validate_system],
        ["role" => "user", "content" => $validate_user]
    ];
    
    $validacao = call_openai($validate_messages, $API_KEY_OPENAI);

    if ((strtolower(trim($validacao)) !== 'true')) {
        http_response_code(403);
        echo json_encode([
            "valid" => false,
            "opcao" => $opcao,
            "prompt" => $prompt,
            "mensagem" => "O GPT considerou que a mensagem não faz sentido. Acesso bloqueado para essa requisição."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit; // importante: garante que nada mais execute
    }


    switch ($opcao) {
        case 'chat':
            $system_prompt = "Você é um assistente virtual que responde perguntas sobre tecnologia em português.";
            break;
        case 'resumo':
            $system_prompt = "Você é um assistente virtual que responde perguntas sobre o mundo da tecnologia. Responda com um resumo em português.";
            break;
        case 'termos':
            $system_prompt = "Você é um assistente virtual que responde perguntas sobre o mundo da tecnologia. Responda explicando termos técnicos em português.";
            break;
        case 'corretor':
            $system_prompt = "Você é um assistente virtual que responde perguntas sobre o mundo da tecnologia. Responda com uma correção de texto em português.";
            break;
        case 'ideias':
            $system_prompt = "Você é um assistente virtual que responde perguntas sobre o mundo da tecnologia. Responda com ideias criativas em português.";
            break;
        default:
            echo json_encode(["error" => "Opção inválida"]);
            exit;
    }

    $final_messages = [
        ["role" => "system", "content" => $system_prompt],
        ["role" => "user", "content" => $validate_user]
    ];

    $resposta = call_openai($final_messages, $API_KEY_OPENAI);

    echo json_encode([
        "valid" => true,
        "opcao" => $opcao,
        "prompt" => $prompt,
        "mensagem" => $resposta
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

}
?>