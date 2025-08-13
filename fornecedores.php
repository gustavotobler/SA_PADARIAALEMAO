<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Fornecedores</title>
  <!-- Estilo da página -->
  <link rel="stylesheet" href="css/site3.css">
  <!-- Ícones do Material Icons -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
  <!-- Cabeçalho da página -->
  <header>
    <div class="topo">
      <!-- Lado esquerdo: botão de voltar -->
      <div class="topo-left">
        <a href="inicial1.html">
          <span class="material-icons icon" title="Voltar">arrow_back</span>
        </a>
      </div>

      <!-- Centro: título e barra de pesquisa -->
      <div class="topo-center">
        <h1>FORNECEDORES</h1>
        <div class="search-container">
          <span class="material-icons">search</span>
          <input type="text" id="search-input" placeholder="Pesquisar...">
          <button id="search-btn" type="button">Pesquisar</button>
        </div>
      </div>

      <!-- Lado direito: botão de adicionar e de alternar ações -->
      <div class="topo-right">
        <!-- Botão de adicionar fornecedor (oculto por padrão) -->
        <a href="cadforn.html" id="add-button" class="hidden">
          <button class="icon-btn add-btn" title="Adicionar">
            <span class="material-icons">add</span>
          </button>
        </a>
        <!-- Botão para mostrar/ocultar ações -->
        <span class="material-icons icon edit-toggle" id="edit-toggle" title="Mostrar/Ocultar Ações">edit</span>
      </div>
    </div>
  </header>

  <!-- Conteúdo principal: tabela de fornecedores -->
  <main>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Fornecedores</th>
          <th>Produto fornecido</th>
          <th>Reabastecimento</th>
          <!-- Cabeçalho das ações (oculto por padrão) -->
          <th class="action-header hidden">Ações</th>
        </tr>
      </thead>
      <tbody id="supplier-table-body">
        <!-- Exemplo de linha de fornecedor -->
        <tr>
          <td>FR01</td><td>CARLINHOS</td><td>Trigo, leite...</td><td>12/03/2025</td>
          <!-- Coluna de ações (oculta por padrão) -->
          <td class="action-cell hidden">
            <div class="action-icons">
              <button class="icon-btn edit-btn"><span class="material-icons">edit</span></button>
              <button class="icon-btn delete-btn"><span class="material-icons">delete</span></button>
              <button class="undo-btn"><span class="material-icons">undo</span></button>
          </td>
        </tr>
        <!-- Outras linhas seguem o mesmo padrão... -->
        <tr>
          <td>FR02</td><td>BOLOS MAIDEN</td><td>Bolos, açúcares...</td><td>08/03/2025</td>
          <td class="action-cell hidden">
            <div class="action-icons">
              <button class="icon-btn edit-btn"><span class="material-icons">edit</span></button>
              <button class="icon-btn delete-btn"><span class="material-icons">delete</span></button>
              <button class="undo-btn"><span class="material-icons">undo</span></button>
            </div>
          </td>
        </tr>
        <tr>
          <td>FR03</td><td>DOCES MARIAS</td><td>Leite condensado</td><td>28/03/2025</td>
          <td class="action-cell hidden">
            <div class="action-icons">
              <button class="icon-btn edit-btn"><span class="material-icons">edit</span></button>
              <button class="icon-btn delete-btn"><span class="material-icons">delete</span></button>
              <button class="undo-btn"><span class="material-icons">undo</span></button>
          </td>
        </tr>
        <tr>
          <td>FR04</td><td>DONA BENTA</td><td>Chocolates, tortas...</td><td>08/03/2025</td>
          <td class="action-cell hidden">
            <div class="action-icons">
              <button class="icon-btn edit-btn"><span class="material-icons">edit</span></button>
              <button class="icon-btn delete-btn"><span class="material-icons">delete</span></button>
              <button class="undo-btn"><span class="material-icons">undo</span></button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </main>
  <!-- Scripts JavaScript -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const toggleBtn = document.querySelector('.edit-toggle');
      const actionHeader = document.querySelector('th.action-header');
      const tableBody = document.getElementById('supplier-table-body');
      const searchBtn = document.getElementById('search-btn');
      const searchInput = document.getElementById('search-input');
      const addButton = document.getElementById('add-button');

      const getActionCells = () => document.querySelectorAll('td.action-cell');

      // Alterna visibilidade das ações (editar/excluir) e botão "Adicionar"
      toggleBtn.addEventListener('click', () => {
        actionHeader.classList.toggle('hidden');
        getActionCells().forEach(td => td.classList.toggle('hidden'));
        addButton.classList.toggle('hidden');
      });

      // Função de busca nos dados da tabela
      function doSearch() {
        const term = searchInput.value.trim().toLowerCase();
        Array.from(tableBody.rows).forEach(row => {
          const match = Array.from(row.cells)
                             .slice(0, 4) // Apenas colunas de dados, não de ações
                             .some(td => td.textContent.toLowerCase().includes(term));
          row.style.display = match ? '' : 'none';
          const cell = row.querySelector('td.action-cell');
          if (cell) cell.classList.toggle('hidden', !match || actionHeader.classList.contains('hidden'));
        });
      }

      // Dispara a busca ao digitar ou clicar no botão
      searchBtn.addEventListener('click', doSearch);
      searchInput.addEventListener('input', doSearch);

      // Vincula ações aos botões de edição, exclusão e desfazer
      function bindActions() {
        // Botão de editar/salvar
        document.querySelectorAll('.edit-btn').forEach(btn => {
          btn.onclick = () => {
            const row = btn.closest('tr');
            const icon = btn.querySelector('.material-icons');
            const editing = row.classList.toggle('editing');
            icon.textContent = editing ? 'save' : 'edit';
            const numCols = row.cells.length - 1;
            for (let i = 0; i < numCols; i++) {
              const cell = row.cells[i];
              if (editing) {
                const inp = document.createElement('input');
                inp.type = 'text';
                inp.value = cell.textContent;
                cell.textContent = '';
                cell.appendChild(inp);
              } else {
                const inp = cell.querySelector('input');
                if (inp) cell.textContent = inp.value;
              }
            }
          };
        });

        // Botão de exclusão com opção de desfazer (undo)
        document.querySelectorAll('.delete-btn').forEach(btn => {
          btn.onclick = () => {
            const row = btn.closest('tr');
            row.classList.add('disabled-row'); // Aplica estilo visual de desativado
            const deleteBtn = row.querySelector('.delete-btn');
            const undoBtn = row.querySelector('.undo-btn');

            deleteBtn.style.display = 'none';
            undoBtn.style.display = 'inline-block'; // Mostra o botão "undo"

            undoBtn.onclick = () => {
              row.classList.remove('disabled-row');
              deleteBtn.style.display = 'inline-block';
              undoBtn.style.display = 'none';
            };
          };
        });
      }

      // Executa o binding inicial
      bindActions();
    });
  </script>
</body>
</html>
