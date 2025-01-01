<?php
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require __DIR__ . '/../vendor/autoload.php';

    class Mail {
        private $mail;
        private $mailAddress;
        private $mailPassword;

        public function __construct() {
            // Load credentials from credentials.json
            $credentialsPath = __DIR__ . '/../credentials.json';
            if (!file_exists($credentialsPath)) {
                Debug::log('Credentials file not found.');
                return;
            }

            $credentials = json_decode(file_get_contents($credentialsPath), true);

            if (!isset($credentials['mail_address']) || !isset($credentials['mail_password'])) {
                Debug::log('Invalid credentials format. Ensure mail_address and mail_password are set.');
                return;
            }

            $this->mailAddress = $credentials['mail_address'];
            $this->mailPassword = $credentials['mail_password'];

            // Initialize PHPMailer
            $this->mail = new PHPMailer(true);
            $this->configureMailer();

            // Send a test email
            // $this->send(
            //     $this->mailAddress, 
            //     'Test Email', 
            //     '
            //         <p>This is a test email from Sala Regala de Muzica.</p>
            //         <a href="https://www.google.com">Google Home Page</a>
            //     '
            // );
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
                Debug::log('Mailer configuration failed: ' . $e->getMessage());
            }
        }

        public function send($to, $subject, $body) {
            try {
                $this->mail->clearAddresses();
                $this->mail->addAddress($to);

                $this->mail->isHTML(true);
                $this->mail->Subject = $subject;
                $this->mail->Body = $body;

                $this->mail->send();
                return true;
            } catch (Exception $e) {
                Debug::log('Mail sending failed: ' . $e->getMessage());
                return false;
            }
        }
    }
?>