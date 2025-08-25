<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Funcionários</title>
  <link rel="stylesheet" href="css/site3.css">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>

<header>
    <div class="topo">
      <!-- Lado esquerdo: botão de voltar -->
      <div class="topo-left">
        <a href="inicial1.php">
          <span class="material-icons icon" title="Voltar">arrow_back</span>
        </a>
      </div>

      <!-- Centro: título e barra de pesquisa -->
      <div class="topo-center">
        <h1>FUNCIONÁRIOS</h1>
        <div class="search-container">
          <span class="material-icons">search</span>
          <form method="POST" action="buscar_funcionarios.php">
            <input type="text" id="search-input" placeholder="Pesquisar...">
            <button id="search-btn" type="button">Pesquisar</button>
          </form>
        </div>
      </div>

      <!-- Lado direito: botão de adicionar e de alternar ações -->
      <div class="topo-right">
        <!-- Botão de adicionar fornecedor (oculto por padrão) -->
        <a href="cadfunc.php" id="add-button" class="hidden">
          <button class="icon-btn add-btn" title="Adicionar">
            <span class="material-icons">add</span>
          </button>
        </a>
        <!-- Botão para mostrar/ocultar ações -->
        <span class="material-icons icon edit-toggle" id="edit-toggle" title="Mostrar/Ocultar Ações">edit</span>
      </div>
    </div>
</header>

<main>
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Nome</th>
        <th>Cargo</th>
        <th>Nascimento</th>
        <th>Admissão</th>
        <th class="action-header hidden">Ações</th>
      </tr>
    </thead>

    <tbody id="func-table-body">
      <?php
      require_once 'conexao.php';

      $sql = "SELECT ID_func, Nome_func, Cargo, Data_nascimento, Data_admissao FROM funcionario";
      $stmt = $pdo->query($sql);
      $funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

      foreach ($funcionarios as $func): ?>
        <tr>
          <td><?= htmlspecialchars($func['ID_func']) ?></td>
          <td><?= htmlspecialchars($func['Nome_func']) ?></td>
          <td><?= htmlspecialchars($func['Cargo']) ?></td>
          <td><?= htmlspecialchars($func['Data_nascimento']) ?></td>
          <td><?= htmlspecialchars($func['Data_admissao']) ?></td>
          
          <!-- CELULA DE AÇÕES -->
          <td class="action-cell hidden">
            <button type="button" class="icon-btn edit-btn">
              <span class="material-icons">edit</span>
            </button>
            
            <form action="excluir_funcionarios.php" method="POST" style="display:inline;">
              <input type="hidden" name="id" value="<?= htmlspecialchars($func['ID_func']) ?>">
              <button type="submit" class="icon-btn delete-btn" onclick="return confirm('Deseja realmente excluir este funcionário?')">
                <span class="material-icons">delete</span>
              </button>
            </form>

            <button type="button" class="undo-btn" style="display:none;">
              <span class="material-icons">undo</span>
            </button>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const toggleBtn = document.getElementById('edit-toggle');
  const actionHeader = document.querySelector('th.action-header');
  const addButton = document.getElementById('add-button');
  const tableBody = document.getElementById('func-table-body');
  const searchInput = document.getElementById('search-input');
  const searchBtn = document.getElementById('search-btn');

  const getActionCells = () => document.querySelectorAll('td.action-cell');

  // Alternar visibilidade das ações
  toggleBtn.addEventListener('click', () => {
    actionHeader.classList.toggle('hidden');
    getActionCells().forEach(td => td.classList.toggle('hidden'));
    addButton.classList.toggle('hidden');
  });

  // Editar/Salvar (apenas frontend)
  tableBody.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const row = btn.closest('tr');
      const icon = btn.querySelector('.material-icons');
      const editing = row.classList.toggle('editing');
      icon.textContent = editing ? 'save' : 'edit';

      for (let i = 0; i < 5; i++) {
        const cell = row.cells[i];
        if (editing) {
          const input = document.createElement('input');
          input.type = 'text';
          input.value = cell.textContent;
          cell.textContent = '';
          cell.appendChild(input);
        } else {
          const input = cell.querySelector('input');
          if (input) cell.textContent = input.value;
        }
      }
    });
  });

  // A busca na tabela (JS)
  function doSearch() {
    const term = searchInput.value.trim().toLowerCase();
    Array.from(tableBody.rows).forEach(row => {
      const match = Array.from(row.cells).slice(0, 5)
        .some(td => td.textContent.toLowerCase().includes(term));
      row.style.display = match ? '' : 'none';
      const cell = row.querySelector('td.action-cell');
      if (cell) cell.classList.toggle('hidden', !match || actionHeader.classList.contains('hidden'));
    });
  }

  searchBtn.addEventListener('click', doSearch);
  searchInput.addEventListener('input', doSearch);
});
</script>
</body>
</html>
