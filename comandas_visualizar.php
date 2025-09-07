<?php
session_start();
require_once 'conexao.php';

// Segurança
if (!isset($_SESSION['ID_func'])) {
    header('Location: index.php');
    exit();
}

// Nome do funcionário
$id_Nome = $_SESSION['ID_func'];
$stmtNome = $pdo->prepare("SELECT Nome_func FROM funcionario WHERE ID_func = :ID_func LIMIT 1");
$stmtNome->execute([':ID_func' => $id_Nome]);
$nomeFunc = $stmtNome->fetchColumn() ?? ($_SESSION['nome_func'] ?? 'Usuário');

// CSRF
if (!isset($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(24));
}
$csrf = $_SESSION['csrf'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Comandas Rápido</title>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<style>
:root {
    --sidebar-bg: linear-gradient(180deg, #0d1b2a, #1b263b);
    --main-bg: rgb(59, 75, 93);
    --primary-text: #f8f9fa;
    --hover-bg: #1e3a5f;
    --highlight: #0077b6;
    --card-bg: #1c2a3a; /* card mais escuro */
    --green: #06a34a;
    --blue: #00b4d8;
    --text: #f8f9fa; /* texto claro */
    --muted: #94a3b8;
}

/* Reset */
*{margin:0;padding:0;box-sizing:border-box;font-family:"Segoe UI",Tahoma,Geneva,Verdana,sans-serif}
body{background-color:var(--main-bg);display:flex;min-height:100vh}

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

/* Main content */
.main-content{
    margin-left:240px;
    width:100%;
    transition:margin-left .3s;
    display:flex;
    justify-content:center;
    align-items:flex-start;
    min-height:100vh;
    padding:40px 20px;
}

/* Card container */
.wrap{
    width:100%;
    max-width:500px;
    display:flex;
    flex-direction:column;
    gap:20px;
}
.card{
    background:var(--card-bg);
    color:var(--text);
    border-radius:14px;
    padding:24px;
    box-shadow:0 12px 36px rgba(0,0,0,.45);
    display:flex;
    flex-direction:column;
    gap:20px;
    align-items:center;
}
.logo{display:flex;align-items:center;gap:12px}
.logo img{height:60px;width:60px;border-radius:10px;object-fit:cover}
h1{font-size:20px;margin:0}
.lead{font-size:14px;color:var(--muted)}
.actions{
    display:flex;
    gap:16px;
    justify-content:center;
    margin-top:10px;
}
.action-card{
  flex:1;
  max-width:200px;
  border-radius:12px;
  padding:14px;
  cursor:pointer;
  text-align:center;
  display:flex;
  flex-direction:column;
  gap:6px;
  align-items:center;
  transition:.25s;
  border:2px solid transparent;
  color:#fff;
}
.action-card span.material-icons{font-size:28px}
.action-card h2{font-size:15px;margin:0}
.action-card.create{background:var(--green)}
.action-card.view{background:var(--blue)}
.action-card:hover{transform:translateY(-2px);box-shadow:0 4px 12px rgba(0,0,0,.25)}
.status{margin-top:6px;font-size:14px;text-align:center}
@media(max-width:540px){
  .card{padding:18px}
  .logo img{height:50px;width:50px}
  .actions{flex-direction:column;gap:12px}
  .action-card{max-width:100%}
}
</style>
</head>
<body>

<!-- Sidebar -->
<nav class="sidebar" id="sidebar">
    <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
    <a href="inicial1.php"><span class="material-icons icon">arrow_back</span><span class="text">Voltar</span></a>
    <a href="comanda.php"><span class="material-icons icon">receipt</span><span class="text">Criar Comanda</span></a>
    <a href="ver_comandas.php"><span class="material-icons icon">visibility</span><span class="text">Ver Comandas</span></a>
</nav>

<!-- Main content -->
<main class="main-content" id="mainContent">
  <div class="wrap">
    <div class="card">
      <div class="logo">
        <img src="img/Logopadaria.png" alt="Logo Padaria">
        <div>
          <h1>Comandas Rápido</h1>
          <div class="lead">Olá, <strong><?= htmlspecialchars($nomeFunc) ?></strong>. Escolha uma ação.</div>
        </div>
      </div>

      <div class="actions">
        <div id="btnCreate" class="action-card create">
          <span class="material-icons">add_circle</span>
          <h2>Criar Comanda</h2>
        </div>
        <div id="btnView" class="action-card view">
          <span class="material-icons">visibility</span>
          <h2>Ver Comandas</h2>
        </div>
      </div>

      <div id="status" class="status" style="display:none"></div>
    </div>
  </div>
</main>

<script>
const sidebar = document.getElementById('sidebar');
const mainContent = document.getElementById('mainContent');

function toggleSidebar(){
    sidebar.classList.toggle('collapsed');
    mainContent.style.marginLeft = sidebar.classList.contains('collapsed') ? '60px' : '240px';
}

const btnCreate = document.getElementById("btnCreate");
const btnView = document.getElementById("btnView");
const statusEl = document.getElementById("status");
const csrf = <?= json_encode($csrf) ?>;

function showStatus(msg, ok=true){
  statusEl.style.display="block";
  statusEl.textContent=msg;
  statusEl.style.color=ok?"#0b7285":"#b91c1c";
}

btnCreate.addEventListener("click", async ()=>{
  if(!confirm("Criar nova comanda agora?")) return;
  showStatus("Criando comanda...");
  try{
    const data = new FormData();
    data.append("acao","nova");
    data.append("csrf", csrf);
    const res = await fetch("comanda.php", {method:"POST", body:data, credentials:"same-origin"});
    if(res.ok){
      const ct = res.headers.get("content-type")||"";
      if(ct.includes("json")){
        const j = await res.json();
        if(j.id||j.novo_id){ location.href="comanda.php?id="+(j.id||j.novo_id); return; }
        if(j.redirect){ location.href=j.redirect; return; }
      }
      location.reload();
    } else { showStatus("Erro ao criar comanda", false); }
  } catch(e){ showStatus("Erro de rede", false); }
});

btnView.addEventListener("click", ()=>{ location.href="comandas_unificado.php"; });
</script>

</body>
</html>
