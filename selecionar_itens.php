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
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" defer></script>
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

    /* ---------------- Modal melhorado: estilos adicionais ---------------- */
    :root {
      --bg: #f5f2ed;
      --accent: #2a9d8f;
      --accent-600: #237c6f;
      --panel: #fff;
      --muted: #6b6b6b;
      --shadow: 0 10px 30px rgba(15, 15, 15, 0.12);
      --radius: 12px;
    }

    /* ---------- Modal base ---------- */
    .modal-root {
      position: fixed;
      inset: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 1200;
      pointer-events: none;
    }

    .modal-backdrop {
      position: absolute;
      inset: 0;
      background: rgba(0, 0, 0, 0.45);
      backdrop-filter: blur(3px);
      opacity: 0;
      transition: opacity .22s;
    }

    .modal-panel {
      position: fixed;
      width: min(720px, 95%);
      max-width: 720px;
      background: var(--panel);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      transform: translateY(8px) scale(.99);
      opacity: 0;
      transition: all .22s;
      pointer-events: auto;
      z-index: 9999;
    }

    #paymentModal.dragging .modal-panel {

      cursor: move;
    }

    /* visible */
    .modal-root[data-open="true"] .modal-backdrop {
      opacity: 1
    }

    .modal-root[data-open="true"] .modal-panel {
      opacity: 1;
      transform: translateY(0) scale(1)
    }

    /* content layout */
    .modal-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      padding: 18px 20px;
      border-bottom: 1px solid #eee;
    }

    .modal-title {
      font-size: 1.05rem;
      font-weight: 700
    }

    .modal-sub {
      font-size: .92rem;
      color: var(--muted)
    }

    .modal-body {
      display: grid;
      grid-template-columns: 1fr 320px;
      gap: 18px;
      padding: 18px 20px;
    }

    .left {
      min-width: 0
    }

    .right {
      background: #fbfbfb;
      border-radius: 10px;
      padding: 12px
    }

    .items-list {
      display: flex;
      flex-direction: column;
      gap: 10px;
      max-height: 280px;
      overflow: auto;
      padding-right: 6px
    }

    .item-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      padding: 8px;
      border-radius: 8px
    }

    .item-name {
      font-weight: 600
    }

    .item-meta {
      font-size: .9rem;
      color: var(--muted)
    }

    .field {
      display: flex;
      flex-direction: column;
      gap: 6px;
      margin-bottom: 10px
    }

    select,
    input {
      width: 100%;
      padding: 10px 12px;
      border-radius: 8px;
      border: 1px solid #e6e6e6;
      font-size: 1rem
    }

    .money {
      font-size: 1.1rem;
      font-weight: 700;
      text-align: right
    }

    .summary {
      display: flex;
      flex-direction: column;
      gap: 6px
    }

    .summary-row {
      display: flex;
      justify-content: space-between;
      color: var(--muted)
    }

    .modal-footer {
      display: flex;
      justify-content: space-between;
      gap: 12px;
      padding: 14px 20px;
      border-top: 1px solid #eee
    }

    .btn {
      padding: 10px 14px;
      border-radius: 10px;
      border: none;
      cursor: pointer;
      font-weight: 600
    }

    .btn-ghost {
      background: transparent;
      color: #444
    }

    .btn-primary {
      background: var(--accent);
      color: #fff
    }

    .hidden {
      display: none
    }

    .sr-only {
      position: absolute !important;
      height: 1px;
      width: 1px;
      overflow: hidden;
      clip: rect(1px, 1px, 1px, 1px);
      white-space: nowrap
    }

    @media (max-width:720px) {
      .modal-body {
        grid-template-columns: 1fr;
      }

      .right {
        order: 2
      }
    }

    .btn:focus,
    input:focus,
    select:focus {
      outline: 3px solid rgba(42, 157, 143, 0.18);
      outline-offset: 2px
    }

    .toast {
      position: fixed;
      right: 16px;
      bottom: 16px;
      background: #222;
      color: #fff;
      padding: 10px 14px;
      border-radius: 8px;
      box-shadow: var(--shadow);
      opacity: 0;
      transform: translateY(8px);
      transition: all .25s;
      z-index: 1300
    }

    .toast.show {
      opacity: 1;
      transform: translateY(0)
    }

    .modal-root {
      display: none;
    }

    .modal-root[data-open="true"] {
      display: block;
      /* ou flex conforme seu layout */
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
          <!-- adicionei apenas id (invisível) para ativar a busca sem alterar visual -->
          <input id="searchInput" type="text" placeholder="Nome ou código">
        </div>

        <!-- HUD de categorias -->
        <div class="category-hud" id="categoryHud">
          <button data-cat="all" class="active">Todas</button>
          <button data-cat="Pães">Pães</button>
          <button data-cat="Bolos">Bolos</button>
          <button data-cat="Salgados">Salgados</button>
          <button data-cat="Café">Cafés</button>
          <button data-cat="Laticínios">Laticínios</button>
          <button data-cat="bebidas">Bebidas</button>
        </div>


        <!-- Lista de produtos -->
        <div class="grid" id="productsGrid">
          <?php if ($produtos): ?>
            <?php foreach ($produtos as $row): ?>
              <div class="card" data-category="<?= htmlspecialchars($row['nome_categoria']) ?>"
                data-name="<?= htmlspecialchars($row['Nome_prod']) ?>">
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
          <div><span>Taxa balcão:</span><span data-tax="0">R$ 0,00</span></div>
        </div>
        <div class="btn-pay" id="btnPay" role="button" tabindex="0">Ir para pagamento → (Total: R$ 0,00)</div>
      </aside>
    </div>

    <!-- ======== MODAL DE PAGAMENTO (cole aqui, ANTES do script) ======== -->
    <div id="paymentModal" class="modal-root" role="dialog" aria-modal="true" aria-labelledby="paymentTitle"
      data-open="false">
      <div class="modal-backdrop" data-dismiss="true" tabindex="-1"></div>

      <div class="modal-panel" role="document">
        <header class="modal-header">
          <div>
            <div id="paymentTitle" class="modal-title">Confirmar pagamento</div>
            <div class="modal-sub">Revise os itens e escolha o método</div>
          </div>
          <div style="display:flex; gap:8px; align-items:center">
            <div class="money" id="pmTotalHeader">R$ 0,00</div>
            <button id="pmCloseTop" class="btn btn-ghost" aria-label="Fechar">✕</button>
          </div>
        </header>

        <div class="modal-body">
          <div class="left">
            <div class="field">
              <label for="pmMethod">Método de pagamento</label>
              <select id="pmMethod" aria-describedby="pmMethodDesc">
                <option value="dinheiro">Dinheiro</option>
                <option value="cartao">Cartão</option>
                <option value="pix">PIX</option>
              </select>
              <div id="pmMethodDesc" class="sr-only">Selecionar método. Se Dinheiro, informe valor recebido para cálculo
                do troco.</div>
            </div>

            <div class="field" id="paidField">
              <label for="pmPaid">Valor recebido</label>
              <input id="pmPaid" inputmode="decimal" autocomplete="off" placeholder="0,00"
                aria-label="Valor recebido" />
              <div id="pmChangeWrap" class="sr-only" aria-live="polite">Troco: R$ 0,00</div>
            </div>

            <div style="margin-top:8px;">
              <div style="font-size:.95rem; color:var(--muted); margin-bottom:6px">Itens</div>
              <div id="itemsList" class="items-list" aria-live="polite">
                <!-- itens serão renderizados aqui -->
              </div>
            </div>
          </div>

          <aside class="right">
            <div class="summary">
              <div class="summary-row"><span>Subtotal</span><span id="pmSubtotal">R$ 0,00</span></div>
              <div class="summary-row"><span>Taxa balcão</span><span id="pmTax">R$ 0,00</span></div>
              <div style="height:8px"></div>
              <div class="summary-row" style="font-size:1.1rem; font-weight:700"><span>Total</span><span id="pmTotal">R$
                  0,00</span></div>

              <div id="pixBox" style="display:none; margin-top:12px; text-align:center">
                <h3 style="margin:8px 0">Pague com PIX</h3>
                <div id="pixQr" style="display:inline-block; width:220px; height:220px;"></div>

                <div style="margin-top:10px; text-align:left">
                  <div style="font-size:.92rem; margin-bottom:4px">Copia e cola:</div>
                  <textarea id="pixCopiaCola" readonly style="width:100%; height:80px;"></textarea>
                  <button id="btnCopyPix" class="btn" style="margin-top:8px; padding:8px; width:100%">Copiar código
                    PIX</button>
                </div>
              </div>

            </div>

            <div style="margin-top:12px; display:flex; gap:8px;">
              <button id="pmCancel" class="btn btn-ghost" style="flex:1">Cancelar</button>
              <button id="pmConfirm" class="btn btn-primary" style="flex:1">Confirmar</button>
            </div>

            <div id="pmNote" style="margin-top:10px; font-size:.92rem; color:var(--muted)"></div>
          </aside>
        </div>

        <footer class="modal-footer">
          <div style="font-size:.92rem; color:var(--muted)">Atalhos: <kbd>Esc</kbd> fechar, <kbd>Enter</kbd> confirmar
          </div>
        </footer>
      </div>
    </div>
    <!-- ======== FIM modal ======== -->

    <!-- toast (adicionado - invisível até mostrar) -->
    <div id="toast" class="toast" role="status" aria-live="polite" aria-atomic="true"></div>


    <script>
      (function () {
        'use strict';

        // Stubs globais rápidos (evitam problemas se for chamado antes da inicialização)
        window.openPaymentModal = window.openPaymentModal || function (opts) {
          window.__pending_open = opts === undefined ? true : opts;
          console.warn('[PAYMENT-STUB] openPaymentModal chamado antes da inicialização; pendente.');
        };
        window.closePaymentModal = window.closePaymentModal || function () {
          window.__pending_close = true;
          console.warn('[PAYMENT-STUB] closePaymentModal chamado antes da inicialização; pendente.');
        };

        /* ---------------- utils ---------------- */
        const $ = (sel, root = document) => (root || document).querySelector(sel);
        const $$ = (sel, root = document) => Array.from((root || document).querySelectorAll(sel));

        function formatReal(v) {
          const n = Number(v) || 0;
          return 'R$ ' + n.toFixed(2).replace('.', ',');
        }
        function parseBR(value) {
          if (value == null) return 0;
          const plain = String(value).replace(/\s/g, '').replace(/\./g, '').replace(',', '.').replace(/[^0-9.\-]/g, '');
          return parseFloat(plain) || 0;
        }
        function escapeHtml(s = '') {
          return String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": "&#39;" }[c]));
        }

        // carrega script externo (resolve se já disponível)
        function loadScript(url, timeout = 6000) {
          return new Promise((resolve, reject) => {
            if (window.QRCode && typeof window.QRCode === 'function') return resolve();
            const existing = Array.from(document.scripts).find(s => s.src && s.src.includes(url));
            if (existing) {
              if (existing.getAttribute('data-loaded-ok')) return resolve();
              existing.addEventListener('load', () => { existing.setAttribute('data-loaded-ok', '1'); resolve(); });
              existing.addEventListener('error', () => reject(new Error('load error')));
              return;
            }
            const s = document.createElement('script');
            s.src = url;
            s.async = true;
            let t = setTimeout(() => { reject(new Error('timeout')); }, timeout);
            s.onload = () => { clearTimeout(t); s.setAttribute('data-loaded-ok', '1'); resolve(); };
            s.onerror = () => { clearTimeout(t); reject(new Error('load error')); };
            document.head.appendChild(s);
          });
        }

        /* ---------------- main (após DOM estar pronto) ---------------- */
        document.addEventListener('DOMContentLoaded', () => {
          try {
            // refs
            const cartItemsContainer = $('.cart-items');
            const totalsSubtotalEl = document.querySelector('.totals div:first-child span:last-child');
            const totalsTaxEl = document.querySelector('.totals div:nth-child(2) span:last-child');
            const btnPay = document.getElementById('btnPay');

            const modal = document.getElementById('paymentModal');
            const backdrop = modal ? modal.querySelector('.modal-backdrop') : null;
            const pmMethod = modal ? modal.querySelector('#pmMethod') : null;
            const pmPaid = modal ? modal.querySelector('#pmPaid') : null;
            const paidField = modal ? modal.querySelector('#paidField') : null;
            const pmChangeWrapEl = modal ? modal.querySelector('#pmChangeWrap') : null;
            const itemsList = modal ? modal.querySelector('#itemsList') : null;
            const pmSubtotal = modal ? modal.querySelector('#pmSubtotal') : null;
            const pmTax = modal ? modal.querySelector('#pmTax') : null;
            const pmTotal = modal ? modal.querySelector('#pmTotal') : null;
            const pmTotalHeader = modal ? modal.querySelector('#pmTotalHeader') : null;
            const pmConfirm = modal ? modal.querySelector('#pmConfirm') : null;
            const pmCancel = modal ? modal.querySelector('#pmCancel') : null;
            const pmCloseTop = modal ? modal.querySelector('#pmCloseTop') : null;
            const toast = document.getElementById('toast');

            const pixBox = modal ? modal.querySelector('#pixBox') : null;
            const pixQr = modal ? modal.querySelector('#pixQr') : null;
            const pixCopiaCola = modal ? modal.querySelector('#pixCopiaCola') : null;
            const btnCopyPix = modal ? modal.querySelector('#btnCopyPix') : null;

            const searchInput = document.getElementById('searchInput');
            const categoryHud = document.getElementById('categoryHud');
            const productCards = () => $$('#productsGrid .card');

            /* ---------- totals ---------- */
            function updateTotals() {
              if (!cartItemsContainer) return;
              let sub = 0;
              $$('.cart-item', cartItemsContainer).forEach(item => {
                const price = parseFloat(item.dataset.price || 0) || 0;
                const qtyEl = item.querySelector('.qty');
                const qty = qtyEl ? parseFloat(qtyEl.textContent.replace(',', '.')) || 0 : 0;
                const itemSub = price * qty;
                const itSubEl = item.querySelector('.item-subtotal');
                if (itSubEl) itSubEl.textContent = formatReal(itemSub);
                sub += itemSub;
              });
              if (totalsSubtotalEl) totalsSubtotalEl.textContent = formatReal(sub);

              const taxRaw = totalsTaxEl && totalsTaxEl.dataset && totalsTaxEl.dataset.tax ? totalsTaxEl.dataset.tax : (totalsTaxEl ? totalsTaxEl.textContent : '0');
              const tax = parseBR(taxRaw) || 0;
              if (totalsTaxEl) totalsTaxEl.textContent = formatReal(tax);

              const total = sub + tax;
              if (btnPay) {
                btnPay.textContent = `Ir para pagamento → (Total: ${formatReal(total)})`;
                btnPay.dataset.total = Number(total).toFixed(2);
              }
            }

            /* ---------- add-to-cart handlers (se já existirem no DOM) ---------- */
            $$('.add-to-cart').forEach(btn => {
              btn.addEventListener('click', () => {
                if (!cartItemsContainer) return;
                const name = btn.dataset.name || 'Produto';
                const price = parseFloat(btn.dataset.price) || 0;
                let qty = 1;
                if (btn.dataset.unit && String(btn.dataset.unit).toLowerCase().includes('kg')) {
                  const input = prompt('Digite o peso em kg (ex: 0.250 para 250 g):');
                  if (!input) return;
                  qty = parseFloat(input.replace(',', '.'));
                  if (isNaN(qty) || qty <= 0) { alert('Peso inválido!'); return; }
                }
                let existing = Array.from(cartItemsContainer.children).find(el => el.querySelector('.item-name') && el.querySelector('.item-name').textContent === name);
                if (existing) {
                  const qtyEl = existing.querySelector('.qty');
                  const newQty = (parseFloat(qtyEl.textContent.replace(',', '.')) || 0) + qty;
                  qtyEl.textContent = String(newQty).replace('.', ',');
                } else {
                  const div = document.createElement('div');
                  div.className = 'cart-item';
                  div.dataset.price = String(price);
                  div.innerHTML = `
              <div class="item-info">
                <button class="qty-btn decrease">−</button>
                <span class="qty">${String(qty).replace('.', ',')}</span>
                <button class="qty-btn increase">＋</button>
                <span class="item-name">${escapeHtml(name)}</span>
              </div>
              <span class="item-subtotal">${formatReal(price * qty)}</span>
            `;
                  cartItemsContainer.appendChild(div);
                }
                updateTotals();
              });
            });

            /* ---------- qty handlers ---------- */
            if (cartItemsContainer) {
              cartItemsContainer.addEventListener('click', e => {
                const itemEl = e.target.closest('.cart-item');
                if (!itemEl) return;
                if (e.target.matches('.increase')) {
                  const qtyEl = itemEl.querySelector('.qty');
                  const newQty = (parseFloat(qtyEl.textContent.replace(',', '.')) || 0) + 1;
                  qtyEl.textContent = newQty.toFixed(2).replace(/\.00$/, '').replace('.', ',');
                  updateTotals();
                } else if (e.target.matches('.decrease')) {
                  const qtyEl = itemEl.querySelector('.qty');
                  const newQty = (parseFloat(qtyEl.textContent.replace(',', '.')) || 0) - 1;
                  if (newQty <= 0) itemEl.remove();
                  else qtyEl.textContent = newQty.toFixed(2).replace(/\.00$/, '').replace('.', ',');
                  updateTotals();
                }
              });
            }

            /* ---------- collect helpers ---------- */
            function collectCartItems() {
              return $$('.cart-item').map(el => {
                const nome = el.querySelector('.item-name') ? el.querySelector('.item-name').textContent.trim() : 'Produto';
                const qtd = el.querySelector('.qty') ? el.querySelector('.qty').textContent.replace(',', '.') : '1';
                const preco = el.dataset.price || '0';
                return { nome, qtd: String(qtd), preco: String(preco) };
              });
            }
            function collectTotals() {
              const cartEls = $$('.cart-item');
              const subtotal = cartEls.reduce((acc, el) => {
                const price = parseFloat(el.dataset.price || 0) || 0;
                const qty = parseFloat((el.querySelector('.qty') || { textContent: '1' }).textContent.replace(',', '.')) || 1;
                return acc + (price * qty);
              }, 0);
              const tax = parseBR(totalsTaxEl ? totalsTaxEl.textContent : 0) || 0;
              return { total: subtotal + tax, subtotal, tax };
            }

            /* ---------- modal open/close ---------- */
            let lastFocused = null;
            function renderItems(items) {
              if (!itemsList) return;
              itemsList.innerHTML = '';
              if (!items || items.length === 0) {
                itemsList.innerHTML = '<div style="color:var(--muted)">Nenhum item no carrinho.</div>';
                return;
              }
              items.forEach(it => {
                const div = document.createElement('div');
                div.className = 'item-row';
                div.innerHTML = `<div>
                <div class="item-name">${escapeHtml(it.nome)}</div>
                <div class="item-meta">${String(it.qtd).replace('.', ',')} × ${formatReal(it.preco)}</div>
              </div>
              <div style="font-weight:700">${formatReal(Number(it.preco) * Number(it.qtd))}</div>`;
                itemsList.appendChild(div);
              });
            }

            function openPaymentModal(opts) {
              if (!modal) return;
              lastFocused = document.activeElement;
              modal.setAttribute('data-open', 'true');
              modal.removeAttribute('aria-hidden');

              const totals = opts && opts.total != null ? opts : collectTotals();
              const items = opts && opts.items ? opts.items : collectCartItems();
              const tax = opts && opts.tax != null ? opts.tax : (totals.tax || 0);
              const total = opts && opts.total != null ? opts.total : totals.total;

              renderItems(items);
              if (pmSubtotal) pmSubtotal.textContent = formatReal(total - tax);
              if (pmTax) pmTax.textContent = formatReal(tax);
              if (pmTotal) pmTotal.textContent = formatReal(total);
              if (pmTotalHeader) pmTotalHeader.textContent = formatReal(total);
              if (pmMethod) pmMethod.value = 'dinheiro';
              if (pmPaid) pmPaid.value = '';
              if (pmChangeWrapEl) pmChangeWrapEl.classList.add('sr-only');
              if (pixBox) pixBox.style.display = 'none';

              modal.dataset.total = String(Number(total).toFixed(2));
              modal.dataset.tax = String(Number(tax).toFixed(2));
            }

            function closePaymentModal() {
              if (!modal) return;
              modal.setAttribute('data-open', 'false');
              modal.setAttribute('aria-hidden', 'true');
              if (lastFocused) lastFocused.focus();
              if (pixQr) pixQr.innerHTML = '';
            }

            // expõe globalmente
            try {
              window.openPaymentModal = (opts) => openPaymentModal(opts);
              window.closePaymentModal = () => closePaymentModal();
              if (window.__pending_open) {
                const pending = window.__pending_open === true ? undefined : window.__pending_open;
                openPaymentModal(pending);
                window.__pending_open = null;
              }
              if (window.__pending_close) {
                closePaymentModal();
                window.__pending_close = null;
              }
            } catch (e) {
              console.warn('Erro ao expor funções globais:', e);
            }

            // botão pay
            if (btnPay) btnPay.addEventListener('click', e => { e.preventDefault(); openPaymentModal(collectTotals()); });

            /* ---------- PIX generation (robust) ---------- */
            async function gerarPix() {
              try {
                if (!modal) return;
                const valorStr = String(modal.dataset.total || '0').replace(',', '.');
                const valorNum = Number(valorStr) || 0;
                const amount = valorNum > 0 ? valorNum.toFixed(2) : null;

                // dados do recebedor (configure aqui)
                let chavePix = "139.138.019-36";
                if (/^[\d.\-() ]+$/.test(chavePix)) chavePix = chavePix.replace(/\D/g, '');
                const nome = "Padaria do Alemão";
                const cidade = "JOINVILLE";

                function tlv(tag, value) {
                  const v = String(value || '');
                  const len = String(v.length).padStart(2, '0');
                  return tag + len + v;
                }

                const mfiGui = tlv('00', 'BR.GOV.BCB.PIX');
                const mfiKey = tlv('01', chavePix);
                const merchantAccountInfo = tlv('26', mfiGui + mfiKey);
                const payloadFormatIndicator = tlv('00', '01');
                const merchantCategoryCode = tlv('52', '0000');
                const transactionCurrency = tlv('53', '986');
                const amountField = amount ? tlv('54', amount) : '';
                const countryCode = tlv('58', 'BR');
                const merchantName = tlv('59', nome);
                const merchantCity = tlv('60', cidade.toUpperCase());
                const txidValue = '***';
                const additionalDataField = tlv('62', tlv('05', txidValue));

                let payload = payloadFormatIndicator
                  + merchantAccountInfo
                  + merchantCategoryCode
                  + transactionCurrency
                  + amountField
                  + countryCode
                  + merchantName
                  + merchantCity
                  + additionalDataField;
                payload += '6304';

                function crc16(str) {
                  const pol = 0x1021;
                  let crc = 0xFFFF;
                  for (let i = 0; i < str.length; i++) {
                    crc ^= str.charCodeAt(i) << 8;
                    for (let j = 0; j < 8; j++) {
                      crc = (crc & 0x8000) ? ((crc << 1) ^ pol) & 0xFFFF : (crc << 1) & 0xFFFF;
                    }
                  }
                  return crc.toString(16).toUpperCase().padStart(4, '0');
                }

                const crc = crc16(payload);
                const fullPayload = payload + crc;

                // preenche copia e cola
                if (pixCopiaCola) pixCopiaCola.value = fullPayload;

                // limpa area do QR
                if (!pixQr) return;
                pixQr.innerHTML = '';

                const size = 300; // gera QR maior; o CSS ajeita

                // tenta usar QRCode lib; se não, tenta carregar; se erro -> fallback imagem
                try {
                  if (!(window.QRCode && typeof window.QRCode === 'function')) {
                    // tenta carregar do CDN (poderá ser bloqueado em algumas redes)
                    await loadScript('https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js', 5000)
                      .catch(() => { /* swallow - faremos fallback por imagem */ });
                  }
                } catch (e) {
                  // ignore
                }

                if (window.QRCode && typeof window.QRCode === 'function') {
                  try {
                    // qrcodejs cria dentro do container; limpa e cria
                    const el = document.createElement('div');
                    pixQr.appendChild(el);
                    new QRCode(el, { text: fullPayload, width: size, height: size });
                  } catch (err) {
                    console.warn('[PIX] QR lib falhou, fallback para imagem:', err);
                    const qrUrl = `https://chart.googleapis.com/chart?chs=${size}x${size}&cht=qr&chl=${encodeURIComponent(fullPayload)}`;
                    pixQr.innerHTML = `<img alt="QR PIX" src="${qrUrl}" style="max-width:100%;height:auto;display:block;margin:0 auto;">`;
                  }
                } else {
                  const qrUrl = `https://chart.googleapis.com/chart?chs=${size}x${size}&cht=qr&chl=${encodeURIComponent(fullPayload)}`;
                  pixQr.innerHTML = `<img alt="QR PIX" src="${qrUrl}" style="max-width:100%;height:auto;display:block;margin:0 auto;">`;
                }

                if (pixBox) pixBox.style.display = 'block';
                return fullPayload;
              } catch (err) {
                console.error('[PIX] erro em gerarPix():', err);
                if (pixBox) pixBox.style.display = 'none';
              }
            }

            /* ---------- eventos PIX / copiar ---------- */
            if (pmMethod) {
              pmMethod.addEventListener('change', () => {
                if (pmMethod.value === 'pix') {
                  if (paidField) paidField.style.display = 'none';
                  gerarPix();
                } else if (pmMethod.value === 'dinheiro') {
                  if (paidField) paidField.style.display = '';
                  if (pixBox) pixBox.style.display = 'none';
                } else {
                  if (paidField) paidField.style.display = 'none';
                  if (pixBox) pixBox.style.display = 'none';
                }
              });
            }

            if (btnCopyPix && pixCopiaCola) {
              btnCopyPix.addEventListener('click', async () => {
                try {
                  if (!pixCopiaCola.value) return;
                  if (navigator.clipboard) await navigator.clipboard.writeText(pixCopiaCola.value);
                  else { pixCopiaCola.select(); document.execCommand('copy'); }
                  showToast('Código PIX copiado!');
                } catch (e) {
                  console.warn('copy failed', e);
                  alert('Não foi possível copiar automaticamente. Selecione e copie manualmente.');
                }
              });
            }

            /* ---------- confirmar / cancelar ---------- */
            if (pmCancel) pmCancel.addEventListener('click', () => closePaymentModal());
            if (pmCloseTop) pmCloseTop.addEventListener('click', () => closePaymentModal());
            if (backdrop) backdrop.addEventListener('click', e => { if (e.target === backdrop) closePaymentModal(); });
            if (pmConfirm) pmConfirm.addEventListener('click', () => {
              closePaymentModal();
              showToast('Pagamento confirmado!');
            });

            /* ---------- toast ---------- */
            let toastTimer = null;
            function showToast(msg, ms = 2000) {
              if (!toast) { alert(msg); return; }
              toast.textContent = msg;
              toast.classList.add('show');
              clearTimeout(toastTimer);
              toastTimer = setTimeout(() => toast.classList.remove('show'), ms);
            }

            /* ---------- drag modal (visual) ---------- */
            const modalPanel = modal ? modal.querySelector('.modal-panel') : null;
            if (modal && modalPanel) {
              let dragging = false, sx = 0, sy = 0, ol = 0, ot = 0;
              const handle = modalPanel.querySelector('.modal-header') || modalPanel;
              handle.style.touchAction = 'none';
              handle.addEventListener('pointerdown', e => {
                if (e.button && e.button !== 0) return;
                dragging = true;
                sx = e.clientX; sy = e.clientY;
                const rect = modalPanel.getBoundingClientRect();
                ol = rect.left; ot = rect.top;
                modalPanel.style.position = 'fixed';
                modalPanel.style.left = ol + 'px';
                modalPanel.style.top = ot + 'px';
                modalPanel.style.transform = 'none';
                handle.setPointerCapture && handle.setPointerCapture(e.pointerId);
              });
              document.addEventListener('pointermove', e => {
                if (!dragging) return;
                const dx = e.clientX - sx, dy = e.clientY - sy;
                let nl = ol + dx, nt = ot + dy;
                const vw = window.innerWidth, vh = window.innerHeight;
                const w = modalPanel.offsetWidth, h = modalPanel.offsetHeight;
                nl = Math.min(Math.max(8, nl), vw - w - 8);
                nt = Math.min(Math.max(8, nt), vh - h - 8);
                modalPanel.style.left = nl + 'px';
                modalPanel.style.top = nt + 'px';
              });
              document.addEventListener('pointerup', () => { dragging = false; });
              document.addEventListener('pointercancel', () => { dragging = false; });
            }

            /* ---------- keyboard ---------- */
            document.addEventListener('keydown', e => {
              if (e.key === 'Escape') closePaymentModal();
              if (e.key === 'Enter' && modal && modal.getAttribute('data-open') === 'true') pmConfirm && pmConfirm.click();
            });

            // inicializa totas
            updateTotals();

            // Search & Category filtering (se presente)
            function filterProducts() {
              const q = (searchInput && searchInput.value || '').trim().toLowerCase();
              const activeBtn = categoryHud ? categoryHud.querySelector('button.active') : null;
              const cat = activeBtn ? activeBtn.dataset.cat : 'all';
              productCards().forEach(card => {
                const name = (card.dataset.name || '').toLowerCase();
                const category = (card.dataset.category || '').toLowerCase();
                const matchesQuery = !q || name.includes(q) || (card.dataset.code && card.dataset.code.toLowerCase().includes(q));
                const matchesCat = (cat === 'all') || (category === String(cat).toLowerCase());
                card.style.display = (matchesQuery && matchesCat) ? '' : 'none';
              });
            }
            if (searchInput) searchInput.addEventListener('input', filterProducts);
            if (categoryHud) categoryHud.addEventListener('click', e => {
              const btn = e.target.closest('button[data-cat]');
              if (!btn) return;
              categoryHud.querySelectorAll('button').forEach(b => b.classList.remove('active'));
              btn.classList.add('active');
              filterProducts();
            });

          } catch (err) {
            console.error('Erro inicializando payment script:', err);
          }
        }); // DOMContentLoaded end

      })(); // IIFE end
    </script>

</body>

</html>