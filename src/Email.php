<?php


namespace Craos\Email;


use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class Email
{
    private PHPMailer $mail;
    private $conta;

    public function __construct()
    {

        $this->mail = new PHPMailer(true);

        $debug = '';
        $this->mail->Debugoutput = function($str, $level) {
            $GLOBALS['debug'] .= "$level: $str\n";
        };
        echo $debug;

        $this->mail->isSMTP();
        $this->mail->setLanguage('pt_br');
        $this->mail->SMTPKeepAlive = true;


    }

    public function Iniciar() {

    }

    /**
     * @param mixed $conta
     */
    public function setConta($conta): void
    {
        $this->conta = $conta;

        try {

            $this->mail->Host = $this->conta->Smtp_host;
            $this->mail->SMTPAuth = $this->conta->Smtp_auth;
            $this->mail->Username = $this->conta->Endereco;
            $this->mail->Password = $this->conta->Senha;
            $this->mail->SMTPSecure = ($this->conta->Smtp_secure == 'TSL') ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
            $this->mail->Port = $this->conta->Smtp_port;
            $this->mail->SMTPOptions = array(
                'ssl' => array(
                    'peer_name' => $this->conta->Smtp_host,
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                    'allow_self_signed' => true,
                    'verify_depth' => 3,
                    'cafile' => 'ca-cert.pem',
                )
            );

            $this->mail->setFrom($this->conta->Endereco, $this->conta->Nome);
            $this->mail->addReplyTo($this->conta->Endereco, $this->conta->Nome);
            $this->mail->AddCustomHeader( "X-Confirm-Reading-To: " . $this->conta->Endereco );
            $this->mail->AddCustomHeader( "Return-Receipt-To: " . $this->conta->Endereco );
            $this->mail->AddCustomHeader( "Disposition-Notification-To: " . $this->conta->Endereco );

            $this->mail->DKIM_domain = $this->conta->Dominio;
            $this->mail->DKIM_private = 'api_dkim_private.pem';
            $this->mail->DKIM_selector = 'api';
            $this->mail->DKIM_passphrase = '';
            $this->mail->DKIM_identity = $this->conta->Endereco;
            $this->mail->DKIM_copyHeaderFields = false;
            $this->mail->DKIM_extraHeaders = ['List-Unsubscribe', 'List-Help'];

        } catch (Exception $e) {
        }


    }
}