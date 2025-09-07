<?php
session_start();

// Segurança: apenas logados
if (!isset($_SESSION['ID_func'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Comandas</title>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
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
.main-content{margin-left:240px;padding:20px 30px;width:100%;transition:margin-left .3s;color:#fff}
.main-content.collapsed{margin-left:60px}

.header{display:flex;align-items:center;gap:10px;margin-bottom:40px}
.header .material-icons{cursor:pointer}

.actions{display:flex;gap:20px;justify-content:center}
.btn{
  padding:12px 20px;
  border:none;
  border-radius:8px;
  cursor:pointer;
  font-size:15px;
  font-weight:600;
  display:flex;
  align-items:center;
  gap:8px;
  transition:.3s;
}
.btn.create{background:#06a34a;color:#fff}
.btn.create:hover{background:#059341}
.btn.view{background:#00b4d8;color:#fff}
.btn.view:hover{background:#0098b8}
</style>
</head>
<body>

<!-- Sidebar -->
<nav class="sidebar" id="sidebar">
    <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
    <a href="inicial1.php"><span class="material-icons icon">arrow_back</span><span class="text">Voltar</span></a>
    <a href="comandas_home.php"><span class="material-icons icon">receipt</span><span class="text">Comandas</span></a>
</nav>

<!-- Conteúdo -->
<main class="main-content" id="mainContent">
  <div class="header">
    <span class="material-icons" onclick="window.location.href='inicial1.php'">arrow_back</span>
    <h2>Comandas</h2>
  </div>

  <div class="actions">
    <button id="btnCreate" class="btn create"><span class="material-icons">add_circle</span> Criar Comanda</button>
    <button id="btnView" class="btn view"><span class="material-icons">visibility</span> Ver Comandas</button>
  </div>
</main>

<script>
const sidebar=document.getElementById('sidebar');
const mainContent=document.getElementById('mainContent');
function toggleSidebar(){
    sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('collapsed');
}
document.getElementById('btnCreate').addEventListener('click',()=>{
    if(confirm("Criar nova comanda agora?")){
        window.location.href="comanda_single.php?acao=nova";
    }
});
document.getElementById('btnView').addEventListener('click',()=>{
    window.location.href="comandas_lista.php";
});
</script>

</body>
</html>
