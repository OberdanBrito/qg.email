<?php


namespace Interact;

use Exception;

class Comunicados
{

    public $Filedate;
    public $Firstuser;
    public $Condominio;
    public $Id;
    public $Assunto;
    public $Corpo;
    public $Anexos;

    function __construct($endpoint, $id)
    {
        $ch = curl_init("$endpoint/comunicados?id=eq.$id");
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
            $this->Assunto = $result['assunto'];
            $this->Corpo = $result['corpo'];

            $this->Anexos = $this->anexos($endpoint, $this->Id);

        } catch (Exception $e) {
            new Exception($e->getMessage(), $e->getCode());
        }
    }

    private function anexos($endpoint, $id) {

        $anexos = new Anexos($endpoint, $id);
        return $anexos->Lista;

    }


}