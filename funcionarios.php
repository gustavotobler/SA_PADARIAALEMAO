<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8"> <!-- Define codificação de caracteres -->
  <title>Funcionários</title> <!-- Título da aba do navegador -->
  
  <!-- Link para arquivo CSS externo -->
  <link rel="stylesheet" href="css/site3.css">
  
  <!-- Importa fonte de ícones do Google Material Icons -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  
  <!-- Estilos CSS internos -->
  <style>
    .hidden { display: none !important; } /* Classe para esconder elementos */

    /* Estilos para linha desabilitada (ex: deletada) */
    .disabled-row {
      background-color: #a9a9a9; /* Fundo cinza */
      color: #666; /* Cor do texto cinza escuro */
    }
    /* Estilo para inputs dentro da linha desabilitada */
    .disabled-row input {
      background: #d3d3d3; /* Fundo cinza claro */
      color: #aaa; /* Texto cinza claro */
      border: none; /* Sem borda */
    }

    /* Botão de desfazer inicialmente escondido */
    .undo-btn {
      display: none;
      background: transparent;
      border: none;
      cursor: pointer; /* Cursor em forma de mãozinha */
    }
  </style>
</head>
<body>
  <header>
    <div class="topo"> <!-- Container topo da página -->
      <div class="topo-left">
        <a href="inicial1.php"> <!-- Link para página inicial -->
          <span class="material-icons icon" title="Voltar">arrow_back</span> <!-- Ícone de seta para voltar -->
        </a>
      </div>

      <div class="topo-center"> <!-- Centro do topo -->
        <h1>FUNCIONÁRIOS</h1> <!-- Título principal -->

        <div class="search-container"> <!-- Container da busca -->
          <span class="material-icons">search</span> <!-- Ícone de lupa -->
          <input type="text" id="search-input" placeholder="Pesquisar..."> <!-- Input texto da busca -->
          <button id="search-btn" type="button">Pesquisar</button> <!-- Botão para buscar -->
        </div>
      </div>

      <div class="topo-right"> <!-- Lado direito do topo -->
        <!-- Link para página de cadastro, inicialmente escondido -->
        <a href="cadfunc.php" id="add-button" class="hidden">
          <button class="icon-btn add-btn" title="Adicionar">
            <span class="material-icons">add</span> <!-- Ícone de adicionar (+) -->
          </button>
        </a>

        <!-- Ícone para mostrar/ocultar ações -->
        <span class="material-icons icon edit-toggle" id="edit-toggle" title="Mostrar/Ocultar Ações">edit</span>
      </div>
    </div>
  </header>

  <main>
    <table> <!-- Tabela de funcionários -->
      <thead>
        <tr>
          <th>ID</th> <!-- Coluna ID -->
          <th>Nome</th> <!-- Coluna Nome -->
          <th>Cargo</th> <!-- Coluna Cargo -->
          <th>Nascimento</th> <!-- Coluna Data de Nascimento -->
          <th>Admissão</th> <!-- Coluna Data de Admissão -->
          <th class="action-header hidden">Ações</th> <!-- Coluna Ações, inicialmente escondida -->
        </tr>
      </thead>

      <tbody id="func-table-body"> <!-- Corpo da tabela -->

        <!-- Linha de funcionário -->
        <tr>
          <td>94787</td> <!-- ID -->
          <td>Peter Parker</td> <!-- Nome -->
          <td>Atendente</td> <!-- Cargo -->
          <td>01/06/1996</td> <!-- Nascimento -->
          <td>15/06/2023</td> <!-- Admissão -->
          <td class="action-cell hidden"> <!-- Célula de ações, inicialmente escondida -->
            <button class="icon-btn edit-btn"><span class="material-icons">edit</span></button> <!-- Botão editar -->
            <button class="icon-btn delete-btn"><span class="material-icons">delete</span></button> <!-- Botão deletar -->
            <button class="undo-btn"><span class="material-icons">undo</span></button> <!-- Botão desfazer -->
          </td>
        </tr>

        <!-- Outras linhas seguem o mesmo padrão -->
        <tr>
          <td>86767</td><td>Humberto Guessinger</td><td>Atendente</td><td>11/10/1993</td><td>19/01/2024</td>
          <td class="action-cell hidden">
            <button class="icon-btn edit-btn"><span class="material-icons">edit</span></button>
            <button class="icon-btn delete-btn"><span class="material-icons">delete</span></button>
            <button class="undo-btn"><span class="material-icons">undo</span></button>
          </td>
        </tr>
        <tr>
          <td>67678</td><td>Sergio Luiz</td><td>Padeiro</td><td>13/01/1972</td><td>20/06/2023</td>
          <td class="action-cell hidden">
            <button class="icon-btn edit-btn"><span class="material-icons">edit</span></button>
            <button class="icon-btn delete-btn"><span class="material-icons">delete</span></button>
            <button class="undo-btn"><span class="material-icons">undo</span></button>
          </td>
        </tr>
        <tr>
          <td>12345</td><td>Lara Gorito Barbosa de Souza</td><td>Caixa</td><td>22/08/1989</td><td>05/04/2021</td>
          <td class="action-cell hidden">
            <button class="icon-btn edit-btn"><span class="material-icons">edit</span></button>
            <button class="icon-btn delete-btn"><span class="material-icons">delete</span></button>
            <button class="undo-btn"><span class="material-icons">undo</span></button>
          </td>
        </tr>
        <tr>
          <td>23456</td><td>Kerry King</td><td>Gerente</td><td>10/05/1980</td><td>15/01/2020</td>
          <td class="action-cell hidden">
            <button class="icon-btn edit-btn"><span class="material-icons">edit</span></button>
            <button class="icon-btn delete-btn"><span class="material-icons">delete</span></button>
            <button class="undo-btn"><span class="material-icons">undo</span></button>
          </td>
        </tr>
        <tr>
          <td>34567</td><td>Mbappe'</td><td>Auxiliar</td><td>03/12/1995</td><td>12/07/2022</td>
          <td class="action-cell hidden">
            <button class="icon-btn edit-btn"><span class="material-icons">edit</span></button>
            <button class="icon-btn delete-btn"><span class="material-icons">delete</span></button>
            <button class="undo-btn"><span class="material-icons">undo</span></button>
          </td>
        </tr>
        <tr>
          <td>45678</td><td>Tony Stark</td><td>Atendente</td><td>17/09/1992</td><td>01/03/2023</td>
          <td class="action-cell hidden">
            <button class="icon-btn edit-btn"><span class="material-icons">edit</span></button>
            <button class="icon-btn delete-btn"><span class="material-icons">delete</span></button>
            <button class="undo-btn"><span class="material-icons">undo</span></button>
          </td>
        </tr>
        <tr>
          <td>56789</td><td>Rafaela Elisa</td><td>Confeiteira</td><td>25/04/1987</td><td>10/10/2021</td>
          <td class="action-cell hidden">
            <button class="icon-btn edit-btn"><span class="material-icons">edit</span></button>
            <button class="icon-btn delete-btn"><span class="material-icons">delete</span></button>
            <button class="undo-btn"><span class="material-icons">undo</span></button>
          </td>
        </tr>
      </tbody>
    </table>
  </main>

  <script>
    // Espera carregar o conteúdo da página para executar o script
    document.addEventListener('DOMContentLoaded', () => {
      // Botão para alternar edição
      const toggleBtn = document.getElementById('edit-toggle');
      
      // Cabeçalho da coluna ações
      const actionHeader = document.querySelector('th.action-header');
      
      // Botão para adicionar funcionário
      const addButton = document.getElementById('add-button');
      
      // Função que retorna todas as células de ação na tabela
      const getActionCells = () => document.querySelectorAll('td.action-cell');

      // Evento ao clicar no botão de alternar ações
      toggleBtn.addEventListener('click', () => {
        actionHeader.classList.toggle('hidden'); // Mostrar/ocultar cabeçalho ações
        getActionCells().forEach(td => td.classList.toggle('hidden')); // Mostrar/ocultar botões ações nas linhas
        addButton.classList.toggle('hidden'); // Mostrar/ocultar botão de adicionar funcionário
      });

      // Seleciona todos os botões de editar e adiciona evento de clique
      document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          const row = btn.closest('tr'); // Linha onde o botão está
          const icon = btn.querySelector('.material-icons'); // Ícone dentro do botão
          const editing = row.classList.toggle('editing'); // Alterna classe editing na linha
          icon.textContent = editing ? 'save' : 'edit'; // Muda ícone para salvar ou editar
          
          // Para as 5 primeiras células da linha (ID, Nome, Cargo, Nascimento, Admissão)
          for (let i = 0; i < 5; i++) {
            const cell = row.cells[i];
            if (editing) {
              // Se está entrando em modo edição, cria input e coloca valor atual
              const input = document.createElement('input');
              input.type = 'text';
              input.value = cell.textContent;
              cell.textContent = ''; // Limpa texto antigo
              cell.appendChild(input); // Adiciona input na célula
            } else {
              // Se está saindo do modo edição, pega valor do input e atualiza célula
              const input = cell.querySelector('input');
              if (input) cell.textContent = input.value;
            }
          }
        });
      });

      // Seleciona todos os botões de deletar e adiciona evento
      document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          const row = btn.closest('tr'); // Linha do botão
          row.classList.add('disabled-row'); // Adiciona estilo desabilitado
          btn.style.display = 'none'; // Esconde botão deletar
          row.querySelector('.undo-btn').style.display = 'inline-block'; // Mostra botão desfazer
        });
      });

      // Seleciona todos os botões de desfazer e adiciona evento
      document.querySelectorAll('.undo-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          const row = btn.closest('tr'); // Linha do botão
          row.classList.remove('disabled-row'); // Remove estilo desabilitado
          btn.style.display = 'none'; // Esconde botão desfazer
          row.querySelector('.delete-btn').style.display = 'inline-block'; // Mostra botão deletar
        });
      });

      // Elementos da busca
      const searchInput = document.getElementById('search-input');
      const searchBtn = document.getElementById('search-btn');
      const tableBody = document.getElementById('func-table-body');

      // Função que realiza a busca e filtra as linhas da tabela
      function doSearch() {
        const term = searchInput.value.trim().toLowerCase(); // Termo da busca em minúsculas e sem espaços extras
        Array.from(tableBody.rows).forEach(row => {
          // Verifica se alguma das primeiras 5 células contém o termo
          const match = Array.from(row.cells).slice(0, 5).some(td => td.textContent.toLowerCase().includes(term));
          row.style.display = match ? '' : 'none'; // Mostra ou esconde a linha
          
          // Controla visibilidade da célula de ações baseado na busca e se ações estão visíveis
          const cell = row.querySelector('td.action-cell');
          if (cell) cell.classList.toggle('hidden', !match || actionHeader.classList.contains('hidden'));
        });
      }

      // Eventos para disparar a busca ao clicar no botão ou digitar no input
      searchBtn.addEventListener('click', doSearch);
      searchInput.addEventListener('input', doSearch);
    });
  </script>
</body>
</html>
