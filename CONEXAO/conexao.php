<?php
function conectarBanco(){
    $dsn = "mysql: host = localhost; dbname=padariadoalemao; charset=utf8mb4";
    $usuario = "root";
    $senha = "";

    try{
        $conexao = new PDO($dsn, $usuario, $senha, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
        return $conexao;
    }
    catch (PDOException $e){
        error_log("Erro ao conectar no banco:" .$e->getMessage());
    //log sem expor erro do usuario
    die("Erro ao conectar o banco");
    }
}
?>