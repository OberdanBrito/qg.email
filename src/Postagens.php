<?php

namespace Interact;

use Exception;

class Postagens
{
    public $config;
    public $log;
    public $Filedate;
    public $Firstuser;
    public $Condominio;
    public $Id;
    public $ComunicadoId;
    public $ContaId;
    public $BaseId;
    public $Reenviar;
    public $Base;
    public $Conta;
    public $Comunicado;

    function __construct($config, $log, $endpoint, $id)
    {
        $this->config = $config;
        $this->log = $log;

        $ch = curl_init("$endpoint/postagem?id=eq.$id");
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
            if (array_key_exists('message', $result) && $result['message'] == 'JSON object requested, multiple (or no) rows returned') {
                header("HTTP/1.0 404 Not Found");
                echo json_encode([
                    'erro' => 'Nenhum destinatário foi cadastrado para esta base',
                    'detalhe' => $result['message'],
                    'numero' => 2
                ]);
                die();
            }

            $this->Filedate = $result['filedate'];
            $this->Firstuser = $result['firstuser'];
            $this->Condominio = $result['condominio'];
            $this->Id = $result['id'];
            $this->ComunicadoId = $result['comunicado'];
            $this->ContaId = $result['conta'];
            $this->BaseId = $result['base'];
            $this->Reenviar = $result['reenviar'];


            $this->Base = new Bases($endpoint, $this->BaseId);
            $this->Conta = new Contas($endpoint, $this->ContaId);
            $this->Conta->ObterDKIM();
            $this->Comunicado = new Comunicados($endpoint, $this->ComunicadoId);

        } catch (Exception $e) {
            new Exception($e->getMessage(), $e->getCode());
        }
    }

    public function VerificarDestinatarios()
    {
        return (count($this->Base->Destinatarios) > 0);
    }

    public function Iniciar()
    {

        foreach ($this->Base->Destinatarios as $destinatario) {

            $envio = new Envio($this->config, $this->log, $this->Conta);
            $objdestinatario = (object)$destinatario;
            $resultado = $envio->Iniciar($objdestinatario, $this->Comunicado->Anexos, $this->Comunicado);
            $this->RegistraResultado(json_encode([
                'firtuser' => $this->Firstuser,
                'comunicado' => $this->ComunicadoId,
                'postagem' => $this->Id,
                'base' => $this->BaseId,
                'destinatario' => $objdestinatario->id,
                'resultado' => $resultado['resultado'],
                'erros' => $resultado['erros'],
            ]));
        }

    }

    private function RegistraResultado($data)
    {

        $endpoint = $this->config->endpoint;
        $ch = curl_init("$endpoint/postagem_log");
        curl_setopt_array($ch, array(
            CURLOPT_POST => TRUE,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data),
                'Prefer: return=representation',
                'Accept: application/vnd.pgrst.object+json'
            ),
        ));

        try {
            $response = curl_exec($ch);

            if ($response === FALSE)
                die(curl_error($ch));

            echo $response;
        } catch (Exception $e) {

            header("HTTP/1.0 404 Not Found");
            echo json_encode([
                'erro' => 'Erro ao salvar os dados do log',
                'detalhe' => $e->getMessage(),
                'numero' => $e->getCode()
            ]);
            die();

        }

    }

}