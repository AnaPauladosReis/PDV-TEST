<?php

// 1. Validar e Limpar Inputs para segurança
$nome = filter_input(INPUT_POST, 'nome', FILTER_UNSAFE_RAW);
$email_usuario = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$mensagem = filter_input(INPUT_POST, 'mensagem', FILTER_UNSAFE_RAW);

// 2. Definir Destinatário e Remetente (SEGURO)
$destinatario = 'anareis87@icloud.com'; // Para onde o email vai
$assunto = 'Email do site - Contato de ' . $nome;

// O remetente do dominio ( contato@reisdev.com)
// Usar o email do usuário no "From" é garantia de ir pro SPAM.
$remetente_seguro = 'contato@reisdev.com'; //fazerr

// 3. Montar o corpo do email
$conteudo = "Nome: $nome\r\n";
$conteudo .= "Email do Usuário: $email_usuario\r\n\r\n";
$conteudo .= "Mensagem:\r\n$mensagem\r\n";

// 4. Montar os Cabeçalhos (Headers)
// Use "Reply-To" para poder responder ao usuário clicando em "Responder"
$cabecalhos = "From: $remetente_seguro\r\n";
$cabecalhos .= "Reply-To: $email_usuario\r\n";
$cabecalhos .= "X-Mailer: PHP/" . phpversion();

// 5. Enviar
if (mail($destinatario, $assunto, $conteudo, $cabecalhos)) {
    echo "<script>
            alert('Mensagem enviada com sucesso!');
            window.location.href = 'index.php';
          </script>";
} else {
    echo "<script>
            alert('Erro ao enviar a mensagem. Tente novamente.');
            window.location.href = 'index.php';
          </script>";
}