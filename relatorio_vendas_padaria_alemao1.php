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
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $sql = "
    SELECT 
        v.ID_vendas,
        v.venda_data,
        f.Nome_func,
        p.Nome_prod,
        iv.Quantidade AS quant_vendas,
        p.Preco_unitario AS preco_unit,
        iv.valor_total AS preco_total,
        v.forma_pagamento
    FROM vendas v
    LEFT JOIN funcionario f ON v.ID_func = f.ID_func
    LEFT JOIN itens_vendas iv ON v.ID_vendas = iv.ID_vendas
    LEFT JOIN produtos p ON iv.ID_produto = p.ID_produto
    ORDER BY v.venda_data DESC";

    $stmt = $conn->query($sql);
    $rows = $stmt->fetchAll();

    // Corrigir possíveis valores null para evitar erros
    foreach ($rows as &$row) {
        $row['ID_vendas']     = $row['ID_vendas'] ?? '';
        $row['venda_data']    = $row['venda_data'] ?? '';
        $row['Nome_func']     = $row['Nome_func'] ?? '';
        $row['Nome_prod']     = $row['Nome_prod'] ?? '';
        $row['quant_vendas']  = $row['quant_vendas'] ?? 0;
        $row['preco_unit']    = $row['preco_unit'] ?? 0;
        $row['preco_total']   = $row['preco_total'] ?? 0;
        $row['forma_pagamento']= $row['forma_pagamento'] ?? '';
    }

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
    --sidebar-bg: linear-gradient(180deg, #0d1b2a, #1b263b);
    --primary-text: #f8f9fa;
    --hover-bg: #1e3a5f;
    --main-bg: rgb(59, 75, 93);
    --card-bg: #ffffff;
    --accent: #1b263b;
    --highlight: #0077b6;
}

/* Reset */
*{margin:0;padding:0;box-sizing:border-box;font-family:"Segoe UI",Tahoma,Geneva,Verdana,sans-serif}
body{background-color:var(--main-bg);display:flex}

/* Sidebar */
.sidebar {
    width:240px;background:var(--sidebar-bg);height:100vh;position:fixed;
    display:flex;flex-direction:column;padding-top:20px;transition:width .3s;
    box-shadow:3px 0 10px rgba(0,0,0,.3)
}
.sidebar.collapsed{width:60px}
.sidebar a{display:flex;align-items:center;color:var(--primary-text);text-decoration:none;padding:15px 20px;white-space:nowrap;transition:background .2s,padding .3s}
.sidebar a:hover{background:var(--hover-bg);border-left:4px solid var(--highlight);padding-left:16px}
.sidebar .icon{margin-right:8px}
.sidebar.collapsed .text{display:none}
.sidebar.collapsed .icon{margin-right:0;justify-content:center}
.toggle-btn{cursor:pointer;text-align:center;margin-bottom:20px;font-size:22px;color:var(--primary-text)}

/* Main */
.main-content{margin-left:240px;padding:20px 30px;width:100%;transition:margin-left .3s}
.main-content.collapsed{margin-left:60px}

h1,h2{text-align:center;margin-bottom:20px;color:#fff}

/* Filtros */
#filters{display:flex;gap:10px;justify-content:center;margin-bottom:20px;flex-wrap:wrap;align-items:flex-end}
.filter-group{display:flex;flex-direction:column;color:#fff}
.filter-group input{padding:8px;border-radius:6px;border:1px solid #ccc}
#clearFilters{background:var(--highlight);color:#fff;padding:8px 15px;border:none;border-radius:6px;cursor:pointer}
#clearFilters:hover{background:#023e8a}

/* Tabela */
table{width:100%;border-collapse:collapse;background:var(--card-bg);border-radius:12px;overflow:hidden;box-shadow:0 3px 8px rgba(0,0,0,.15)}
thead{background:var(--accent);color:var(--primary-text)}
thead th{padding:14px 10px;text-align:center;font-size:.9rem;font-weight:600;letter-spacing:.5px}
tbody td{padding:12px 10px;border-bottom:1px solid #eee;font-size:.9rem;text-align:center}
tbody tr:nth-child(even){background:#f9fbfd}
tbody tr:hover{background:var(--highlight);color:#fff;transition:.2s}

/* Paginação */
.pagination{display:flex;justify-content:center;align-items:center;gap:10px;margin-top:15px}
.pagination button{padding:8px 12px;border:none;background:var(--accent);color:var(--primary-text);border-radius:6px;cursor:pointer}
.pagination button:disabled{background:#999;cursor:default}

/* Gráficos */
.chart-section{margin:40px auto;background:var(--card-bg);padding:20px;border-radius:12px;max-width:700px;display:none}
.chart-section.active{display:block}
.chart-container{position:relative;height:400px;width:100%}
.filter-info{text-align:center;margin-bottom:10px;font-weight:bold;color:#fff}

/* Responsivo */
@media(max-width:768px){
  .main-content{margin-left:0;padding:1rem}
  table{font-size:.8rem}
  thead{display:none}
  tbody td{display:block;text-align:right;padding:8px;border:none;border-bottom:1px solid #eee}
  tbody td::before{content: attr(data-label);float:left;font-weight:600;color:#555}
  #filters{flex-direction:column;gap:10px;align-items:stretch}
}
</style>
</head>
<body>

<nav class="sidebar" id="sidebar">
    <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
    <a href="inicial1.php"><span class="material-icons icon">arrow_back</span><span class="text">Voltar</span></a>
    <a href="#" onclick="showSection('tabela')"><span class="material-icons icon">table_chart</span><span class="text">Tabela</span></a>
    <a href="#" onclick="showSection('grafico-produto')"><span class="material-icons icon">inventory</span><span class="text">Gráfico Produto</span></a>
    <a href="#" onclick="showSection('grafico-pagamento')"><span class="material-icons icon">credit_card</span><span class="text">Gráfico Pagamento</span></a>
    <a href="#" onclick="showSection('grafico-funcionario')"><span class="material-icons icon">person</span><span class="text">Gráfico Funcionário</span></a>
    <a href="#" onclick="showSection('grafico-dia')"><span class="material-icons icon">calendar_today</span><span class="text">Gráfico Diário</span></a>

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
        <form method="POST" action="relatorio_vendas_pdf.php" target="_blank" style="text-align:center;margin-bottom:15px;">
    <input type="hidden" name="startDate" id="pdfStartDate">
    <input type="hidden" name="endDate" id="pdfEndDate">
    <input type="hidden" name="search" id="pdfSearch">
    <button type="submit" style="padding:8px 15px;background:#0077b6;color:#fff;border:none;border-radius:6px;cursor:pointer;">
        📄 Gerar PDF
    </button>
</form>

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
        <td data-label="ID"><?= htmlspecialchars($row['ID_vendas'] ?? '') ?></td>
        <td data-label="Data"><?= htmlspecialchars($row['venda_data'] ?? '') ?></td>
        <td data-label="Funcionário"><?= htmlspecialchars($row['Nome_func'] ?? '') ?></td>
        <td data-label="Produto"><?= htmlspecialchars($row['Nome_prod'] ?? '') ?></td>
        <td data-label="Qtd"><?= htmlspecialchars($row['quant_vendas'] ?? 0) ?></td>
        <td data-label="Preço"><?= number_format($row['preco_unit'] ?? 0, 2, ',', '.') ?></td>
        <td data-label="Total"><?= number_format($row['preco_total'] ?? 0, 2, ',', '.') ?></td>
        <td data-label="Pagamento"><?= htmlspecialchars($row['forma_pagamento'] ?? '') ?></td>
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

<section id="grafico-produto" class="chart-section"><h2>Vendas por Produto</h2><div class="chart-container"><canvas id="produtoChart"></canvas></div></section>
<section id="grafico-pagamento" class="chart-section"><h2>Vendas por Pagamento (R$)</h2><div class="chart-container"><canvas id="pagamentoChart"></canvas></div></section>
<section id="grafico-funcionario" class="chart-section"><h2>Vendas por Funcionário (R$)</h2><div class="chart-container"><canvas id="funcionarioChart"></canvas></div></section>
<section id="grafico-dia" class="chart-section"><h2>Total Vendido por Dia (R$)</h2><div class="chart-container"><canvas id="diaChart"></canvas></div></section>
</main>

<script>
const sidebar=document.getElementById('sidebar');
const mainContent=document.getElementById('mainContent');
function toggleSidebar(){sidebar.classList.toggle('collapsed');mainContent.classList.toggle('collapsed')}
function showSection(id){
    document.querySelectorAll('.section,.chart-section').forEach(s=>s.style.display='none');
    document.getElementById(id).style.display='block';
    if(id.startsWith('grafico-')) setTimeout(updateCharts,50);
}

// Filtros + Paginação
const tableRows=Array.from(document.querySelectorAll('#vendasTable tbody tr'));
const startDateInput=document.getElementById('startDate');
const endDateInput=document.getElementById('endDate');
const searchInput=document.getElementById('search');
const clearBtn=document.getElementById('clearFilters');
const filterInfo=document.getElementById('filterInfo');
let currentPage=1;const rowsPerPage=9;let filteredIndices=[];

// função robusta para parse BR (1.234,56 -> 1234.56)
function parseBR(str){
    if(!str && str !== 0) return 0;
    let s = String(str).trim();
    // remove qualquer caractere que não seja dígito, ponto ou vírgula ou hífen
    s = s.replace(/[^\d\.,\-]/g,'');
    // se tiver vírgula, assumir formato BR: remove pontos (milhares) e troca vírgula por ponto
    if(s.indexOf(',') !== -1){
        s = s.replace(/\./g,'').replace(',', '.');
    } else {
        // sem vírgula, pode ter pontos como milhares -> remover
        s = s.replace(/\./g,'');
    }
    const n = parseFloat(s);
    return isNaN(n) ? 0 : n;
}

function applyFilters(){
    const start=startDateInput.value;
    const end=endDateInput.value;
    const search=searchInput.value.toLowerCase();
    filteredIndices=[];
    tableRows.forEach((row,idx)=>{
        const date=row.cells[1].textContent.trim();
        const produto=row.cells[3].textContent.toLowerCase();
        const func=row.cells[2].textContent.toLowerCase();
        // date string comparison: se input tem valor (YYYY-MM-DD) comparamos com prefix da célula
        let passDate=true;
        if(start){
            passDate = (date >= start);
        }
        if(end){
            passDate = passDate && (date <= end);
        }
        const passSearch = produto.includes(search) || func.includes(search);
        if(passDate && passSearch) filteredIndices.push(idx);
    });

    const totalPages=Math.ceil(filteredIndices.length/rowsPerPage) || 1;
    if(currentPage>totalPages) currentPage=totalPages;

    tableRows.forEach((row,idx)=>{
        const pos = filteredIndices.indexOf(idx);
        row.style.display = (pos >= (currentPage-1)*rowsPerPage && pos < currentPage*rowsPerPage) ? '' : 'none';
    });

    document.getElementById('pageInfo').textContent=`Página ${currentPage} de ${totalPages}`;
    document.getElementById('prevBtn').disabled = currentPage === 1;
    document.getElementById('nextBtn').disabled = currentPage === totalPages;
    filterInfo.textContent = filteredIndices.length ? `Vendas encontradas: ${filteredIndices.length}` : 'Nenhuma venda encontrada';

    // atualiza os charts (reflete datas / busca)
    updateCharts();
}

startDateInput.addEventListener('change',()=>{currentPage=1;applyFilters()});
endDateInput.addEventListener('change',()=>{currentPage=1;applyFilters()});
searchInput.addEventListener('input',()=>{currentPage=1;applyFilters()});
clearBtn.addEventListener('click',()=>{startDateInput.value='';endDateInput.value='';searchInput.value='';currentPage=1;applyFilters()});
document.getElementById('prevBtn').addEventListener('click',()=>{if(currentPage>1){currentPage--;applyFilters()}})
document.getElementById('nextBtn').addEventListener('click',()=>{if(currentPage<Math.ceil(filteredIndices.length/rowsPerPage)){currentPage++;applyFilters()}})

// Gráficos — inicializa com objetos vazios
const defaultColors=['#FF6384','#36A2EB','#FFCE56','#4BC0C0','#9966FF','#FF9F40','#8E44AD','#2ECC71','#E74C3C','#34495E','#F39C12','#1ABC9C'];
let produtoChart = new Chart(document.getElementById('produtoChart'),{
    type:'bar',
    data:{labels:[],datasets:[{label:'Quantidade',data:[],backgroundColor:[],borderWidth:1}]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}}
});
let pagamentoChart = new Chart(document.getElementById('pagamentoChart'),{
    type:'pie',
    data:{labels:[],datasets:[{data:[],backgroundColor:[]} ]},
    options:{responsive:true,maintainAspectRatio:false}
});
let funcionarioChart = new Chart(document.getElementById('funcionarioChart'),{
    type:'bar',
    data:{labels:[],datasets:[{label:'Total (R$)',data:[],backgroundColor:[],borderWidth:1}]},
    options:{responsive:true,maintainAspectRatio:false,scales:{y:{beginAtZero:true}}}
});
let diaChart = new Chart(document.getElementById('diaChart'),{
    type:'line',
    data:{labels:[],datasets:[{label:'Total vendido (R$)',data:[],borderColor:'',backgroundColor:'',fill:true,tension:.3}]},
    options:{responsive:true,maintainAspectRatio:false,scales:{y:{beginAtZero:true}}}
});

function updateCharts(){
    // preparar agregações a partir das linhas filtradas
    const prodCounts = {}; // quantidade por produto
    const pagTotals = {}; // valor total por forma de pagamento (R$)
    const funcTotals = {}; // valor total por funcionario (R$)
    const diaTotals = {}; // total por data

    if(filteredIndices.length === 0){
        // sem dados: limpar charts
        produtoChart.data.labels = []; produtoChart.data.datasets[0].data = []; produtoChart.data.datasets[0].backgroundColor = [];
        pagamentoChart.data.labels = []; pagamentoChart.data.datasets[0].data = []; pagamentoChart.data.datasets[0].backgroundColor = [];
        funcionarioChart.data.labels = []; funcionarioChart.data.datasets[0].data = []; funcionarioChart.data.datasets[0].backgroundColor = [];
        diaChart.data.labels = []; diaChart.data.datasets[0].data = [];
        produtoChart.update(); pagamentoChart.update(); funcionarioChart.update(); diaChart.update();
        return;
    }

    filteredIndices.forEach(idx=>{
        const row = tableRows[idx];
        const produto = row.cells[3].textContent.trim();
        const qtdStr = row.cells[4].textContent.trim();
        const totalStr = row.cells[6].textContent.trim();
        const func = row.cells[2].textContent.trim();
        const pagamento = row.cells[7].textContent.trim();
        const date = row.cells[1].textContent.trim();

        const qtd = parseBR(qtdStr);
        const total = parseBR(totalStr);

        prodCounts[produto] = (prodCounts[produto] || 0) + qtd;
        pagTotals[pagamento] = (pagTotals[pagamento] || 0) + total;
        funcTotals[func] = (funcTotals[func] || 0) + total;
        // armazenar por date string; manter raw string, ordenaremos depois
        diaTotals[date] = (diaTotals[date] || 0) + total;
    });

    // Produto chart: labels e dados (quantidade)
    const prodLabels = Object.keys(prodCounts);
    const prodData = Object.values(prodCounts);
    produtoChart.data.labels = prodLabels;
    produtoChart.data.datasets[0].data = prodData;
    produtoChart.data.datasets[0].backgroundColor = prodLabels.map((_,i)=>defaultColors[i % defaultColors.length]);
    produtoChart.update();

    // Pagamento chart: agora soma valores (R$)
    const pagLabels = Object.keys(pagTotals);
    const pagData = pagLabels.map(l => +pagTotals[l].toFixed(2));
    pagamentoChart.data.labels = pagLabels;
    pagamentoChart.data.datasets[0].data = pagData;
    pagamentoChart.data.datasets[0].backgroundColor = pagLabels.map((_,i)=>defaultColors[i % defaultColors.length]);
    pagamentoChart.update();

    // Funcionário chart: soma em R$
    const funcLabels = Object.keys(funcTotals);
    const funcData = funcLabels.map(l => +funcTotals[l].toFixed(2));
    funcionarioChart.data.labels = funcLabels;
    funcionarioChart.data.datasets[0].data = funcData;
    funcionarioChart.data.datasets[0].backgroundColor = funcLabels.map((_,i)=>defaultColors[i % defaultColors.length]);
    funcionarioChart.update();

    // Dia chart: ordenar por data (tentar converter para Date)
    const diaEntries = Object.entries(diaTotals).map(([k,v])=>{
        // tenta criar Date (aceita 'YYYY-MM-DD' ou ISO); fallback: keep string
        const d = new Date(k);
        return { key:k, dateObj: isNaN(d.getTime()) ? null : d, value: +v.toFixed(2) };
    });
    diaEntries.sort((a,b)=>{
        if(a.dateObj && b.dateObj) return a.dateObj - b.dateObj;
        return a.key.localeCompare(b.key);
    });
    const diaLabels = diaEntries.map(e => e.key);
    const diaData = diaEntries.map(e => e.value);
    diaChart.data.labels = diaLabels;
    diaChart.data.datasets[0].data = diaData;
    const dayColor = defaultColors[1];
    diaChart.data.datasets[0].borderColor = dayColor;
    diaChart.data.datasets[0].backgroundColor = dayColor + '33';
    diaChart.update();
}

// primeira aplicação de filtros (inicial)
applyFilters();
</script>
</body>
</html>
    