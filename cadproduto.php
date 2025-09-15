<?php 
session_start();
require_once 'conexao.php';

// 🔒 Verificações de segurança
if (!isset($_SESSION['funcionario']) || !isset($_SESSION['nivel'])) {
  echo "<script>alert('Você precisa estar logado!');window.location.href='inicial1.php';</script>";
  exit;
}
if ($_SESSION['nivel'] != 1) {
  echo "<script>alert('Erro, você não possui o nível de acesso');window.location.href='estoque.php';</script>";
  exit;
}

// 📦 Busca fornecedores
try {
  $stmt = $pdo->query("SELECT ID_forn, Nome_forn FROM fornecedores ORDER BY Nome_forn");
  $fornecedores = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  die("Erro ao buscar fornecedores: " . $e->getMessage());
}

// 📦 Busca categorias
try {
  $stmt2 = $pdo->query("SELECT id_categorias, nome_categoria FROM categorias ORDER BY nome_categoria");
  $categorias = $stmt2->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  die("Erro ao buscar categorias: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Cadastro de Produto</title>
<style>
  * { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif; }
  body { background:rgb(59, 75, 93); min-height:100vh; display:flex; flex-direction:column; }
  header { background:rgb(27,68,95); padding:15px 20px; color:white; display:flex; align-items:center; gap:15px; box-shadow:0 3px 10px rgba(0,0,0,0.15);}
  header .back-btn { background:transparent; border:none; color:white; cursor:pointer; font-size:24px; }
  header h1 { flex:1; font-weight:700; font-size:1.5rem; user-select:none;}
  main { flex:1; display:flex; justify-content:center; padding:25px 15px;}
  form { background:#fff; padding:30px 35px; border-radius:15px; box-shadow:0 12px 25px rgba(0,0,0,0.12); max-width:600px; width:100%; }
  form h2 { text-align:center; margin-bottom:30px; color:#2c3e50; font-size:1.8rem;}
  label { display:block; margin-bottom:6px; font-weight:600; color:#34495e; }
  input, select { width:100%; padding:12px 15px; margin-bottom:15px; border:1px solid #ccc; border-radius:10px; font-size:0.95rem; transition: all 0.3s ease;}
  input:focus, select:focus { border-color:rgb(27,68,95); box-shadow:0 0 8px rgba(52,152,219,0.3); outline:none;}
  button[type="submit"] { width:100%; padding:14px; background:rgb(27,68,95); border:none; color:white; font-size:1rem; font-weight:600; border-radius:10px; cursor:pointer; transition: background-color 0.3s;}
  button[type="submit"]:hover { background:rgb(0,102,153);}
  .flex-group { display:flex; gap:10px; margin-bottom:15px;}
  .flex-group>div{flex:1;}
  @media(max-width:600px){.flex-group{flex-direction:column;}}
  .erro { color:#e74c3c; font-size:0.85rem; margin-top:-10px; margin-bottom:10px; display:block; }
</style>
</head>
<body>
<header>
  <button class="back-btn" onclick="window.location.href='estoque.php'">←</button>
  <h1>Cadastro de Produto</h1>
</header>

<main>
<form method="POST" novalidate action="cadastros/cadastro_produto.php" id="form-produto">
  <h2>Cadastro de Produto</h2>

  <label for="ID_forn">Fornecedor:</label>
  <select name="ID_forn" id="ID_forn">
      <option value="">Selecione</option>
      <?php foreach($fornecedores as $forn): ?>
          <option value="<?= $forn['ID_forn'] ?>"><?= htmlspecialchars($forn['Nome_forn']) ?></option>
      <?php endforeach; ?>
  </select>

  <label for="id_categorias">Categoria:</label>
  <select name="id_categorias" id="id_categorias">
      <option value="">Selecione</option>
      <?php foreach($categorias as $cat): ?>
          <option value="<?= $cat['id_categorias'] ?>"><?= htmlspecialchars($cat['nome_categoria']) ?></option>
      <?php endforeach; ?>
  </select>

  <label for="Nome_prod">Nome do Produto:</label>
  <input type="text" name="Nome_prod" id="Nome_prod" maxlength="60"/>

  <div class="flex-group">
    <div>
      <label for="Preco_unitario">Preço Unitário (R$):</label>
      <input type="text" name="Preco_unitario" id="Preco_unitario" placeholder="R$ 0,00"/>
    </div>
    <div>
      <label for="Unid_medida">Unidade de medida:</label>
      <select name="Unid_medida" id="Unid_medida">
          <option value="">Selecione</option>
          <option>kg</option>
          <option>g</option>
          <option>mL</option>
      </select>
    </div>
  </div>

  <div class="flex-group">
    <div>
      <label for="Validade">Validade (dd/mm/aaaa):</label>
      <input type="text" name="Validade" id="Validade" placeholder="dd/mm/aaaa"/>
    </div>
    <div>
      <label for="Qntd_produto">Quantidade em Estoque:</label>
      <input type="number" name="Qntd_produto" id="Qntd_produto" min="0"/>
    </div>
  </div>

  <button type="submit">Cadastrar Produto</button>
</form>
</main>

<script>
document.addEventListener("DOMContentLoaded", function(){
  const form = document.getElementById("form-produto");
  const campos = ["ID_forn","id_categorias","Nome_prod","Preco_unitario","Unid_medida","Validade","Qntd_produto"];
  const Preco_unitario = document.getElementById("Preco_unitario");
  const Validade = document.getElementById("Validade");
  const Qntd_produto = document.getElementById("Qntd_produto");

  function limparErros(){
    campos.forEach(id=>{
      const el=document.getElementById(id);
      if(el) el.style.borderColor="#ccc";
      const span = document.getElementById("erro-"+id);
      if(span) span.remove();
    });
  }

  function mostrarErro(el,msg){
    el.style.borderColor="#e74c3c";
    const span = document.createElement("span");
    span.className = "erro";
    span.id = "erro-"+el.id;
    span.innerText = msg;
    el.parentNode.insertBefore(span, el.nextSibling);
  }

  // Máscara data dd/mm/aaaa
  Validade.addEventListener("input", ()=>{
    let v = Validade.value.replace(/\D/g,"").slice(0,8);
    if(v.length>2) v=v.slice(0,2)+'/'+v.slice(2);
    if(v.length>5) v=v.slice(0,5)+'/'+v.slice(5);
    Validade.value=v;
  });

  // Máscara preço
  Preco_unitario.addEventListener("input", function(){
    let valor = this.value.replace(/\D/g,"");
    valor = (valor/100).toFixed(2) + "";
    valor = valor.replace(".",",");
    valor = valor.replace(/\B(?=(\d{3})+(?!\d))/g,".");
    this.value = "R$ "+valor;
  });

  form.addEventListener("submit", (e)=>{
    e.preventDefault();
    limparErros();
    let ok = true;

    campos.forEach(id=>{
      const el = document.getElementById(id);
      if(el && el.value.trim()===""){
        mostrarErro(el,"Campo obrigatório.");
        ok=false;
      }
    });

    // Preço válido
    if(Preco_unitario.value.trim()){
      let num = Preco_unitario.value.replace(/\D/g,"");
      if(isNaN(num) || Number(num)<0){
        mostrarErro(Preco_unitario,"Preço inválido.");
        ok=false;
      }
    }

    // Validade da data
    if(Validade.value.trim()){
      const parts = Validade.value.split("/");
      if(parts.length!==3){
        mostrarErro(Validade,"Data inválida.");
        ok=false;
      } else {
        const d = new Date(parts[2], parts[1]-1, parts[0]);
        if(isNaN(d.getTime())){
          mostrarErro(Validade,"Data inválida.");
          ok=false;
        }
      }
    }

    // Quantidade não negativa
    if(Qntd_produto.value && Number(Qntd_produto.value)<0){
      mostrarErro(Qntd_produto,"Quantidade não pode ser negativa.");
      ok=false;
    }

    if(ok) form.submit();
    else alert("Por favor, preencha todos os campos destacados antes de enviar.");
  });
});
</script>
</body>
</html>
