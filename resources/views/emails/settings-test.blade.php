<x-emails.layout title="El correo funciona." eyebrow="Prueba de configuración" preheader="La configuración SMTP del CMS está enviando correos." footer="Enviado desde Configuración del sitio en el CMS de Feat.">
    <p style="margin:0;">Este es un correo de prueba enviado con la configuración guardada en el CMS. Si lo estás leyendo, los avisos de nuevos contactos y las confirmaciones a clientes ya pueden enviarse.</p>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:24px 0 0;background-color:#F7F3EA;border:1px solid #E4DECE;border-radius:16px;">
        <x-emails.detail label="Servidor">{{ $settings->mail_mailer === 'smtp' ? $settings->mail_host.':'.$settings->mail_port.' ('.strtoupper($settings->mail_encryption).')' : $settings->mail_mailer }}</x-emails.detail>
        <x-emails.detail label="Remitente">{{ $settings->mail_from_name }} &lt;{{ $settings->mail_from_address }}&gt;</x-emails.detail>
    </table>
</x-emails.layout>
