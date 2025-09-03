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
    /* Modern dark-blue theme for your page
   Replace the existing <style> contents with this CSS.
   Palette: deep navy + teal accent, modern cards, smooth shadows.
*/

    /* ---------- Tokens / Palette ---------- */
    :root {
      --navy-900: #071229;
      --navy-800: #0d1b2a;
      --navy-700: #153044;
      --teal-500: #2aa19a;
      --teal-600: #238678;
      --teal-700: #1d6a60;
      --muted: #9aa6b2;
      --card-bg: #0f1720;
      /* slightly lighter than sidebar */
      --panel-bg: rgba(255, 255, 255, 0.03);
      --glass: rgba(255, 255, 255, 0.04);
      --accent-glow: 0 8px 30px rgba(36, 161, 154, 0.12);
      --soft-shadow: 0 8px 18px rgba(2, 6, 23, 0.45);
      --radius: 12px;
      --radius-sm: 8px;
      --text-light: #e6eef2;
      --text-mid: #cddbe3;
      --text-dark: #dbe9ef;
      --transition: 220ms cubic-bezier(.2, .9, .2, 1);
    }

    /* ---------- Reset & base ---------- */
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0
    }

    html,
    body {
      height: 100%
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: rgb(59, 75, 93);
      color: var(--text-light);
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
      display: flex;
    }

    /* anchors/buttons */
    a {
      color: inherit;
      text-decoration: none
    }

    button {
      font-family: inherit;
      cursor: pointer
    }

    /* ---------- Layout ---------- */
    .container {
      display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 1rem;
      padding: 1rem;
      max-width: 1200px;
      margin: 18px auto;
    }

    /* ---------- Product panel / controls ---------- */
    .product-panel {
      display: flex;
      flex-direction: column;
      gap: 1rem
    }

    .controls {
      display: flex;
      gap: .5rem;
      align-items: center
    }

    .controls input[type="text"] {
      flex: 1;
      padding: .6rem .75rem;
      border-radius: 10px;
      border: 1px solid rgba(255, 255, 255, 0.06);
      background: rgba(255, 255, 255, 0.02);
      color: var(--text-light);
      outline: none;
      transition: box-shadow var(--transition), border var(--transition);
    }

    .controls input[type="text"]:focus {
      box-shadow: var(--accent-glow);
      border-color: var(--teal-500);
    }

    /* select/buttons in controls */
    .controls select,
    .controls button {
      padding: .55rem .75rem;
      border-radius: 10px;
      border: 1px solid rgba(255, 255, 255, 0.06);
      background: linear-gradient(180deg, rgba(255, 255, 255, 0.02), rgba(255, 255, 255, 0.01));
      color: var(--text-light);
    }

    /* ---------- Category HUD ---------- */
    .category-hud {
      display: flex;
      gap: .5rem;
      padding: .5rem 0;
      overflow-x: auto;
    }

    .category-hud button {
      padding: .45rem .9rem;
      border-radius: 999px;
      border: 1px solid rgba(255, 255, 255, 0.06);
      background: transparent;
      color: var(--text-mid);
      white-space: nowrap;
      font-weight: 700;
      transition: background var(--transition), color var(--transition), transform var(--transition);
    }

    .category-hud button:hover {
      transform: translateY(-3px)
    }

    .category-hud button.active {
      background: linear-gradient(180deg, var(--teal-500), var(--teal-600));
      color: white;
      box-shadow: var(--accent-glow);
    }

    /* ---------- Product grid & cards ---------- */
    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
      gap: 12px;
    }

    .card {
      background: linear-gradient(180deg, rgba(255, 255, 255, 0.02), rgba(255, 255, 255, 0.01));
      border-radius: 12px;
      overflow: hidden;
      box-shadow: var(--soft-shadow);
      display: flex;
      flex-direction: column;
      transition: transform var(--transition), box-shadow var(--transition);
      border: 1px solid rgba(255, 255, 255, 0.03);
    }

    .card:hover {
      transform: translateY(-6px);
      box-shadow: 0 18px 40px rgba(0, 0, 0, 0.6)
    }

    .card img {
      width: 100%;
      height: 100px;
      object-fit: cover;
      display: block;
      background: #071226
    }

    .card .info {
      padding: .6rem;
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: space-between
    }

    .card .info h4 {
      font-size: .95rem;
      color: var(--text-light);
      margin-bottom: .4rem;
    }

    .card .info .price {
      font-weight: 800;
      color: var(--teal-500)
    }

    /* info-footer with price left and add button right */
    .card .info-footer {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px
    }

    .add-to-cart {
      background: linear-gradient(180deg, var(--teal-500), var(--teal-600));
      color: white;
      border: none;
      padding: .25rem .55rem;
      border-radius: 8px;
      font-weight: 800;
      box-shadow: var(--accent-glow);
      transition: transform var(--transition);
    }

    .add-to-cart:hover {
      transform: translateY(-3px)
    }

    /* ---------- Sidebar (left) - fixed navigation ---------- */
    .sidebar {
      width: 240px;
      background: linear-gradient(180deg, var(--navy-900), var(--navy-800));
      height: 100vh;
      position: fixed;
      left: 0;
      top: 0;
      display: flex;
      flex-direction: column;
      padding-top: 18px;
      gap: 8px;
      box-shadow: 6px 0 30px rgba(0, 0, 0, 0.55);
      z-index: 60;
      transition: width 300ms ease;
    }

    .sidebar.collapsed {
      width: 64px
    }

    /* logo area */
    .sidebar .logo {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 8px 12px 12px
    }

    .sidebar .logo img {
      max-width: 140px;
      height: auto
    }

    /* nav links */
    .sidebar a {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 14px;
      color: var(--text-light);
      margin: 6px 10px;
      border-radius: 8px;
      transition: background var(--transition), transform var(--transition)
    }

    .sidebar a .icon {
      font-size: 20px;
      display: inline-flex;
      align-items: center;
      justify-content: center
    }

    .sidebar a .text {
      font-weight: 700;
      white-space: nowrap;
      color: var(--text-mid)
    }

    .sidebar a:hover {
      background: rgba(255, 255, 255, 0.03);
      transform: translateY(-3px);
      box-shadow: 0 8px 18px rgba(0, 0, 0, 0.45);
      border-left: 4px solid var(--teal-600);
      padding-left: 10px
    }

    .sidebar.collapsed .text {
      display: none
    }

    .sidebar.collapsed .icon {
      margin-right: 0;
      justify-content: center;
      width: 100%
    }

    /* toggle button */
    .toggle-btn {
      cursor: pointer;
      text-align: center;
      margin-bottom: 6px;
      font-size: 20px;
      color: var(--text-light);
      padding: 10px 14px
    }

    /* logout area */
    .sidebar .logout {
      margin-top: auto;
      padding: 12px;
      display: flex;
      justify-content: center
    }

    .sidebar .logout button {
      background: transparent;
      border: 2px solid rgba(255, 255, 255, 0.06);
      color: var(--text-light);
      padding: 8px 12px;
      border-radius: 10px;
    }

    .sidebar .logout button:hover {
      background: rgba(255, 255, 255, 0.03);
      transform: translateY(-3px)
    }

    /* ---------- Main content area (to the right of sidebar) ---------- */
    .main-content {
      margin-left: 240px;
      padding: 18px;
      width: calc(100% - 240px);
      transition: margin-left 300ms ease;
    }

    .sidebar.collapsed~.main-content {
      margin-left: 64px;
      width: calc(100% - 64px)
    }

    /* header (for pages that include search at top) */
    .page-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      margin-bottom: 12px
    }

    .page-header h1 {
      font-size: 20px;
      font-weight: 800;
      color: var(--text-light);
      padding: 10px 14px;
      border-radius: 10px;
      background: linear-gradient(90deg, rgba(255, 255, 255, 0.02), rgba(255, 255, 255, 0.01))
    }

    /* search container style (if used standalone) */
    .search-container {
      display: flex;
      align-items: center;
      gap: 8px;
      background: var(--panel-bg);
      padding: 8px 10px;
      border-radius: 999px;
      box-shadow: 0 8px 18px rgba(0, 0, 0, 0.45);
      max-width: 520px;
      width: 100%
    }

    .search-container input {
      border: none;
      outline: none;
      background: transparent;
      color: var(--text-light);
      padding: 6px;
      width: 100%
    }

    .search-container button {
      background: linear-gradient(180deg, var(--teal-500), var(--teal-600));
      border: none;
      color: #fff;
      padding: 8px 12px;
      border-radius: 999px;
      font-weight: 800
    }

    /* ---------- Cart sidebar (right column) ---------- */
    .cart-sidebar {
      background: linear-gradient(180deg, rgba(255, 255, 255, 0.02), rgba(255, 255, 255, 0.01));
      border-radius: 12px;
      padding: 14px;
      box-shadow: var(--soft-shadow);
      align-self: start;
    }

    .cart-sidebar h3 {
      color: var(--text-light);
      margin-bottom: 10px
    }

    /* cart items */
    .cart-items {
      display: flex;
      flex-direction: column;
      gap: 10px;
      max-height: 44vh;
      overflow: auto;
      padding-right: 6px
    }

    .cart-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px
    }

    .cart-item .item-info {
      display: flex;
      gap: 8px;
      align-items: center;
      min-width: 0
    }

    .cart-item .item-name {
      font-weight: 700;
      color: var(--text-mid);
      max-width: 140px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap
    }

    .cart-item .item-subtotal {
      font-weight: 800;
      color: var(--teal-500)
    }

    /* qty controls */
    .qty-btn {
      background: transparent;
      border: 1px solid rgba(255, 255, 255, 0.04);
      color: var(--text-light);
      border-radius: 8px;
      width: 30px;
      height: 30px;
      display: inline-grid;
      place-items: center
    }

    .qty {
      min-width: 36px;
      text-align: center;
      font-weight: 700
    }

    /* totals and pay button */
    .totals {
      margin-top: 10px;
      border-top: 1px dashed rgba(255, 255, 255, 0.03);
      padding-top: 12px
    }

    .totals div {
      display: flex;
      justify-content: space-between;
      color: var(--muted);
      margin-bottom: 8px
    }

    .totals .row.total {
      font-weight: 900;
      color: var(--text-light);
      font-size: 1.05rem
    }

    .btn-pay {
      display: block;
      width: 100%;
      padding: 10px;
      border-radius: 10px;
      background: linear-gradient(180deg, var(--teal-500), var(--teal-600));
      color: #fff;
      font-weight: 900;
      border: none;
      box-shadow: var(--accent-glow)
    }

    /* ---------- Modal (payment) ---------- */
    .modal-root {
      position: fixed;
      inset: 0;
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 140;
      pointer-events: none
    }

    /* show modal root when data-open is true */
    .modal-root[data-open="true"] {
      display: flex;
      pointer-events: auto;
    }

    .modal-backdrop {
      position: absolute;
      inset: 0;
      background: rgba(0, 0, 0, 0.5);
      backdrop-filter: blur(3px);
      opacity: 0;
      transition: opacity 240ms ease
    }

    .modal-panel {
      position: relative;
      width: min(760px, 96%);
      background: rgba(8, 15, 22, 0.9);
      border-radius: 12px;
      box-shadow: var(--soft-shadow);
      transform: translateY(12px) scale(.98);
      opacity: 0;
      transition: all 260ms cubic-bezier(.2, .9, .2, 1);
      pointer-events: auto;
      border: 1px solid rgba(255, 255, 255, 0.03)
    }

    .modal-root[data-open="true"] .modal-backdrop {
      opacity: 1;
      pointer-events: auto
    }

    .modal-root[data-open="true"] .modal-panel {
      transform: translateY(0) scale(1);
      opacity: 1;
      pointer-events: auto
    }

    /* modal header */
    .modal-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      padding: 18px 20px;
      background: linear-gradient(90deg, var(--teal-500), var(--teal-600));
      color: #fff
    }

    .modal-header .left {
      display: flex;
      gap: 12px;
      align-items: center
    }

    .modal-header .icon {
      width: 44px;
      height: 44px;
      border-radius: 10px;
      display: grid;
      place-items: center;
      background: rgba(255, 255, 255, 0.08)
    }

    /* modal body */
    .modal-body {
      display: grid;
      grid-template-columns: 1fr 320px;
      gap: 18px;
      padding: 18px;
      background: linear-gradient(180deg, rgba(255, 255, 255, 0.02), rgba(255, 255, 255, 0.01))
    }

    .field label {
      display: block;
      font-weight: 700;
      margin-bottom: 6px;
      color: var(--text-light)
    }

    select,
    input {
      width: 100%;
      padding: 12px 14px;
      border-radius: 10px;
      border: 1px solid rgba(255, 255, 255, 0.03);
      background: rgba(255, 255, 255, 0.02);
      color: var(--text-light)
    }

    /* items list */
    .items-list {
      display: flex;
      flex-direction: column;
      gap: 10px;
      max-height: 300px;
      overflow: auto;
      padding-right: 6px
    }

    .item-row {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 10px;
      border-radius: 10px;
      background: linear-gradient(180deg, rgba(255, 255, 255, 0.02), rgba(255, 255, 255, 0.01));
      border: 1px solid rgba(255, 255, 255, 0.02)
    }

    .item-avatar {
      width: 44px;
      height: 44px;
      border-radius: 10px;
      display: grid;
      place-items: center;
      color: var(--teal-600);
      background: rgba(36, 161, 154, 0.06);
      font-weight: 800
    }

    /* right summary */
    .right {
      background: linear-gradient(180deg, rgba(255, 255, 255, 0.02), rgba(255, 255, 255, 0.01));
      border-radius: 10px;
      padding: 14px;
      border: 1px solid rgba(255, 255, 255, 0.02)
    }

    .summary-row {
      display: flex;
      justify-content: space-between;
      color: var(--muted);
      padding: 6px 0
    }

    .summary-row.total {
      font-weight: 900;
      color: var(--text-light);
      font-size: 1.05rem
    }

    /* modal footer */
    .modal-footer {
      padding: 14px 20px;
      border-top: 1px solid rgba(255, 255, 255, 0.02);
      display: flex;
      justify-content: space-between;
      gap: 10px
    }

    .btn {
      padding: 10px 14px;
      border-radius: 10px;
      font-weight: 800;
      border: none
    }

    .btn-ghost {
      background: transparent;
      color: var(--muted)
    }

    .btn-primary {
      background: linear-gradient(180deg, var(--teal-500), var(--teal-600));
      color: #fff;
      box-shadow: var(--accent-glow)
    }

    /* change (troco) */
    #pmChangeWrap {
      margin-top: 8px;
      font-weight: 800;
      color: var(--teal-600)
    }

    /* ---------- Table styles used in other pages (keeps consistent look) ---------- */
    .table-wrap {
      overflow-x: auto;
      padding-top: 6px
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background: rgba(255, 255, 255, 0.02);
      border-radius: 10px;
      overflow: hidden;
      box-shadow: var(--soft-shadow)
    }

    thead {
      background: linear-gradient(180deg, var(--navy-700), var(--navy-800));
      color: var(--text-light)
    }

    thead th {
      padding: 12px 10px;
      text-align: left;
      font-weight: 800;
      font-size: 14px
    }

    tbody td {
      padding: 12px 10px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.02);
      color: var(--text-mid)
    }

    tbody tr:nth-child(even) {
      background: rgba(255, 255, 255, 0.01)
    }

    tbody tr:hover {
      background: rgba(255, 255, 255, 0.02)
    }

    /* action buttons */
    .icon-btn {
      display: inline-grid;
      place-items: center;
      width: 40px;
      height: 40px;
      border-radius: 10px;
      border: none;
      margin: 0 6px;
      transition: transform var(--transition)
    }

    .icon-btn.edit-btn {
      background: linear-gradient(180deg, #0e86bf, #0b6c9a);
      color: #fff;
      box-shadow: 0 10px 30px rgba(6, 86, 130, 0.12)
    }

    .icon-btn.delete-btn {
      background: linear-gradient(180deg, #e64545, #c73030);
      color: #fff;
      box-shadow: 0 10px 30px rgba(231, 69, 69, 0.12)
    }

    .icon-btn:hover {
      transform: translateY(-4px)
    }

    /* ---------- Toast ---------- */
    .toast {
      position: fixed;
      right: 18px;
      bottom: 18px;
      background: rgba(0, 0, 0, 0.7);
      color: #fff;
      padding: 10px 14px;
      border-radius: 10px;
      box-shadow: 0 8px 28px rgba(0, 0, 0, 0.6);
      opacity: 0;
      transform: translateY(12px);
      transition: all 240ms ease;
      z-index: 9999
    }

    .toast.show {
      opacity: 1;
      transform: none
    }

    /* ---------- Utilities & responsiveness ---------- */
    .hidden {
      display: none !important
    }

    @media (max-width: 1100px) {
      .container {
        max-width: 940px
      }

      .grid {
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr))
      }

      .modal-body {
        grid-template-columns: 1fr 280px
      }
    }

    @media (max-width: 820px) {
      .container {
        grid-template-columns: 1fr;
        padding: 12px
      }

      .sidebar {
        position: fixed;
        z-index: 70
      }

      .main-content {
        margin-left: 64px;
        width: calc(100% - 64px)
      }

      .modal-body {
        grid-template-columns: 1fr
      }

      .cart-sidebar {
        order: 2
      }
    }

    @media (max-width:520px) {
      table thead {
        display: none
      }

      tbody td {
        display: block;
        text-align: right;
        padding: 10px
      }

      tbody td::before {
        content: attr(data-label);
        float: left;
        font-weight: 700;
        color: var(--muted)
      }

      .sidebar {
        display: none
      }

      /* small screens: you might want a hamburger to toggle */
    }

    /* small fade-in helper */
    .fade-in {
      animation: fadeIn .36s cubic-bezier(.2, .9, .2, 1) both
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(6px)
      }

      to {
        opacity: 1;
        transform: none
      }
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

    <a href="#" id="linkPayment">
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

            // linkPayment (sidebar)
            const linkPayment = document.getElementById('linkPayment');
            if (linkPayment) {
              linkPayment.addEventListener('click', e => {
                e.preventDefault();
                openPaymentModal(collectTotals());
              });
            }

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
            if (categoryHud) {
              const catButtons = Array.from(categoryHud.querySelectorAll('button[data-cat]'));
              catButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                  catButtons.forEach(b => b.classList.remove('active'));
                  btn.classList.add('active');
                  filterProducts();
                });
              });
            }

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
