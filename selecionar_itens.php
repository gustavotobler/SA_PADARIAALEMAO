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

    :root {
      --sidebar-bg: #2e2e2e;
      --primary-text: #fff;
      --hover-bg: #444;
    }

    /* Sidebar fixa padrão */
    .sidebar {
      width: 240px;
      background: var(--sidebar-bg);
      height: 100vh;
      position: fixed;
      display: flex;
      flex-direction: column;
      padding-top: 20px;
      transition: width 0.3s;
    }

    .sidebar.collapsed {
      width: 60px;
    }

    /* Links da sidebar */
    .sidebar a {
      display: flex;
      align-items: center;
      color: var(--primary-text);
      text-decoration: none;
      padding: 15px 20px;
      white-space: nowrap;
    }

    .sidebar a:hover {
      background: var(--hover-bg);
    }

    .sidebar .icon {
      margin-right: 8px;
      font-size: 20px;
      display: flex;
      align-items: center;
    }

    .sidebar.collapsed .text {
      display: none;
    }

    .sidebar.collapsed .icon {
      margin-right: 0;
      justify-content: center;
    }

    /* Botão de toggle */
    .toggle-btn {
      cursor: pointer;
      text-align: center;
      margin-bottom: 20px;
      font-size: 20px;
      color: var(--primary-text);
    }

    /* Ajusta emojis */
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

    .sidebar .back-link {
      display: flex;
      align-items: center;
      transition: all 0.3s ease;
      /* anima margem, rotação, etc */
    }

    .sidebar .back-link .icon {
      font-size: 24px;
      display: flex;
      align-items: center;
      transition: transform 0.3s ease, margin 0.3s ease;
      /* anima rotação e margem */
      margin-right: 8px;
    }

    .sidebar.collapsed .back-link {
      justify-content: center;
      /* centraliza quando colapsa */
    }

    .sidebar.collapsed .back-link .icon {
      margin-right: 0;
      transform: rotate(180deg);
    }

    .main-content {
      margin-left: 240px;
      /* mesma largura da sidebar */
      transition: margin-left 0.3s;
    }

    .sidebar.collapsed~.main-content {
      margin-left: 60px;
      /* quando colapsa */
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

    <a href="#" onclick="openPaymentModal(); return false;">
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
          <button data-cat="Pães">Pães</button> 
          <button data-cat="Bolos">Bolos</button>
          <button data-cat="Salgados">Salgados</button>
          <button data-cat="Café">Cafés</button>
          <button data-cat="Laticínios">Laticínios</button>
          <button data-cat="bebidas">Bebidas</button>
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

      <!-- Sidebar do carrinho (fica na direita por causa do grid) -->
      <aside class="cart-sidebar">
        <h3>Carrinho</h3>
        <div class="cart-items"></div>
        <div class="totals">
          <div><span>Subtotal:</span><span>R$ 0,00</span></div>
          <div><span>Taxa balcão:</span><span>R$ 0,00</span></div>
        </div>
        <div class="btn-pay" id="btnPay" role="button" tabindex="0">Ir para pagamento → (Total: R$ 0,00)</div>
      </aside>
    </div>

    <script>
      document.addEventListener("DOMContentLoaded", () => {
        // ELEMENTOS
        const searchInput = document.querySelector('.controls input[type="text"]');
        const cards = document.querySelectorAll('.grid .card');
        const hudButtons = document.querySelectorAll('.category-hud button');
        const cartItemsContainer = document.querySelector('.cart-items');
        const totalsSubtotalEl = document.querySelector('.totals div:first-child span:last-child');
        const totalsTaxEl = document.querySelector('.totals div:nth-child(2) span:last-child');
        const btnPay = document.getElementById('btnPay') || document.querySelector('.btn-pay');

        // FORMATAÇÃO DE MOEDA
        function formatReal(v) {
          const n = Number(v) || 0;
          return 'R$ ' + n.toFixed(2).replace('.', ',');
        }

        // 3) ATUALIZA TODOS OS TOTAIS (agora suporta decimal)
        function updateTotals() {
          let sub = 0;
          cartItemsContainer.querySelectorAll('.cart-item').forEach(item => {
            const price = parseFloat(item.dataset.price) || 0;
            const qty = parseFloat(item.querySelector('.qty').textContent) || 0;
            const itemSub = price * qty;
            item.querySelector('.item-subtotal').textContent = formatReal(itemSub);
            sub += itemSub;
          });
          totalsSubtotalEl.textContent = formatReal(sub);

          const tax = parseFloat(totalsTaxEl.dataset.tax || 0) || 0;
          totalsTaxEl.textContent = formatReal(tax);

          const total = sub + tax;
          if (btnPay) {
            btnPay.textContent = `Ir para pagamento → (Total: ${formatReal(total)})`;
            btnPay.dataset.total = total.toFixed(2); // salva valor numérico
          }
        }

        // FILTRO DE BUSCA
        if (searchInput) {
          searchInput.addEventListener('input', () => {
            const term = searchInput.value.toLowerCase();
            const activeCat = document.querySelector('.category-hud button.active').dataset.cat;
            cards.forEach(card => {
              const title = card.querySelector('h4').textContent.toLowerCase();
              const match = title.includes(term) && (activeCat === 'all' || card.dataset.category === activeCat);
              card.style.display = match ? '' : 'none';
            });
          });
        }

        // FILTRO DE CATEGORIA
        hudButtons.forEach(btn => {
          btn.addEventListener('click', () => {
            hudButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            if (searchInput) searchInput.dispatchEvent(new Event('input'));
          });
        });

        // BOTÃO “＋” NOS CARDS (pede peso para produtos por kg)
        document.querySelectorAll('.add-to-cart').forEach(btn => {
          btn.addEventListener('click', () => {
            const name = btn.dataset.name;
            const price = parseFloat(btn.dataset.price) || 0;
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

        // CONTROLE DE +/− NA SIDEBAR
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

        // INSERIR PRODUTO POR CÓDIGO ESCANEADO
        const scanInput = document.getElementById('scan-input');
        if (scanInput) {
          scanInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
              const code = e.target.value.trim();

              // Código para "Doritos" (exemplo)
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

              e.target.value = '';
            }
          });
        }

        // Fecha scan modal (se existir)
        const btnCloseScan = document.getElementById('close-scan');
        if (btnCloseScan) {
          btnCloseScan.addEventListener('click', () => {
            const sm = document.getElementById('scan-modal');
            if (sm) sm.classList.add('hidden');
          });
        }

        // --- MODAL DE PAGAMENTO (cria dinamicamente se não existir) ---
        let paymentModal = document.getElementById('payment-modal');
        if (!paymentModal) {
          paymentModal = document.createElement('div');
          paymentModal.id = 'payment-modal';
          paymentModal.className = 'hidden';
          paymentModal.innerHTML = `
      <div class="modal-backdrop" style="position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;"></div>
      <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="payment-title"
           style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:#fff;padding:1.2rem;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.2);z-index:1001;width:320px;max-width:90%;">
        <h3 id="payment-title" style="margin-bottom:8px;">Confirmar pagamento</h3>
        <div>
          <div style="margin-bottom:8px;"><strong>Total:</strong> <span id="pm-total">R$ 0,00</span></div>
          <label style="display:block; margin-top:6px; font-size:0.95rem;">
            Método de pagamento:
            <select id="pm-method" style="width:100%; padding:.45rem; margin-top:6px;">
              <option value="dinheiro">Dinheiro</option>
              <option value="cartao">Cartão</option>
              <option value="pix">PIX</option>
            </select>
          </label>

          <label id="pm-paid-label" style="display:block; margin-top:10px; font-size:0.95rem;">
            Valor recebido:
            <input id="pm-paid" type="text" placeholder="0,00" style="width:100%; padding:.4rem; margin-top:6px;" />
          </label>

          <div id="pm-change-wrap" style="margin-top:8px; display:none;">
            <strong>Troco:</strong> <span id="pm-change">R$ 0,00</span>
          </div>

          <div style="display:flex; gap:.5rem; margin-top:12px; justify-content:flex-end;">
            <button id="pm-cancel" style="padding:.45rem .8rem; border-radius:6px; background:#ddd; border:none; cursor:pointer;">Cancelar</button>
            <button id="pm-confirm" style="padding:.45rem .8rem; border-radius:6px; background:#2a9d8f; color:#fff; border:none; cursor:pointer;">Confirmar</button>
          </div>
        </div>
      </div>
    `;
          document.body.appendChild(paymentModal);
        }

        // refs modal
        const pmTotalEl = document.getElementById('pm-total');
        const pmMethod = document.getElementById('pm-method');
        const pmPaid = document.getElementById('pm-paid');
        const pmPaidLabel = document.getElementById('pm-paid-label');
        const pmChangeWrap = document.getElementById('pm-change-wrap');
        const pmChange = document.getElementById('pm-change');
        const pmCancel = document.getElementById('pm-cancel');
        const pmConfirm = document.getElementById('pm-confirm');
        const pmBackdrop = paymentModal.querySelector('.modal-backdrop');

        function openPaymentModal() {
          const total = parseFloat(btnPay.dataset.total || '0');
          pmTotalEl.textContent = formatReal(total);
          pmPaid.value = '';
          pmChange.textContent = formatReal(0);
          pmChangeWrap.style.display = 'none';
          paymentModal.classList.remove('hidden');
          pmMethod.focus();
        }

        function closePaymentModal() {
          paymentModal.classList.add('hidden');
        }

        function updatePaidFieldVisibility() {
          const method = pmMethod.value;
          if (method === 'dinheiro') {
            pmPaidLabel.style.display = 'block';
            pmChangeWrap.style.display = pmPaid.value ? '' : 'none';
          } else {
            pmPaidLabel.style.display = 'none';
            pmChangeWrap.style.display = 'none';
          }
        }

        pmPaid.addEventListener('input', () => {
          const raw = pmPaid.value.replace(/\./g, '').replace(',', '.').replace(/[^\d.]/g, '');
          const paid = parseFloat(raw || '0');
          const total = parseFloat(btnPay.dataset.total || '0');
          if (!isNaN(paid)) {
            const change = paid - total;
            pmChange.textContent = formatReal(change > 0 ? change : 0);
            pmChangeWrap.style.display = change > 0 ? '' : 'none';
          } else {
            pmChange.textContent = formatReal(0);
            pmChangeWrap.style.display = 'none';
          }
        });

        pmMethod.addEventListener('change', updatePaidFieldVisibility);
        pmCancel.addEventListener('click', closePaymentModal);
        if (pmBackdrop) pmBackdrop.addEventListener('click', closePaymentModal);

        // Confirmar pagamento (sem enviar para pagamento.php) — simula confirmação e limpa carrinho
        pmConfirm.addEventListener('click', () => {
          const total = parseFloat(btnPay.dataset.total || '0');

          // coleta itens do carrinho
          const itens = [];
          document.querySelectorAll('.cart-item').forEach(item => {
            itens.push({
              nome: item.querySelector('.item-name').textContent,
              qtd: item.querySelector('.qty').textContent,
              preco: item.dataset.price
            });
          });

          if (itens.length === 0) {
            alert("O carrinho está vazio!");
            closePaymentModal();
            return;
          }

          const method = pmMethod.value;
          if (method === 'dinheiro') {
            const raw = pmPaid.value.replace(/\./g, '').replace(',', '.').replace(/[^\d.]/g, '');
            const paid = parseFloat(raw || '0');
            if (isNaN(paid) || paid < total) {
              alert('Valor recebido insuficiente.');
              return;
            }
          }

          // Ação: simular sucesso — limpar carrinho e atualizar totais
          cartItemsContainer.innerHTML = '';
          updateTotals();
          closePaymentModal();
          alert('Pagamento confirmado com sucesso!');
        });

        // abrir modal pelo botão
        if (btnPay) {
          btnPay.addEventListener('click', (e) => {
            openPaymentModal();
          });
        }

        // Atalhos globais F1/F2 (F1 abre scan modal, F2 abre modal de pagamento)
        window.addEventListener('keydown', function (e) {
          if (e.key === 'F1') {
            e.preventDefault();
            e.stopImmediatePropagation();
            const scanModal = document.getElementById('scan-modal');
            const scanInputEl = document.getElementById('scan-input');
            if (scanModal) {
              scanModal.classList.remove('hidden');
              if (scanInputEl) scanInputEl.focus();
            }
          }
          if (e.key === 'F2') {
            e.preventDefault();
            e.stopImmediatePropagation();
            openPaymentModal();
          }
        }, true);

        // inicial
        updateTotals();
      });

      // Sidebar toggle (mantive tua função)
      const sidebar = document.getElementById('sidebar');
      const mainContent = document.getElementById('mainContent');
      function toggleSidebar() {
        if (sidebar) sidebar.classList.toggle('collapsed');
        if (mainContent) mainContent.classList.toggle('collapsed');
      }
    </script>

    <!-- MODAL DE PAGAMENTO -->
    <div id="payment-modal" class="hidden">
      <div class="modal-backdrop"></div>
      <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="payment-title">
        <h3 id="payment-title">Confirmar pagamento</h3>

        <div style="margin:8px 0;">
          <div><strong>Total:</strong> <span id="pm-total">R$ 0,00</span></div>
          <label style="display:block; margin-top:8px;">
            Método de pagamento:
            <select id="pm-method" style="width:100%; padding:.4rem; margin-top:4px;">
              <option value="dinheiro">Dinheiro</option>
              <option value="cartao">Cartão</option>
              <option value="pix">PIX</option>
            </select>
          </label>

          <label id="pm-paid-label" style="display:block; margin-top:8px;">
            Valor recebido:
            <input id="pm-paid" type="text" placeholder="0,00" style="width:100%; padding:.4rem; margin-top:4px;" />
          </label>

          <div id="pm-change-wrap" style="margin-top:8px; display:none;">
            <strong>Troco:</strong> <span id="pm-change">R$ 0,00</span>
          </div>

          <div style="display:flex; gap:.5rem; margin-top:12px; justify-content:flex-end;">
            <button id="pm-cancel"
              style="padding:.5rem .8rem; border-radius:6px; background:#ddd; border:none; cursor:pointer;">Cancelar</button>
            <button id="pm-confirm"
              style="padding:.5rem .8rem; border-radius:6px; background:#2a9d8f; color:#fff; border:none; cursor:pointer;">Confirmar</button>
          </div>
        </div>
      </div>
    </div>

</body>

</html>