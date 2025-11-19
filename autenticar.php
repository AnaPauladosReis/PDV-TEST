<?php
// LEMBRAR: A sessão DEVE ser iniciada no topo, antes de qualquer saída de HTML.
session_start();

require_once('conexao.php');

// 1. Validar se os dados vieram (boa prática)
if (empty($_POST['email']) || empty($_POST['senha'])) {
    $_SESSION['login_error'] = 'Por favor, preencha o email e a senha.';
    header('Location: index.php'); 
    exit();
}

$email = $_POST['email'];
$senha_digitada = $_POST['senha'];

try {
    // 2. Buscar o usuário APENAS pelo email
    $query = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
    $query->bindValue(':email', $email);
    $query->execute();
    
    $usuario = $query->fetch(PDO::FETCH_ASSOC); 

    // 3. Verificar se o usuário existe E se a senha está correta
    if ($usuario && password_verify($senha_digitada, $usuario['senha'])) {
        
        // Senha correta!
        // Regenerar o ID da sessão previne ataques de "session fixation".
        session_regenerate_id(true); 

        // 4. Salvar dados importantes na sessão
        $_SESSION['id_usuario'] = $usuario['id']; 
        $_SESSION['nome_usuario'] = $usuario['nome'];
        $_SESSION['nivel_usuario'] = $usuario['nivel'];

        // 5. Redirecionar com base no nível
        $nivel = $usuario['nivel'];
        if ($nivel == 'Administrador') {
            // Caminho correto baseado na sua imagem
            header('Location: login/painel-adm/index.php'); 
            exit();
        } else if ($nivel == 'Cliente') {
            // Fazer a pagina do cliente até 22 de Novembro
            header('Location: login/painel-cliente/index.php'); 
            exit();
        } else {
            $_SESSION['login_error'] = 'Nível de usuário não reconhecido.';
            header('Location: index.php');
            exit();
        }

    } else {
        // Usuário não encontrado ou senha incorreta
        $_SESSION['login_error'] = 'Dados Incorretos! Tente novamente.';
        header('Location: index.php');
        exit();
    }

} catch (PDOException $e) {
    // Tratar erro de banco de dados
    $_SESSION['login_error'] = 'Erro no sistema. Tente mais tarde.';
    header('Location: index.php');
    exit();
}