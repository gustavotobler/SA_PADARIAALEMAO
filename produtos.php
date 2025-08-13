<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8"> <!-- Define a codificação dos caracteres para UTF-8 -->
  <title>Produtos</title> <!-- Título da página que aparece na aba do navegador -->

  <!-- Link para importar os ícones do Google Material Icons -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

  <!-- Link para o arquivo CSS que estiliza a página -->
  <link href="css/site3.css" rel="stylesheet">
</head>
<body>
  <header>
    <div class="topo"> <!-- Container da barra superior -->
      <div class="topo-left"> <!-- Área esquerda da barra superior -->
        <a href="inicial1.html"> <!-- Link que leva para a página inicial -->
          <!-- Ícone de seta para voltar, usando Material Icons -->
          <span class="material-icons icon" title="Voltar">arrow_back</span>
        </a>
      </div>

      <div class="topo-center"> <!-- Área central da barra superior -->
        <h1>PRODUTOS</h1> <!-- Título da página -->

        <!-- Container do campo de pesquisa -->
        <div class="search-container">
          <!-- Ícone de lupa ao lado do input -->
          <span class="material-icons">search</span>

          <!-- Campo de texto para digitar o termo de pesquisa -->
          <input type="text" id="search-input" placeholder="Pesquisar...">

          <!-- Botão para iniciar a busca -->
          <button id="search-btn" type="button">Pesquisar</button>
        </div>
      </div>

      <div class="topo-right"> <!-- Área direita da barra superior -->
        <!-- Link para a página de cadastro de produto, com botão de adicionar (começa escondido) -->
        <a href="cadproduto.html" id="add-button" class="hidden">
          <button class="icon-btn add-btn" title="Adicionar">
            <!-- Ícone de adicionar -->
            <span class="material-icons">add</span>
          </button>
        </a>

        <!-- Ícone que funciona como botão para mostrar ou ocultar as ações da tabela -->
        <span class="material-icons icon edit-toggle" id="edit-toggle" title="Mostrar/Ocultar Ações">edit</span>
      </div>
    </div>
  </header>

  <main>
    <table>
      <thead>
        <tr>
          <!-- Cabeçalho da tabela -->
          <th>ID FORNECEDOR</th>
          <th>ID</th>
          <th>Nome do produto</th>
          <th>Preço</th>
          <th>Quantidade</th>
          <!-- Coluna para ações como editar e excluir, inicialmente escondida -->
          <th class="action-header hidden">Ações</th>
        </tr>
      </thead>
      <tbody id="supplier-table-body">
        <!-- Linha da tabela com produto exemplo 1 -->
        <tr>
          <td>Serviço próprio</td>
          <td>P1</td>
          <td>Pão Francês</td>
          <td>R$ 0,50</td>
          <td>100/Por dia</td>
          <td class="action-cell hidden"> <!-- Célula de ações, escondida inicialmente -->
            <div class="action-icons">
              <!-- Botão de editar -->
              <button class="icon-btn edit-btn"><span class="material-icons">edit</span></button>
              <!-- Botão de excluir -->
              <button class="icon-btn delete-btn"><span class="material-icons">delete</span></button>
              <!-- Botão para desfazer exclusão -->
              <button class="icon-btn undo-btn"><span class="material-icons">undo</span></button>
            </div>
          </td>
        </tr>
        <!-- Linha da tabela com produto exemplo 2 -->
        <tr>
          <td>Serviço próprio</td>
          <td>P2</td>
          <td>Pão integral</td>
          <td>R$ 6,00</td>
          <td>62</td>
          <td class="action-cell hidden">
            <div class="action-icons">
              <button class="icon-btn edit-btn"><span class="material-icons">edit</span></button>
              <button class="icon-btn delete-btn"><span class="material-icons">delete</span></button>
              <button class="icon-btn undo-btn"><span class="material-icons">undo</span></button>
            </div>
          </td>
        </tr>
        <!-- Linha da tabela com produto exemplo 3 -->
        <tr>
          <td>FR02</td>
          <td>P3</td>
          <td>Bolo de Chocolate</td>
          <td>R$ 12,00</td>
          <td>15</td>
          <td class="action-cell hidden">
            <div class="action-icons">
              <button class="icon-btn edit-btn"><span class="material-icons">edit</span></button>
              <button class="icon-btn delete-btn"><span class="material-icons">delete</span></button>
              <button class="icon-btn undo-btn"><span class="material-icons">undo</span></button>
            </div>
          </td>
        </tr>
        <!-- Linha da tabela com produto exemplo 4 -->
        <tr>
          <td>FR02</td>
          <td>P4</td>
          <td>Bolo de morango</td>
          <td>R$ 14,00</td>
          <td>35</td>
          <td class="action-cell hidden">
            <div class="action-icons">
              <button class="icon-btn edit-btn"><span class="material-icons">edit</span></button>
              <button class="icon-btn delete-btn"><span class="material-icons">delete</span></button>
              <button class="icon-btn undo-btn"><span class="material-icons">undo</span></button>
            </div>
          </td>
        </tr>
        <!-- Linha da tabela com produto exemplo 5 -->
        <tr>
          <td>FR02</td>
          <td>P5</td>
          <td>Bolo de abacaxi</td>
          <td>R$ 15,00</td>
          <td>27</td>
          <td class="action-cell hidden">
            <div class="action-icons">
              <button class="icon-btn edit-btn"><span class="material-icons">edit</span></button>
              <button class="icon-btn delete-btn"><span class="material-icons">delete</span></button>
              <button class="icon-btn undo-btn"><span class="material-icons">undo</span></button>
            </div>
          </td>
        </tr>
        <!-- Linha da tabela com produto exemplo 6 -->
        <tr>
          <td>Serviço próprio</td>
          <td>P6</td>
          <td>Cuca de banana</td>
          <td>R$ 10,00</td>
          <td>40</td>
          <td class="action-cell hidden">
            <div class="action-icons">
              <button class="icon-btn edit-btn"><span class="material-icons">edit</span></button>
              <button class="icon-btn delete-btn"><span class="material-icons">delete</span></button>
              <button class="icon-btn undo-btn"><span class="material-icons">undo</span></button>
            </div>
          </td>
        </tr>
        <!-- Linha da tabela com produto exemplo 7 -->
        <tr>
          <td>Serviço próprio</td>
          <td>P7</td>
          <td>Rosca doce</td>
          <td>R$ 5,00</td>
          <td>53</td>
          <td class="action-cell hidden">
            <div class="action-icons">
              <button class="icon-btn edit-btn"><span class="material-icons">edit</span></button>
              <button class="icon-btn delete-btn"><span class="material-icons">delete</span></button>
              <button class="icon-btn undo-btn"><span class="material-icons">undo</span></button>
            </div>
          </td>
        </tr>
        <!-- Linha da tabela com produto exemplo 8 -->
        <tr>
          <td>Serviço próprio</td>
          <td>P8</td>
          <td>Orelha de gato</td>
          <td>R$ 19,00/KG</td>
          <td>20</td>
          <td class="action-cell hidden">
            <div class="action-icons">
              <button class="icon-btn edit-btn"><span class="material-icons">edit</span></button>
              <button class="icon-btn delete-btn"><span class="material-icons">delete</span></button>
              <button class="icon-btn undo-btn"><span class="material-icons">undo</span></button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </main>

  <script>
    // Quando o conteúdo do documento for carregado, executa o código
    document.addEventListener('DOMContentLoaded', () => {
      // Seleciona o botão que alterna a visualização das ações
      const toggleBtn = document.querySelector('.edit-toggle');

      // Seleciona o cabeçalho da coluna de ações
      const actionHeader = document.querySelector('th.action-header');

      // Seleciona o corpo da tabela
      const tableBody = document.getElementById('supplier-table-body');

      // Seleciona o botão para adicionar produto
      const addButton = document.getElementById('add-button');

      // Função que retorna todas as células da coluna ações da tabela
      const getActionCells = () => document.querySelectorAll('td.action-cell');

      // Quando o botão de toggle for clicado
      toggleBtn.addEventListener('click', () => {
        // Alterna a classe 'hidden' no cabeçalho da coluna Ações para mostrar/esconder
        actionHeader.classList.toggle('hidden');

        // Para cada célula da coluna Ações, alterna a visibilidade
        getActionCells().forEach(td => td.classList.toggle('hidden'));

        // Alterna a visibilidade do botão "Adicionar"
        addButton.classList.toggle('hidden');
      });

      // Configura os eventos para o botão de pesquisar e campo de input
      document.getElementById('search-btn').addEventListener('click', doSearch);
      document.getElementById('search-input').addEventListener('input', doSearch);

      // Função que executa a busca de produtos
      function doSearch() {
        // Pega o texto digitado no campo de busca e transforma em minúsculas
        const term = document.getElementById('search-input').value.toLowerCase();

        // Para cada linha da tabela
        Array.from(tableBody.rows).forEach(row => {
          // Verifica se algum dos primeiros 5 campos da linha contém o termo buscado
          const match = Array.from(row.cells).slice(0, 5).some(td =>
            td.textContent.toLowerCase().includes(term)
          );

          // Se encontrar o termo, exibe a linha, senão esconde
          row.style.display = match ? '' : 'none';

          // Controla visibilidade da célula de ações baseado no filtro e no cabeçalho
          const cell = row.querySelector('td.action-cell');
          if (cell) cell.classList.toggle('hidden', !match || actionHeader.classList.contains('hidden'));
        });
      }

      // Função que adiciona os eventos aos botões de ação na tabela
      function bindActions() {
        // Para cada botão de editar
        document.querySelectorAll('.edit-btn').forEach(btn => {
          btn.onclick = () => {
            // Encontra a linha da tabela correspondente ao botão clicado
            const row = btn.closest('tr');

            // Encontra o ícone dentro do botão para alterar seu texto (edit/save)
            const icon = btn.querySelector('.material-icons');

            // Alterna a classe 'editing' para entrar ou sair do modo edição
            const editing = row.classList.toggle('editing');

            // Altera o ícone conforme o modo
            icon.textContent = editing ? 'save' : 'edit';

            // Número de colunas para editar (exclui a coluna ações)
            const numCols = row.cells.length - 1;

            // Para cada célula de dados da linha
            for (let i = 0; i < numCols; i++) {
              const cell = row.cells[i];
              if (editing) {
                // Se entrou no modo edição, substitui o texto por um input para edição
                const inp = document.createElement('input');
                inp.type = 'text';
                inp.value = cell.textContent;
                cell.textContent = ''; // Limpa o texto antigo
                cell.appendChild(inp); // Adiciona o input
              } else {
                // Se saiu do modo edição, pega o valor do input e substitui o texto da célula
                const inp = cell.querySelector('input');
                if (inp) cell.textContent = inp.value;
              }
            }
          };
        });

        // Para cada botão de excluir
        document.querySelectorAll('.delete-btn').forEach(btn => {
          btn.onclick = () => {
            // Encontra a linha da tabela
            const row = btn.closest('tr');

            // Marca a linha como desabilitada (você pode usar CSS para riscar ou mudar cor)
            row.classList.add('disabled-row');

            // Esconde o botão excluir
            btn.style.display = 'none';

            // Mostra o botão desfazer
            const undoBtn = row.querySelector('.undo-btn');
            if (undoBtn) undoBtn.style.display = 'inline-block';
          };
        });

        // Para cada botão desfazer exclusão
        document.querySelectorAll('.undo-btn').forEach(btn => {
          btn.onclick = () => {
            // Encontra a linha da tabela
            const row = btn.closest('tr');

            // Remove a marcação de linha desabilitada
            row.classList.remove('disabled-row');

            // Esconde o botão desfazer
            btn.style.display = 'none';

            // Mostra o botão excluir
            const deleteBtn = row.querySelector('.delete-btn');
            if (deleteBtn) deleteBtn.style.display = 'inline-block';
          };
        });
      }

      // Chama a função para ligar os eventos nos botões existentes
      bindActions();
    });
  </script>
</body>
</html>
