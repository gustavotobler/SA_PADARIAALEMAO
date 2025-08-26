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
        * { box-sizing:border-box; }
        body { margin:0; font-family:Segoe UI, sans-serif; background:var(--main-bg); display:flex; }
        .sidebar { width:240px; background:var(--sidebar-bg); height:100vh; position:fixed; display:flex; flex-direction:column; padding-top:20px; }
        .sidebar a { color:var(--primary-text); padding:15px 20px; text-decoration:none; display:flex; align-items:center; }
        .sidebar a:hover { background:var(--hover-bg); }
        .sidebar .icon { margin-right:8px; }
        .main-content { margin-left:240px; padding:30px; width:100%; }
        h1,h2 { text-align:center; margin-bottom:20px; }
        #filters { display:flex; gap:10px; justify-content:center; margin-bottom:20px; flex-wrap:wrap; }
        .filter-group { display:flex; flex-direction:column; }
        .filter-group label { margin-bottom:5px; }
        .filter-group input { padding:8px; border-radius:4px; border:1px solid #ccc; }
        table { width:100%; border-collapse:collapse; background:var(--card-bg); border-radius:12px; overflow:hidden; }
        th,td { padding:12px 15px; text-align:center; border-bottom:1px solid #eee; }
        th { background:var(--accent); color:var(--primary-text); }
        tr:nth-child(even){background:#f9f9f9;}
        tr:hover{background:var(--highlight);}
        .chart-section { margin:40px auto; background:var(--card-bg); padding:20px; border-radius:12px; max-width:700px; display:none; }
        .chart-section.active { display:block; }
        .back-button { display:flex; align-items:center; justify-content:center; margin-bottom:20px; background:var(--accent); color:var(--primary-text); padding:10px 20px; border-radius:8px; text-decoration:none; font-size:16px; }
        .back-button:hover { background:#555; }
    </style>
</head>
<body>

<nav class="sidebar">
    <a href="inicial1.php"><span class="material-icons icon">arrow_back</span>Voltar</a>
    <a href="#" onclick="showSection('tabela')">📋 Tabela de Estoque</a>
    <a href="#" onclick="showSection('grafico-qtd')">📦 Gráf. Quantidade</a>
    <a href="#" onclick="showSection('grafico-valor')">💰 Gráf. Valor</a>
</nav>

<main class="main-content">
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
        </div>

        <table>
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
                    <td><?= $row['Qntd_produto'] ?></td>
                    <td><?= number_format($row['Preco'], 2, ',', '.') ?></td>
                    <td><?= number_format($row['valor_total'], 2, ',', '.') ?></td>
                    <td><?= $row['Validade'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section id="grafico-qtd" class="chart-section">
        <h2>Quantidade em Estoque por Produto</h2>
        <canvas id="qtdChart"></canvas>
    </section>

    <section id="grafico-valor" class="chart-section">
        <h2>Valor Total em Estoque por Produto</h2>
        <canvas id="valorChart"></canvas>
    </section>
</main>

<script>
let qtdChartInstance = null;
let valorChartInstance = null;

function showSection(id){
    // Oculta todas as seções
    document.querySelectorAll('.section, .chart-section').forEach(s => s.style.display = 'none');
    
    // Mostra a seção selecionada
    const section = document.getElementById(id);
    section.style.display = 'block';

    // Se for gráfico, inicializa ou atualiza
    if(id === 'grafico-qtd' || id === 'grafico-valor'){
        setTimeout(updateCharts, 50); // Delay curto para garantir que o canvas está visível
    }
}

// Referências da tabela e filtros
const tableRows = Array.from(document.querySelectorAll('tbody tr'));
const startDateInput = document.getElementById('startDate');
const endDateInput = document.getElementById('endDate');
const searchInput = document.getElementById('search');

function applyFilters(){
    const start = startDateInput.value;
    const end = endDateInput.value;
    const search = searchInput.value.toLowerCase();

    tableRows.forEach(row => {
        const validade = row.cells[7].textContent;
        const nome = row.cells[1].textContent.toLowerCase();
        const id = row.cells[0].textContent.toLowerCase();

        let passDate = true;
        if(start) passDate = validade >= start;
        if(end) passDate = passDate && validade <= end;

        let passSearch = nome.includes(search) || id.includes(search);

        row.style.display = (passDate && passSearch) ? '' : 'none';
    });
}

// Filtros
startDateInput.addEventListener('change', applyFilters);
endDateInput.addEventListener('change', applyFilters);
searchInput.addEventListener('input', applyFilters);

// Atualiza os gráficos
function updateCharts(){
    const visibleRows = tableRows.filter(r => r.style.display !== 'none');
    const labels = visibleRows.map(r => r.cells[1].textContent);
    const qtds = visibleRows.map(r => parseFloat(r.cells[4].textContent));
    const valores = visibleRows.map(r => parseFloat(r.cells[6].textContent.replace('.', '').replace(',', '.')));
    const colors = ['#36A2EB','#FF6384','#FFCE56','#4BC0C0','#9966FF','#FF9F40','#2ECC71','#E74C3C'];

    // Destrói gráficos anteriores se existirem
    if(qtdChartInstance) qtdChartInstance.destroy();
    if(valorChartInstance) valorChartInstance.destroy();

    qtdChartInstance = new Chart(document.getElementById('qtdChart'), {
        type: 'bar',
        data: { labels, datasets: [{ label: 'Quantidade', data: qtds, backgroundColor: colors }] },
        options: { responsive: true }
    });

    valorChartInstance = new Chart(document.getElementById('valorChart'), {
        type: 'pie',
        data: { labels, datasets: [{ label: 'Valor em R$', data: valores, backgroundColor: colors }] },
        options: { responsive: true }
    });
}

// Inicializa mostrando a tabela
showSection('tabela');
applyFilters();
</script>

</body>
</html>
