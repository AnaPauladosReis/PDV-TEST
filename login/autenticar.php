<?php
require_once('../conexao.php');
@session_start();

$email = $_POST['email'];
$senha = $_POST['senha'];

$query = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email AND senha = :senha");
$query->bindValue(':email', $email);
$query->bindValue(':senha', $senha);
$query->execute();

$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);


if ($total_reg > 0) {

    //Criar as variaveis de sessão
    $_SESSION['nome_usuario'] = $res[0]['nome'];
    $_SESSION['nivel_usuario'] = $res[0]['nivel'];

    $nivel = $res[0]['nivel'];
    if ($nivel == 'Administrador') {
        echo "<script>window.location='painel-adm'</script>";
    } else if ($nivel == 'Cliente') {
        echo "<script>window.location='painel-cliente'</script>";
    } else {
        echo "<script>alert('Nível de usuário não reconhecido.');</script>";
        echo "<script>window.location='index.php'</script>";
    }
} else {
    echo "<script>alert('Dados Incorretos! Tente novamente.');</script>";
    echo "<script>window.location='index.php'</script>";
}
