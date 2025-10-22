-- Cria a tabela `mensagens` usada pela aplicação
CREATE TABLE IF NOT EXISTS mensagens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  opcao VARCHAR(50) NOT NULL,
  mensagem TEXT NOT NULL,
  resposta TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

