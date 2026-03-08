<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'mail.fitaadesivaexpress.com.br';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'vendas@fitaadesivaexpress.com.br';
    $mail->Password   = 'V#Md-0;er2w(U7iN';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;

    $mail->setFrom('vendas@fitaadesivaexpress.com.br', 'Fita Adesiva Express');
    $mail->addReplyTo($email, $nome);
    $mail->addAddress('vendas@fitec.com.br');
    $mail->addAddress('vendas2@fitec.com.br');
    $mail->addAddress('rafaelsiewerdtoca@gmail.com');

    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Subject = "Novo orçamento de $nome — Fita Adesiva Express";

    $corpo  = "<h2 style='color:#1a56db;'>Nova Solicitação de Orçamento — Fita Adesiva Express</h2>";
    $corpo .= "<table cellpadding='8' style='border-collapse:collapse;width:100%;max-width:500px;'>";
    $corpo .= "<tr><td style='font-weight:bold;background:#f1f5f9;'>Nome</td><td>$nome</td></tr>";
    if ($empresa)    $corpo .= "<tr><td style='font-weight:bold;background:#f1f5f9;'>Empresa</td><td>$empresa</td></tr>";
    $corpo .= "<tr><td style='font-weight:bold;background:#f1f5f9;'>E-mail</td><td>$email</td></tr>";
    $corpo .= "<tr><td style='font-weight:bold;background:#f1f5f9;'>WhatsApp</td><td>$telefone</td></tr>";
    if ($produto)    $corpo .= "<tr><td style='font-weight:bold;background:#f1f5f9;'>Produto</td><td>$produto</td></tr>";
    if ($quantidade) $corpo .= "<tr><td style='font-weight:bold;background:#f1f5f9;'>Quantidade</td><td>$quantidade</td></tr>";
    if ($mensagem)   $corpo .= "<tr><td style='font-weight:bold;background:#f1f5f9;'>Observações</td><td>$mensagem</td></tr>";
    $corpo .= "</table>";

    $mail->Body = $corpo;

    $mail->send();
    echo json_encode(['ok' => true]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $mail->ErrorInfo]);
}
