<?php
$host = 'localhost';
$dbname = 'padariadoalemao';
$user = 'root';
$pass = '';

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
                        <td data-label="ID"><?= $row['ID_vendas'] ?></td>
                        <td data-label="Data"><?= $row['venda_data'] ?></td>
                        <td data-label="Funcionário"><?= $row['Nome_func'] ?></td>
                        <td data-label="Produto"><?= $row['Nome_prod'] ?></td>
                        <td data-label="Qtd"><?= $row['quant_vendas'] ?></td>
                        <td data-label="Preço"><?= number_format($row['preco_unit'], 2, ',', '.') ?></td>
                        <td data-label="Total"><?= number_format($row['preco_total'], 2, ',', '.') ?></td>
                        <td data-label="Pagamento"><?= $row['forma_pagamento'] ?></td>
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
<section id="grafico-pagamento" class="chart-section"><h2>Vendas por Pagamento</h2><div class="chart-container"><canvas id="pagamentoChart"></canvas></div></section>
<section id="grafico-funcionario" class="chart-section"><h2>Vendas por Funcionário</h2><div class="chart-container"><canvas id="funcionarioChart"></canvas></div></section>
<section id="grafico-dia" class="chart-section"><h2>Total Vendido por Dia</h2><div class="chart-container"><canvas id="diaChart"></canvas></div></section>
</main>

<script>
const sidebar=document.getElementById('sidebar');
const mainContent=document.getElementById('mainContent');
function toggleSidebar(){sidebar.classList.toggle('collapsed');mainContent.classList.toggle('collapsed')}
function showSection(id){document.querySelectorAll('.section,.chart-section').forEach(s=>s.style.display='none');document.getElementById(id).style.display='block';if(id.startsWith('grafico-'))setTimeout(updateCharts,50)}

// Filtros + Paginação
const tableRows=Array.from(document.querySelectorAll('#vendasTable tbody tr'));
const startDateInput=document.getElementById('startDate');
const endDateInput=document.getElementById('endDate');
const searchInput=document.getElementById('search');
const clearBtn=document.getElementById('clearFilters');
const filterInfo=document.getElementById('filterInfo');
let currentPage=1;const rowsPerPage=9;let filteredIndices=[];

function applyFilters(){
    const start=startDateInput.value;const end=endDateInput.value;const search=searchInput.value.toLowerCase();
    filteredIndices=[];tableRows.forEach((row,idx)=>{const date=row.cells[1].textContent;const produto=row.cells[3].textContent.toLowerCase();const func=row.cells[2].textContent.toLowerCase();let passDate=true;if(start)passDate=date>=start;if(end)passDate=passDate&&date<=end;let passSearch=produto.includes(search)||func.includes(search);if(passDate&&passSearch)filteredIndices.push(idx)});
    const totalPages=Math.ceil(filteredIndices.length/rowsPerPage)||1;if(currentPage>totalPages)currentPage=totalPages;
    tableRows.forEach((row,idx)=>{const pos=filteredIndices.indexOf(idx);row.style.display=(pos>=(currentPage-1)*rowsPerPage&&pos<currentPage*rowsPerPage)?'':'none'});
    document.getElementById('pageInfo').textContent=`Página ${currentPage} de ${totalPages}`;
    document.getElementById('prevBtn').disabled=currentPage===1;document.getElementById('nextBtn').disabled=currentPage===totalPages;
    filterInfo.textContent=filteredIndices.length?`Vendas encontradas: ${filteredIndices.length}`:'Nenhuma venda encontrada';updateCharts()
}
startDateInput.addEventListener('change',()=>{currentPage=1;applyFilters()});
endDateInput.addEventListener('change',()=>{currentPage=1;applyFilters()});
searchInput.addEventListener('input',()=>{currentPage=1;applyFilters()});
clearBtn.addEventListener('click',()=>{startDateInput.value='';endDateInput.value='';searchInput.value='';currentPage=1;applyFilters()});
document.getElementById('prevBtn').addEventListener('click',()=>{if(currentPage>1){currentPage--;applyFilters()}})
document.getElementById('nextBtn').addEventListener('click',()=>{if(currentPage<Math.ceil(filteredIndices.length/rowsPerPage)){currentPage++;applyFilters()}})

// Gráficos
const defaultColors=['#FF6384','#36A2EB','#FFCE56','#4BC0C0','#9966FF','#FF9F40','#8E44AD','#2ECC71','#E74C3C'];
let produtoChart=new Chart(document.getElementById('produtoChart'),{type:'bar',data:{labels:[],datasets:[{label:'Quantidade',data:[],backgroundColor:[],borderWidth:1}]}})
let pagamentoChart=new Chart(document.getElementById('pagamentoChart'),{type:'pie',data:{labels:[],datasets:[{data:[],backgroundColor:[]}]}})
let funcionarioChart=new Chart(document.getElementById('funcionarioChart'),{type:'bar',data:{labels:[],datasets:[{label:'Vendas',data:[],backgroundColor:[],borderWidth:1}]}})
let diaChart=new Chart(document.getElementById('diaChart'),{type:'line',data:{labels:[],datasets:[{label:'Total vendido',data:[],borderColor:'',backgroundColor:'',fill:true,tension:.3}]}})

function updateCharts(){
    const prodCounts={},pagCounts={},funcCounts={},diaTotals={};
    filteredIndices.forEach(idx=>{const row=tableRows[idx];const qtd=+row.cells[4].textContent.replace(',','.');const total=+row.cells[6].textContent.replace(',','.');const produto=row.cells[3].textContent;const func=row.cells[2].textContent;const pagamento=row.cells[7].textContent;const date=row.cells[1].textContent;prodCounts[produto]=(prodCounts[produto]||0)+qtd;pagCounts[pagamento]=(pagCounts[pagamento]||0)+1;funcCounts[func]=(funcCounts[func]||0)+1;diaTotals[date]=(diaTotals[date]||0)+total});
    produtoChart.data.labels=Object.keys(prodCounts);produtoChart.data.datasets[0].data=Object.values(prodCounts);produtoChart.data.datasets[0].backgroundColor=produtoChart.data.labels.map((_,i)=>defaultColors[i%defaultColors.length]);produtoChart.update();
    pagamentoChart.data.labels=Object.keys(pagCounts);pagamentoChart.data.datasets[0].data=Object.values(pagCounts);pagamentoChart.data.datasets[0].backgroundColor=pagamentoChart.data.labels.map((_,i)=>defaultColors[i%defaultColors.length]);pagamentoChart.update();
    funcionarioChart.data.labels=Object.keys(funcCounts);funcionarioChart.data.datasets[0].data=Object.values(funcCounts);funcionarioChart.data.datasets[0].backgroundColor=funcionarioChart.data.labels.map((_,i)=>defaultColors[i%defaultColors.length]);funcionarioChart.update();
    diaChart.data.labels=Object.keys(diaTotals);diaChart.data.datasets[0].data=Object.values(diaTotals);const dayColor=defaultColors[1];diaChart.data.datasets[0].borderColor=dayColor;diaChart.data.datasets[0].backgroundColor=dayColor+'33';diaChart.update()
}
applyFilters();
</script>
</body>
</html>
