<?php
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require __DIR__ . '/../vendor/autoload.php';

    class Mail {
        private $mail;
        private $mailAddress;
        private $mailPassword;

        public function __construct() {
            // Incarcam credentialele din credentials.json
            $credentialsPath = __DIR__ . '/../credentials.json';
            if (!file_exists($credentialsPath)) {
                Debug::log('Nu a fost gasit fisierul de credentiale.');
                return;
            }

            $credentials = json_decode(file_get_contents($credentialsPath), true);

            if (!isset($credentials['mail_address']) || !isset($credentials['mail_password'])) {
                Debug::log('Formatul credentialelor este invalid.');
                return;
            }

            $this->mailAddress = $credentials['mail_address'];
            $this->mailPassword = $credentials['mail_password'];

            // Initializam PHPMailer
            $this->mail = new PHPMailer(true);
            $this->configureMailer();
        }

        private function configureMailer() {
            try {
                $this->mail->isSMTP();
                $this->mail->Host = 'smtp.gmail.com';
                $this->mail->SMTPAuth = true;
                $this->mail->Username = $this->mailAddress;
                $this->mail->Password = $this->mailPassword;
                $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $this->mail->Port = 587;

                $this->mail->setFrom($this->mailAddress, 'Sala Regala de Muzica');
                $this->mail->addReplyTo($this->mailAddress, 'Sala Regala de Muzica');

            } catch (Exception $e) {
                Debug::log('Eroare in configurarea PHPMailer: ' . $e->getMessage());
            }
        }

        public function send($to, $subject, $body, $attachments = [], $inMemoryAttachments = []) {
            try {
                $this->mail->clearAddresses();
                $this->mail->addAddress($to);
        
                $this->mail->isHTML(true);
                $this->mail->Subject = $subject;
                $this->mail->Body = $body;
        
                // Adaugam atasamentele (fisiere)
                if (!empty($attachments)) {
                    foreach ($attachments as $attachment) {
                        if (file_exists($attachment)) {
                            $this->mail->addAttachment($attachment);
                        } else {
                            Debug::log("Nu a fost gasit atasamentul: $attachment");
                        }
                    }
                }
        
                // Adaugam atasamentele variabile (din memorie)
                if (!empty($inMemoryAttachments)) {
                    foreach ($inMemoryAttachments as $attachment) {
                        $this->mail->addStringAttachment($attachment['data'], $attachment['name']);
                    }
                }
        
                $this->mail->send();
                return true;
            } catch (Exception $e) {
                Debug::log('Eroare in trimiterea mailului: ' . $e->getMessage());
                return false;
            }
        }                   
    }
?>