<?php

use Craos\Email;

require_once 'Email.php';
$email = new Email\Email();

$conta = (object)array(
    'host' => 'smtp.office365.com',
    'auth' => true,
    'endereco' => 'correspondencia@animaclube.com.br',
    'senha' => 'Sam74172',
    'secure' => 'tls',
    'port' => 587,
    'ssl' => 'ca-cert.pem',
    'remetente' => 'Condomínio Ânima Clube',
    'dominio' => 'animaclube.com.br'
);

$email->setParametrosCorrespondente($conta);