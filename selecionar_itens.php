<?php
require_once('conexao.php');

// Buscar produtos
$stmt = $pdo->prepare("
    SELECT p.*, c.nome_categoria
    FROM produtos p
    JOIN categorias c ON p.id_categorias = c.id_categorias
");
$stmt->execute();
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Selecionar itens</title>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <style>
  /* Reset básico */
  * { margin: 0; padding: 0; box-sizing: border-box; }

  :root {
    --sidebar-bg: #2e2e2e;
    --primary-text: #fff;
    --hover-bg: #444;
    --accent: #2a9d8f;
  }

  body {
    font-family: 'Segoe UI', sans-serif;
    background: #f5f2ed;
    color: #333;
  }

  a { text-decoration: none; color: inherit; }

  /* Layout principal */
  .container{
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 1rem;
    padding: 1rem;
    max-width: 1200px;
    margin: 0 auto;
  }

  .main-content{
    margin-left: 240px; /* mesma largura da sidebar */
    transition: margin-left 0.3s;
  }

  /* Sidebar (consolidado) */
  .sidebar{
    width: 240px;
    background: var(--sidebar-bg);
    height: 100vh;
    position: fixed;
    display: flex;
    flex-direction: column;
    padding-top: 20px;
    transition: width 0.3s;
  }

  .sidebar.collapsed{ width: 60px; }

  .sidebar a{
    display: flex;
    align-items: center;
    color: var(--primary-text);
    text-decoration: none;
    padding: 15px 20px;
    white-space: nowrap;
  }

  .sidebar a:hover{ background: var(--hover-bg); }

  .sidebar .icon{ margin-right: 8px; font-size: 20px; display: flex; align-items: center; }
  .sidebar .emoji{ margin-right: 8px; display: inline-block; width: 20px; text-align: center; }
  .sidebar.collapsed .emoji{ margin-right: 0; width: 100%; }

  .sidebar.collapsed .text{ display: none; }
  .sidebar.collapsed .icon{ margin-right: 0; justify-content: center; }

  .sidebar .back-link{ display: flex; align-items: center; transition: all 0.3s ease; }
  .sidebar .back-link .icon{ font-size: 24px; display:flex; align-items:center; transition: transform 0.3s ease, margin 0.3s ease; margin-right: 8px; }
  .sidebar.collapsed .back-link{ justify-content: center; }
  .sidebar.collapsed .back-link .icon{ margin-right: 0; transform: rotate(180deg); }

  /* Toggle button (consolidado) */
  .toggle-btn{
    cursor: pointer;
    text-align: center;
    margin-bottom: 20px;
    font-size: 20px;
    color: var(--primary-text);
    padding: 15px 20px;
    display: flex;
    justify-content: center;
    align-items: center;
    line-height: 1;
  }

  .sidebar.collapsed ~ .main-content{ margin-left: 60px; }

  /* Área de produtos */
  .product-panel{ display: flex; flex-direction: column; gap: 1rem; }

  .controls{
    display: flex;
    align-items: center;
    gap: .5rem;
  }

  .controls input[type="text"]{
    flex: 1;
    padding: .5rem;
    border: 1px solid #ccc;
    border-radius: 4px;
  }

  .controls select, .controls button{
    padding: .5rem;
    border: 1px solid #ccc;
    border-radius: 4px;
    background: white;
    cursor: pointer;
  }

  .controls .back-button{
    display: flex;
    align-items: center;
    justify-content: center;
    padding: .5rem;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
  }

  /* Mini HUD de categorias */
  .category-hud{
    display:flex;
    gap:.5rem;
    padding:.5rem 0;
    overflow-x:auto;
  }

  .category-hud button{
    padding:.5rem 1rem;
    border:1px solid var(--accent);
    border-radius:20px;
    background:white;
    color:var(--accent);
    cursor:pointer;
    flex-shrink:0;
  }

  .category-hud button.active{ background:var(--accent); color:#fff; }

  /* Grid de produtos */
  .grid{
    display:grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap:1rem;
  }

  .card{
    background:white;
    border-radius:8px;
    overflow:hidden;
    box-shadow:0 2px 4px rgba(0,0,0,0.1);
    display:flex;
    flex-direction:column;
  }

  .card img{
    width:100%;
    height:100px;
    object-fit:cover;
  }

  /* Informações dentro do card (consolidado) */
  .card .info{
    padding:.5rem;
    flex:1;
    display:flex;
    flex-direction:column;
    gap:.5rem;
  }

  .card .info h4{ font-size:.9rem; margin-bottom:.25rem; }
  .card .info .price{ font-weight:bold; color:var(--accent); }

  .card .info-footer{
    display:flex;
    align-items:center;
    justify-content:space-between;
  }

  .add-to-cart{
    background:var(--accent);
    color:#fff;
    border:none;
    border-radius:4px;
    padding:.25rem .5rem;
    cursor:pointer;
    align-self:flex-end;
  }

  .add-to-cart:hover{ background:#237c6f; }

  .card .info-footer .add-to-cart{ padding:.25rem .5rem; }

  /* Sidebar do carrinho (painel direito) */
  .cart-sidebar{
    padding:1rem;
    background:#fff;
    border-radius:8px;
    box-shadow:0 2px 6px rgba(0,0,0,0.06);
    max-height: calc(100vh - 40px);
    overflow:auto;
  }

  .cart-sidebar h3{ margin-bottom:.75rem; }

  .cart-items{
    display:flex;
    flex-direction:column;
    gap:.75rem;
    margin-bottom:1rem;
  }

  .cart-item{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:.5rem;
  }

  .cart-item .item-info{
    display:flex;
    align-items:center;
    gap:6px;
    flex:1;
    min-width:0;
  }

  .item-name{
    min-width:0;
    overflow:hidden;
    text-overflow:ellipsis;
    white-space:nowrap;
    display:inline-block;
  }

  /* Botões de quantidade */
  .qty-btn{
    background:none;
    border:1px solid var(--accent);
    border-radius:4px;
    width:28px;
    height:28px;
    cursor:pointer;
    font-size:1rem;
    line-height:1;
  }

  .qty{
    width:40px;
    text-align:center;
    display:inline-block;
  }

  .item-subtotal{ font-weight:bold; margin-left:8px; white-space:nowrap; }

  /* Totais e pagamento */
  .totals{
    border-top:1px solid #eee;
    padding-top:1rem;
    margin-bottom:1rem;
  }

  .totals div{ display:flex; justify-content:space-between; margin-bottom:.5rem; }

  .btn-pay{
    display:block;
    width:100%;
    padding:.75rem;
    background:var(--accent);
    color:white;
    text-align:center;
    border-radius:4px;
    cursor:pointer;
    border:none;
  }

  /* Modal (consolidado) */
  .hidden{ display:none; }

  .modal-backdrop{
    position:fixed;
    inset:0;
    background: rgba(0,0,0,0.5);
    z-index:1000;
  }

  .modal{
    position:fixed;
    top:50%;
    left:50%;
    transform:translate(-50%,-50%);
    background:#fff;
    padding:1rem;
    border-radius:8px;
    z-index:1001;
    width:360px;
    max-width:95%;
    box-shadow:0 8px 30px rgba(0,0,0,0.25);
    text-align:left;
  }

  #scan-modal .modal-content{ width:300px; text-align:center; }
  #scan-modal input{
    width:100%; padding:.5rem; margin:1rem 0; border:1px solid #ccc; border-radius:4px;
  }
  #scan-modal button{
    padding:.5rem 1rem; border:none; background:var(--accent); color:#fff; border-radius:4px; cursor:pointer;
  }

  .modal h3{ margin-bottom:.5rem; }
  .modal .modal-body{ max-height:60vh; overflow:auto; margin-bottom:.75rem; }
  .modal .modal-footer{ display:flex; gap:.5rem; justify-content:flex-end; }

  .btn-secondary{
    padding:.5rem .75rem; border-radius:6px; border:1px solid #ccc; background:white; cursor:pointer;
  }
  .btn-primary{
    padding:.5rem .75rem; border-radius:6px; border:none; background:var(--accent); color:white; cursor:pointer;
  }

  /* HUD de atalhos */
  .shortcut-hud{
    position:fixed;
    bottom:10px;
    right:10px;
    background:rgba(0,0,0,0.7);
    color:white;
    padding:.5rem 1rem;
    border-radius:8px;
    font-size:.9rem;
    display:flex;
    gap:1rem;
    z-index:1000;
  }

  .shortcut-hud kbd{
    background:#333;
    color:#fff;
    padding:2px 6px;
    border-radius:4px;
    font-size:.9rem;
  }

  /* pequenas helpers */
  .item-name{ min-width:0; }

</style>
</head>

<body>
  <!-- Sidebar fixa lateral -->
  <nav class="sidebar" id="sidebar"
    style="--sidebar-bg:#2e2e2e; color:#fff; position:fixed; height:100vh; width:240px; padding-top:20px;">
    <div class="toggle-btn" onclick="toggleSidebar()">☰</div>
    <a href="inicial1.php" class="back-link"
      style="display:flex; align-items:center; padding:12px 16px; color:inherit;">
      <span class="material-icons icon">arrow_back</span>
      <span class="text">Voltar</span>
    </a>

    <a href="#" onclick="showSection('tabela')"
      style="display:flex; align-items:center; padding:12px 16px; color:inherit;">
      <span class="emoji">🍞</span>
      <span class="text">Produtos</span>
    </a>

    <a href="#" onclick="showSection('comanda.php')"
      style="display:flex; align-items:center; padding:12px 16px; color:inherit;">
      <span class="emoji">🧾</span>
      <span class="text">Comanda</span>
    </a>
  </nav>

  <!-- Conteúdo principal -->
  <main class="main-content" id="mainContent" style="margin-left:240px; padding:18px;">
    <div class="container">
      <!-- Painel de Produtos -->
      <section class="product-panel">
        <div class="controls">
          <input type="text" placeholder="Nome ou código">
        </div>

        <!-- HUD de categorias -->
        <div class="category-hud">
          <button data-cat="all" class="active">Todas</button>
          <button data-cat="Café">Cafés</button>
          <button data-cat="Sucos">Sucos</button>
          <button data-cat="bebidas">Bebidas</button>
          <button data-cat="Pães">Pães</button>
          <button data-cat="Bolos">Bolos</button>
          <button data-cat="Salgados">Salgados</button>
          <button data-cat="Laticínios">Laticínios</button>
        </div>

        <!-- Lista de produtos -->
        <div class="grid">
          <?php if ($produtos): ?>
            <?php foreach ($produtos as $row): ?>
              <div class="card" data-category="<?= htmlspecialchars($row['nome_categoria']) ?>">
                <div class="info">
                  <h4><?= htmlspecialchars($row['Nome_prod']) ?></h4>
                  <div class="info-footer">
                    <span class="price">R$ <?= number_format($row['Preco_unitario'], 2, ',', '.') ?></span>
                    <button class="add-to-cart" data-name="<?= htmlspecialchars($row['Nome_prod']) ?>"
                      data-price="<?= htmlspecialchars($row['Preco_unitario']) ?>" <?= !empty($row['Unid_medida']) ? 'data-unit="' . htmlspecialchars($row['Unid_medida']) . '"' : '' ?>>
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

      <!-- Sidebar do carrinho -->
      <aside class="cart-sidebar" style="width:320px;">
        <h3>Carrinho</h3>
        <div class="cart-items" id="cart-items"></div>

        <div class="totals">
          <div><span>Subtotal:</span><span id="subtotal-display">R$ 0,00</span></div>
          <div><span>Taxa balcão:</span><span id="tax-display" data-tax="0">R$ 0,00</span></div>
        </div>

        <button class="btn-pay" id="open-payment-modal">Ir para Pagamento</button>
      </aside>
    </div>
  </main>

  <!-- Modal de Pagamento -->
  <div id="payment-modal-backdrop" class="hidden modal-backdrop"></div>
  <div id="payment-modal" class="hidden modal" role="dialog" aria-modal="true" aria-labelledby="payment-title">
    <h3 id="payment-title">Finalizar Pagamento</h3>
    <div class="modal-body">
      <div id="modal-items-list">
        <!-- itens serão renderizados aqui -->
      </div>

      <div style="margin-top:8px;">
        <div style="display:flex; justify-content:space-between; margin-bottom:6px;"><strong>Subtotal:</strong><span
            id="modal-subtotal">R$ 0,00</span></div>
        <div style="display:flex; justify-content:space-between; margin-bottom:6px;"><strong>Taxa:</strong><span
            id="modal-tax">R$ 0,00</span></div>
        <div style="display:flex; justify-content:space-between; font-size:1.1rem; margin-top:8px;">
          <strong>Total:</strong><span id="modal-total">R$ 0,00</span></div>
      </div>

      <div style="margin-top:10px;">
        <label for="payment-method">Meio de pagamento</label>
        <select id="payment-method" style="width:100%; padding:.5rem; margin-top:6px;">
          <option value="dinheiro">Dinheiro</option>
          <option value="cartao">Cartão</option>
          <option value="pix">PIX</option>
        </select>
      </div>

      <div id="payment-message" style="margin-top:10px; color:green; display:none;"></div>
    </div>

    <div class="modal-footer">
      <button class="btn-secondary" id="close-payment-modal">Cancelar</button>
      <button class="btn-primary" id="confirm-payment">Confirmar pagamento</button>
    </div>
  </div>

  <!-- (Opcional) Modal de Scan — CSS existe; se quiser habilitar inclua marcação similar -->
  <!-- Observação: este markup de scan-modal não estava no original — mantive referências JS assumindo que exista -->
  <div id="scan-modal" class="hidden">
    <div class="modal-backdrop"></div>
    <div class="modal-content">
      <h3>Scanner</h3>
      <input id="scan-input" placeholder="Escaneie ou digite o código" />
      <div style="display:flex; gap:.5rem; justify-content:center;">
        <button id="close-scan">Fechar</button>
      </div>
    </div>
  </div>

  <script>
    // ---------- UTILITÁRIOS ----------
    function formatReal(v) {
      return 'R$ ' + v.toFixed(2).replace('.', ',');
    }
    function parseRealString(str) {
      // Recebe strings tipo "R$ 1.234,56" ou "1.234,56"
      if (!str) return 0;
      let s = String(str).replace('R$', '').trim();
      // remove pontos de milhar
      s = s.replace(/\./g, '').replace(',', '.');
      const n = parseFloat(s);
      return isNaN(n) ? 0 : n;
    }

    // ---------- ESTADO DO CARRINHO ----------
    const cart = []; // array de objetos {name, price, qty}

    // Elements
    const cartItemsContainer = document.getElementById('cart-items');
    const subtotalDisplay = document.getElementById('subtotal-display');
    const taxDisplay = document.getElementById('tax-display');
    const openPaymentBtn = document.getElementById('open-payment-modal');

    // Taxa fixa — você pode ajustar dinamicamente
    const TAX_VALUE = parseFloat(taxDisplay.dataset.tax || '0');

    // Atualiza visual do carrinho e totals
    function renderCart() {
      cartItemsContainer.innerHTML = '';
      let sub = 0;
      cart.forEach((it, idx) => {
        const itemEl = document.createElement('div');
        itemEl.className = 'cart-item';
        itemEl.dataset.index = idx;
        itemEl.innerHTML = `
          <div class="item-info">
            <button class="qty-btn decrease">−</button>
            <span class="qty">${(+it.qty).toFixed(2)}</span>
            <button class="qty-btn increase">＋</button>
            <span class="item-name" title="${it.name}">${it.name}</span>
          </div>
          <span class="item-subtotal">${formatReal(it.price * it.qty)}</span>
        `;
        cartItemsContainer.appendChild(itemEl);
        sub += it.price * it.qty;
      });

      subtotalDisplay.textContent = formatReal(sub);
      taxDisplay.textContent = formatReal(TAX_VALUE);
      openPaymentBtn.textContent = `Ir para pagamento → (Total: ${formatReal(sub + TAX_VALUE)})`;
    }

    // Adiciona item (ou incrementa)
    function addToCart(name, price, qty = 1) {
      // procura pelo mesmo nome
      const existing = cart.find(i => i.name === name);
      if (existing) {
        existing.qty = +(existing.qty + qty).toFixed(2);
      } else {
        cart.push({ name, price: +price, qty: +qty });
      }
      renderCart();
    }

    // Remove item por índice
    function removeCartIndex(index) {
      cart.splice(index, 1);
      renderCart();
    }

    // ---------- INTERAÇÕES PRINCIPAIS ----------
    document.addEventListener('DOMContentLoaded', () => {
      // Elements iniciais
      const searchInput = document.querySelector('.controls input[type="text"]');
      const cards = document.querySelectorAll('.grid .card');
      const hudButtons = document.querySelectorAll('.category-hud button');

      // Filtro busca
      searchInput.addEventListener('input', () => {
        const term = searchInput.value.toLowerCase();
        const activeCat = document.querySelector('.category-hud button.active').dataset.cat;
        cards.forEach(card => {
          const title = card.querySelector('h4').textContent.toLowerCase();
          const match = title.includes(term) && (activeCat === 'all' || card.dataset.category === activeCat);
          card.style.display = match ? '' : 'none';
        });
      });

      // Filtro categoria
      hudButtons.forEach(btn => {
        btn.addEventListener('click', () => {
          hudButtons.forEach(b => b.classList.remove('active'));
          btn.classList.add('active');
          searchInput.dispatchEvent(new Event('input'));
        });
      });

      // Botões + nos cards
      document.querySelectorAll('.add-to-cart').forEach(btn => {
        btn.addEventListener('click', () => {
          const name = btn.dataset.name;
          const price = parseFloat(btn.dataset.price);
          let qty = 1;

          if (btn.dataset.unit === 'kg') {
            const input = prompt('Digite o peso em kg (ex: 0.250 para 250 g):');
            if (!input) return;
            qty = parseFloat(input.replace(',', '.'));
            if (isNaN(qty) || qty <= 0) { alert('Peso inválido!'); return; }
          }
          addToCart(name, price, qty);
        });
      });

      // Controle + / - clicando no container do carrinho
      cartItemsContainer.addEventListener('click', (e) => {
        const itemEl = e.target.closest('.cart-item');
        if (!itemEl) return;
        const idx = parseInt(itemEl.dataset.index, 10);
        if (e.target.matches('.increase')) {
          cart[idx].qty = +(cart[idx].qty + 1).toFixed(2);
          renderCart();
        } else if (e.target.matches('.decrease')) {
          cart[idx].qty = +(cart[idx].qty - 1).toFixed(2);
          if (cart[idx].qty <= 0.0001) removeCartIndex(idx);
          else renderCart();
        }
      });

      // Scan input (se existir)
      const scanInput = document.getElementById('scan-input');
      if (scanInput) {
        scanInput.addEventListener('keydown', (e) => {
          if (e.key === 'Enter') {
            const code = e.target.value.trim();
            if (code === '1234') {
              addToCart('Doritos', 7.50, 1);
            }
            e.target.value = '';
          }
        });

        document.getElementById('close-scan')?.addEventListener('click', () => {
          document.getElementById('scan-modal').classList.add('hidden');
        });
      }

      // Atualiza inicial
      renderCart();

      // --- EVENTOS DO MODAL DE PAGAMENTO ---
      const paymentModal = document.getElementById('payment-modal');
      const paymentBackdrop = document.getElementById('payment-modal-backdrop');
      const openPaymentModalBtn = document.getElementById('open-payment-modal');
      const closePaymentModalBtn = document.getElementById('close-payment-modal');
      const confirmPaymentBtn = document.getElementById('confirm-payment');
      const modalItemsList = document.getElementById('modal-items-list');
      const modalSubtotal = document.getElementById('modal-subtotal');
      const modalTax = document.getElementById('modal-tax');
      const modalTotal = document.getElementById('modal-total');
      const paymentMessage = document.getElementById('payment-message');
      const paymentMethodEl = document.getElementById('payment-method');

      function openPaymentModal() {
        if (cart.length === 0) {
          alert('O carrinho está vazio!');
          return;
        }

        // monta a lista
        modalItemsList.innerHTML = '';
        let sub = 0;
        cart.forEach(i => {
          const row = document.createElement('div');
          row.style.display = 'flex';
          row.style.justifyContent = 'space-between';
          row.style.marginBottom = '6px';
          row.innerHTML = `<span style="min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${i.name} × ${(+i.qty).toFixed(2)}</span><span>${formatReal(i.price * i.qty)}</span>`;
          modalItemsList.appendChild(row);
          sub += i.price * i.qty;
        });

        modalSubtotal.textContent = formatReal(sub);
        modalTax.textContent = formatReal(TAX_VALUE);
        modalTotal.textContent = formatReal(sub + TAX_VALUE);

        paymentMessage.style.display = 'none';
        paymentMessage.textContent = '';

        paymentBackdrop.classList.remove('hidden');
        paymentModal.classList.remove('hidden');
      }

      function closePaymentModal() {
        paymentBackdrop.classList.add('hidden');
        paymentModal.classList.add('hidden');
      }

      openPaymentModalBtn.addEventListener('click', openPaymentModal);
      closePaymentModalBtn.addEventListener('click', closePaymentModal);
      paymentBackdrop.addEventListener('click', closePaymentModal);

      // Confirmar pagamento -> envia para pagamento.php via POST (fetch)
      confirmPaymentBtn.addEventListener('click', () => {
        if (cart.length === 0) { alert('Carrinho vazio'); return; }

        // prepara dados
        const itens = cart.map(it => ({ nome: it.name, qtd: it.qty, preco: it.price }));
        const subtotal = parseRealString(modalSubtotal.textContent);
        const total = parseRealString(modalTotal.textContent);
        const metodo = paymentMethodEl.value;

        // bloqueia botão
        confirmPaymentBtn.disabled = true;
        confirmPaymentBtn.textContent = 'Enviando...';

        // envia para pagamento.php (espera texto/JSON de retorno)
        fetch('pagamento.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json; charset=utf-8' },
          body: JSON.stringify({ itens, subtotal, total, metodo })
        }).then(async res => {
          confirmPaymentBtn.disabled = false;
          confirmPaymentBtn.textContent = 'Confirmar pagamento';
          if (!res.ok) {
            const txt = await res.text().catch(() => 'Erro no servidor');
            paymentMessage.style.display = 'block';
            paymentMessage.style.color = 'red';
            paymentMessage.textContent = 'Erro: ' + txt;
            return;
          }
          const text = await res.text().catch(() => '');
          paymentMessage.style.display = 'block';
          paymentMessage.style.color = 'green';
          paymentMessage.textContent = text || 'Pagamento registrado com sucesso.';

          // limpar carrinho local (se desejar)
          cart.length = 0;
          renderCart();

          // atualiza modal valores para zero
          modalItemsList.innerHTML = '';
          modalSubtotal.textContent = 'R$ 0,00';
          modalTax.textContent = 'R$ 0,00';
          modalTotal.textContent = 'R$ 0,00';

          // opcional: fechar modal após 2s
          setTimeout(closePaymentModal, 1500);
        }).catch(err => {
          confirmPaymentBtn.disabled = false;
          confirmPaymentBtn.textContent = 'Confirmar pagamento';
          paymentMessage.style.display = 'block';
          paymentMessage.style.color = 'red';
          paymentMessage.textContent = 'Erro de conexão: ' + err.message;
        });
      });

      // Atalho teclado: F1 abre scanner (se houver), F2 abre modal de pagamento
      window.addEventListener('keydown', function (e) {
        if (e.key === 'F1') {
          e.preventDefault();
          document.getElementById('scan-modal')?.classList.remove('hidden');
          document.getElementById('scan-input')?.focus();
        }
        if (e.key === 'F2') {
          e.preventDefault();
          openPaymentModal();
        }
      }, true);
    });
  </script>

</body>

</html>