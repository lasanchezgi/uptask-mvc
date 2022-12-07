<?php

    namespace Classes;

    use PHPMailer\PHPMailer\PHPMailer;

    class Email {
        protected $email;
        protected $nombre;
        protected $token;

        // Constructor
        public function __construct($email, $nombre, $token)
        {
            $this->email = $email;
            $this->nombre = $nombre;
            $this->token = $token;
        }

        // Enviar email de confirmación
        public function enviarConfirmacion() {
            // Desde mailtrap
            $mail = new PHPMailer();
            $mail->isSMTP();
            $mail->Host = 'smtp.mailtrap.io';
            $mail->SMTPAuth = true;
            $mail->Port = 2525;
            $mail->Username = '96e4a64d61af4b';
            $mail->Password = '46524a3b5d2a77';

            // Configuración del email
            $mail->setFrom('cuentas@uptask.com');
            $mail->addAddress('cuentas@uptask.com','uptask.com');
            $mail->Subject = 'Confirmar tu cuenta';

            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';

            $contenido = '<html>';
            $contenido .= "<p><strong>Hola " . $this->nombre . "</strong> Has creado tu cuenta en UpTask, solo debes confirmarla en el siguiente enlace </p>";
            $contenido .= "<p>Presiona aquí: <a href='http://localhost:5000/confirmar?token=" . $this->token . "'>Confirmar cuenta</a></p>";
            $contenido .= "<p>Si tu no creaste esta cuenta, puedes ignorar este mensaje </p>";
            $contenido .= '</html>';

            $mail->Body = $contenido;

            // Enviar el email
            $mail->send();
        }

        // Enviar email de instrucciones
        public function enviarInstrucciones() {
            // Desde mailtrap
            $mail = new PHPMailer();
            $mail->isSMTP();
            $mail->Host = 'smtp.mailtrap.io';
            $mail->SMTPAuth = true;
            $mail->Port = 2525;
            $mail->Username = '96e4a64d61af4b';
            $mail->Password = '46524a3b5d2a77';

            // Configuración del email
            $mail->setFrom('cuentas@uptask.com');
            $mail->addAddress('cuentas@uptask.com','uptask.com');
            $mail->Subject = 'Restablece tu password';

            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';

            $contenido = '<html>';
            $contenido .= "<p><strong>Hola " . $this->nombre . "</strong> Parece que has olvidado tu passwors, sigue el siguiente enlace para restablecerlo </p>";
            $contenido .= "<p>Presiona aquí: <a href='http://localhost:5000/restablecer?token=" . $this->token . "'>Restablecer tu password</a></p>";
            $contenido .= "<p>Si tu no creaste esta cuenta, puedes ignorar este mensaje </p>";
            $contenido .= '</html>';

            $mail->Body = $contenido;

            // Enviar el email
            $mail->send();
        }
    }