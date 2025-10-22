<?php
// Inicia a sessão PHP para armazenar dados entre requisições
session_start();

// --- Lógica de Exibição ---
$resposta_gpt = $_SESSION['resposta'] ?? null;
$erro = $_SESSION['erro'] ?? null;

// Limpa os dados da sessão após recuperá-los
unset($_SESSION['resposta'], $_SESSION['erro']);

// --- Lógica de Processamento do Formulário ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {



    if (empty($API_KEY_OPENAI)) {
        $_SESSION['erro'] = "Configuração ausente: a chave da API do OpenAI não foi encontrada.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    $prompt = trim($_POST['mensagem']);
    $opcao = $_POST['opcao'];

    /**
     * Função que faz a chamada para a API do OpenAI (VERSÃO ROBUSTA)
     * @param array $mensagem
     * @param string $API_KEY_OPENAI
     * @return string
     */

    function sql_docker($opcao, $mensagem, $resposta){
        // Altere esta linha de volta para o IP local
        $servername = "127.0.0.1";
        $username = "appuser";
        $password = "apppass";
        $database = "appdb";
        $port = 3306; // É uma boa prática definir a porta também

        try {
            // Adicione a porta ao DSN (string de conexão)
            $conn = new PDO("mysql:host=$servername;port=$port;dbname=$database;charset=utf8mb4", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // O resto do seu código está perfeito
            $stmt = $conn->prepare("INSERT INTO mensagens (opcao, mensagem, resposta) VALUES (:opcao, :mensagem, :resposta)");
            $stmt->bindParam(':opcao', $opcao);
            $stmt->bindParam(':mensagem', $mensagem);
            $stmt->bindParam(':resposta', $resposta);

            $stmt->execute();
            $conn = null;
            return true;
        } catch (PDOException $e) {
            error_log("Erro MySQL: " . $e->getMessage());
            return false;
        }
    }
    function call_openai($mensagem, $API_KEY_OPENAI) {
        $curl = curl_init("https://api.openai.com/v1/chat/completions");
        curl_setopt_array($curl, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_HTTPHEADER => [
                        "Content-Type: application/json",
                        "Authorization: Bearer " . $API_KEY_OPENAI
                ],
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode([
                        "model" => "gpt-4o-mini",
                        "messages" => $mensagem
                ])
        ]);

        $response = curl_exec($curl);
        $curl_error = curl_error($curl);
        curl_close($curl);

        if ($curl_error) {
            return "Erro de comunicação com a API: " . $curl_error;
        }

        $result = json_decode($response, true);

        if (isset($result['error'])) {
            return "Erro da API OpenAI: " . ($result['error']['message'] ?? 'Ocorreu um erro desconhecido.');
        }

        if (isset($result["choices"][0]["message"]["content"])) {
            return $result["choices"][0]["message"]["content"];
        }

        return "Não foi possível obter uma resposta válida da API.";
    }

    // (O restante do seu código PHP continua o mesmo...)

    // PRIMEIRA CHAMADA: Validação do prompt
    $validate_system = "se o prompt fizer sentindo retorne true e se não retorne false";
    $validate_user = "Opção: $opcao\nPrompt: $prompt";
    $validate_messages = [
            ["role" => "system", "content" => $validate_system],
            ["role" => "user", "content" => $validate_user]
    ];

    $validacao = call_openai($validate_messages, $API_KEY_OPENAI);

    $system_prompt = '';
    switch ($opcao) {
        case 'chat':
            $system_prompt = "Você é um assistente virtual que responde perguntas sobre tecnologia em português.";
            break;
        case 'resumo':
            $system_prompt = "Você é um assistente virtual que resume textos sobre o mundo da tecnologia em português.";
            break;
        case 'termos':
            $system_prompt = "Você é um assistente virtual que explica termos técnicos do mundo da tecnologia em português.";
            break;
        case 'corretor':
            $system_prompt = "Você é um assistente virtual que corrige textos em português.";
            break;
        case 'ideias':
            $system_prompt = "Você é um assistente virtual que gera ideias criativas sobre tecnologia em português.";
            break;
        default:
            $_SESSION['erro'] = "Opção inválida";
    }

    if (strpos($validacao, 'Erro') === 0 || isset($_SESSION['erro'])) {
        $_SESSION['erro'] = $validacao;
    } else {
        $final_messages = [
                ["role" => "system", "content" => $system_prompt],
                ["role" => "user", "content" => $prompt]
        ];
        $_SESSION['resposta'] = call_openai($final_messages, $API_KEY_OPENAI);
        sql_docker($opcao, $prompt, $_SESSION['resposta']);
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>ChatBot com IA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="./styles.css" />
</head>
<body>
<header>
    <nav class="navbar">
        <div class="container-fluid">
            <a class="navbar-brand text-white" href="#">
                <img src="logo.svg" alt="logo" width="30" height="30" class="me-2"/>
                <span>GPTBridge</span>
            </a>
        </div>
    </nav>
</header>
<main>
    <div class="container">
        <div class="chat-container">
            <form id="chat-form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="mb-3">
                    <label for="opcao" class="form-label">Selecione uma opção:</label>
                    <select class="form-select" id="opcao" name="opcao" required>
                        <option value="" selected disabled>Escolha uma opção</option>
                        <option value="chat">Chat de Perguntas e Respostas</option>
                        <option value="resumo">Gerador de Resumos</option>
                        <option value="termos">Explicador de Termos Acadêmicos</option>
                        <option value="corretor">Corretor de Texto Básico</option>
                        <option value="ideias">Sugestão de Ideias para Trabalho</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="mensagem" class="form-label">Sua mensagem:</label>
                    <div class="input-group">
                        <input id="mensagem" name="mensagem" type="text" class="form-control" placeholder="Escreva sua pergunta aqui..." required />
                        <button class="btn btn-primary" type="submit">Enviar</button>
                    </div>
                </div>
            </form>

            <?php if ($resposta_gpt || $erro): ?>
                <div id="resposta-chat" class="resposta-container mt-4">
                    <div class="card">
                        <div class="card-header">
                            <strong><?php echo $erro ? 'Erro:' : 'Resposta:'; ?></strong>
                        </div>
                        <div class="card-body" id="resposta-texto">
                            <?php if ($erro): ?>
                                <div class="alert alert-danger" role="alert">
                                    <?php echo htmlspecialchars($erro); ?>
                                </div>
                            <?php else: ?>
                                <p><?php echo nl2br(htmlspecialchars($resposta_gpt)); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>
<footer class="rodape">
    <div class="text-center p-3 text-white">
        © <?php echo date("Y"); ?> Copyright:
        <a href="https://www.brazcubas.edu.br/" target="_blank" class="text-white">brazcubas.edu.br</a>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="./script.js"></script>
</body>
</html>