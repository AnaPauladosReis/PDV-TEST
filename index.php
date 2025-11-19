<?php
session_start();


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('conexao.php');

// 2. MOVER A LÓGICA DE CADASTRO PARA CIMA (ANTES DE QUALQUER HTML)
if (isset($_POST['btn-cadastrar'])) {
    
    $nome = $_POST['nomeCad'];
    $email = $_POST['emailCad'];
    $senha = $_POST['senhaCad'];
    $nivel = 'Cliente'; // Nível padrão para auto-cadastro

    // 2a. Validar dados (simples)
    if (empty($nome) || empty($email) || empty($senha)) {
        $_SESSION['register_error'] = "Todos os campos são obrigatórios.";
    
    } else {
        try {
            // 2b. VERIFICAR SE O EMAIL JÁ ESTÁ CADASTRADO (com prepare)
            $query = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email");
            $query->bindValue(':email', $email);
            $query->execute();
            
            // rowCount() é uma forma de contar
            if ($query->rowCount() > 0) {
                $_SESSION['register_error'] = "Este email já está cadastrado!";
            } else {
                
                // 2c. CRIPTOGRAFAR (HASH) A SENHA!
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

                // 2d. Inserir no banco (com prepare)
                $query = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, nivel) VALUES (:nome, :email, :senha, :nivel)");
                $query->bindValue(':nome', $nome);
                $query->bindValue(':email', $email);
                $query->bindValue(':senha', $senha_hash); // Salva o HASH
                $query->bindValue(':nivel', $nivel);
                $query->execute();

                // 2e. Redirecionar com mensagem de sucesso
                $_SESSION['login_success'] = "Cadastro realizado com sucesso! Faça o login.";
                header("Location: index.php");
                exit();
            }
        } catch (PDOException $e) {
    // MUDANÇA TEMPORÁRIA PARA DEPURAR:
    $_SESSION['register_error'] = "ERRO DO BANCO: " . $e->getMessage(); 
    }
    }
    
    // Se deu erro no cadastro, redireciona de volta para mostrar o modal
    header("Location: index.php?modal=register"); // Truque para reabrir o modal
    exit();
}


// 3. Preparar mensagens de erro/sucesso para exibir no HTML
$login_error = '';
if (isset($_SESSION['login_error'])) {
    $login_error = $_SESSION['login_error'];
    unset($_SESSION['login_error']); // Limpa a msg após lê-la
}

$login_success = '';
if (isset($_SESSION['login_success'])) {
    $login_success = $_SESSION['login_success'];
    unset($_SESSION['login_success']); // Limpa a msg
}

$register_error = '';
if (isset($_SESSION['register_error'])) {
    $register_error = $_SESSION['register_error'];
    unset($_SESSION['register_error']); // Limpa a msg
}

?>
<link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
<link href="login/login.css" rel="stylesheet"> 
<script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-Piv4xVNRyMGpqkS2by6br4gNJ7DXjqk09RmUpJ8jgGtD7zP9yug3goQfGII0yAns" crossorigin="anonymous"></script>

<body>
    <div id="login">
        <div class="container">
            <div id="login-row" class="row justify-content-center align-items-center">
                <div id="login-column" class="col-md-6">
                    <div id="login-box" class="col-md-12">
                        <form id="login-form" class="form" action="autenticar.php" method="post">
                            <h3 class="text-center text-info">Login</h3>

                            <?php if (!empty($login_error)): ?>
                                <div class="alert alert-danger" role="alert">
                                    <?php echo $login_error; ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($login_success)): ?>
                                <div class="alert alert-success" role="alert">
                                    <?php echo $login_success; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label for="username" class="text-info">Usuário:</label><br>
                                <input type="text" name="email" id="username" class="form-control" placeholder="Insira seu email" required="">
                            </div>
                            <div class="form-group">
                                <label for="password" class="text-info">Senha:</label><br>
                                <input type="password" name="senha" id="password" class="form-control" placeholder="Insira sua senha" required="">
                            </div>
                            <div class="form-group">
                                <input type="submit" name="submit" class="btn btn-info btn-md" value="Logar">
                            </div>
                            <div id="register-link" class="text-right mt-1">
                                <a href="" data-toggle="modal" data-target="#modal-cadastrar" class="text-info">Cadastre-se</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>


<div class="modal" id="modal-cadastrar" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cadastre-se</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form method="post" action="index.php">
                <div class="modal-body">

                    <?php if (!empty($register_error)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo $register_error; ?>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="nomeCad">Nome</label>
                        <input type="text" class="form-control" id="nomeCad" name="nomeCad" required="">
                    </div>
                    <div class="form-group">
                        <label for="emailCad">Email</label>
                        <input type="email" class="form-control" id="emailCad" name="emailCad" required="">
                    </div>
                    <div class="form-group">
                        <label for="senhaCad">Senha</label>
                        <input type="password" class="form-control" id="senhaCad" name="senhaCad" required="">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-primary" name="btn-cadastrar">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Script para reabrir o modal de cadastro automaticamente se houver um erro de registro
if (isset($_GET['modal']) && $_GET['modal'] == 'register') {
    echo "<script>
        $(document).ready(function(){
            $('#modal-cadastrar').modal('show');
        });
    </script>";
}
?>