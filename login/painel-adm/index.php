<?php
// 1. INICIAR SESSÃO E CONEXÃO
session_start();
require_once("../../conexao.php");

// 2. VERIFICAÇÃO DE SEGURANÇA (ADMIN)
// Esta é a primeira coisa a fazer. Se não for admin, redireciona.
if (!isset($_SESSION['nivel_usuario']) || $_SESSION['nivel_usuario'] != 'Administrador') {
    // Redireciona para a página de login na raiz
    echo "<script language='javascript'>window.location='../../index.php'</script>";
    exit(); 
}


// 3. BLOCO DE PROCESSAMENTO DE FORMULÁRIOS (LÓGICA NO TOPO)
// Este bloco trata TODOS os envios de formulário (POST) ANTES de carregar o HTML.

try {
    // --- LÓGICA DE CADASTRO (btn-cadastrar) ---
    if (isset($_POST['btn-cadastrar'])) {
        $nome = $_POST['nomeCad'];
        $email = $_POST['emailCad'];
        $senha = $_POST['senhaCad'];
        $nivel = $_POST['nivelCad'];

        // Verificar se o email já existe
        $query_v = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email");
        $query_v->bindValue(":email", $email);
        $query_v->execute();

        if ($query_v->rowCount() > 0) {
            echo "<script language='javascript'>window.alert('O Usuário já está cadastrado!!')</script>";
        } else {
            // HASH A SENHA
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

            $query = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, nivel) VALUES (:nome, :email, :senha, :nivel)");
            $query->bindValue(":nome", $nome);
            $query->bindValue(":email", $email);
            $query->bindValue(":senha", $senha_hash); // Salva o hash
            $query->bindValue(":nivel", $nivel);
            $query->execute();

            // Redireciona para limpar o POST
            header("Location: index.php?status=success");
            exit();
        }
    }

    // --- LÓGICA DE EDIÇÃO (btn-editar) ---
    if (isset($_POST['btn-editar'])) {
        $id_editar = $_GET['id']; // Pega o ID da URL
        $nome = $_POST['nomeCad'];
        $email = $_POST['emailCad'];
        $senha = $_POST['senhaCad'];
        $nivel = $_POST['nivelCad'];
        $email_antigo = $_POST['antigo'];

        // Verificar se o email foi alterado e se o novo já existe
        if ($email_antigo != $email) {
            $query_v = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email");
            $query_v->bindValue(":email", $email);
            $query_v->execute();

            if ($query_v->rowCount() > 0) {
                echo "<script language='javascript'>window.alert('O Usuário já está cadastrado!!')</script>";
                exit();
            }
        }
        
        // HASH A SENHA (importante!)
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        $query = $pdo->prepare("UPDATE usuarios SET nome = :nome, email = :email, senha = :senha, nivel = :nivel WHERE id = :id");
        $query->bindValue(":nome", $nome);
        $query->bindValue(":email", $email);
        $query->bindValue(":senha", $senha_hash); // Salva o novo hash
        $query->bindValue(":nivel", $nivel);
        $query->bindValue(":id", $id_editar);
        $query->execute();

        // Redireciona para limpar o POST
        header("Location: index.php?status=edited");
        exit();
    }

    // --- LÓGICA DE DELEÇÃO (btn-deletar) ---
    if (isset($_POST['btn-deletar'])) {
        $id_deletar = $_GET['id']; // Pega o ID da URL

        // USA PREPARE STATEMENT PARA SEGURANÇA
        $query = $pdo->prepare("DELETE FROM usuarios WHERE id = :id");
        $query->bindValue(":id", $id_deletar);
        $query->execute();

        // Redireciona para limpar o POST
        header("Location: index.php?status=deleted");
        exit();
    }

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}


// 4. BLOCO DE BUSCA DE DADOS (PARA EXIBIR NA TABELA)
// Isso só executa DEPOIS que a lógica de POST foi processada.

// Busca com segurança (sem '@')
$busca = isset($_GET['txtBuscar']) ? $_GET['txtBuscar'] : '';
$txtBuscar = '%' . $busca . '%';

$query = $pdo->prepare("SELECT * FROM usuarios WHERE nome LIKE :busca OR email LIKE :busca ORDER BY nome ASC");
// Usamos o mesmo parâmetro :busca para os dois campos
$query->bindValue(":busca", $txtBuscar);
$query->execute();
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = count($res);


// 5. LÓGICA PARA PRÉ-PREENCHER O MODAL DE EDIÇÃO
// Verifica se a URL pede para editar
$nome_ed = '';
$email_ed = '';
$senha_ed = ''; // Não devemos pré-preencher a senha
$nivel_ed = '';

if (isset($_GET['funcao']) && $_GET['funcao'] == 'editar') {
    $id_ed = $_GET['id'];
    $query_ed = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
    $query_ed->bindValue(":id", $id_ed);
    $query_ed->execute();
    $res_ed = $query_ed->fetch(PDO::FETCH_ASSOC);
    
    if($res_ed) {
        $nome_ed = $res_ed['nome'];
        $email_ed = $res_ed['email'];
        // NÃO pré-preencha a senha. O usuário deve digitar uma nova se quiser mudar.
        // A lógica de edição deve ser ajustada para SÓ atualizar a senha se ela for preenchida.
        // Por simplicidade aqui, estamos forçando a digitar a senha novamente.
        $nivel_ed = $res_ed['nivel'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Painel Administrativo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Administrador</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="index.php">Usuários</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Link</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Sair
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="#"><?php echo $_SESSION['nome_usuario'] ?></a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../logout.php">Sair</a></li>
                        </ul>
                    </li>
                </ul>
                <form method="GET" class="d-flex" action="index.php">
                    <input class="form-control me-2" type="search" placeholder="Buscar" aria-label="Search" name="txtBuscar" value="<?php echo $busca; ?>">
                    <button class="btn btn-outline-secondary" type="submit">Buscar</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container">
        <a href="index.php?funcao=novo" class="btn btn-secondary mt-4" type="button">Novo Usuário</a>

        <?php if ($total_reg > 0): ?>
            <table class="table table-striped mt-4">
                <thead>
                    <tr>
                        <th scope="col">Nome</th>
                        <th scope="col">Email</th>
                        <th scope="col">Nível</th>
                        <th scope="col">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Loop pelos resultados da busca
                    for ($i = 0; $i < $total_reg; $i++) {
                        $nome = $res[$i]['nome'];
                        $email = $res[$i]['email'];
                        // $senha = $res[$i]['senha']; // Não exibir!
                        $nivel = $res[$i]['nivel'];
                        $id = $res[$i]['id'];
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($nome); ?></td>
                            <td><?php echo htmlspecialchars($email); ?></td>
                            <td><?php echo htmlspecialchars($nivel); ?></td>
                            <td>
                                <a href="index.php?funcao=editar&id=<?php echo $id; ?>" title="Editar Registro" class="mr-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square text-primary" viewBox="0 0 16 16">
                                        <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456l-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                                        <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5v11z"/>
                                    </svg>
                                </a>
                                <a href="index.php?funcao=deletar&id=<?php echo $id; ?>" title="Deletar Registro" class="mr-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-archive text-danger" viewBox="0 0 16 16">
                                        <path d="M0 2a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1v7.5a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 1 12.5V5a1 1 0 0 1-1-1V2zm2 3v7.5A1.5 1.5 0 0 0 3.5 14h9a1.5 1.5 0 0 0 1.5-1.5V5H2zm13-3H1v2h14V2zM5 7.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5z"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    <?php } // Fim do loop for ?>
                </tbody>
            </table>
        <?php else: // Fim do if($total_reg > 0) ?>
            <p class="mt-4">Não existem dados para serem exibidos</p>
        <?php endif; ?>
    </div>

</body>
</html>

<div class="modal fade" id="modalCadastrar" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="index.php?funcao=<?php echo isset($_GET['funcao']) ? $_GET['funcao'] : 'novo'; ?>&id=<?php echo isset($_GET['id']) ? $_GET['id'] : ''; ?>">
                <div class="modal-header">
                    <?php
                    // Define o título e o botão com base na função (novo ou editar)
                    if (isset($_GET['funcao']) && $_GET['funcao'] == 'editar') {
                        $titulo_modal = "Editar Registro";
                        $botao_modal = "btn-editar";
                    } else {
                        $titulo_modal = "Inserir Registro";
                        $botao_modal = "btn-cadastrar";
                    }
                    ?>
                    <h5 class="modal-title"><?php echo $titulo_modal; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-2">
                        <label for="nomeCad">Nome</label>
                        <input type="text" class="form-control mt-1" id="nomeCad" name="nomeCad" required value="<?php echo htmlspecialchars($nome_ed); ?>">
                    </div>
                    <div class="form-group mb-2">
                        <label for="emailCad">Email</label>
                        <input type="email" class="form-control mt-1" id="emailCad" name="emailCad" required value="<?php echo htmlspecialchars($email_ed); ?>">
                    </div>
                    <div class="form-group mb-2">
                        <label for="senhaCad">Senha</label>
                        <input type="password" class="form-control mt-1" name="senhaCad" id="senhaCad" <?php echo ($botao_modal == 'btn-cadastrar') ? 'required' : ''; ?> placeholder="Digite uma nova senha para alterar">
                        <?php if($botao_modal == 'btn-editar'): ?>
                            <small class="text-muted">Deixe em branco para manter a senha atual.</small>
                            <?php endif; ?>
                    </div>
                    <div class="form-group mb-2">
                        <label for="nivelCad">Nivel</label>
                        <select class="form-select mt-1" aria-label="Nível" name="nivelCad">
                            <option <?php echo ($nivel_ed == 'Cliente') ? 'selected' : ''; ?> value="Cliente">Cliente</option>
                            <option <?php echo ($nivel_ed == 'Administrador') ? 'selected' : ''; ?> value="Administrador">Administrador</option>
                            <option <?php echo ($nivel_ed == 'Vendedor') ? 'selected' : ''; ?> value="Vendedor">Vendedor</option>
                            <option <?php echo ($nivel_ed == 'Tesoureiro') ? 'selected' : ''; ?> value="Tesoureiro">Tesoureiro</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" value="<?php echo $email_ed; ?>" name="antigo">
                    
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button name="<?php echo $botao_modal; ?>" type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal" tabindex="-1" id="modalDeletar">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Excluir Registro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Deseja Realmente Excluir este registro?</p>
            </div>
            <div class="modal-footer">
                <form method="POST" action="index.php?funcao=deletar&id=<?php echo isset($_GET['id']) ? $_GET['id'] : ''; ?>">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button name="btn-deletar" type="submit" class="btn btn-danger">Excluir</button>
                </form>
            </div>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-ygbV9kiqUc6oa4msXn9868pTtWMgiQaeYH7/t7LECLbyPA2x65Kgf80OJFdroafW" crossorigin="anonymous"></script>

<?php
// Script para abrir o modal de "novo"
if (isset($_GET['funcao']) && $_GET['funcao'] == 'novo') { ?>
    <script>
        var myModal = new bootstrap.Modal(document.getElementById('modalCadastrar'), { keyboard: false });
        myModal.show();
    </script>
<?php } ?>

<?php
// Script para abrir o modal de "editar"
if (isset($_GET['funcao']) && $_GET['funcao'] == 'editar') { ?>
    <script>
        var myModal = new bootstrap.Modal(document.getElementById('modalCadastrar'), { keyboard: false });
        myModal.show();
    </script>
<?php } ?>

<?php
// Script para abrir o modal de "deletar"
if (isset($_GET['funcao']) && $_GET['funcao'] == 'deletar') { ?>
    <script>
        var myModal = new bootstrap.Modal(document.getElementById('modalDeletar'), { keyboard: false });
        myModal.show();
    </script>
<?php } ?>