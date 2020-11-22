<?php


namespace Craos\Email;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

class oldEnvio
{
    private $config;
    private $log;
    private $mail;
    private $conta;

    public function __construct($config, $log, $conta)
    {
        $this->config = $config;
        $this->log = $log;
        $this->conta = $conta;

        $this->mail = new PHPMailer(true);
        try {

            $this->mail->SMTPDebug = SMTP::DEBUG_CLIENT;
            $this->mail->Debugoutput = function ($str, $level) {
                $this->log->info('PHPMailer', ['server' => $str]);
            };

            $this->mail->isSMTP();
            $this->mail->setLanguage('pt_br');
            $this->mail->SMTPKeepAlive = true;
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
            $this->mail->DKIM_identity = $this->mail->From;
            $this->mail->DKIM_copyHeaderFields = false;
            $this->mail->DKIM_extraHeaders = ['List-Unsubscribe', 'List-Help'];

        } catch (Exception $e) {

            $this->log->error('Configurações PHPMailer', [
                'detalhes' => $this->mail->ErrorInfo
            ]);
        }

    }

    public function Iniciar($destinatarios, $anexos, $comunicado)
    {
        $resultado = false;
        $erros = null;

        try {

            $bloco = $destinatarios->bloco;
            $unidade = $destinatarios->unidade;
            $nome = $destinatarios->nome;
            $identificador = $destinatarios->identificador;

            $this->mail->addAddress($destinatarios->email, "Bloco:$bloco Unidade:$unidade Resp:$nome Ident:$identificador");
            $this->mail->isHTML(true);
            $this->mail->Subject = utf8_decode($comunicado->Assunto);
            $this->mail->Body = utf8_decode($comunicado->Corpo);
            $this->mail->send();
            $resultado = true;

        } catch (Exception $e) {
            $erros = $this->mail->ErrorInfo;
            //$this->mail->smtp->reset();
        }

        $this->mail->clearAddresses();
        $this->mail->clearAttachments();

        return [
            'resultado' => $resultado,
            'erros' => $erros,
            'conta' => $this->conta->Id,
            'destinatario' => [
                'email' => $destinatarios->email,
                'dominio' => $this->ObterProvedor($destinatarios->email),
                'bloco' => $bloco,
                'unidade' => $unidade,
                'nome' => $nome,
                'identificador' => $identificador
            ],
            'comunicado' => $comunicado->Id
        ];

    }

    private function ObterProvedor($email)
    {

        $parts = explode('@', $email);
        return $parts[1];

    }
}