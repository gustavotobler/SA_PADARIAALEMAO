<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8"> <!-- Define a codificação de caracteres para UTF-8 -->
  <title>Produtos</title> <!-- Título que aparece na aba do navegador -->
  
  <!-- Importa os ícones do Google Material Icons -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  
  <!-- Importa o arquivo CSS para estilizar a página -->
  <link rel="stylesheet" href="css/site3.css">
</head>
<body>
  <header>
    <div class="topo"> <!-- Barra superior da página -->
      <div class="topo-left"> <!-- Área esquerda da barra superior -->
        <a href="inicial2.html"> <!-- Link que leva para a página inicial (ou outra) -->
          <!-- Ícone de seta para voltar -->
          <span class="material-icons icon" title="Voltar">arrow_back</span>
        </a>
      </div>

      <div class="topo-center"> <!-- Área central da barra superior -->
        <h1>PRODUTOS</h1> <!-- Título da página -->

        <div class="search-container"> <!-- Container da pesquisa -->
          <!-- Ícone de lupa -->
          <span class="material-icons">search</span>

          <!-- Campo de texto para digitar a pesquisa -->
          <input type="text" id="search-input" placeholder="Pesquisar...">

          <!-- Botão para iniciar a pesquisa -->
          <button id="search-btn" type="button">Pesquisar</button>
        </div>
      </div>

      <div class="topo-right"> <!-- Área direita da barra superior -->
        <!-- Está vazio para manter o espaçamento e o layout alinhado -->
      </div>
    </div>
  </header>

  <main>
    <table>
      <thead>
        <tr>
          <!-- Cabeçalho da tabela com os nomes das colunas -->
          <th>ID FORNECEDOR</th>
          <th>ID</th>
          <th>Nome do produto</th>
          <th>Preço</th>
          <th>Quantidade</th>
        </tr>
      </thead>
      <tbody id="supplier-table-body">
        <!-- Linhas com os dados dos produtos -->
        <tr><td>Serviço próprio</td><td>P1</td><td>Pão Francês</td><td>R$ 0,50</td><td>100/Por dia</td></tr>
        <tr><td>Serviço próprio</td><td>P2</td><td>Pão integral</td><td>R$ 6,00</td><td>62</td></tr>
        <tr><td>FR02</td><td>P3</td><td>Bolo de Chocolate</td><td>R$ 12,00</td><td>15</td></tr>
        <tr><td>FR02</td><td>P4</td><td>Bolo de morango</td><td>R$ 14,00</td><td>35</td></tr>
        <tr><td>FR02</td><td>P5</td><td>Bolo de abacaxi</td><td>R$ 15,00</td><td>27</td></tr>
        <tr><td>Serviço próprio</td><td>P6</td><td>Cuca de banana</td><td>R$ 10,00</td><td>40</td></tr>
        <tr><td>Serviço próprio</td><td>P7</td><td>Rosca doce</td><td>R$ 5,00</td><td>53</td></tr>
        <tr><td>Serviço próprio</td><td>P8</td><td>Orelha de gato</td><td>R$ 19,00/KG</td><td>20</td></tr>
      </tbody>
    </table>
  </main>

  <script>
    // Aguarda o carregamento completo do conteúdo HTML
    document.addEventListener('DOMContentLoaded', () => {
      const tableBody = document.getElementById('supplier-table-body'); // Pega o corpo da tabela

      // Função que realiza a pesquisa filtrando as linhas da tabela
      function doSearch() {
        const term = document.getElementById('search-input').value.toLowerCase(); // Pega o texto da busca e converte para minúsculas

        // Converte as linhas da tabela para array e percorre cada uma
        Array.from(tableBody.rows).forEach(row => {
          // Verifica se alguma célula da linha contém o termo pesquisado (ignorando maiúsculas/minúsculas)
          const match = Array.from(row.cells).some(td => 
            td.textContent.toLowerCase().includes(term)
          );

          // Se encontrou o termo, exibe a linha, senão esconde
          row.style.display = match ? '' : 'none';
        });
      }

      // Quando clicar no botão de pesquisar, chama a função doSearch
      document.getElementById('search-btn').addEventListener('click', doSearch);

      // Quando digitar no campo de pesquisa, chama a função doSearch em tempo real
      document.getElementById('search-input').addEventListener('input', doSearch);
    });
  </script>
</body>
</html>
