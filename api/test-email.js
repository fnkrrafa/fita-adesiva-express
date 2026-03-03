const nodemailer = require('nodemailer');

module.exports = async function handler(req, res) {
  const port = parseInt(process.env.SMTP_PORT);
  const transporter = nodemailer.createTransport({
    host: process.env.SMTP_HOST,
    port,
    secure: port === 465,
    auth: {
      user: process.env.SMTP_USER,
      pass: process.env.SMTP_PASS,
    },
    tls: { rejectUnauthorized: false },
  });

  try {
    await transporter.verify();

    const info = await transporter.sendMail({
      from: `"Fita Adesiva Express" <${process.env.SMTP_USER}>`,
      to: 'rafaelsiewerdtoca@gmail.com',
      subject: '✅ Teste SMTP — Fita Adesiva Express',
      html: `
        <h2 style="color:#1a56db;">Teste de E-mail — Fita Adesiva Express</h2>
        <p>Se você recebeu este e-mail, o SMTP está configurado corretamente!</p>
        <table cellpadding="8" style="border-collapse:collapse;width:100%;max-width:400px;">
          <tr><td style="font-weight:bold;background:#f1f5f9;">Host</td><td>${process.env.SMTP_HOST}</td></tr>
          <tr><td style="font-weight:bold;background:#f1f5f9;">Porta</td><td>${process.env.SMTP_PORT}</td></tr>
          <tr><td style="font-weight:bold;background:#f1f5f9;">Usuário</td><td>${process.env.SMTP_USER}</td></tr>
          <tr><td style="font-weight:bold;background:#f1f5f9;">Data/Hora</td><td>${new Date().toLocaleString('pt-BR', { timeZone: 'America/Sao_Paulo' })}</td></tr>
        </table>
      `,
    });

    return res.status(200).json({
      ok: true,
      messageId: info.messageId,
      accepted: info.accepted,
      rejected: info.rejected,
      serverResponse: info.response,
    });
  } catch (err) {
    console.error('Erro no teste SMTP:', err);
    return res.status(500).json({
      error: 'Falha no teste SMTP',
      detail: err.message,
      code: err.code,
    });
  }
};
