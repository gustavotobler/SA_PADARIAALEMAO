<?php
session_start();

$host = 'localhost';
$dbname = 'padariadoalemao';
$user = 'root';
$pass = '';
if ($_SESSION['nivel'] != 1) {
    echo "<script>alert('Erro, você não possui o nível de acesso');window.location.href='inicial1.php';</script>";
    exit;
}
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
        p.Preco_unitario,
        (p.Qntd_produto * p.Preco_unitario) AS valor_total,
        p.Validade,
        c.id_categorias,
        c.nome_categoria
    FROM produtos p
    LEFT JOIN fornecedores f ON p.ID_forn = f.ID_forn
    LEFT JOIN categorias c ON p.id_categorias = c.id_categorias
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
  --navy-900: #071229;
  --navy-800: #0d1b2a;
  --navy-700: #153044;
  --teal-500: #2aa19a;
  --teal-600: #238678;
  --teal-700: #1d6a60;
  --muted:    #9aa6b2;
  --card-bg:  rgba(255,255,255,0.02);
  --highlight:#10293f;
  --glass:    rgba(255,255,255,0.05);
  --accent-glow: 0 8px 30px rgba(36,161,154,0.15);
  --soft-shadow: 0 8px 18px rgba(2,6,23,0.6);
  --radius:   12px;
  --transition: 220ms cubic-bezier(.2,.9,.2,1);
  --text-light:#e6eef2;
  --text-mid: #cddbe3;
}

/* Reset */
*{margin:0;padding:0;box-sizing:border-box}
body{
  font-family:'Segoe UI',sans-serif;
  background:rgb(59, 75, 93);
  color: var(--text-light);
  display:flex;
}

/* Sidebar */
.sidebar {
  width:240px;
  background: linear-gradient(180deg,var(--navy-900),var(--navy-800));
  height:100vh;
  position:fixed;
  display:flex;
  flex-direction:column;
  padding-top:20px;
  transition: width var(--transition);
  box-shadow: 6px 0 30px rgba(0,0,0,0.55);
}

.sidebar.collapsed { width:64px }

.sidebar a {
  display:flex;
  align-items:center;
  gap:12px;
  padding:12px 16px;
  color:var(--text-mid);
  margin:6px 10px;
  border-radius:10px;
  transition: background var(--transition), transform var(--transition);
}

.sidebar a:hover {
  background: var(--glass);
  transform:translateY(-3px);
  box-shadow: var(--accent-glow);
  border-left:3px solid var(--teal-500);
  padding-left:12px;
}

.sidebar.collapsed .text { display:none }

/* Toggle */
.toggle-btn{
  cursor:pointer;
  text-align:center;
  margin-bottom:20px;
  font-size:20px;
  color:var(--text-light);
}

/* Main content */
.main-content {
  margin-left:240px;
  padding:20px 30px;
  width:100%;
  transition: margin-left var(--transition);
}
.main-content.collapsed { margin-left:64px }

/* Titles */
h1,h2{
  text-align:center;
  margin-bottom:20px;
  font-weight:800;
  color:var(--text-light);
}

/* Filters */
#filters {
  display:flex;
  gap:10px;
  justify-content:center;
  margin-bottom:12px;
  flex-wrap:wrap;
  align-items:flex-end;
}
.filter-group{display:flex;flex-direction:column}
.filter-group label{margin-bottom:5px;color:var(--muted)}
.filter-group input{
  padding:.6rem .75rem;
  border-radius:10px;
  border:1px solid rgba(255,255,255,0.06);
  background:rgba(255,255,255,0.02);
  color:var(--text-light);
  outline:none;
  transition: box-shadow var(--transition), border var(--transition);
}
.filter-group input:focus{
  box-shadow: var(--accent-glow);
  border-color: var(--teal-500);
}
#clearFilters{
  background:linear-gradient(180deg,var(--teal-500),var(--teal-600));
  color:#fff;
  padding:.55rem 1rem;
  border:none;
  border-radius:10px;
  cursor:pointer;
  font-weight:700;
  box-shadow:var(--accent-glow);
}
#clearFilters:hover{transform:translateY(-3px)}

/* Table */
table{
  width:100%;
  border-collapse:collapse;
  background:rgb(76, 88, 101);
  border-radius:12px;
  overflow:hidden;
  box-shadow: var(--soft-shadow);
}
th,td{padding:12px 10px;text-align:center}
th{
  background: linear-gradient(180deg,var(--navy-700),var(--navy-800));
  color:var(--text-light);
  font-weight:800;
}
td{color:var(--text-mid);border-bottom:1px solid rgba(255,255,255,0.03)}
tr:nth-child(even){background:rgba(255,255,255,0.01)}
tr:hover{background:var(--highlight)}

/* Chart */
.chart-section{
  margin:40px auto;
  background:var(--card-bg);
  padding:20px;
  border-radius:12px;
  max-width:700px;
  box-shadow: var(--soft-shadow);
  display:none;
}
.chart-section.active{display:block}
.chart-container{position:relative;height:400px;width:100%}
.filter-info{text-align:center;margin-bottom:10px;font-weight:700;color:var(--text-light)}

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
            <span class="text">Tabela de Estoque</span>
        </a>
        <a href="#" onclick="showSection('grafico-qtd')">
            <span class="emoji">📦</span>
            <span class="text">Gráf. Quantidade</span>
        </a>
        <a href="#" onclick="showSection('grafico-valor')">
            <span class="emoji">💰</span>
            <span class="text">Gráf. Valor</span>
        </a>
        <a href="#" onclick="showSection('grafico-fornecedor')">
            <span class="emoji">🏭</span>
            <span class="text">Gráf. Fornecedor</span>
        </a>
        <a href="#" onclick="showSection('grafico-validade')">
            <span class="emoji">⏳</span>
            <span class="text">Gráf. Validade</span>
        </a>
        <a href="#" onclick="showSection('grafico-preco')">
            <span class="emoji">💵</span>
            <span class="text">Gráf. Preço</span>
        </a>
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
                            <th>Categoria</th>
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
                                <td><?= $row['nome_categoria'] ?? '---' ?></td>
                                <td><?= $row['Unid_medida'] ?></td>
                                <td><?= number_format($row['Qntd_produto'], 2, ',', '.') ?></td>
                                <td><?= number_format($row['Preco_unitario'], 2, ',', '.') ?></td>
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

        <section id="grafico-categoria" class="chart-section">
            <h2>Quantidade por Categoria</h2>
            <div class="chart-container"><canvas id="categoriaChart"></canvas></div>
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
            if (id.startsWith('grafico-')) setTimeout(updateCharts, 50);
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
                if (start) passDate = validade >= start;
                if (end) passDate = passDate && validade <= end;

                let passSearch = true;
                if (search) passSearch = nome.includes(search) || id.includes(search);

                row.style.display = (passDate && passSearch) ? '' : 'none';
                if (row.style.display !== 'none') countVisible++;
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
                const diffDays = (new Date(val) - today) / (1000 * 60 * 60 * 24);
                if (diffDays <= 30 && diffDays >= 0) {
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

        // Quantidade por categoria
        const categoriaMap = {};
        visibleRows.forEach(r => {
            const categoria = r.cells[3].textContent || '---'; // coluna Categoria
            categoriaMap[categoria] = (categoriaMap[categoria] || 0) + parseFloat(r.cells[5].textContent.replace('.', '').replace(',', '.'));
        });
        charts.categoriaChart = new Chart(document.getElementById('categoriaChart'), {
            type: 'bar',
            data: { labels: Object.keys(categoriaMap), datasets: [{ label: 'Qtd por Categoria', data: Object.values(categoriaMap), backgroundColor: colors }] },
            options: { responsive: true, maintainAspectRatio: false }
        });


        showSection('tabela');
        applyFilters();
    </script>
</body>

</html>