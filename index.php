<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"> <!-- Define a codificação de caracteres para UTF-8 -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Para o site ser responsivo -->
    <title>Padaria do Alemão - Login</title> <!-- Título que aparece na aba do navegador -->
    <link rel="stylesheet" type="text/css" href="css/site.css"> <!-- Link para o arquivo CSS externo -->
</head>
<body>

    <header>
        <h1>LOGIN</h1> <!-- Título da página -->
        <img src="img/Logopadaria.png" title="Logo Padaria" alt="Logo da Padaria"> <!-- Logo da padaria com título e texto alternativo -->
    </header>

    <main>
        <!-- Formulário de login -->
        <form id="form">
            <label for="email">E-mail:</label> <!-- Label para o input de email -->
            <input type="text" name="txtemail" id="email" class="inputs required" required> <!-- Campo de email obrigatório -->

            <label for="senha">Senha:</label> <!-- Label para o input de senha -->
            <input type="password" id="senha" name="senha" required> <!-- Campo de senha obrigatório -->

            <button type="submit">Entrar</button> <!-- Botão para enviar o formulário -->

            <a href="RECUP_SENHA.html" class="esqueceu-senha" id="esqueceu-senha">Esqueceu a senha?</a> <!-- Link para recuperação de senha -->
        </form>
    </main>

    <footer>
		<div class ="fonte">
        <strong>Copyright &copy; Alemões</strong> <!-- Copyright da página -->
		</div>
    </footer>

    <script>
        // Adiciona um listener para o evento de envio do formulário
        document.getElementById("form").addEventListener("submit", function(event) {
            event.preventDefault(); // Evita que o formulário seja enviado de forma tradicional (recarregando a página)

            // Pega os valores digitados no email e converte para minúsculas
            const email = document.getElementById("email").value.toLowerCase();
            const senha = document.getElementById("senha").value; // Pega a senha

            // Lista simulando os funcionários cadastrados com email e senha
            const funcionarios = [
                { nome: 'Lara Gorito Barbosa de Souza', email: 'laragorito@padaria.com', senha: 'caixa123' },
                { nome: 'Humberto Guessinger', email: 'humbertoguessinger@padaria.com', senha: 'atendente123' },
                { nome: 'Sergio Luiz', email: 'sergioluiz@padaria.com', senha: 'padeiro123' },
                { nome: 'Mbappe', email: 'mbappe@padaria.com', senha: 'auxiliar123' },
                { nome: 'Tony Stark', email: 'tonystark@padaria.com', senha: 'atendente123' },
                { nome: 'Rafaela Elisa', email: 'rafaelaelisa@padaria.com', senha: 'confeiteira123' },
                { nome: 'Kerry King', email: 'kerryking@padaria.com', senha: 'admin123' }
            ];

            // Procura na lista um funcionário que tenha email e senha iguais aos digitados
            const usuario = funcionarios.find(f => f.email === email && f.senha === senha);

            if (usuario) {
                // Define o nível de acesso: se o nome for 'Kerry King' é admin, senão funcionário normal
                const nivel = usuario.nome === 'Kerry King' ? 'admin' : 'funcionario';

                // Salva o nível de usuário no localStorage para usar depois
                localStorage.setItem("nivel_usuario", nivel);

                // Redireciona o usuário para a página inicial de acordo com o nível dele
                if (nivel === "admin") {
                    window.location.href = "inicial1.html";
                } else {
                    window.location.href = "inicial2.html";
                }
            } else {
                // Se não encontrou usuário com email e senha corretos, avisa com um alert
                alert("Email ou senha inválidos.");
            }
        });
    </script>

</body>
</html>
