<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$nome      = htmlspecialchars($data['nome']       ?? '');
$empresa   = htmlspecialchars($data['empresa']    ?? '');
$email     = htmlspecialchars($data['email']      ?? '');
$telefone  = htmlspecialchars($data['telefone']   ?? '');
$produto   = htmlspecialchars($data['produto']    ?? '');
$quantidade= htmlspecialchars($data['quantidade'] ?? '');
$mensagem  = htmlspecialchars($data['mensagem']   ?? '');

if (!$nome || !$email || !$telefone) {
    http_response_code(400);
    echo json_encode(['error' => 'Campos obrigatórios ausentes']);
    exit;
}

// Configurações SMTP
$smtp_host = 'mail.fitaadesivaexpress.com.br';
$smtp_port = 465;
$smtp_user = 'vendas@fitaadesivaexpress.com.br';
$smtp_pass = 'V#Md-0;er2w(U7iN';

$destinatarios = 'vendas@fitec.com.br, vendas2@fitec.com.br';
$assunto = "Novo orçamento de $nome — Fita Adesiva Express";

$corpo  = "Nova Solicitação de Orçamento — Fita Adesiva Express\n\n";
$corpo .= "Nome: $nome\n";
if ($empresa)    $corpo .= "Empresa: $empresa\n";
$corpo .= "E-mail: $email\n";
$corpo .= "WhatsApp: $telefone\n";
if ($produto)    $corpo .= "Produto: $produto\n";
if ($quantidade) $corpo .= "Quantidade: $quantidade\n";
if ($mensagem)   $corpo .= "Observações: $mensagem\n";

// Conexão SMTP via SSL (porta 465)
$socket = fsockopen("ssl://$smtp_host", $smtp_port, $errno, $errstr, 10);
if (!$socket) {
    http_response_code(500);
    echo json_encode(['error' => "Não foi possível conectar ao SMTP: $errstr"]);
    exit;
}

function smtp_send($socket, $cmd) {
    fwrite($socket, "$cmd\r\n");
    return fgets($socket, 512);
}
function smtp_read($socket) {
    return fgets($socket, 512);
}

smtp_read($socket); // banner
smtp_send($socket, "EHLO fitaadesivaexpress.com.br");
// Ler todas as linhas do EHLO
while (true) {
    $line = smtp_read($socket);
    if ($line[3] === ' ') break;
}

smtp_send($socket, "AUTH LOGIN");
smtp_read($socket);
smtp_send($socket, base64_encode($smtp_user));
smtp_read($socket);
smtp_send($socket, base64_encode($smtp_pass));
$auth_resp = smtp_read($socket);

if (strpos($auth_resp, '235') === false) {
    fclose($socket);
    http_response_code(500);
    echo json_encode(['error' => 'Falha na autenticação SMTP']);
    exit;
}

smtp_send($socket, "MAIL FROM:<$smtp_user>");
smtp_read($socket);

foreach (explode(',', $destinatarios) as $dest) {
    smtp_send($socket, "RCPT TO:<" . trim($dest) . ">");
    smtp_read($socket);
}

smtp_send($socket, "DATA");
smtp_read($socket);

$mensagem_completa  = "From: Fita Adesiva Express <$smtp_user>\r\n";
$mensagem_completa .= "To: $destinatarios\r\n";
$mensagem_completa .= "Subject: $assunto\r\n";
$mensagem_completa .= "Reply-To: $email\r\n";
$mensagem_completa .= "\r\n";
$mensagem_completa .= $corpo;
$mensagem_completa .= "\r\n.";

smtp_send($socket, $mensagem_completa);
smtp_read($socket);
smtp_send($socket, "QUIT");
fclose($socket);

echo json_encode(['ok' => true]);
