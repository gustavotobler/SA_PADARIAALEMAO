<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Dashboard de Vendas</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --sidebar-bg: #2e2e2e;
            --primary-text: #fff;
            --hover-bg: #444;
            --main-bg: #fcf6eb;
            --card-bg: #fff;
            --accent: #3f3f3f;
            --highlight: #e0f7ff;
        }
        * { 
          box-sizing: border-box; 
        }
        body { 
             margin: 0; 
             font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
             background-color: var(--main-bg); 
             color: #333; 
             display: flex; 
        }
        .sidebar { 
                 width: 240px; 
                 background-color: var(--sidebar-bg); 
                 height: 100vh; position: fixed; display: flex; 
                 flex-direction: column; 
                 padding-top: 20px; 
        }
        .sidebar h2 { 
                    color: var(--primary-text); 
                    text-align: center; 
                    margin-bottom: 30px; 
        }
        .sidebar a { 
                   color: var(--primary-text); 
                   padding: 15px 20px; 
                   text-decoration: none; 
                   transition: background 0.3s; 
        }
        .sidebar a:hover { 
                         background-color: var(--hover-bg); 
                        }
        .main-content { 
                      margin-left: 240px; 
                      padding: 30px; width: 100%; 
        }
        h1, h2 { 
               text-align: center; 
               margin-bottom: 20px; 
        }
        #filters { 
                 display: flex; 
                 flex-wrap: wrap; 
                 justify-content: center; 
                 gap: 10px; margin-bottom: 20px; 
        }
        #filters .filter-group { 
                               display: flex; 
                               flex-direction: column; 
                               align-items: start; 
        }
        #filters label { 
                       font-size: 12px; 
                       margin-left: 2px; 
        }
        #filters input { 
                       padding: 8px 12px; 
                       border: 1px solid #ccc; 
                       border-radius: 4px; 
        }
        table { 
              width: 100%; 
              border-collapse: collapse; 
              background-color: var(--card-bg); 
              box-shadow: 0 4px 16px rgba(0,0,0,0.1); 
              border-radius: 12px; 
              overflow: hidden; 
        }
        caption { 
                caption-side: top; 
                text-align: left; 
                font-weight: bold; 
                padding: 10px; 
        }
        th, td { 
               padding: 12px 15px; 
               text-align: center; 
               border-bottom: 1px solid #eee; 
        }
        th { 
           background-color: var(--accent); 
           color: var(--primary-text); 
        }
        th[scope="col"], td[scope="row"] 
        { 
        text-align: left; 
        }
        tr:nth-child(even) 
        { 
        background-color: #f9f9f9; 
        }
        tr:hover { 
                 background-color: var(--highlight); 
        }
        .chart-section, .sheet-section { 
                                       margin: 40px auto; 
                                       background: var(--card-bg); 
                                       padding: 20px; 
                                       border-radius: 12px; 
                                       box-shadow: 0 6px 12px rgba(0,0,0,0.15); 
                                       max-width: 700px; 
        }
        #grafico-pagamento canvas { 
                                  max-height: 325px; 
        }
        canvas { 
               max-width: 100%; 
               height: auto; 
        }
        .section { 
                 display: none; 
        }
        .section.active { 
                        display: block; 
        }
        .pagination { 
                    display: flex; 
                    justify-content: center; 
                    align-items: center; 
                    gap: 10px; 
                    margin-top: 15px; 
        }
        .pagination button { 
                           padding: 8px 12px; 
                           border: none; 
                           background-color: var(--accent); 
                           color: var(--primary-text); 
                           border-radius: 4px; 
                           cursor: pointer; 
        }
        .pagination button:disabled { 
                                    background-color: #999; 
                                    cursor: default; 
        }
        .back-button { 
                     display: flex; 
                     align-items: center; 
                     justify-content: center; 
                     padding: 10px 20px; 
                     margin: 10px; 
                     background-color: var(--accent); 
                     color: var(--primary-text); 
                     border-radius: 8px; 
                     text-decoration: none; 
                     font-size: 16px; 
                     font-weight: 500; 
                     transition: background-color 0.2s, transform 0.1s; 
        }
        .back-button .icon { 
                           margin-right: 8px; 
                           font-size: 18px; 
        }
        .back-button:hover { 
                           background-color: #555; 
                           transform: translateY(-1px); 
        }
        .back-button:active { 
                            background-color: #333; 
                            transform: translateY(0); 
        }
    </style>
</head>
<body>

<nav class="sidebar" aria-label="Menu de navegação">
    <a href="inicial2.php">
        <span class="material-icons icon" title="Voltar">arrow_back</span>
      </a>
    <a href="#" onclick="showSection('tabela')" aria-controls="tabela">📋 Tabela de Vendas</a>
    <a href="#" onclick="showSection('grafico-produto')" aria-controls="grafico-produto">📦 Gráf. Produto</a>
    <a href="#" onclick="showSection('grafico-pagamento')" aria-controls="grafico-pagamento">💳 Gráf. Pagamento</a>
    <a href="#" onclick="showSection('grafico-funcionario')" aria-controls="grafico-funcionario">👨‍💼 Gráf. Funcionário</a>
    <a href="#" onclick="showSection('grafico-dia')" aria-controls="grafico-dia">📅 Gráf. Total Vendido</a>
</nav>
    
<main class="main-content">
    <section id="tabela" class="section active" aria-labelledby="titulo-tabela">
        <h1 id="titulo-tabela">Relatório de Vendas</h1>
        <div id="filters" role="region" aria-label="Filtros de busca">
            <div class="filter-group">
                <label for="startDate">📅 Data Inicial</label>
                <input type="date" id="startDate" aria-labelledby="startDate">
            </div>
            <div class="filter-group">
                <label for="endDate">📅 Data Final</label>
                <input type="date" id="endDate" aria-labelledby="endDate">
            </div>
            <div class="filter-group">
                <label for="search">🔍 Produto</label>
                <input type="text" id="search" placeholder="Pesquisar por nome do Produto..." aria-labelledby="search">
            </div>
        </div>
        <table aria-describedby="descr-tabela">
            <thead>
                <tr>
                    <th scope="col">ID Venda</th>
                    <th scope="col">Data</th>
                    <th scope="col">Nome Funcionário</th>
                    <th scope="col">Nome Produto</th>
                    <th scope="col">Quantidade</th>
                    <th scope="col">Preço Unitário</th>
                    <th scope="col">Total</th>
                    <th scope="col">Pagamento</th>
                </tr>
            </thead>
            <tbody>
<tr><td>1</td><td>2025-01-03</td><td>Sergio Luiz<td>Pão Francês</td><td>5</td><td>15.5</td><td>77.5</td><td>Dinheiro</td></tr>
<tr><td>2</td><td>2025-01-03</td><td>Peter Parker</td><td>Coxinha</td><td>4</td><td>8.0</td><td>32</td><td>Cartão</td></tr>
<tr><td>3</td><td>2025-01-15</td><td>Sergio Luiz</td><td>Pastel</td><td>3</td><td>10.0</td><td>30</td><td>PIX</td></tr>
<tr><td>4</td><td>2025-01-15</td><td>Mbappe</td><td>Rosca</td><td>2</td><td>30.0</td><td>60</td><td>Dinheiro</td></tr>
<tr><td>5</td><td>2025-02-05</td><td>Mbappe</td><td>Pão Fatiado</td><td>6</td><td>10.0</td><td>60</td><td>Cartão</td></tr>
<tr><td>6</td><td>2025-02-05</td><td>Matuê</td><td>Chineque</td><td>3</td><td>10.0</td><td>30</td><td>PIX</td></tr>
<tr><td>7</td><td>2025-02-20</td><td>Sergio Luiz</td><td>Cuca</td><td>4</td><td>12.0</td><td>48</td><td>PIX</td></tr>
<tr><td>8</td><td>2025-02-20</td><td>Tony Stark</td><td>Pão Francês</td><td>7</td><td>15.5</td><td>108.5</td><td>Cartão</td></tr>
<tr><td>9</td><td>2025-03-01</td><td>Tony Stark</td><td>Pastel</td><td>2</td><td>10.0</td><td>20</td><td>PIX</td></tr>
<tr><td>10</td><td>2025-03-01</td><td>Kerry King</td><td>Pão Fatiado</td><td>8</td><td>10.0</td><td>80</td><td>Dinheiro</td></tr>
<tr><td>11</td><td>2025-03-18</td><td>Tim Maia</td><td>Coxinha</td><td>5</td><td>8.0</td><td>40</td><td>Cartão</td></tr>
<tr><td>12</td><td>2025-03-18</td><td>Kerry King</td><td>Chineque</td><td>3</td><td>10.0</td><td>30</td><td>PIX</td></tr>
<tr><td>13</td><td>2025-04-02</td><td>Sergio Luiz</td><td>Cuca</td><td>2</td><td>12.0</td><td>24</td><td>PIX</td></tr>
<tr><td>14</td><td>2025-04-06</td><td>Tony Stark</td><td>Chineque</td><td>4</td><td>10.0</td><td>40</td><td>Cartão</td></tr>
<tr><td>15</td><td>2025-04-07</td><td>Kerry King</td><td>Cuca</td><td>5</td><td>12.0</td><td>60</td><td>PIX</td></tr>
<tr><td>16</td><td>2025-04-10</td><td>Peter Parker</td><td>Pão Fatiado</td><td>1</td><td>10.0</td><td>10</td><td>Dinheiro</td></tr>
<tr><td>17</td><td>2025-04-12</td><td>Peter Parker</td><td>Rosca</td><td>3</td><td>30.0</td><td>90</td><td>Cartão</td></tr>
<tr><td>18</td><td>2025-04-17</td><td>Mbappe</td><td>Pastel</td><td>4</td><td>10.0</td><td>40</td><td>PIX</td></tr>
<tr><td>19</td><td>2025-04-17</td><td>Tim Maia</td><td>Cuca</td><td>2</td><td>12.0</td><td>24</td><td>PIX</td></tr>
<tr><td>20</td><td>2025-04-19</td><td>Tim Maia</td><td>Pão Francês</td><td>7</td><td>7.0</td><td>49</td><td>PIX</td></tr>
<tr><td>21</td><td>2025-04-27</td><td>Sergio Luiz</td><td>Pastel</td><td>2</td><td>10.0</td><td>20</td><td>Dinheiro</td></tr>
<tr><td>22</td><td>2025-04-29</td><td>Tim Maia</td><td>Pão Francês</td><td>7</td><td>7.0</td><td>49</td><td>PIX</td></tr>
<tr><td>23</td><td>2025-05-02</td><td>Sergio Luiz</td><td>Pão Fatiado</td><td>7</td><td>10.0</td><td>70</td><td>Cartão</td></tr>
<tr><td>24</td><td>2025-05-02</td><td>Kerry King</td><td>Chineque</td><td>5</td><td>10.0</td><td>50</td><td>PIX</td></tr>
<tr><td>25</td><td>2025-05-09</td><td>Peter Parker</td><td>Chineque</td><td>1</td><td>10.0</td><td>10</td><td>PIX</td></tr>
<tr><td>26</td><td>2025-05-10</td><td>Matuê</td><td>Pão Fatiado</td><td>6</td><td>10.0</td><td>60</td><td>Dinheiro</td></tr>
<tr><td>27</td><td>2025-05-12</td><td>Matuê</td><td>Pão Fatiado</td><td>3</td><td>10.0</td><td>30</td><td>Cartão</td></tr>
<tr><td>28</td><td>2025-05-14</td><td>Mbappe</td><td>Coxinha</td><td>6</td><td>8.0</td><td>48</td><td>Dinheiro</td></tr>
<tr><td>29</td><td>2025-05-14</td><td>Tony Stark</td><td>Rosca</td><td>3</td><td>30.0</td><td>90</td><td>Cartão</td></tr>
<tr><td>30</td><td>2025-06-08</td><td>Tony Stark</td><td>Pastel</td><td>2</td><td>10.0</td><td>20</td><td>PIX</td></tr>
<tr><td>31</td><td>2025-06-08</td><td>Sergio Luiz</td><td>Pão Francês</td><td>9</td><td>15.5</td><td>139.5</td><td>Cartão</td></tr>
<tr><td>32</td><td>2025-06-12</td><td>Sergio Luiz</td><td>Coxinha</td><td>4</td><td>8.0</td><td>32</td><td>Cartão</td></tr>
<tr><td>33</td><td>2025-06-15</td><td>Mbappe</td><td>Cuca</td><td>1</td><td>12.0</td><td>12</td><td>PIX</td></tr>
<tr><td>34</td><td>2025-06-20</td><td>Mbappe</td><td>Pão Francês</td><td>2</td><td>15.5</td><td>31</td><td>Dinheiro</td></tr>
<tr><td>35</td><td>2025-06-22</td><td>Peter Parker</td><td>Pão Fatiado</td><td>4</td><td>10.0</td><td>40</td><td>Cartão</td></tr>
<tr><td>36</td><td>2025-06-22</td><td>Matuê</td><td>Cuca</td><td>2</td><td>12.0</td><td>24</td><td>PIX</td></tr>
<tr><td>37</td><td>2025-06-29</td><td>Tim Maia</td><td>Pão Fatiado</td><td>3</td><td>10.0</td><td>30</td><td>Cartão</td></tr>
<tr><td>38</td><td>2025-06-29</td><td>Kerry King</td><td>Pão Francês</td><td>2</td><td>15.5</td><td>31</td><td>Dinheiro</td></tr>
<tr><td>39</td><td>2025-07-06</td><td>Mbappe</td><td>Coxinha</td><td>7</td><td>8.0</td><td>56</td><td>Dinheiro</td></tr>
<tr><td>40</td><td>2025-07-06</td><td>Sergio Luiz</td><td>Chineque</td><td>1</td><td>10.0</td><td>10</td><td>PIX</td></tr>
<tr><td>41</td><td>2025-07-21</td><td>Peter Parker</td><td>Rosca</td><td>4</td><td>30.0</td><td>120</td><td>PIX</td></tr>
<tr><td>42</td><td>2025-07-21</td><td>Matuê</td><td>Pastel</td><td>3</td><td>10.0</td><td>30</td><td>Dinheiro</td></tr>
<tr><td>43</td><td>2025-08-03</td><td>Mbappe</td><td>Pão Francês</td><td>5</td><td>15.5</td><td>77.5</td><td>Cartão</td></tr>
<tr><td>44</td><td>2025-08-03</td><td>Mbappe</td><td>Pão Fatiado</td><td>6</td><td>10.0</td><td>60</td><td>PIX</td></tr>
<tr><td>45</td><td>2025-08-19</td><td>Tim Maia</td><td>Cuca</td><td>3</td><td>12.0</td><td>36</td><td>Dinheiro</td></tr>
<tr><td>46</td><td>2025-08-19</td><td>Sergio Luiz</td><td>Chineque</td><td>5</td><td>10.0</td><td>50</td><td>Cartão</td></tr>
<tr><td>47</td><td>2025-09-10</td><td>Peter Parker</td><td>Pastel</td><td>2</td><td>10.0</td><td>20</td><td>PIX</td></tr>
<tr><td>48</td><td>2025-09-10</td><td>Peter Parker</td><td>Rosca</td><td>1</td><td>30.0</td><td>30</td><td>Cartão</td></tr>
<tr><td>49</td><td>2025-09-26</td><td>Sergio Luiz</td><td>Pão Fatiado</td><td>8</td><td>10.0</td><td>80</td><td>Cartão</td></tr>
<tr><td>50</td><td>2025-09-26</td><td>Matuê</td><td>Pão Francês</td><td>4</td><td>15.5</td><td>62</td><td>PIX</td></tr>
<tr><td>51</td><td>2025-10-14</td><td>Tony Stark</td><td>Coxinha</td><td>6</td><td>8.0</td><td>48</td><td>Dinheiro</td></tr>
<tr><td>52</td><td>2025-10-14</td><td>Kerry King</td><td>Chineque</td><td>2</td><td>10.0</td><td>20</td><td>Cartão</td></tr>
<tr><td>53</td><td>2025-10-30</td><td>Tony Stark</td><td>Cuca</td><td>4</td><td>12.0</td><td>48</td><td>PIX</td></tr>
<tr><td>54</td><td>2025-10-30</td><td>Sergio Luiz</td><td>Pastel</td><td>3</td><td>10.0</td><td>30</td><td>PIX</td></tr>
<tr><td>55</td><td>2025-11-11</td><td>Sergio Luiz</td><td>Rosca</td><td>5</td><td>30.0</td><td>150</td><td>Cartão</td></tr>
<tr><td>56</td><td>2025-11-11</td><td>Mbappe</td><td>Pão Fatiado</td><td>7</td><td>10.0</td><td>70</td><td>PIX</td></tr>
<tr><td>57</td><td>2025-12-05</td><td>Peter Parker</td><td>Pão Francês</td><td>6</td><td>15.5</td><td>93</td><td>Cartão</td></tr>
<tr><td>58</td><td>2025-12-05</td><td>Peter Parker</td><td>Pão Fatiado</td><td>4</td><td>10.0</td><td>40</td><td>PIX</td></tr>
<tr><td>59</td><td>2025-12-12</td><td>Peter Parker</td><td>Chineque</td><td>3</td><td>10.0</td><td>30</td><td>Cartão</td></tr>
<tr><td>60</td><td>2025-12-12</td><td>Matuê</td><td>Pastel</td><td>2</td><td>10.0</td><td>20</td><td>PIX</td></tr>
<tr><td>61</td><td>2025-12-15</td><td>Sergio Luiz</td><td>Chineque</td><td>4</td><td>10.0</td><td>40</td><td>PIX</td></tr>
<tr><td>62</td><td>2025-12-20</td><td>Matuê</td><td>Rosca</td><td>3</td><td>30.0</td><td>90</td><td>Dinheiro</td></tr>
<tr><td>63</td><td>2025-12-22</td><td>Sergio Luiz</td><td>Pastel</td><td>5</td><td>10.0</td><td>50</td><td>Cartão</td></tr>
</table>
<div class="pagination" role="navigation" aria-label="Paginação de resultados">
    <button id="prevBtn" disabled aria-label="Página anterior">&larr; Anterior</button>
    <span id="pageInfo" aria-live="polite">Página 1</span>
    <button id="nextBtn" aria-label="Próxima página">Próxima &rarr;</button>
</div>
</section>

<section id="grafico-produto" class="section chart-section" aria-labelledby="titulo-produto">
<h2 id="titulo-produto">Vendas por Produto</h2>
<canvas id="produtoChart" role="img" aria-label="Gráfico de vendas por produto"></canvas>
</section>

<section id="grafico-pagamento" class="section chart-section" aria-labelledby="titulo-pagamento">
<h2 id="titulo-pagamento">Vendas por Forma de Pagamento</h2>
<canvas id="pagamentoChart" role="img" aria-label="Gráfico de vendas por forma de pagamento"></canvas>
</section>

<section id="grafico-funcionario" class="section chart-section" aria-labelledby="titulo-funcionario">
<h2 id="titulo-funcionario">Vendas por Funcionário</h2>
<canvas id="funcionarioChart" role="img" aria-label="Gráfico de vendas por funcionário"></canvas>
</section>

<section id="grafico-dia" class="section chart-section" aria-labelledby="titulo-dia">
<h2 id="titulo-dia">Vendas</h2>
<canvas id="diaChart" role="img" aria-label="Gráfico de vendas"></canvas>
</section>
</main>

<script>
    
function showSection(id) {
document.querySelectorAll('.section').forEach(el => el.classList.remove('active'));
document.getElementById(id).classList.add('active');
}

    const rawRows = Array.from(document.querySelectorAll('#tabela tbody tr')).map(row => ({
        id: row.cells[0].textContent,
        date: row.cells[1].textContent,
        funcionario: row.cells[2].textContent,
        produto: row.cells[3].textContent,
        quantidade: +row.cells[4].textContent,
        preco: +row.cells[5].textContent,
        total: +row.cells[6].textContent,
        pagamento: row.cells[7].textContent
    }));

    const defaultColors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#8E44AD', '#2ECC71', '#E74C3C'];

    const ctxProd = document.getElementById('produtoChart');
    const ctxPag  = document.getElementById('pagamentoChart');
    const ctxFunc = document.getElementById('funcionarioChart');
    const ctxDia  = document.getElementById('diaChart');

    let produtoChart = new Chart(ctxProd, {type:'bar', data: {labels:[], datasets:[{label:'Quantidade', data:[], backgroundColor:[], borderWidth:1}]}});
    let pagamentoChart = new Chart(ctxPag,  {type:'pie', data: {labels:[], datasets:[{label:'Utilizada', data:[], backgroundColor:[]} ]}});
    let funcionarioChart = new Chart(ctxFunc,{type:'bar', data: {labels:[], datasets:[{label:'Vendas', data:[], backgroundColor:[], borderWidth:1}]}});
    let diaChart = new Chart(ctxDia,   {type:'line', data:{labels:[], datasets:[{label:'Total vendido (R$)', data:[], borderColor:'', backgroundColor:'', fill:true, tension:0.3}]}});

    const rowsPerPage = 9;
    let currentPage = 1;
    let filteredIndices = [];

    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const pageInfo = document.getElementById('pageInfo');

    prevBtn.addEventListener('click', () => { if(currentPage>1){ currentPage--; applyFilters(); }});
    nextBtn.addEventListener('click', () => { if(currentPage < Math.ceil(filteredIndices.length/rowsPerPage)){ currentPage++; applyFilters(); }});

    function applyFilters() {
        const start = document.getElementById('startDate').value;
        const end   = document.getElementById('endDate').value;
        const search= document.getElementById('search').value.trim();

        filteredIndices = [];
        rawRows.forEach((item, idx) => {
            let passDate = true;
            if (start) passDate = item.date >= start;
            if (passDate && end) passDate = item.date <= end;
            let passSearch = item.produto.includes(search);
            if(passDate && passSearch) filteredIndices.push(idx);
        });

       
        const totalPages = Math.ceil(filteredIndices.length/rowsPerPage) || 1;
        if(currentPage>totalPages) currentPage = totalPages; 

        document.querySelectorAll('#tabela tbody tr').forEach((row, idx) => {
            const pos = filteredIndices.indexOf(idx);
            if(pos >= (currentPage-1)*rowsPerPage && pos < currentPage*rowsPerPage) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        pageInfo.textContent = `Página ${currentPage} de ${totalPages}`;
        prevBtn.disabled = currentPage === 1;
        nextBtn.disabled = currentPage === totalPages;

        updateCharts();
    }

    function updateCharts() {
        const prodCounts = {}, pagCounts = {}, funcCounts = {}, diaTotals = {};
        filteredIndices.forEach(idx => {
            const item = rawRows[idx];
            prodCounts[item.produto]      = (prodCounts[item.produto]||0)      + item.quantidade;
            pagCounts[item.pagamento]     = (pagCounts[item.pagamento]||0)     + 1;
            funcCounts[item.funcionario]  = (funcCounts[item.funcionario]||0)  + 1;
            diaTotals[item.date]          = (diaTotals[item.date]||0)          + item.total;
        });

        produtoChart.data.labels = Object.keys(prodCounts);
        produtoChart.data.datasets[0].data = Object.values(prodCounts);
        produtoChart.data.datasets[0].backgroundColor = produtoChart.data.labels.map((_,i) => defaultColors[i % defaultColors.length]);
        produtoChart.update();

        pagamentoChart.data.labels = Object.keys(pagCounts);
        pagamentoChart.data.datasets[0].data = Object.values(pagCounts);
        pagamentoChart.data.datasets[0].backgroundColor = pagamentoChart.data.labels.map((_,i) => defaultColors[i % defaultColors.length]);
        pagamentoChart.update();

        funcionarioChart.data.labels = Object.keys(funcCounts);
        funcionarioChart.data.datasets[0].data = Object.values(funcCounts);
        funcionarioChart.data.datasets[0].backgroundColor = funcionarioChart.data.labels.map((_,i) => defaultColors[i % defaultColors.length]);
        funcionarioChart.update();

        diaChart.data.labels = Object.keys(diaTotals);
        diaChart.data.datasets[0].data = Object.values(diaTotals);
        const dayColor = defaultColors[1];
        diaChart.data.datasets[0].borderColor = dayColor;
        diaChart.data.datasets[0].backgroundColor = dayColor + '33';
        diaChart.update();
    }

    document.getElementById('startDate').addEventListener('change', () => { currentPage=1; applyFilters(); });
    document.getElementById('endDate').addEventListener('change', () => { currentPage=1; applyFilters(); });
    document.getElementById('search').addEventListener('input', () => { currentPage=1; applyFilters(); });

    applyFilters();
</script>

</body>
</html>