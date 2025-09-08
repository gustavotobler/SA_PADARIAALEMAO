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
    --btn-surface: rgba(255,255,255,0.03);
    --btn-border: rgba(255,255,255,0.06);
    --glass: rgba(255,255,255,0.02);
}

/* Reset */
*{margin:0;padding:0;box-sizing:border-box;font-family:"Segoe UI",Tahoma,Geneva,Verdana,sans-serif}
body{
  background: linear-gradient(180deg,var(--main-bg),#0b2e3f);
  color:var(--text);
  display:flex;
  min-height:100vh;
  -webkit-font-smoothing:antialiased;
}

/* Sidebar (mantida) */
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
    max-width:560px;
    display:flex;
    flex-direction:column;
    gap:20px;
}
.card{
    background:var(--card-bg);
    color:var(--text);
    border-radius:14px;
    padding:28px;
    box-shadow:0 18px 48px rgba(0,0,0,.55);
    display:flex;
    flex-direction:column;
    gap:20px;
    align-items:center;
    border:1px solid var(--glass);
}
.logo{display:flex;align-items:center;gap:12px}
.logo img{height:60px;width:60px;border-radius:10px;object-fit:cover}
h1{font-size:20px;margin:0}
.lead{font-size:14px;color:var(--muted)}

/* Actions area: cards botões mais bonitos */
.actions{
    display:flex;
    gap:16px;
    justify-content:center;
    margin-top:10px;
    width:100%;
}
.action-card{
  flex:1;
  max-width:240px;
  border-radius:12px;
  padding:16px;
  cursor:pointer;
  text-align:center;
  display:flex;
  flex-direction:column;
  gap:8px;
  align-items:center;
  transition:transform .18s ease, box-shadow .18s ease, border-color .18s ease;
  border:1px solid transparent;
  color:#fff;
  position:relative;
  overflow:hidden;
  min-height:96px;
  justify-content:center;
  box-shadow: 0 8px 22px rgba(2,8,23,0.45);
  background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
}

/* Icon badge */
.action-card .icobg{
  width:56px;height:56px;border-radius:12px;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,0.03);backdrop-filter:blur(4px);
  box-shadow: inset 0 -6px 18px rgba(0,0,0,0.25);
}
.action-card span.material-icons{font-size:26px}

/* Create button style (green accent) */
.action-card.create{
  border:1px solid rgba(6,163,90,0.12);
  background: linear-gradient(180deg, rgba(6,163,90,0.08), rgba(6,110,70,0.03));
}
.action-card.create .icobg{background: linear-gradient(90deg,#14a352,#16ca6b); box-shadow:none;}
.action-card.create span.material-icons{color:#fff}

/* View button style (blue accent) */
.action-card.view{
  border:1px solid rgba(0,180,216,0.12);
  background: linear-gradient(180deg, rgba(0,180,216,0.06), rgba(1,60,80,0.03));
}
.action-card.view .icobg{background: linear-gradient(90deg,#0ea5ff,#00b4d8); box-shadow:none;}
.action-card.view span.material-icons{color:#fff}

/* Hover / focus */
.action-card:hover,
.action-card:focus{
  transform:translateY(-6px) scale(1.01);
  box-shadow: 0 20px 48px rgba(2,12,24,0.6);
  outline: none;
}
.action-card:active{
  transform:translateY(-2px);
}

/* headings inside */
.action-card h2{font-size:15px;margin:0;font-weight:700;color:var(--text)}
.action-card p{font-size:13px;color:var(--muted);margin:0}

/* status text */
.status{margin-top:6px;font-size:14px;text-align:center;color:var(--muted)}

/* mobile */
@media(max-width:720px){
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
<main class="main-content" id="mainContent" role="main">
  <div class="wrap">
    <div class="card" role="region" aria-labelledby="title">
      <div class="logo">
        <img src="img/Logopadaria.png" alt="Logo Padaria">
        <div>
          <h1 id="title">Comandas Rápido</h1>
          <div class="lead">Olá, <strong><?= htmlspecialchars($nomeFunc) ?></strong>. Escolha uma ação.</div>
        </div>
      </div>

      <div class="actions" role="toolbar" aria-label="Ações rápidas">
        <div id="btnCreate" class="action-card create" role="button" tabindex="0" aria-pressed="false" aria-label="Criar comanda">
          <div class="icobg"><span class="material-icons">add_circle</span></div>
          <h2>Criar Comanda</h2>
          <p>Ir para a tela de criação de comanda</p>
        </div>

        <div id="btnView" class="action-card view" role="button" tabindex="0" aria-label="Ver comandas">
          <div class="icobg"><span class="material-icons">visibility</span></div>
          <h2>Ver Comandas</h2>
          <p>Listar e gerenciar comandas existentes</p>
        </div>
      </div>

      <div id="status" class="status" aria-live="polite" style="display:none"></div>
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

/* acessibilidade: ativar via teclado */
function addKeyboardActivation(el, handler){
  el.addEventListener('keydown', function(e){
    if(e.key === 'Enter' || e.key === ' '){
      e.preventDefault();
      handler();
    }
  });
}

/* Mostrar status temporário (usado se necessário) */
function showStatus(msg, ok=true){
  statusEl.style.display="block";
  statusEl.textContent=msg;
  statusEl.style.color = ok ? '#9ee6d1' : '#fca5a5';
  setTimeout(()=>{ statusEl.style.display="none"; }, 3000);
}

/* Apenas navega para a página de criação — NÃO faz POST/CREATE */
btnCreate.addEventListener("click", ()=>{
  // navega sem criar nada; o usuário poderá criar/editar na página comanda.php
  location.href = "comanda.php";
});
addKeyboardActivation(btnCreate, ()=> location.href = "comanda.php");

/* Ver comandas */
btnView.addEventListener("click", ()=>{ location.href="ver_comandas.php"; });
addKeyboardActivation(btnView, ()=> location.href = "ver_comandas.php");
</script>

</body>
</html>
