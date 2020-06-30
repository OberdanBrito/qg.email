<?php


namespace Interact;


use Exception;

class Contas
{

    public $Filedate;
    public $Firstuser;
    public $Condominio;
    public $Id;
    public $Endereco;
    public $Senha;
    public $Smtp_host;
    public $Smtp_secure;
    public $Smtp_port;
    public $Smtp_auth;
    public $Nome;
    public $Habilitado;
    public $Dominio;

    function __construct($endpoint, $id)
    {
        $ch = curl_init("$endpoint/correspondentes?id=eq.$id");
        curl_setopt_array($ch, array(
            CURLOPT_POST => FALSE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_HTTPHEADER => array(
                'Prefer: return=representation',
                'Accept: application/vnd.pgrst.object+json'
            ),
        ));

        try {
            $response = curl_exec($ch);

            if ($response === FALSE)
                die(curl_error($ch));

            $result = json_decode($response, TRUE);
            $this->Filedate = $result['filedate'];
            $this->Firstuser = $result['firstuser'];
            $this->Condominio = $result['condominio'];
            $this->Id = $result['id'];
            $this->Endereco = $result['endereco'];
            $this->Senha = $result['senha'];
            $this->Smtp_host = $result['smtp_host'];
            $this->Smtp_secure = $result['smtp_secure'];
            $this->Smtp_port = $result['smtp_port'];
            $this->Smtp_auth = $result['smtp_auth'];
            $this->Nome = $result['nome'];
            $this->Habilitado = $result['habilitado'];

        } catch (Exception $e) {
            new Exception($e->getMessage(), $e->getCode());
        }
    }

    private function ObterProvedor() {

        $parts = explode('@', $this->Endereco);
        $this->Dominio = $parts[1];
        return $this->Dominio;

    }

    public function ObterDKIM() {

        $domain = $this->ObterProvedor();
        $selector = 'api';
        $privatekeyfile = $selector . '_dkim_private.pem';
        $publickeyfile = $selector . '_dkim_public.pem';

        if (file_exists($privatekeyfile)) {
            $privatekey = file_get_contents($privatekeyfile);
            $publickey = file_get_contents($publickeyfile);
        } else {
            $pk = openssl_pkey_new(
                [
                    'digest_alg' => 'sha256',
                    'private_key_bits' => 2048,
                    'private_key_type' => OPENSSL_KEYTYPE_RSA,
                ]
            );
            openssl_pkey_export_to_file($pk, $privatekeyfile);
            $pubKey = openssl_pkey_get_details($pk);
            $publickey = $pubKey['key'];
            file_put_contents($publickeyfile, $publickey);
            $privatekey = file_get_contents($privatekeyfile);
        }

        $dnskey = "$selector._domainkey.$domain IN TXT";
        $dnsvalue = '"v=DKIM1; h=sha256; t=s; p=" ';
        $dnsvalue2 = '"v=DKIM1\; h=sha256\; t=s\; p=" ';
        $publickey = preg_replace('/^-+.*?-+$/m', '', $publickey);
        $publickey = str_replace(["\r", "\n"], '', $publickey);
        $keyparts = str_split($publickey, 253); //Becomes 255 when quotes are included

        foreach ($keyparts as $keypart) {
            $dnsvalue .= '"' . trim($keypart) . '" ';
            $dnsvalue2 .= '"' . trim($keypart) . '" ';
        }

    }

}