<?php
$host = 'localhost';
$dbname = 'padariadoalemao';
$user = 'root';
$pass = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Consulta de vendas com join
    $sql = "
    SELECT 
        v.ID_vendas,
        v.venda_data,
        f.Nome_func,
        p.Nome_prod,
        v.quant_vendas,
        v.preco_unit,
        v.preco_total,
        v.forma_pagamento
    FROM vendas v
    JOIN funcionario f ON v.ID_func = f.ID_func
    JOIN compras c ON v.ID_vendas = c.ID_vendas
    JOIN produtos p ON c.ID_produto = p.ID_produto
    ORDER BY v.venda_data DESC
";
    $stmt = $conn->query($sql);
    $rows = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Dashboard de Vendas</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --sidebar-bg: #2e2e2e;
            --primary-text: #fff;
            --hover-bg: #444;
            --main-bg: #fcf6eb;
            --card-bg: #fff;
            --accent: #3f3f3f;
            --highlight: #e0f7ff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            background-color: var(--main-bg);
        }

        .sidebar {
            width: 240px;
            background-color: var(--sidebar-bg);
            height: 100vh;
            position: fixed;
            display: flex;
            flex-direction: column;
            padding-top: 20px;
            transition: width 0.3s;
        }

        .sidebar.collapsed {
            width: 60px;
        }

        /* Esconde apenas o texto quando colapsada */
        .sidebar.collapsed .text {
            display: none;
        }


        .sidebar a {
            display: flex;
            align-items: center;
            color: var(--primary-text);
            text-decoration: none;
            padding: 15px 20px;
            white-space: nowrap;
        }
        .sidebar a:hover {
            background-color: var(--hover-bg);
        }

        .toggle-btn {
            cursor: pointer;
            text-align: center;
            margin-bottom: 20px;
            font-size: 20px;
            color: var(--primary-text);
        }

        .main-content {
            margin-left: 240px;
            padding: 20px 30px;
            width: 100%;
            transition: margin-left 0.3s;
        }

        .main-content.collapsed {
            margin-left: 60px;
        }

        h1,
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        #filters {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-bottom: 10px;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            margin-bottom: 5px;
        }

        .filter-group input {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        #clearFilters {
            background: var(--accent);
            color: var(--primary-text);
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        #clearFilters:hover {
            background: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: var(--card-bg);
            border-radius: 12px;
            overflow: hidden;
        }

        th,
        td {
            padding: 12px 10px;
            text-align: center;
            border-bottom: 1px solid #eee;
        }

        th {
            background: var(--accent);
            color: var(--primary-text);
        }

        tr:nth-child(even) {
            background: #f9f9f9;
        }

        tr:hover {
            background: var(--highlight);
        }

        .chart-section {
            margin: 40px auto;
            background: var(--card-bg);
            padding: 20px;
            border-radius: 12px;
            max-width: 700px;
            display: none;
        }

        .chart-section.active {
            display: block;
        }

        .chart-container {
            position: relative;
            height: 400px;
            width: 100%;
        }

        .filter-info {
            text-align: center;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 15px;
        }

        .pagination button {
            padding: 8px 12px;
            border: none;
            background-color: var(--accent);
            color: var(--primary-text);
            border-radius: 4px;
            cursor: pointer;
        }

        .pagination button:disabled {
            background-color: #999;
            cursor: default;
        }
  /* Esconde apenas o texto quando colapsada */
  .sidebar.collapsed .text {
            display: none;
        }

        /* Ajusta emojis quando a sidebar está colapsada */
        .sidebar .emoji {
            margin-right: 8px;
            display: inline-block;
            width: 20px;
            text-align: center;
        }

        .sidebar.collapsed .emoji {
            margin-right: 0;
            width: 100%;
        }

        .back-link {
            display: flex;
            align-items: center;
            transition: all 0.3s;
            /* suaviza posição e rotação */
        }

        .back-link .icon {
            transition: transform 0.3s;
            margin-right: 8px;
        }

        /* Quando a sidebar estiver colapsada */
        .sidebar.collapsed .back-link {
            justify-content: center;
            /* centraliza horizontalmente */
        }

        .sidebar.collapsed .back-link .icon {
            margin-right: 0;
            transform: rotate(180deg);
            /* rotação da seta */
        }

        .sidebar a:hover {
            background: var(--hover-bg);
        }

        .sidebar .icon {
            margin-right: 8px;
        }

        .sidebar.collapsed .text {
            display: none;
        }

        .sidebar.collapsed .icon {
            margin-right: 0;
            justify-content: center;
        }

    </style>
</head>

<body>


    <nav class="sidebar" id="sidebar">
        <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
        <a href="inicial1.php" class="back-link">
            <span class="material-icons icon">arrow_back</span>
            <span class="text">Voltar</span>
        </a>
        <a href="#" onclick="showSection('tabela')">
            <span class="emoji">📋</span>
            <span class="text">Tabela de Vendas</span>
        </a>
        <a href="#" onclick="showSection('grafico-produto')">
            <span class="emoji">📦</span>
            <span class="text">Gráf. Produto</span>
        </a>
        <a href="#" onclick="showSection('grafico-pagamento')">
            <span class="emoji">💳</span>
            <span class="text">Gráf. Pagamento</span>
        </a>
        <a href="#" onclick="showSection('grafico-funcionario')">
            <span class="emoji">👨‍💼</span>
            <span class="text">Gráf. Funcionário</span>
        </a>
        <a href="#" onclick="showSection('grafico-dia')">
            <span class="emoji">📅</span>
            <span class="text">Gráf. Total Vendido</span>
        </a>
    </nav>

    <main class="main-content" id="mainContent">
        <section id="tabela" class="section active">
            <h1>Relatório de Vendas</h1>

            <div id="filters">
                <div class="filter-group">
                    <label for="startDate">📅 Data Inicial</label>
                    <input type="date" id="startDate">
                </div>
                <div class="filter-group">
                    <label for="endDate">📅 Data Final</label>
                    <input type="date" id="endDate">
                </div>
                <div class="filter-group">
                    <label for="search">🔍 Produto / Funcionário</label>
                    <input type="text" id="search" placeholder="Pesquisar...">
                </div>
                <button id="clearFilters">Limpar Filtros</button>
            </div>

            <div class="filter-info" id="filterInfo">Total de vendas: <?= count($rows) ?></div>

            <div style="overflow-x:auto;">
                <table id="vendasTable">
                    <thead>
                        <tr>
                            <th>ID Venda</th>
                            <th>Data</th>
                            <th>Funcionário</th>
                            <th>Produto</th>
                            <th>Quantidade</th>
                            <th>Preço Unitário</th>
                            <th>Total</th>
                            <th>Pagamento</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <td><?= $row['ID_vendas'] ?></td>
                                <td><?= $row['venda_data'] ?></td>
                                <td><?= $row['Nome_func'] ?></td>
                                <td><?= $row['Nome_prod'] ?></td>
                                <td><?= $row['quant_vendas'] ?></td>
                                <td><?= number_format($row['preco_unit'], 2, ',', '.') ?></td>
                                <td><?= number_format($row['preco_total'], 2, ',', '.') ?></td>
                                <td><?= $row['forma_pagamento'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="pagination">
                <button id="prevBtn" disabled>&larr; Anterior</button>
                <span id="pageInfo">Página 1</span>
                <button id="nextBtn">Próxima &rarr;</button>
            </div>
        </section>

        <!-- Gráficos -->
        <section id="grafico-produto" class="chart-section">
            <h2>Vendas por Produto</h2>
            <div class="chart-container"><canvas id="produtoChart"></canvas></div>
        </section>

        <section id="grafico-pagamento" class="chart-section">
            <h2>Vendas por Pagamento</h2>
            <div class="chart-container"><canvas id="pagamentoChart"></canvas></div>
        </section>

        <section id="grafico-funcionario" class="chart-section">
            <h2>Vendas por Funcionário</h2>
            <div class="chart-container"><canvas id="funcionarioChart"></canvas></div>
        </section>

        <section id="grafico-dia" class="chart-section">
            <h2>Total Vendido por Dia</h2>
            <div class="chart-container"><canvas id="diaChart"></canvas></div>
        </section>
    </main>

    <script>
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');

        function toggleSidebar() {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('collapsed');
        }

        function showSection(id) {
            document.querySelectorAll('.section, .chart-section').forEach(s => s.style.display = 'none');
            document.getElementById(id).style.display = 'block';
            if (id.startsWith('grafico-')) setTimeout(updateCharts, 50);
        }

        // Preparar dados da tabela
        const tableRows = Array.from(document.querySelectorAll('#vendasTable tbody tr'));
        const startDateInput = document.getElementById('startDate');
        const endDateInput = document.getElementById('endDate');
        const searchInput = document.getElementById('search');
        const clearBtn = document.getElementById('clearFilters');
        const filterInfo = document.getElementById('filterInfo');

        let currentPage = 1;
        const rowsPerPage = 9;
        let filteredIndices = [];

        function applyFilters() {
            const start = startDateInput.value;
            const end = endDateInput.value;
            const search = searchInput.value.toLowerCase();

            filteredIndices = [];
            tableRows.forEach((row, idx) => {
                const date = row.cells[1].textContent;
                const produto = row.cells[3].textContent.toLowerCase();
                const func = row.cells[2].textContent.toLowerCase();

                let passDate = true;
                if (start) passDate = date >= start;
                if (end) passDate = passDate && date <= end;
                let passSearch = produto.includes(search) || func.includes(search);

                if (passDate && passSearch) filteredIndices.push(idx);
            });

            const totalPages = Math.ceil(filteredIndices.length / rowsPerPage) || 1;
            if (currentPage > totalPages) currentPage = totalPages;

            tableRows.forEach((row, idx) => {
                const pos = filteredIndices.indexOf(idx);
                row.style.display = (pos >= (currentPage - 1) * rowsPerPage && pos < currentPage * rowsPerPage) ? '' : 'none';
            });

            document.getElementById('pageInfo').textContent = `Página ${currentPage} de ${totalPages}`;
            document.getElementById('prevBtn').disabled = currentPage === 1;
            document.getElementById('nextBtn').disabled = currentPage === totalPages;

            filterInfo.textContent = filteredIndices.length ? `Vendas encontradas: ${filteredIndices.length}` : 'Nenhuma venda encontrada';
            updateCharts();
        }

        startDateInput.addEventListener('change', () => { currentPage = 1; applyFilters(); });
        endDateInput.addEventListener('change', () => { currentPage = 1; applyFilters(); });
        searchInput.addEventListener('input', () => { currentPage = 1; applyFilters(); });
        clearBtn.addEventListener('click', () => {
            startDateInput.value = ''; endDateInput.value = ''; searchInput.value = '';
            currentPage = 1; applyFilters();
        });

        document.getElementById('prevBtn').addEventListener('click', () => { if (currentPage > 1) { currentPage--; applyFilters(); } });
        document.getElementById('nextBtn').addEventListener('click', () => { if (currentPage < Math.ceil(filteredIndices.length / rowsPerPage)) { currentPage++; applyFilters(); } });

        // Gráficos
        const defaultColors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#8E44AD', '#2ECC71', '#E74C3C'];
        const ctxProd = document.getElementById('produtoChart');
        const ctxPag = document.getElementById('pagamentoChart');
        const ctxFunc = document.getElementById('funcionarioChart');
        const ctxDia = document.getElementById('diaChart');

        let produtoChart = new Chart(ctxProd, { type: 'bar', data: { labels: [], datasets: [{ label: 'Quantidade', data: [], backgroundColor: [], borderWidth: 1 }] } });
        let pagamentoChart = new Chart(ctxPag, { type: 'pie', data: { labels: [], datasets: [{ label: 'Utilizada', data: [], backgroundColor: [] }] } });
        let funcionarioChart = new Chart(ctxFunc, { type: 'bar', data: { labels: [], datasets: [{ label: 'Vendas', data: [], backgroundColor: [], borderWidth: 1 }] } });
        let diaChart = new Chart(ctxDia, { type: 'line', data: { labels: [], datasets: [{ label: 'Total vendido', data: [], borderColor: '', backgroundColor: '', fill: true, tension: 0.3 }] } });

        function updateCharts() {
            const prodCounts = {}, pagCounts = {}, funcCounts = {}, diaTotals = {};
            filteredIndices.forEach(idx => {
                const row = tableRows[idx];
                const qtd = +row.cells[4].textContent;
                const total = +row.cells[6].textContent;
                const produto = row.cells[3].textContent;
                const func = row.cells[2].textContent;
                const pagamento = row.cells[7].textContent;
                const date = row.cells[1].textContent;

                prodCounts[produto] = (prodCounts[produto] || 0) + qtd;
                pagCounts[pagamento] = (pagCounts[pagamento] || 0) + 1;
                funcCounts[func] = (funcCounts[func] || 0) + 1;
                diaTotals[date] = (diaTotals[date] || 0) + total;
            });

            produtoChart.data.labels = Object.keys(prodCounts);
            produtoChart.data.datasets[0].data = Object.values(prodCounts);
            produtoChart.data.datasets[0].backgroundColor = produtoChart.data.labels.map((_, i) => defaultColors[i % defaultColors.length]);
            produtoChart.update();

            pagamentoChart.data.labels = Object.keys(pagCounts);
            pagamentoChart.data.datasets[0].data = Object.values(pagCounts);
            pagamentoChart.data.datasets[0].backgroundColor = pagamentoChart.data.labels.map((_, i) => defaultColors[i % defaultColors.length]);
            pagamentoChart.update();

            funcionarioChart.data.labels = Object.keys(funcCounts);
            funcionarioChart.data.datasets[0].data = Object.values(funcCounts);
            funcionarioChart.data.datasets[0].backgroundColor = funcionarioChart.data.labels.map((_, i) => defaultColors[i % defaultColors.length]);
            funcionarioChart.update();

            diaChart.data.labels = Object.keys(diaTotals);
            diaChart.data.datasets[0].data = Object.values(diaTotals);
            const dayColor = defaultColors[1];
            diaChart.data.datasets[0].borderColor = dayColor;
            diaChart.data.datasets[0].backgroundColor = dayColor + '33';
            diaChart.update();
        }

        applyFilters();
    </script>
</body>

</html>