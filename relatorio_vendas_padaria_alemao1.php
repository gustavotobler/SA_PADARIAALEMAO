<?php
$host = 'localhost';
$dbname = 'padariadoalemao';
$user = 'root';
$pass = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // 🔹 Consulta de vendas com join
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--main-bg);
            color: #333;
            display: flex;
        }

        .sidebar {
            width: 240px;
            background-color: var(--sidebar-bg);
            height: 100vh;
            position: fixed;
            display: flex;
            flex-direction: column;
            padding-top: 20px;
        }

        .sidebar h2 {
            color: var(--primary-text);
            text-align: center;
            margin-bottom: 30px;
        }

        .sidebar a {
            color: var(--primary-text);
            padding: 15px 20px;
            text-decoration: none;
            transition: background 0.3s;
        }

        .sidebar a:hover {
            background-color: var(--hover-bg);
        }

        .main-content {
            margin-left: 240px;
            padding: 30px;
            width: 100%;
        }

        h1,
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        #filters {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        #filters .filter-group {
            display: flex;
            flex-direction: column;
            align-items: start;
        }

        #filters label {
            font-size: 12px;
            margin-left: 2px;
        }

        #filters input {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: var(--card-bg);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            overflow: hidden;
        }

        caption {
            caption-side: top;
            text-align: left;
            font-weight: bold;
            padding: 10px;
        }

        th,
        td {
            padding: 12px 15px;
            text-align: center;
            border-bottom: 1px solid #eee;
        }

        th {
            background-color: var(--accent);
            color: var(--primary-text);
        }

        th[scope="col"],
        td[scope="row"] {
            text-align: left;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: var(--highlight);
        }

        .chart-section,
        .sheet-section {
            margin: 40px auto;
            background: var(--card-bg);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            max-width: 700px;
        }

        #grafico-pagamento canvas {
            max-height: 325px;
        }

        canvas {
            max-width: 100%;
            height: auto;
        }

        .section {
            display: none;
        }

        .section.active {
            display: block;
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

        .back-button {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            margin: 10px;
            background-color: var(--accent);
            color: var(--primary-text);
            border-radius: 8px;
            text-decoration: none;
            font-size: 16px;
            font-weight: 500;
            transition: background-color 0.2s, transform 0.1s;
        }

        .back-button .icon {
            margin-right: 8px;
            font-size: 18px;
        }

        .back-button:hover {
            background-color: #555;
            transform: translateY(-1px);
        }

        .back-button:active {
            background-color: #333;
            transform: translateY(0);
        }
    </style>
</head>

<body>

    <nav class="sidebar" aria-label="Menu de navegação">
        <a href="inicial1.php">
            <span class="material-icons icon" title="Voltar">arrow_back</span>
        </a>

        </a>
        <a href="#" onclick="showSection('tabela')" aria-controls="tabela">📋 Tabela de Vendas</a>
        <a href="#" onclick="showSection('grafico-produto')" aria-controls="grafico-produto">📦 Gráf. Produto</a>
        <a href="#" onclick="showSection('grafico-pagamento')" aria-controls="grafico-pagamento">💳 Gráf. Pagamento</a>
        <a href="#" onclick="showSection('grafico-funcionario')" aria-controls="grafico-funcionario">👨‍💼 Gráf.
            Funcionário</a>
        <a href="#" onclick="showSection('grafico-dia')" aria-controls="grafico-dia">📅 Gráf. Total Vendido</a>
    </nav>

    <main class="main-content">
        <section id="tabela" class="section active" aria-labelledby="titulo-tabela">
            <h1 id="titulo-tabela">Relatório de Vendas</h1>
            <div id="filters" role="region" aria-label="Filtros de busca">
                <div class="filter-group">
                    <label for="startDate">📅 Data Inicial</label>
                    <input type="date" id="startDate" aria-labelledby="startDate">
                </div>
                <div class="filter-group">
                    <label for="endDate">📅 Data Final</label>
                    <input type="date" id="endDate" aria-labelledby="endDate">
                </div>
                <div class="filter-group">
                    <label for="search">🔍 Produto</label>
                    <input type="text" id="search" placeholder="Pesquisar por nome do Produto..."
                        aria-labelledby="search">
                </div>
            </div>
            <table aria-describedby="descr-tabela">
                <thead>
                    <tr>
                        <th scope="col">ID Venda</th>
                        <th scope="col">Data</th>
                        <th scope="col">Nome Funcionário</th>
                        <th scope="col">Nome Produto</th>
                        <th scope="col">Quantidade</th>
                        <th scope="col">Preço Unitário</th>
                        <th scope="col">Total</th>
                        <th scope="col">Pagamento</th>
                    </tr>
                </thead>
            </table>
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
            <div class="pagination" role="navigation" aria-label="Paginação de resultados">
                <button id="prevBtn" disabled aria-label="Página anterior">&larr; Anterior</button>
                <span id="pageInfo" aria-live="polite">Página 1</span>
                <button id="nextBtn" aria-label="Próxima página">Próxima &rarr;</button>
            </div>
        </section>

        <section id="grafico-produto" class="section chart-section" aria-labelledby="titulo-produto">
            <h2 id="titulo-produto">Vendas por Produto</h2>
            <canvas id="produtoChart" role="img" aria-label="Gráfico de vendas por produto"></canvas>
        </section>

        <section id="grafico-pagamento" class="section chart-section" aria-labelledby="titulo-pagamento">
            <h2 id="titulo-pagamento">Vendas por Forma de Pagamento</h2>
            <canvas id="pagamentoChart" role="img" aria-label="Gráfico de vendas por forma de pagamento"></canvas>
        </section>

        <section id="grafico-funcionario" class="section chart-section" aria-labelledby="titulo-funcionario">
            <h2 id="titulo-funcionario">Vendas por Funcionário</h2>
            <canvas id="funcionarioChart" role="img" aria-label="Gráfico de vendas por funcionário"></canvas>
        </section>

        <section id="grafico-dia" class="section chart-section" aria-labelledby="titulo-dia">
            <h2 id="titulo-dia">Vendas</h2>
            <canvas id="diaChart" role="img" aria-label="Gráfico de vendas"></canvas>
        </section>
    </main>

    <script>

        function showSection(id) {
            document.querySelectorAll('.section').forEach(el => el.classList.remove('active'));
            document.getElementById(id).classList.add('active');
        }

        const rawRows = Array.from(document.querySelectorAll('#tabela tbody tr')).map(row => ({
            id: row.cells[0].textContent,
            date: row.cells[1].textContent,
            funcionario: row.cells[2].textContent,
            produto: row.cells[3].textContent,
            quantidade: +row.cells[4].textContent,
            preco: +row.cells[5].textContent,
            total: +row.cells[6].textContent,
            pagamento: row.cells[7].textContent
        }));

        const defaultColors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#8E44AD', '#2ECC71', '#E74C3C'];

        const ctxProd = document.getElementById('produtoChart');
        const ctxPag = document.getElementById('pagamentoChart');
        const ctxFunc = document.getElementById('funcionarioChart');
        const ctxDia = document.getElementById('diaChart');

        let produtoChart = new Chart(ctxProd, { type: 'bar', data: { labels: [], datasets: [{ label: 'Quantidade', data: [], backgroundColor: [], borderWidth: 1 }] } });
        let pagamentoChart = new Chart(ctxPag, { type: 'pie', data: { labels: [], datasets: [{ label: 'Utilizada', data: [], backgroundColor: [] }] } });
        let funcionarioChart = new Chart(ctxFunc, { type: 'bar', data: { labels: [], datasets: [{ label: 'Vendas', data: [], backgroundColor: [], borderWidth: 1 }] } });
        let diaChart = new Chart(ctxDia, { type: 'line', data: { labels: [], datasets: [{ label: 'Total vendido (R$)', data: [], borderColor: '', backgroundColor: '', fill: true, tension: 0.3 }] } });

        const rowsPerPage = 9;
        let currentPage = 1;
        let filteredIndices = [];

        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const pageInfo = document.getElementById('pageInfo');

        prevBtn.addEventListener('click', () => { if (currentPage > 1) { currentPage--; applyFilters(); } });
        nextBtn.addEventListener('click', () => { if (currentPage < Math.ceil(filteredIndices.length / rowsPerPage)) { currentPage++; applyFilters(); } });

        function applyFilters() {
            const start = document.getElementById('startDate').value;
            const end = document.getElementById('endDate').value;
            const search = document.getElementById('search').value.trim();

            filteredIndices = [];
            rawRows.forEach((item, idx) => {
                let passDate = true;
                if (start) passDate = item.date >= start;
                if (passDate && end) passDate = item.date <= end;
                let passSearch = item.produto.includes(search);
                if (passDate && passSearch) filteredIndices.push(idx);
            });


            const totalPages = Math.ceil(filteredIndices.length / rowsPerPage) || 1;
            if (currentPage > totalPages) currentPage = totalPages;

            document.querySelectorAll('#tabela tbody tr').forEach((row, idx) => {
                const pos = filteredIndices.indexOf(idx);
                if (pos >= (currentPage - 1) * rowsPerPage && pos < currentPage * rowsPerPage) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });

            pageInfo.textContent = `Página ${currentPage} de ${totalPages}`;
            prevBtn.disabled = currentPage === 1;
            nextBtn.disabled = currentPage === totalPages;

            updateCharts();
        }

        function updateCharts() {
            const prodCounts = {}, pagCounts = {}, funcCounts = {}, diaTotals = {};
            filteredIndices.forEach(idx => {
                const item = rawRows[idx];
                prodCounts[item.produto] = (prodCounts[item.produto] || 0) + item.quantidade;
                pagCounts[item.pagamento] = (pagCounts[item.pagamento] || 0) + 1;
                funcCounts[item.funcionario] = (funcCounts[item.funcionario] || 0) + 1;
                diaTotals[item.date] = (diaTotals[item.date] || 0) + item.total;
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

        document.getElementById('startDate').addEventListener('change', () => { currentPage = 1; applyFilters(); });
        document.getElementById('endDate').addEventListener('change', () => { currentPage = 1; applyFilters(); });
        document.getElementById('search').addEventListener('input', () => { currentPage = 1; applyFilters(); });

        applyFilters();
    </script>

</body>

</html>