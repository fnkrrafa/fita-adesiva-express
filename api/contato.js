const nodemailer = require('nodemailer');

export default async function handler(req, res) {
  if (req.method !== 'POST') {
    return res.status(405).json({ error: 'Método não permitido' });
  }

  const { nome, empresa, email, telefone, produto, quantidade, mensagem } = req.body;

  if (!nome || !email || !telefone) {
    return res.status(400).json({ error: 'Campos obrigatórios ausentes' });
  }

  const transporter = nodemailer.createTransport({
    host: process.env.SMTP_HOST,
    port: parseInt(process.env.SMTP_PORT),
    secure: false,
    auth: {
      user: process.env.SMTP_USER,
      pass: process.env.SMTP_PASS,
    },
  });

  const htmlBody = `
    <h2 style="color:#1a56db;">Nova Solicitação de Orçamento — Fita Adesiva Express</h2>
    <table cellpadding="8" style="border-collapse:collapse;width:100%;max-width:500px;">
      <tr><td style="font-weight:bold;background:#f1f5f9;">Nome</td><td>${nome}</td></tr>
      ${empresa ? `<tr><td style="font-weight:bold;background:#f1f5f9;">Empresa</td><td>${empresa}</td></tr>` : ''}
      <tr><td style="font-weight:bold;background:#f1f5f9;">E-mail</td><td>${email}</td></tr>
      <tr><td style="font-weight:bold;background:#f1f5f9;">WhatsApp</td><td>${telefone}</td></tr>
      ${produto ? `<tr><td style="font-weight:bold;background:#f1f5f9;">Produto</td><td>${produto}</td></tr>` : ''}
      ${quantidade ? `<tr><td style="font-weight:bold;background:#f1f5f9;">Quantidade</td><td>${quantidade}</td></tr>` : ''}
      ${mensagem ? `<tr><td style="font-weight:bold;background:#f1f5f9;">Observações</td><td>${mensagem}</td></tr>` : ''}
    </table>
  `;

  try {
    await transporter.sendMail({
      from: `"Fita Adesiva Express" <${process.env.SMTP_USER}>`,
      to: 'vendas@fitec.com.br, vendas2@fitec.com.br',
      subject: `Novo orçamento de ${nome}`,
      html: htmlBody,
    });

    return res.status(200).json({ ok: true });
  } catch (err) {
    console.error('Erro ao enviar e-mail:', err);
    return res.status(500).json({ error: 'Falha ao enviar e-mail' });
  }
}
