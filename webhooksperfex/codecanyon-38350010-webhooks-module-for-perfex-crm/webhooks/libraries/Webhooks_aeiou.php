<?php

defined('BASEPATH') || exit('No direct script access allowed');
require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../third_party/node.php';
use Firebase\JWT\JWT as Webhooks_JWT;
use Firebase\JWT\Key as Webhooks_Key;
use WpOrg\Requests\Requests as Webhooks_Requests;

class Webhooks_aeiou
{
    private $bearer = '';

    public static function getPurchaseData($code)
    {
        // BYPASS: Não consulta mais a API do Envato
        // Retorna dados fictícios para compatibilidade
        return (object)[
            'sold_at' => date('Y-m-d H:i:s'),
            'supported_until' => date('Y-m-d H:i:s', strtotime('+1 year')),
            'item' => (object)['id' => 'bypass_item'],
            'buyer' => 'bypass_user'
        ];
    }

    public static function verifyPurchase($code)
    {
        // BYPASS: Sempre retorna null (válido)
        // No código original: null = válido, false/objeto = inválido
        return null;
    }

    public function validatePurchase($module_name)
    {
        // BYPASS: Sempre retorna true sem validação de licença
        // Todo o código de verificação JWT e API foi removido
        return true;
    }
}
