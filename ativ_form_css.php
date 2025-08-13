<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Cadastro de Categorias - Padaria</title>
  <link rel="stylesheet" type="text/css" href="formulario.css" media="all"/>

</head>
<body>

  <h2> Cadastro de Categoria de Itens da Padaria</h2>


    
  <form action="#" method="post">
    <table border="1" cellpadding="8" cellspacing="0">
      <tr>
        <th colspan="2">Preencha os dados da nova categoria</th>
      </tr>
      <tr>
        <td><label for="ref">Referência (ref.):</label></td>
        <td><input type="text" id="ref" name="ref" placeholder="Ex: cafes" required></td>
      </tr>
      <tr>
        <td><label for="nome">Nome da Categoria:</label></td>
        <td><input type="text" id="nome" name="nome" placeholder="Ex: Cafés" required></td>
      </tr>
      <tr>
        <td><label for="pai">Categoria  1:</label></td>
        <td>
          <select id="pai" name="pai">
            <option value="">-- Nenhuma --</option>
            <option value="cafes">Cafés</option>
            <option value="sucos">Sucos</option>
            <option value="bolos">Bolos</option>
            <option value="salgados">Salgados</option>
            <option value="paes">Pães</option>
          </select>
        </td>
      </tr>
      <tr>
        <td><label for="ordem">Ordem de Exibição:</label></td>
        <td><input type="number" id="ordem" name="ordem" min="1" placeholder="Ex: 1, 2, 3..."></td>
      </tr>
      <tr>
        <td><label for="data">Data de Atualização:</label></td>
        <td><input type="datetime-local" id="data" name="data"></td>
      </tr>
      <tr>
        <td colspan="2" align="center">
          <button type="submit">Salvar Categoria</button>
          <button type="reset">Limpar</button>
        </td>
      </tr>
    </table>
  </form>

  <center>
<address>
  IAN LUCAS BORBA - ESTUDANTE TEC. DESENVOLVIMENTO DE SISTEMAS
</address>

  </center>

</body>
</html>
