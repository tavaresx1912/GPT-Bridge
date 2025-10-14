GPT-Bridge

Descrição
GPT-Bridge é uma aplicação web simples em PHP que faz chamadas à API da OpenAI. O projeto contém uma página única (index.php) com formulário e uma rota backend auxiliar (backend_gpt.php) para chamadas via AJAX ou POST.

Segurança de segredos (importante)
- Nunca faça commit de chaves/segredos no repositório.
- A chave da OpenAI é lida da variável de ambiente OPENAI_API_KEY. Você pode usar um arquivo local .env (não versionado) durante o desenvolvimento.
- Um arquivo .env.example foi incluído para servir de modelo. Copie-o para .env e preencha com sua chave local (não faça commit).

Configuração
1) Defina a variável de ambiente OPENAI_API_KEY
   - Windows (PowerShell):
     setx OPENAI_API_KEY "sua-chave-aqui"
     (Abra um novo terminal após executar o comando.)
   - ou crie um arquivo .env na raiz do projeto baseado em .env.example:
     OPENAI_API_KEY="sua-chave-aqui"

2) Servir o projeto
   - PHP embutido (dev):
     php -S localhost:8000
   - Depois acesse http://localhost:8000/index.php

Arquivos principais
- index.php: Página principal (processa POST do formulário e chama a API).
- backend_gpt.php: Endpoint JSON para chamadas programáticas.

Observações
- Se OPENAI_API_KEY não estiver configurada, a aplicação exibirá uma mensagem de erro amigável.
- Para remover segredos já presentes no histórico do Git, é necessário reescrever o histórico (git filter-repo ou BFG) e forçar push. Além disso, lembre-se de revogar/rotacionar a chave exposta.

