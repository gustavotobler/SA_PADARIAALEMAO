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
    width:240px;
    background:var(--sidebar-bg);
    height:100vh;
    position:fixed;
    display:flex;
    flex-direction:column;
    padding-top:20px;
    transition:width .3s;
    box-shadow:3px 0 10px rgba(0,0,0,.3);
}
.sidebar.collapsed{width:60px}
.sidebar a{
    display:flex;align-items:center;color:var(--primary-text);text-decoration:none;
    padding:15px 20px;white-space:nowrap;transition:background .2s,padding .3s
}
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
.filter-group input{padding:8px;border-radius:6px;border:1px solid #ccc;background:#f0f0f0;color:#000}
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
    <a href="#" onclick="showSection('grafico-qtd')"><span class="material-icons icon">inventory</span><span class="text">Gráfico Quantidade</span></a>
    <a href="#" onclick="showSection('grafico-valor')"><span class="material-icons icon">attach_money</span><span class="text">Gráfico Valor</span></a>
    <a href="#" onclick="showSection('grafico-fornecedor')"><span class="material-icons icon">factory</span><span class="text">Gráfico Fornecedor</span></a>
    <a href="#" onclick="showSection('grafico-validade')"><span class="material-icons icon">hourglass_bottom</span><span class="text">Gráfico Validade</span></a>
    <a href="#" onclick="showSection('grafico-preco')"><span class="material-icons icon">paid</span><span class="text">Gráfico Preço</span></a>
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
            <label for="search">🔍 Produto / Categoria / Fornecedor</label>
            <input type="text" id="search" placeholder="Pesquisar...">
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
                        <td data-label="ID"><?= $row['ID_produto'] ?></td>
                        <td data-label="Nome"><?= $row['Nome_prod'] ?></td>
                        <td data-label="Fornecedor"><?= $row['Nome_forn'] ?? '---' ?></td>
                        <td data-label="Categoria"><?= $row['nome_categoria'] ?? '---' ?></td>
                        <td data-label="Unidade"><?= $row['Unid_medida'] ?></td>
                        <td data-label="Quantidade"><?= number_format($row['Qntd_produto'],2,',','.') ?></td>
                        <td data-label="Preço"><?= number_format($row['Preco_unitario'],2,',','.') ?></td>
                        <td data-label="Valor"><?= number_format($row['valor_total'],2,',','.') ?></td>
                        <td data-label="Validade"><?= !empty($row['Validade']) ? date('d/m/Y', strtotime($row['Validade'])) : '---' ?></td>
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

<section id="grafico-qtd" class="chart-section"><h2>Quantidade em Estoque por Produto</h2><canvas id="chartQtd"></canvas></section>
<section id="grafico-valor" class="chart-section"><h2>Valor Total em Estoque por Produto</h2><canvas id="chartValor"></canvas></section>
<section id="grafico-fornecedor" class="chart-section"><h2>Distribuição por Fornecedor</h2><canvas id="chartForn"></canvas></section>
<section id="grafico-validade" class="chart-section"><h2>Validade dos Produtos</h2><canvas id="chartValidade"></canvas></section>
<section id="grafico-preco" class="chart-section"><h2>Preço Unitário dos Produtos</h2><canvas id="chartPreco"></canvas></section>

</main>

<script>
const sidebar = document.getElementById('sidebar');
const mainContent = document.getElementById('mainContent');
function toggleSidebar(){
    sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('collapsed');
}

function showSection(id){
    document.querySelectorAll('.section, .chart-section').forEach(sec => sec.classList.remove('active'));
    document.getElementById(id).classList.add('active');
}

// Simples paginação
let currentPage=1; const rowsPerPage=10;
const table = document.getElementById('estoqueTable').getElementsByTagName('tbody')[0];
const rows = table.getElementsByTagName('tr');
const pageInfo = document.getElementById('pageInfo');
const prevBtn = document.getElementById('prevBtn');
const nextBtn = document.getElementById('nextBtn');

function displayRows(){
    const start=(currentPage-1)*rowsPerPage;
    const end=start+rowsPerPage;
    for(let i=0;i<rows.length;i++){rows[i].style.display=(i>=start && i<end)?'':'none'}
    pageInfo.textContent=`Página ${currentPage} de ${Math.ceil(rows.length/rowsPerPage)}`;
    prevBtn.disabled=currentPage===1;
    nextBtn.disabled=currentPage>=Math.ceil(rows.length/rowsPerPage);
}
prevBtn.onclick=()=>{currentPage--;displayRows();}
nextBtn.onclick=()=>{currentPage++;displayRows();}
displayRows();

// Filtros
const startDate = document.getElementById('startDate');
const endDate = document.getElementById('endDate');
const search = document.getElementById('search');
const clearFilters = document.getElementById('clearFilters');

function applyFilters(){
    let sDate = startDate.value? new Date(startDate.value):null;
    let eDate = endDate.value? new Date(endDate.value):null;
    let term = search.value.toLowerCase();
    let count=0;
    Array.from(rows).forEach(r=>{
        let cells=r.getElementsByTagName('td');
        let val = cells[8].textContent.trim();
        let rowDate = val!=='---'?new Date(val.split('/').reverse().join('-')):null;
        let text = Array.from(cells).slice(1,4).map(c=>c.textContent.toLowerCase()).join(' ');
        let show=true;
        if(sDate && rowDate && rowDate<sDate) show=false;
        if(eDate && rowDate && rowDate>eDate) show=false;
        if(term && !text.includes(term)) show=false;
        r.style.display=show?'':'none';
        if(show) count++;
    });
    document.getElementById('filterInfo').textContent=`Produtos filtrados: ${count}`;
    currentPage=1;
    displayRows();
}
[startDate,endDate,search].forEach(el=>el.addEventListener('input',applyFilters));
clearFilters.onclick=()=>{startDate.value='';endDate.value='';search.value='';applyFilters();};
</script>
</body>
</html>
