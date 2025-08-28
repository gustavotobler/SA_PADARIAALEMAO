<?php
require_once('conexao.php');

// Buscar produtos
$stmt = $pdo->prepare("SELECT * FROM produtos");
$stmt->execute();
$produtos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Selecionar itens</title>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <style>
    /* Reset básico */
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f5f2ed;
      color: #333;
    }

    a {
      text-decoration: none;
      color: inherit;
    }

    /* Layout principal */
    .container {
      display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 1rem;
      padding: 1rem;
      max-width: 1200px;
      margin: 0 auto;
    }

    /* Área de produtos */
    .product-panel {
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    .controls {
      display: flex;
      gap: .5rem;
    }

    .controls input[type="text"] {
      flex: 1;
      padding: .5rem;
      border: 1px solid #ccc;
      border-radius: 4px;
    }

    .controls select,
    .controls button {
      padding: .5rem;
      border: 1px solid #ccc;
      border-radius: 4px;
      background: white;
      cursor: pointer;
    }

    /* Mini HUD de categorias */
    .category-hud {
      display: flex;
      gap: .5rem;
      padding: .5rem 0;
      overflow-x: auto;
    }

    .category-hud button {
      padding: .5rem 1rem;
      border: 1px solid #2a9d8f;
      border-radius: 20px;
      background: white;
      color: #2a9d8f;
      cursor: pointer;
      flex-shrink: 0;
    }

    .category-hud button.active {
      background: #2a9d8f;
      color: white;
    }

    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
      gap: 1rem;
    }

    .card {
      background: white;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      display: flex;
      flex-direction: column;
    }

    .card img {
      width: 100%;
      height: 100px;
      object-fit: cover;
    }

    .card .info {
      padding: .5rem;
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .card .info h4 {
      font-size: .9rem;
      margin-bottom: .5rem;
    }

    .card .info .price {
      font-weight: bold;
      color: #2a9d8f;
    }

    /* Sidebar do carrinho */
    /* Botão de toggle centralizado verticalmente */
    .sidebar {
      width: 240px;
      background: var(--sidebar-bg);
      height: 100vh;
      position: fixed;
      display: flex;
      flex-direction: column;
      padding-top: 10px;
      /* reduz o espaço do topo */
      transition: width 0.3s;
    }

    .toggle-btn {
      cursor: pointer;
      text-align: center;
      font-size: 20px;
      /* mantém o tamanho do ícone */
      color: var(--primary-text);
      margin-bottom: 20px;
      /* espaço entre toggle e link Voltar */
      height: 0px;
      /* altura do botão mais compacta */
      display: flex;
      justify-content: center;
      align-items: center;
      line-height: 1;
    }




    .sidebar .toggle-btn {
      padding: 15px 20px;
      /* mesmo padding dos links */
      font-size: 20px;
      color: #fff;
      text-align: left;
    }

    .sidebar.collapsed {
      width: 60px;
    }

    .sidebar.collapsed .text {
      display: none;
    }

    .cart-items {
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: .75rem;
      margin-bottom: 1rem;
    }

    .cart-item {
      display: flex;
      justify-content: space-between;
    }

    .cart-item span:first-child {
      flex: 1;
    }

    .totals {
      border-top: 1px solid #eee;
      padding-top: 1rem;
      margin-bottom: 1rem;
    }

    .totals div {
      display: flex;
      justify-content: space-between;
      margin-bottom: .5rem;
    }

    .btn-pay {
      display: block;
      width: 100%;
      padding: .75rem;
      background: #2a9d8f;
      color: white;
      text-align: center;
      border-radius: 4px;
      cursor: pointer;
    }

    .cart-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .cart-item .item-info {
      display: flex;
      align-items: center;
      gap: 4px;
      flex-wrap: nowrap;
    }

    /* Botões de quantidade */
    .qty-btn {
      flex: none;
      background: none;
      border: 1px solid #2a9d8f;
      border-radius: 4px;
      width: 24px;
      height: 24px;
      cursor: pointer;
      font-size: 1rem;
      line-height: 1;
    }

    .qty {
      flex: none;
      width: 36px;
      /* largura suficiente para até 3 dígitos */
      text-align: center;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .item-subtotal {
      font-weight: bold;
    }

    .card .info {
      display: flex;
      flex-direction: column;
      gap: .5rem;
    }

    .add-to-cart {
      background: #2a9d8f;
      color: #fff;
      border: none;
      border-radius: 4px;
      padding: .25rem .5rem;
      cursor: pointer;
      align-self: flex-end;
    }

    .add-to-cart:hover {
      background: #237c6f;
    }

    /* empilha título + rodapé */
    .card .info {
      display: flex;
      flex-direction: column;
      gap: .5rem;
    }

    /* alinha preço à esquerda e o + à direita */
    .card .info-footer {
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    /* estilo do botão + */
    .card .info-footer .add-to-cart {
      background: #2a9d8f;
      color: #fff;
      border: none;
      padding: .25rem .5rem;
      border-radius: 4px;
      cursor: pointer;
    }

    .shortcut-hud {
      position: fixed;
      bottom: 10px;
      right: 10px;
      background: rgba(0, 0, 0, 0.7);
      color: white;
      padding: .5rem 1rem;
      border-radius: 8px;
      font-size: .9rem;
      display: flex;
      gap: 1rem;
      z-index: 1000;
    }

    .shortcut-hud kbd {
      background: #333;
      color: #fff;
      padding: 2px 6px;
      border-radius: 4px;
      font-size: .9rem;
    }

    .controls {
      display: flex;
      align-items: center;
      gap: .5rem;
    }

    .controls .back-button {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: .5rem;
      border-radius: 4px;
      cursor: pointer;
      text-decoration: none;
    }

    .hidden {
      display: none;
    }

    #scan-modal .modal-backdrop {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      z-index: 1000;
    }

    #scan-modal .modal-content {
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background: #fff;
      padding: 1.5rem;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
      z-index: 1001;
      width: 300px;
      text-align: center;
    }

    #scan-modal input {
      width: 100%;
      padding: .5rem;
      margin: 1rem 0;
      border: 1px solid #ccc;
      border-radius: 4px;
    }

    #scan-modal button {
      padding: .5rem 1rem;
      border: none;
      background: #2a9d8f;
      color: #fff;
      border-radius: 4px;
      cursor: pointer;
    }

    /* Sidebar fixa estilo do relatório */
    .sidebar {
      width: 240px;
      background: #2e2e2e;
      height: 100vh;
      position: fixed;
      display: flex;
      flex-direction: column;
      padding-top: 20px;
      transition: width 0.3s;
      border-radius: 0;
    }

    .sidebar.collapsed {
      width: 60px;
    }

    .sidebar a {
      color: #fff;
      padding: 15px 20px;
      text-decoration: none;
      display: flex;
      align-items: center;
      white-space: nowrap;
    }

    .sidebar a:hover {
      background: #444;
    }

    .sidebar .icon {
      margin-right: 8px;
      font-size: 20px;
      line-height: 1;
      display: flex;
      align-items: center;
    }

    .sidebar.collapsed .text {
      display: none;
    }

    .toggle-btn {
      cursor: pointer;
      text-align: center;
      margin-bottom: 20px;
      font-size: 20px;
      color: #fff;
    }

    /* Ajusta o conteúdo para a sidebar fixa */
    .main-content {
      margin-left: 240px;
      padding: 20px;
      width: calc(100% - 240px);
      transition: margin-left 0.3s;
    }

    .main-content.collapsed {
      margin-left: 60px;
      width: calc(100% - 60px);
    }


    /* Esconde apenas o texto quando colapsada */
    .sidebar.collapsed .text {
      display: none;
    }

    /* Ajusta emojis quando a sidebar está colapsada */
    .sidebar .emoji {
      margin-right: 8px;
      display: inline-block;
      width: 20px;
      text-align: center;
    }

    .sidebar.collapsed .emoji {
      margin-right: 0;
      width: 100%;
    }

    .back-link {
      display: flex;
      align-items: center;
      transition: all 0.3s;
      /* suaviza posição e rotação */
    }

    .back-link .icon {
      transition: transform 0.3s;
      margin-right: 8px;
    }

    /* Quando a sidebar estiver colapsada */
    .sidebar.collapsed .back-link {
      justify-content: center;
      /* centraliza horizontalmente */
    }

    .sidebar.collapsed .back-link .icon {
      margin-right: 0;
      transform: rotate(180deg);
      /* rotação da seta */
    }
  </style>
</head>

<body>
  <!-- Sidebar fixa lateral -->
  <nav class="sidebar" id="sidebar">
    <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
    <a href="inicial1.php" class="back-link">
      <span class="material-icons icon">arrow_back</span>
      <span class="text">Voltar</span>
    </a>

    <a href="#" onclick="showSection('tabela')">
      <span class="emoji">🍞</span>
      <span class="text">Produtos</span>
    </a>

    <a href="#" onclick="showSection('comanda.php')">
      <span class="emoji">🧾</span>
      <span class="text">Comanda</span>
    </a>

    <a href="#" onclick="showSection('pagamento.php')">
      <span class="emoji">💳</span>
      <span class="text">Pagamento</span>
    </a>
  </nav>


  <!-- Conteúdo principal -->
  <main class="main-content" id="mainContent">
    <div class="container">
      <!-- Painel de Produtos -->
      <section class="product-panel">
        <div class="controls">
          <input type="text" placeholder="Nome ou código">
        </div>

        <!-- HUD de categorias -->
        <div class="category-hud">
          <button data-cat="all" class="active">Todas</button>
          <button data-cat="cafe">Cafés</button>
          <button data-cat="sucos">Sucos</button>
          <button data-cat="paes">Pães</button>
          <button data-cat="bolos">Bolos</button>
          <button data-cat="salgados">Salgados</button>
        </div>

        <!-- Lista de produtos -->
        <div class="grid">
          <?php if ($produtos): ?>
            <?php foreach ($produtos as $row): ?>
              <div class="card">
                <div class="info">
                  <h4><?= htmlspecialchars($row['Nome_prod']) ?></h4>
                  <div class="info-footer">
                    <span class="price">R$ <?= number_format($row['Preco'], 2, ',', '.') ?></span>
                    <button class="add-to-cart" data-name="<?= htmlspecialchars($row['Nome_prod']) ?>"
                      data-price="<?= htmlspecialchars($row['Preco']) ?>" <?= !empty($row['Unid_medida']) ? 'data-unit="' . htmlspecialchars($row['Unid_medida']) . '"' : '' ?>>
                      +
                    </button>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p>Nenhum produto cadastrado.</p>
          <?php endif; ?>
        </div>
      </section>

      <!-- Sidebar do carrinho (fica na direita por causa do grid) -->
      <aside class="cart-sidebar">
        <h3>Carrinho</h3>
        <div class="cart-items"></div>
        <div class="totals">
          <div><span>Subtotal:</span><span>R$ 0,00</span></div>
          <div><span>Taxa balcão:</span><span>R$ 0,00</span></div>
        </div>
        <a href="pagamento.php">
          <div class="btn-pay" id="btnPay">Ir para pagamento → (Total: R$ 0,00)</div>
        </a>
      </aside>
    </div>

    <script>
      // 1) Captura global de F1/F2
      window.addEventListener('keydown', function (e) {
        if (e.key === 'F1') {
          e.preventDefault();
          e.stopImmediatePropagation();
          document.getElementById('scan-modal').classList.remove('hidden');
          document.getElementById('scan-input').focus();
        }
        if (e.key === 'F2') {
          e.preventDefault();
          e.stopImmediatePropagation();
          window.location.href = 'pagamento.php';
        }
      }, true);

      document.addEventListener("DOMContentLoaded", () => {
        // ELEMENTOS
        const searchInput = document.querySelector('.controls input[type="text"]');
        const cards = document.querySelectorAll('.grid .card');
        const hudButtons = document.querySelectorAll('.category-hud button');
        const cartItemsContainer = document.querySelector('.cart-items');
        const totalsSubtotalEl = document.querySelector('.totals div:first-child span:last-child');
        const totalsTaxEl = document.querySelector('.totals div:nth-child(2) span:last-child');
        const btnPay = document.querySelector('.btn-pay');

        // FORMATAÇÃO DE MOEDA
        function formatReal(v) {
          return 'R$ ' + v.toFixed(2).replace('.', ',');
        }

        // 3) ATUALIZA TODOS OS TOTAIS (agora suporta decimal)
        function updateTotals() {
          let sub = 0;
          cartItemsContainer.querySelectorAll('.cart-item').forEach(item => {
            const price = parseFloat(item.dataset.price);
            const qty = parseFloat(item.querySelector('.qty').textContent);
            const itemSub = price * qty;
            item.querySelector('.item-subtotal').textContent = formatReal(itemSub);
            sub += itemSub;
          });
          totalsSubtotalEl.textContent = formatReal(sub);

          const tax = parseFloat(totalsTaxEl.dataset.tax || 0);
          totalsTaxEl.textContent = formatReal(tax);

          btnPay.textContent = `Ir para pagamento → (Total: ${formatReal(sub + tax)})`;
        }

        // 1) FILTRO DE BUSCA
        searchInput.addEventListener('input', () => {
          const term = searchInput.value.toLowerCase();
          const activeCat = document.querySelector('.category-hud button.active').dataset.cat;
          cards.forEach(card => {
            const title = card.querySelector('h4').textContent.toLowerCase();
            const match = title.includes(term) && (activeCat === 'all' || card.dataset.category === activeCat);
            card.style.display = match ? '' : 'none';
          });
        });

        // 2) FILTRO DE CATEGORIA
        hudButtons.forEach(btn => {
          btn.addEventListener('click', () => {
            hudButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            searchInput.dispatchEvent(new Event('input'));
          });
        });

        // 4) BOTÃO “＋” NOS CARDS (pede peso para produtos por kg)
        document.querySelectorAll('.add-to-cart').forEach(btn => {
          btn.addEventListener('click', () => {
            const name = btn.dataset.name;
            const price = parseFloat(btn.dataset.price);
            let qty = 1;

            if (btn.dataset.unit === 'kg') {
              const input = prompt('Digite o peso em kg (ex: 0.250 para 250 g):');
              if (!input) return;
              qty = parseFloat(input.replace(',', '.'));
              if (isNaN(qty) || qty <= 0) {
                alert('Peso inválido!');
                return;
              }
            }

            let existing = Array.from(cartItemsContainer.children)
              .find(el => el.querySelector('.item-name').textContent === name);

            if (existing) {
              const qtyEl = existing.querySelector('.qty');
              const newQty = parseFloat(qtyEl.textContent) + qty;
              qtyEl.textContent = newQty.toFixed(2);
            } else {
              const div = document.createElement('div');
              div.className = 'cart-item';
              div.dataset.price = price;
              div.innerHTML = `
                <div class="item-info">
                  <button class="qty-btn decrease">−</button>
                  <span class="qty">${qty.toFixed(2)}</span>
                  <button class="qty-btn increase">＋</button>
                  <span class="item-name">${name}</span>
                </div>
                <span class="item-subtotal">${formatReal(price * qty)}</span>
              `;
              cartItemsContainer.appendChild(div);
            }

            updateTotals();
          });
        });

        // 5) CONTROLE DE +/− NA SIDEBAR
        cartItemsContainer.addEventListener('click', e => {
          const itemEl = e.target.closest('.cart-item');
          if (!itemEl) return;

          if (e.target.matches('.increase')) {
            const qtyEl = itemEl.querySelector('.qty');
            const newQty = parseFloat(qtyEl.textContent) + 1;
            qtyEl.textContent = newQty.toFixed(2);
            updateTotals();
          }
          if (e.target.matches('.decrease')) {
            const qtyEl = itemEl.querySelector('.qty');
            const newQty = parseFloat(qtyEl.textContent) - 1;
            if (newQty <= 0) itemEl.remove();
            else qtyEl.textContent = newQty.toFixed(2);
            updateTotals();
          }
        });

        // 6) INSERIR PRODUTO POR CÓDIGO ESCANEADO
        document.getElementById('scan-input').addEventListener('keydown', (e) => {
          if (e.key === 'Enter') {
            const code = e.target.value.trim();

            // Código para "Doritos"
            if (code === '1234') {
              const name = 'Doritos';
              const price = 7.50;
              const qty = 1;

              let existing = Array.from(cartItemsContainer.children)
                .find(el => el.querySelector('.item-name').textContent === name);

              if (existing) {
                const qtyEl = existing.querySelector('.qty');
                const newQty = parseFloat(qtyEl.textContent) + qty;
                qtyEl.textContent = newQty.toFixed(2);
              } else {
                const div = document.createElement('div');
                div.className = 'cart-item';
                div.dataset.price = price;
                div.innerHTML = `
                  <div class="item-info">
                    <button class="qty-btn decrease">−</button>
                    <span class="qty">${qty.toFixed(2)}</span>
                    <button class="qty-btn increase">＋</button>
                    <span class="item-name">${name}</span>
                  </div>
                  <span class="item-subtotal">${formatReal(price * qty)}</span>
                `;
                cartItemsContainer.appendChild(div);
              }

              updateTotals();
            }

            // Limpa o campo para novo código
            e.target.value = '';
          }
        });

        // Fecha modal “Cancelar”
        document.getElementById('close-scan').addEventListener('click', () => {
          document.getElementById('scan-modal').classList.add('hidden');
        });

        // 7) INICIALIZAÇÃO
        // Enviar pedido para o banco
        document.getElementById("btnPay").addEventListener("click", () => {
          let itens = [];
          document.querySelectorAll('.cart-item').forEach(item => {
            itens.push({
              nome: item.querySelector('.item-name').textContent,
              qtd: item.querySelector('.qty').textContent,
              preco: item.dataset.price
            });
          });

          if (itens.length === 0) {
            alert("O carrinho está vazio!");
            return;
          }

          let total = parseFloat(totalsSubtotalEl.textContent.replace("R$", "").replace(",", "."));

          fetch("pagamento.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "itens=" + JSON.stringify(itens) + "&total=" + total
          })
            .then(res => res.text())
            .then(data => {
              alert(data);
              window.location.href = "pagamento.php";
            })
            .catch(err => console.error(err));
        });
        updateTotals();
      });
    </script>

    <script>
      const sidebar = document.getElementById('sidebar');
      const mainContent = document.getElementById('mainContent');
      function toggleSidebar() {
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('collapsed');
      }
    </script>

</body>

</html>