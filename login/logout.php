<?php
// Inicia a sessão para poder manipulá-la
session_start();

// Limpa todas as variáveis de sessão
session_unset();

// Destrói a sessão
session_destroy();

// Redireciona para a página de login (na raiz do projeto)
header("Location: ../index.php"); 
exit();
?>