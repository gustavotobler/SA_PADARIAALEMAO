<?php
session_start();
require_once '../conexao.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['ID_func'] ?? null;
    if (!$id || !is_numeric($id)) {
        die("ID inválido!");
    }
    $id = intval($id);

    $nome       = $_POST['Nome_func'] ?? null;
    $telefone   = $_POST['Telefone'] ?? null;
    $sexo       = $_POST['Sexo'] ?? null;
    $rg         = $_POST['RG'] ?? null;
    $cpf        = $_POST['CPF'] ?? null;
    $esta_civil = $_POST['Esta_civil'] ?? null;
    $uf         = $_POST['UF'] ?? null;
    $cidade     = $_POST['Cidade'] ?? null;
    $bairro     = $_POST['Bairro'] ?? null;
    $tipo       = $_POST['Tipo'] ?? null;
    $cep        = $_POST['CEP'] ?? null;
    $num_casa   = $_POST['Num_casa'] ?? null;
    $logradouro = $_POST['Logradouro'] ?? null;
    $email      = $_POST['Email'] ?? null;
    $nivel      = $_POST['nivel_de_acesso'] ?? null;
    $cargo      = $_POST['Cargo'] ?? null;

    // Formata datas para o banco
    function formatarDataBanco($data){
        if(!$data) return null;
        $partes = explode("/", $data);
        if(count($partes) == 3){
            return $partes[2]."-".$partes[1]."-".$partes[0];
        }
        return null;
    }

    $data_nasc = formatarDataBanco($_POST['Data_nascimento'] ?? null);
    $data_adm  = formatarDataBanco($_POST['Data_admissao'] ?? null);

    // Mantém a senha antiga se o campo estiver vazio
    $senha = !empty($_POST['Senha']) ? password_hash($_POST['Senha'], PASSWORD_DEFAULT) : null;

    $sql ="UPDATE funcionario SET 
        Nome_func=:Nome_func,
        Telefone=:Telefone,
        Sexo=:Sexo,
        RG=:RG,
        CPF=:CPF,
        Esta_civil=:Esta_civil,
        UF=:UF,
        Cidade=:Cidade,
        Bairro=:Bairro,
        Tipo=:Tipo,
        CEP=:CEP,
        Num_casa=:Num_casa,
        Logradouro=:Logradouro,
        Senha=COALESCE(:Senha,Senha),
        Email=:Email,
        nivel_de_acesso=:nivel_de_acesso,
        Data_nascimento=:Data_nascimento,
        Data_admissao=:Data_admissao,
        Cargo=:Cargo
    WHERE ID_func=:id";

    $stmt = $pdo->prepare($sql);
    $executou = $stmt->execute([
        'Nome_func'=>$nome,
        'Telefone'=>$telefone,
        'Sexo'=>$sexo,
        'RG'=>$rg,
        'CPF'=>$cpf,
        'Esta_civil'=>$esta_civil,
        'UF'=>$uf,
        'Cidade'=>$cidade,
        'Bairro'=>$bairro,
        'Tipo'=>$tipo,
        'CEP'=>$cep,
        'Num_casa'=>$num_casa,
        'Logradouro'=>$logradouro,
        'Senha'=>$senha,
        'Email'=>$email,
        'nivel_de_acesso'=>$nivel,
        'Data_nascimento'=>$data_nasc,
        'Data_admissao'=>$data_adm,
        'Cargo'=>$cargo,
        'id'=>$id
    ]);
    
    if($executou){
        echo "<script>alert('Funcionário alterado com sucesso!');window.location.href='../funcionarios.php';</script>";
        exit;
    } else {
        echo "<script>alert('Erro ao atualizar o funcionário.');</script>";
    }
}    
?>
