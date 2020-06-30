<?php


namespace Interact;


use Exception;

class Destinatarios
{

    public $Lista;

    function __construct($endpoint, $id)
    {
        $ch = curl_init("$endpoint/destinatarios?base=eq.$id");
        curl_setopt_array($ch, array(
            CURLOPT_POST => FALSE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_SSL_VERIFYPEER => FALSE
        ));

        try {
            $response = curl_exec($ch);

            if ($response === FALSE)
                die(curl_error($ch));

            $this->Lista = json_decode($response, TRUE);

        } catch (Exception $e) {
            new Exception($e->getMessage(), $e->getCode());
        }
    }

}