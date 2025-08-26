<?php
$host = 'localhost';
$dbname = 'padariadoalemao';
$user = 'root';
$pass = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $sql = "
        SELECT 
            p.ID_produto,
            p.Nome_prod,
            f.Nome_forn,
            p.Unid_medida,
            p.Qntd_produto,
            p.Preco,
            (p.Qntd_produto * p.Preco) AS valor_total,
            p.Validade
        FROM produtos p
        LEFT JOIN fornecedores f ON p.ID_forn = f.ID_forn
        ORDER BY p.Nome_prod ASC
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
    <title>Relatório de Estoque</title>
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

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Segoe UI, sans-serif;
            background: var(--main-bg);
            display: flex;
        }

        .sidebar {
            width: 240px;
            background: var(--sidebar-bg);
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

        .sidebar a {
            color: var(--primary-text);
            padding: 15px 20px;
            text-decoration: none;
            display: flex;
            align-items: center;
            white-space: nowrap;
        }

        .sidebar a:hover { background: var(--hover-bg); }

        .sidebar .icon { margin-right: 8px; }

        .sidebar.collapsed .text { display: none; }
        .sidebar.collapsed .icon { margin-right: 0; justify-content: center; }

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

        .main-content.collapsed { margin-left: 60px; }

        h1, h2 { text-align: center; margin-bottom: 20px; }

        #filters {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-bottom: 10px;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .filter-group { display: flex; flex-direction: column; }

        .filter-group label { margin-bottom: 5px; }

        .filter-group input { padding: 8px; border-radius: 4px; border: 1px solid #ccc; }

        #clearFilters {
            background: var(--accent);
            color: var(--primary-text);
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        #clearFilters:hover { background: #555; }

        table {
            width: 100%;
            border-collapse: collapse;
            background: var(--card-bg);
            border-radius: 12px;
            overflow: hidden;
        }

        th, td { padding: 12px 10px; text-align: center; border-bottom: 1px solid #eee; }

        th { background: var(--accent); color: var(--primary-text); }

        tr:nth-child(even) { background: #f9f9f9; }

        tr:hover { background: var(--highlight); }

        .chart-section {
            margin: 40px auto;
            background: var(--card-bg);
            padding: 20px;
            border-radius: 12px;
            max-width: 700px;
            display: none;
        }

        .chart-section.active { display: block; }

        .chart-container { position: relative; height: 400px; width: 100%; }

        .filter-info { text-align: center; margin-bottom: 10px; font-weight: bold; }
    </style>
</head>

<body>

    <nav class="sidebar" id="sidebar">
        <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
        <a href="inicial1.php"><span class="material-icons icon">arrow_back</span><span class="text">Voltar</span></a>
        <a href="#" onclick="showSection('tabela')"><span class="text">📋 Tabela de Estoque</span></a>
        <a href="#" onclick="showSection('grafico-qtd')"><span class="text">📦 Gráf. Quantidade</span></a>
        <a href="#" onclick="showSection('grafico-valor')"><span class="text">💰 Gráf. Valor</span></a>
        <a href="#" onclick="showSection('grafico-fornecedor')"><span class="text">🏭 Gráf. Fornecedor</span></a>
        <a href="#" onclick="showSection('grafico-validade')"><span class="text">⏳ Gráf. Validade</span></a>
        <a href="#" onclick="showSection('grafico-preco')"><span class="text">💵 Gráf. Preço</span></a>
    </nav>

    <main class="main-content" id="mainContent">
        <section id="tabela" class="section active">
            <h1>Relatório de Estoque</h1>

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
                    <label for="search">🔍 Produto / ID</label>
                    <input type="text" id="search" placeholder="Pesquisar por nome ou ID...">
                </div>
                <button id="clearFilters">Limpar Filtros</button>
            </div>

            <div class="filter-info" id="filterInfo">Total de produtos: <?= count($rows) ?></div>

            <div style="overflow-x:auto;">
                <table id="estoqueTable">
                    <thead>
                        <tr>
                            <th>ID Produto</th>
                            <th>Nome</th>
                            <th>Fornecedor</th>
                            <th>Unidade</th>
                            <th>Quantidade</th>
                            <th>Preço Unitário</th>
                            <th>Valor Total</th>
                            <th>Validade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <td><?= $row['ID_produto'] ?></td>
                                <td><?= $row['Nome_prod'] ?></td>
                                <td><?= $row['Nome_forn'] ?? '---' ?></td>
                                <td><?= $row['Unid_medida'] ?></td>
                                <td><?= number_format($row['Qntd_produto'], 2, ',', '.') ?></td>
                                <td><?= number_format($row['Preco'], 2, ',', '.') ?></td>
                                <td><?= number_format($row['valor_total'], 2, ',', '.') ?></td>
                                <td><?= date('d/m/Y', strtotime($row['Validade'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Gráficos -->
        <section id="grafico-qtd" class="chart-section">
            <h2>Quantidade em Estoque por Produto</h2>
            <div class="chart-container"><canvas id="qtdChart"></canvas></div>
        </section>

        <section id="grafico-valor" class="chart-section">
            <h2>Valor Total em Estoque por Produto</h2>
            <div class="chart-container"><canvas id="valorChart"></canvas></div>
        </section>

        <section id="grafico-fornecedor" class="chart-section">
            <h2>Quantidade Total por Fornecedor</h2>
            <div class="chart-container"><canvas id="fornecedorChart"></canvas></div>
        </section>

        <section id="grafico-validade" class="chart-section">
            <h2>Produtos Próximos da Validade (≤ 30 dias)</h2>
            <div class="chart-container"><canvas id="validadeChart"></canvas></div>
        </section>

        <section id="grafico-preco" class="chart-section">
            <h2>Distribuição de Preços Unitários</h2>
            <div class="chart-container"><canvas id="precoChart"></canvas></div>
        </section>
    </main>

    <script>
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');

        function toggleSidebar() {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('collapsed');
        }

        let charts = {};

        function showSection(id) {
            document.querySelectorAll('.section, .chart-section').forEach(s => s.style.display = 'none');
            document.getElementById(id).style.display = 'block';
            if(id.startsWith('grafico-')) setTimeout(updateCharts, 50);
        }

        const tableRows = Array.from(document.querySelectorAll('tbody tr'));
        const startDateInput = document.getElementById('startDate');
        const endDateInput = document.getElementById('endDate');
        const searchInput = document.getElementById('search');
        const clearBtn = document.getElementById('clearFilters');
        const filterInfo = document.getElementById('filterInfo');

        function applyFilters() {
            const start = startDateInput.value;
            const end = endDateInput.value;
            const search = searchInput.value.toLowerCase().trim();
            let countVisible = 0;

            tableRows.forEach(row => {
                const validade = row.cells[7].textContent.split('/').reverse().join('-'); // dd/mm/yyyy → yyyy-mm-dd
                const nome = row.cells[1].textContent.toLowerCase();
                const id = row.cells[0].textContent.toLowerCase();

                let passDate = true;
                if(start) passDate = validade >= start;
                if(end) passDate = passDate && validade <= end;

                let passSearch = true;
                if(search) passSearch = nome.includes(search) || id.includes(search);

                row.style.display = (passDate && passSearch) ? '' : 'none';
                if(row.style.display !== 'none') countVisible++;
            });

            filterInfo.textContent = countVisible > 0 ? `Produtos encontrados: ${countVisible}` : 'Nenhum produto encontrado';
        }

        clearBtn.addEventListener('click', () => {
            startDateInput.value = '';
            endDateInput.value = '';
            searchInput.value = '';
            applyFilters();
        });

        startDateInput.addEventListener('change', applyFilters);
        endDateInput.addEventListener('change', applyFilters);
        searchInput.addEventListener('input', applyFilters);

        function updateCharts() {
            const visibleRows = tableRows.filter(r => r.style.display !== 'none');
            const labels = visibleRows.map(r => r.cells[1].textContent);
            const qtds = visibleRows.map(r => parseFloat(r.cells[4].textContent.replace('.', '').replace(',', '.')));
            const valores = visibleRows.map(r => parseFloat(r.cells[6].textContent.replace('.', '').replace(',', '.')));
            const fornecedores = visibleRows.map(r => r.cells[2].textContent || '---');
            const precos = visibleRows.map(r => parseFloat(r.cells[5].textContent.replace('.', '').replace(',', '.')));
            const colors = ['#36A2EB', '#FF6384', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#2ECC71', '#E74C3C'];

            Object.values(charts).forEach(c => c.destroy());

            charts.qtdChart = new Chart(document.getElementById('qtdChart'), {
                type: 'bar',
                data: { labels, datasets: [{ label: 'Quantidade', data: qtds, backgroundColor: colors }] },
                options: { responsive: true, maintainAspectRatio: false }
            });

            charts.valorChart = new Chart(document.getElementById('valorChart'), {
                type: 'pie',
                data: { labels, datasets: [{ label: 'Valor em R$', data: valores, backgroundColor: colors }] },
                options: { responsive: true, maintainAspectRatio: false }
            });

            // Por fornecedor
            const fornecedorMap = {};
            visibleRows.forEach(r => { const f = r.cells[2].textContent || '---'; fornecedorMap[f] = (fornecedorMap[f] || 0) + parseFloat(r.cells[4].textContent); });
            charts.fornecedorChart = new Chart(document.getElementById('fornecedorChart'), {
                type: 'bar',
                data: { labels: Object.keys(fornecedorMap), datasets: [{ label: 'Qtd Total', data: Object.values(fornecedorMap), backgroundColor: colors }] },
                options: { responsive: true, maintainAspectRatio: false }
            });

            // Próximos da validade
            const today = new Date();
            const proxValidadeLabels = [], proxValidadeQtds = [];
            visibleRows.forEach(r => {
                const val = r.cells[7].textContent.split('/').reverse().join('-'); // dd/mm/yyyy → yyyy-mm-dd
                const diffDays = (new Date(val) - today) / (1000*60*60*24);
                if(diffDays <= 30 && diffDays >= 0){
                    proxValidadeLabels.push(r.cells[1].textContent);
                    proxValidadeQtds.push(parseFloat(r.cells[4].textContent));
                }
            });
            charts.validadeChart = new Chart(document.getElementById('validadeChart'), {
                type: 'bar',
                data: { labels: proxValidadeLabels, datasets: [{ label: 'Qtd Próx Validade', data: proxValidadeQtds, backgroundColor: colors }] },
                options: { responsive: true, maintainAspectRatio: false }
            });

            // Preço unitário
            charts.precoChart = new Chart(document.getElementById('precoChart'), {
                type: 'pie',
                data: { labels, datasets: [{ label: 'Preço Unitário', data: precos, backgroundColor: colors }] },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }

        showSection('tabela');
        applyFilters();
    </script>
</body>
</html>
