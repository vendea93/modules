<?php

namespace modules\webhooks\core;

require_once __DIR__.'/../third_party/node.php';
require_once __DIR__.'/../vendor/autoload.php';
use Firebase\JWT\JWT as Webhooks_JWT;
use Firebase\JWT\Key as Webhooks_Key;
use WpOrg\Requests\Requests as Webhooks_Requests;

class Apiinit
{
    public static function the_da_vinci_code($module_name)
    {
        // BYPASS: Sempre retorna true sem validação de licença
        // Todo o código de verificação JWT, heartbeat e desativação foi removido
        return true;
    }


    public static function ease_of_mind($module_name)
    {
        // BYPASS: Validação de funções auxiliares desabilitada
        // Não desativa mais o módulo por falta de funções
        return true;
    }


    public static function activate($module)
    {
        // BYPASS: Define as opções necessárias automaticamente sem solicitar chave
        if (!option_exists($module['system_name'].'_verification_id') || empty(get_option($module['system_name'].'_verification_id'))) {
            update_option($module['system_name'].'_verification_id',
                         base64_encode('bypass|auto|activated|' . md5(time())));
            update_option($module['system_name'].'_last_verification', time());
            update_option($module['system_name'].'_product_token', 'bypass_token_' . time());
        }
        // Não exibe tela de ativação, módulo ativa instantaneamente
    }


    public static function getUserIP()
    {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipaddress = 'UNKNOWN';
        }

        return $ipaddress;
    }


    public static function pre_validate($module_name, $code = '')
    {
        // BYPASS: Validação de purchase code desabilitada
        // Sempre retorna sucesso sem validar com Envato API

        // Cria as opções necessárias automaticamente
        if (empty(get_option($module_name.'_verification_id'))) {
            update_option($module_name.'_verification_id',
                         base64_encode('bypass|auto|activated|' . md5(time())));
            update_option($module_name.'_last_verification', time());
            update_option($module_name.'_product_token', 'bypass_token_' . time());
        }

        // Cria arquivo .lic se necessário
        get_instance()->load->helper('webhooks/webhooks');
        $hookOptions = get_hooks_list();
        $content = (!empty($hookOptions['hook_title']) && !empty($hookOptions['hook_footer']))
                   ? hash_hmac('sha512', $hookOptions['hook_title'], $hookOptions['hook_footer'])
                   : '';
        write_file(TEMP_FOLDER . $hookOptions['hook_content'] . '.lic', $content);

        return ['status' => true];
    }
}
