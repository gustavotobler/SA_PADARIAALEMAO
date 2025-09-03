<?php
require_once('conexao.php');

// Buscar produtos
$stmt = $pdo->prepare("\n    SELECT p.*, c.nome_categoria\nFROM produtos p\nJOIN categorias c ON p.id_categorias = c.id_categorias\n");
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

    /* ---------- Modal moderno ---------- */
    :root {
      --bg: #f5f2ed;
      --accent: #2a9d8f;
      --accent-600: #237c6f;
      --panel: rgba(255, 255, 255, 0.86);
      --muted: #6b6b6b;
      --shadow-lg: 0 20px 40px rgba(15, 15, 15, 0.15);
      --radius: 14px;
    }

    /* Container do modal - interações só quando aberto */
    .modal-root {
      position: fixed;
      inset: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 1200;
      pointer-events: none;
      /* inativo quando fechado */
    }

    /* Backdrop com blur suave */
    .modal-root .modal-backdrop {
      position: absolute;
      inset: 0;
      background: linear-gradient(180deg, rgba(0, 0, 0, 0.45), rgba(0, 0, 0, 0.5));
      backdrop-filter: blur(4px) saturate(1.05);
      opacity: 0;
      transition: opacity .28s cubic-bezier(.2, .9, .2, 1);
    }

    /* Painel principal - vidro + sombra */
    .modal-panel {
      position: relative;
      width: min(760px, 95%);
      max-width: 760px;
      background: var(--panel);
      border-radius: var(--radius);
      box-shadow: var(--shadow-lg);
      transform: translateY(12px) scale(.99);
      opacity: 0;
      transition: all .28s cubic-bezier(.2, .9, .2, 1);
      pointer-events: auto;
      z-index: 9999;
      overflow: hidden;
      border: 1px solid rgba(255, 255, 255, 0.6);
    }

    /* Header com degradê e título elegante */
    .modal-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      padding: 18px 20px;
      background: linear-gradient(90deg, rgba(42, 157, 143, 1) 0%, rgba(35, 124, 111, 1) 100%);
      color: #fff;
    }

    .modal-header .left {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .modal-header .icon {
      width: 44px;
      height: 44px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgba(255, 255, 255, 0.12);
      font-size: 22px;
      box-shadow: 0 6px 18px rgba(15, 15, 15, 0.08);
    }

    .modal-title {
      font-size: 1.05rem;
      font-weight: 700;
      letter-spacing: .2px;
    }

    .modal-sub {
      font-size: .9rem;
      opacity: .92;
    }

    /* total no cabeçalho */
    .money {
      font-size: 1.05rem;
      font-weight: 800;
      background: rgba(255, 255, 255, 0.12);
      padding: 8px 12px;
      border-radius: 8px;
    }

    /* Corpo dividido com espaçamento mais generoso */
    .modal-body {
      display: grid;
      grid-template-columns: 1fr 320px;
      gap: 20px;
      padding: 20px;
      background: linear-gradient(180deg, rgba(255, 255, 255, 0.02), rgba(255, 255, 255, 0));
    }

    /* Left */
    .field label {
      font-weight: 600;
      margin-bottom: 6px;
      display: block;
      color: #333;
    }

    select,
    input {
      width: 100%;
      padding: 12px 14px;
      border-radius: 10px;
      border: 1px solid #eee;
      background: #fff;
      box-shadow: inset 0 1px 0 rgba(0, 0, 0, 0.02);
      font-size: 1rem;
    }

    /* Itens list - estilo cartão */
    .items-list {
      display: flex;
      flex-direction: column;
      gap: 10px;
      max-height: 300px;
      overflow: auto;
      padding-right: 6px;
    }

    .item-row {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 10px;
      border-radius: 10px;
      background: linear-gradient(180deg, rgba(250, 250, 250, 1), rgba(248, 248, 248, 1));
      border: 1px solid rgba(0, 0, 0, 0.03);
    }

    .item-avatar {
      width: 44px;
      height: 44px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      color: var(--accent-600);
      background: rgba(42, 157, 143, 0.07);
      flex-shrink: 0;
    }

    .item-meta {
      font-size: .92rem;
      color: var(--muted);
    }

    .item-qty {
      margin-left: 6px;
      background: rgba(0, 0, 0, 0.06);
      padding: 4px 8px;
      border-radius: 999px;
      font-weight: 700;
      font-size: .9rem;
    }

    /* Right summary */
    .right {
      background: linear-gradient(180deg, #ffffff, #fbfbfb);
      border-radius: 10px;
      padding: 14px;
      border: 1px solid rgba(0, 0, 0, 0.03);
    }

    .summary-row {
      display: flex;
      justify-content: space-between;
      color: var(--muted);
      padding: 6px 0;
    }

    .summary-row.total {
      font-weight: 800;
      font-size: 1.08rem;
      color: #222;
    }

    /* Botões */
    .modal-footer {
      padding: 14px 20px;
      border-top: 1px solid rgba(0, 0, 0, 0.04);
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 10px;
    }

    .btn {
      padding: 10px 14px;
      border-radius: 10px;
      cursor: pointer;
      font-weight: 700;
      border: none;
    }

    .btn-ghost {
      background: transparent;
      color: #444;
    }

    .btn-primary {
      background: linear-gradient(180deg, var(--accent), var(--accent-600));
      color: #fff;
      box-shadow: 0 8px 20px rgba(36, 130, 115, 0.18);
      transition: transform .12s ease, box-shadow .12s ease;
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 14px 30px rgba(36, 130, 115, 0.18);
    }

    /* Troco (visível apenas quando preenchido) */
    #pmChangeWrap {
      margin-top: 8px;
      font-weight: 700;
      color: var(--accent-600);
    }

    /* animações visíveis */
    .modal-root[data-open="true"] .modal-backdrop {
      opacity: 1;
      pointer-events: auto;
    }

    .modal-root[data-open="true"] .modal-panel {
      opacity: 1;
      transform: translateY(0) scale(1);
      pointer-events: auto;
    }

    /* Responsivo */
    @media (max-width:720px) {
      .modal-body {
        grid-template-columns: 1fr;
      }

      .right {
        order: 2;
      }

      .money {
        display: none;
      }
    }

    /* Estilo de scrollbar sutil */
    .items-list::-webkit-scrollbar {
      width: 8px;
    }

    .items-list::-webkit-scrollbar-thumb {
      background: rgba(0, 0, 0, 0.08);
      border-radius: 8px;
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

    <div id="paymentModal" class="modal-root" role="dialog" aria-modal="true" aria-labelledby="paymentTitle"
      data-open="false">
      <div class="modal-backdrop" data-dismiss="true" tabindex="-1"></div>

      <div class="modal-panel" role="document" aria-live="polite">
        <header class="modal-header">
          <div class="left">
            <div class="icon" aria-hidden="true"><span class="material-icons" style="font-size:22px">payment</span>
            </div>
            <div>
              <div id="paymentTitle" class="modal-title">Confirmar pagamento</div>
              <div class="modal-sub">Revise os itens e escolha o método</div>
            </div>
          </div>

          <div style="display:flex; gap:10px; align-items:center">
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
              <div class="summary-row total"><span>Total</span><span id="pmTotal">R$ 0,00</span></div>
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


    <!-- toast (adicionado - invisível até mostrar) -->
    <div id="toast" class="toast" role="status" aria-live="polite" aria-atomic="true"></div>


    <script>
      (function () {
        'use strict';

        /* ---------------- utils ---------------- */
        const $ = (sel, root = document) => (root || document).querySelector(sel);
        const $$ = (sel, root = document) => Array.from((root || document).querySelectorAll(sel));

        function formatReal(v) {
          const n = Number(v) || 0;
          // garante que recebe número; usa toFixed antes de trocar ponto por vírgula
          return 'R$ ' + n.toFixed(2).replace('.', ',');
        }

        function parseBR(value) {
          // aceita "1.234,56" ou "1234.56" ou "12,34" etc
          if (value == null) return 0;
          const s = String(value).trim();
          // remove espaços, remove pontos de milhar, converte todas as vírgulas em ponto,
          // remove qualquer caractere que não seja dígito, ponto ou sinal
          const plain = s.replace(/\s/g, '').replace(/\./g, '').replace(/,/g, '.').replace(/[^0-9.\-]/g, '');
          return parseFloat(plain) || 0;
        }

        function escapeHtml(s = '') {
          return String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": "&#39;" }[c]));
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

            /* ---------- add-to-cart handlers ---------- */
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
                <button class="qty-btn decrease" aria-label="Diminuir quantidade">−</button>
                <span class="qty">${String(qty).replace('.', ',')}</span>
                <button class="qty-btn increase" aria-label="Aumentar quantidade">＋</button>
                <span class="item-name">${escapeHtml(name)}</span>
              </div>
              <span class="item-subtotal">${formatReal(price * qty)}</span>
            `;
                  cartItemsContainer.appendChild(div);
                }
                updateTotals();
              });
            });

            /* ---------- qty handlers (delegation) ---------- */
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
                const nome = String(it.nome || 'Produto');
                const qtdStr = String(it.qtd || '1').replace('.', ',');
                const preco = Number(it.preco || 0);
                const total = preco * Number(String(it.qtd || '1').replace(',', '.'));
                const initial = nome.trim().charAt(0).toUpperCase();

                const div = document.createElement('div');
                div.className = 'item-row';
                div.innerHTML = `
            <div class="item-avatar" aria-hidden="true">${escapeHtml(initial)}</div>
            <div style="flex:1; min-width:0">
              <div class="item-name" style="font-weight:700">${escapeHtml(nome)}</div>
              <div class="item-meta">${qtdStr} × ${formatReal(preco)}</div>
            </div>
            <div style="text-align:right">
              <div style="font-weight:800">${formatReal(total)}</div>
              <div class="item-qty" aria-hidden="true">${qtdStr}</div>
            </div>
          `;
                itemsList.appendChild(div);
              });
            }

            function openPaymentModal(opts) {
              if (!modal) return;
              lastFocused = document.activeElement;
              // abrir
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

              if (pmMethod) {
                pmMethod.value = 'dinheiro';
                // mostra/oculta paidField conforme método
                paidField && (paidField.style.display = (pmMethod.value === 'dinheiro' ? '' : 'none'));
              }
              if (pmPaid) pmPaid.value = '';
              if (pmChangeWrapEl) {
                pmChangeWrapEl.classList.add('sr-only');
                pmChangeWrapEl.textContent = `Troco: ${formatReal(0)}`;
              }

              modal.dataset.total = String(Number(total).toFixed(2));
              modal.dataset.tax = String(Number(tax).toFixed(2));

              // foco no input de recebido se for dinheiro (pequeno delay para acessibilidade)
              setTimeout(() => {
                if (pmMethod && pmMethod.value === 'dinheiro' && pmPaid) pmPaid.focus();
              }, 80);
            }

            function closePaymentModal() {
              if (!modal) return;
              modal.setAttribute('data-open', 'false');
              modal.setAttribute('aria-hidden', 'true');
              if (lastFocused) lastFocused.focus();
            }

            /* expose global stubs & real functions */
            window.openPaymentModal = window.openPaymentModal || function (opts) {
              // se chamado antes da inicialização do script, guardamos pendente
              window.__pending_open = opts === undefined ? true : opts;
              console.warn('[PAYMENT-STUB] openPaymentModal chamado antes da inicialização; pendente.');
            };
            window.closePaymentModal = window.closePaymentModal || function () {
              window.__pending_close = true;
              console.warn('[PAYMENT-STUB] closePaymentModal chamado antes da inicialização; pendente.');
            };
            // expõe as funções reais
            window.openPaymentModal = (opts) => openPaymentModal(opts);
            window.closePaymentModal = () => closePaymentModal();
            // processa pendentes
            if (window.__pending_open) {
              const pending = window.__pending_open === true ? undefined : window.__pending_open;
              openPaymentModal(pending);
              window.__pending_open = null;
            }
            if (window.__pending_close) {
              closePaymentModal();
              window.__pending_close = null;
            }

            // botão pay
            if (btnPay) btnPay.addEventListener('click', e => { e.preventDefault(); openPaymentModal(collectTotals()); });

            /* ---------- eventos método de pagamento ---------- */
            if (pmMethod) {
              pmMethod.addEventListener('change', () => {
                if (pmMethod.value === 'dinheiro') {
                  if (paidField) paidField.style.display = '';
                  // focus no input
                  setTimeout(() => pmPaid && pmPaid.focus(), 40);
                } else {
                  if (paidField) paidField.style.display = 'none';
                  if (pmChangeWrapEl) pmChangeWrapEl.classList.add('sr-only');
                }
              });
            }

            /* ---------- show change (troco) while typing ---------- */
            if (pmPaid && pmChangeWrapEl && modal) {
              pmPaid.addEventListener('input', () => {
                const paid = parseBR(pmPaid.value || '0');
                const total = parseFloat(modal.dataset.total || '0') || 0;
                const change = paid - total;
                if (change > 0.001) {
                  pmChangeWrapEl.textContent = `Troco: ${formatReal(change)}`;
                  pmChangeWrapEl.classList.remove('sr-only');
                } else {
                  pmChangeWrapEl.textContent = `Troco: ${formatReal(0)}`;
                  pmChangeWrapEl.classList.add('sr-only');
                }
              });
            }

            /* ---------- confirmar / cancelar ---------- */
            if (pmCancel) pmCancel.addEventListener('click', () => closePaymentModal());
            if (pmCloseTop) pmCloseTop.addEventListener('click', () => closePaymentModal());
            if (backdrop) backdrop.addEventListener('click', e => {
              // fecha somente se o click aconteceu no backdrop (fora do painel)
              if (e.target === backdrop || e.target.closest('.modal-backdrop')) closePaymentModal();
            });
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

            /* ---------- drag modal (visual) - opcional ---------- */
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
              const stopDrag = () => { dragging = false; };
              document.addEventListener('pointerup', stopDrag);
              document.addEventListener('pointercancel', stopDrag);
            }

            /* ---------- keyboard ---------- */
            document.addEventListener('keydown', e => {
              if (e.key === 'Escape') closePaymentModal();
              if (e.key === 'Enter' && modal && modal.getAttribute('data-open') === 'true') {
                // evita disparar quando foco está em campos de input que precisam de Enter
                const active = document.activeElement;
                const isInput = active && (active.tagName === 'INPUT' || active.tagName === 'TEXTAREA' || active.isContentEditable);
                if (!isInput) pmConfirm && pmConfirm.click();
              }
            });

            // inicializa totais
            updateTotals();

            // Search & Category filtering
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

            /* ---------- restore sidebar state (localStorage) ---------- */
            try {
              const sb = document.getElementById('sidebar');
              if (sb && localStorage.getItem('sidebarCollapsed') === 'true') {
                sb.classList.add('collapsed');
              }
            } catch (e) { /* no-op */ }

          } catch (err) {
            console.error('Erro inicializando payment script:', err);
          }
        }); // DOMContentLoaded end

        // Toggle sidebar (global para onclick inline)
        window.toggleSidebar = function toggleSidebar() {
          const sb = document.getElementById('sidebar');
          if (!sb) return;
          sb.classList.toggle('collapsed');
          try { localStorage.setItem('sidebarCollapsed', sb.classList.contains('collapsed')); } catch (e) { /* no-op */ }
        };

      })(); // IIFE end
    </script>

</body>

</html>