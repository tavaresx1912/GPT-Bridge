<?php
// Inicia a sessão PHP para armazenar dados entre requisições
session_start();

// Recupera a resposta do GPT armazenada na sessão (se existir)
$resposta_gpt = isset($_SESSION['resposta']) ? $_SESSION['resposta'] : null;
// Recupera mensagens de erro armazenadas na sessão (se existir)
$erro = isset($_SESSION['erro']) ? $_SESSION['erro'] : null;

// Limpa os dados da sessão após recuperá-los (para não exibir novamente)
if ($resposta_gpt || $erro) {
    unset($_SESSION['resposta']);
    unset($_SESSION['erro']);
}

// Verifica se o formulário foi enviado via método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Chave da API do OpenAI via variável de ambiente (nunca faça commit de segredos)
    $API_KEY_OPENAI = "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";

    if (!$API_KEY_OPENAI) {
        $_SESSION['erro'] = "Configuração ausente: defina a variável de ambiente OPENAI_API_KEY no servidor.";
        header("Location: index.php");
        exit;
    }

    // Captura os dados enviados pelo formulário
    $prompt = $_POST['mensagem'];
    $opcao = $_POST['opcao'];
    /**
     * Função que faz a chamada para a API do OpenAI
     * @param array $mensagem - Array com as mensagens para enviar ao GPT
     * @param string $API_KEY_OPENAI - Chave da API
     * @return string - Resposta do GPT
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
        // Inicializa uma sessão cURL para a API do OpenAI
        $curl = curl_init("https://api.openai.com/v1/chat/completions");

        // Configura as opções da requisição cURL
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,  // Retorna a resposta como string
            CURLOPT_SSL_VERIFYPEER => false,  // Desabilita verificação SSL (apenas para desenvolvimento local)
            CURLOPT_SSL_VERIFYHOST => false,  // Desabilita verificação do host SSL (apenas para desenvolvimento local)
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",  // Define o tipo de conteúdo como JSON
                "Authorization: Bearer $API_KEY_OPENAI"  // Adiciona o token de autenticação
            ],
            CURLOPT_POST => true,  // Define que é uma requisição POST
            CURLOPT_POSTFIELDS => json_encode([  // Converte os dados para JSON
                "model" => "gpt-4o-mini",  // Define o modelo do GPT a ser usado
                "messages" => $mensagem  // Envia as mensagens
            ])
        ]);

        // Executa a requisição e armazena a resposta
        $response = curl_exec($curl);
        // Fecha a conexão cURL
        curl_close($curl);

        // Decodifica a resposta JSON em um array PHP
        $result = json_decode($response, true);
        // Retorna apenas o conteúdo da mensagem de resposta
        return $result["choices"][0]["message"]["content"];
    }

    // PRIMEIRA CHAMADA: Validação do prompt
    // Prompt do sistema que instrui o GPT a validar se a mensagem faz sentido
    $validate_system = "se o prompt fizer sentindo retorne true e se não retorne false";
    // Prompt do usuário contendo a opção selecionada e a mensagem
    $validate_user = "Opção: $opcao
    Prompt: $prompt";

    // Monta o array de mensagens para validação
    $validate_messages = [
        ["role" => "system", "content" => $validate_system],
        ["role" => "user", "content" => $validate_user]
    ];

    // Chama o GPT para validar se a mensagem faz sentido
    $validacao = call_openai($validate_messages, $API_KEY_OPENAI);

    // Verifica se a validação retornou algo diferente de "true"
    if ($validacao !== 'true') {
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
                // Se a opção não for válida, armazena erro
                $_SESSION['erro'] = "Opção inválida";
        }
    } else {
        // SEGUNDA CHAMADA: Processamento real da requisição
        // Define o prompt do sistema de acordo com a opção selecionada
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
                // Se a opção não for válida, armazena erro
                $_SESSION['erro'] = "Opção inválida";
        }

        // Se não houver erro, faz a chamada final ao GPT
        if (!isset($_SESSION['erro'])) {
            // Monta o array de mensagens com o prompt personalizado
            $final_messages = [
                ["role" => "system", "content" => $system_prompt],
                ["role" => "user", "content" => $validate_user]
            ];

            // Chama o GPT e armazena a resposta na sessão
            $_SESSION['resposta'] = call_openai($final_messages, $API_KEY_OPENAI);

            sql_docker($opcao, $prompt, $_SESSION['resposta']);
        }
    }

    // Redireciona para a mesma página usando método GET (padrão PRG - Post-Redirect-Get)
    // Isso limpa o POST e reseta o formulário
    header("Location: index.php");
    exit;
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>ChatBot com IA</title>
    
    <!-- Pré-conecta aos servidores do Google Fonts para melhorar performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    
    <!-- Importa a fonte Public Sans do Google Fonts -->
    <link
            href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100..900;1,100..900&display=swap"
            rel="stylesheet"
    />
    
    <!-- Importa o CSS do Bootstrap 5.3.8 -->
    <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
            rel="stylesheet"
            integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
            crossorigin="anonymous"
    />
    
    <!-- Importa o CSS customizado do projeto -->
    <link rel="stylesheet" href="./styles.css" />
</head>

<body>
<!-- Cabeçalho da página -->
<header>
    <nav class="navbar">
        <div class="container-fluid">
            <a class="navbar-brand text-white" href="#">
                <!-- Logo do projeto -->
                <img src="logo.svg" alt="logo" width="30" height="30" class="me-2"/>
                <span>GPTBridge</span>
            </a>
        </div>
    </nav>
</header>

<!-- Conteúdo principal da página -->
<main>
    <div class="container">
        <div class="chat-container">
            <!-- Formulário que envia os dados via POST para a mesma página -->
            <form id="chat-form" method="post">
                
                <!-- Campo de seleção da opção -->
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

                <!-- Campo de entrada da mensagem -->
                <div class="mb-3">
                    <label for="mensagem" class="form-label">Sua mensagem:</label>
                    <div class="input-group">
                        <input
                                id="mensagem"
                                name="mensagem"
                                type="text"
                                class="form-control"
                                placeholder="Escreva sua pergunta aqui..."
                                required
                        />
                        <!-- Botão de envio -->
                        <button class="btn btn-primary" type="submit">Enviar</button>
                    </div>
                </div>
            </form>

            <!-- Área de exibição da resposta ou erro (só aparece se houver dados) -->
            <?php if ($resposta_gpt || $erro): ?>
            <div id="resposta-chat" class="resposta-container">
                <div class="card">
                    <div class="card-header">
                        <!-- Exibe "Erro:" ou "Resposta:" dependendo do caso -->
                        <strong><?php echo $erro ? 'Erro:' : 'Resposta:'; ?></strong>
                    </div>
                    <div class="card-body" id="resposta-texto">
                        <?php if ($erro): ?>
                            <!-- Se houver erro, exibe em um alerta vermelho -->
                            <div class="alert alert-danger" role="alert">
                                <?php echo htmlspecialchars($erro); ?>
                            </div>
                        <?php else: ?>
                            <!-- Se houver resposta, exibe formatada (nl2br converte quebras de linha em <br>) -->
                            <p><?php echo nl2br(htmlspecialchars($resposta_gpt)); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- Rodapé da página -->
<footer class="rodape">
    <div class="text-center p-3 text-white">
        © 2025 Copyright:
        <a href="https://www.brazcubas.edu.br/" target="_blank" class="text-white">brazcubas.edu.br</a>
    </div>
</footer>

<!-- Importa o JavaScript do Bootstrap (necessário para componentes interativos) -->
<script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"
></script>
</body>
</html>