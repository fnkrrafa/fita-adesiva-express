<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

$configPath = dirname($_SERVER['DOCUMENT_ROOT']) . '/config.php';
if (!file_exists($configPath)) {
    $configPath = dirname(__DIR__) . '/config.php';
}
require_once $configPath;
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

$assunto = "Novo orçamento de $nome — Fita Adesiva Express";

function criarMailer($email, $nome) {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = SMTP_PORT;
    $mail->setFrom(SMTP_USER, 'Fita Adesiva Express');
    $mail->addReplyTo($email, $nome);
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    return $mail;
}

$destinatarios = [
    'rafaelsiewerdtoca@gmail.com',
    'vendas@fitec.com.br',
    'vendas2@fitec.com.br',
];

$enviados = 0;
foreach ($destinatarios as $dest) {
    try {
        $mail = criarMailer($email, $nome);
        $mail->addAddress($dest);
        $mail->Subject = $assunto;
        $mail->Body    = $corpo;
        $mail->send();
        $enviados++;
    } catch (Exception $e) {
        // continua para o próximo destinatário
    }
}

if ($enviados > 0) {
    echo json_encode(['ok' => true, 'enviados' => $enviados]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Falha ao enviar para todos os destinatários']);
}
