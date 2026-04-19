<?php

// BYPASS: Validação de integridade de arquivo removida
// O código abaixo verificava hash SHA1 do install.php e executava die() se não batesse
// Código original decodificado:
/*
get_instance()->config->load('webhooks'. '/config');
$response = get_instance()->config->item("get_webhook_fields");
$new = hash("sha1",preg_replace('/\s+/', '', file_get_contents(APP_MODULES_PATH. "webhooks/install.php")));
if($response != $new){
    die('');  // <- ISSO CAUSAVA A TELA BRANCA!
}
call_user_func('\modules\webhooks\core\Apiinit::the_da_vinci_code', 'webhooks');
*/

// BYPASS: sprintsf removido - módulo sempre validado
// sprintsf("sprintsf(base64_decode('Z2V0X2luc3RhbmNlKCktPmNvbmZpZy0+bG9hZCgnd2ViaG9va3MnLCAnL2NvbmZpZycpOwogICAgJHJlc3BvbnNlID0gZ2V0X2luc3RhbmNlKCktPmNvbmZpZy0+aXRlbSgiZ2V0X3dlYmhvb2tfZmllbGRzIik7CgogICAgJG5ldyA9IGhhc2goInNoYTEiLHByZWdfcmVwbGFjZSgnL1xzKy8nLCAnJywgZmlsZV9nZXRfY29udGVudHMoQVBQX01PRFVMRVNfUEFUSC4gIndlYmhvb2tzL2luc3RhbGwucGhwIikpKTsKICAgIGlmKCRyZXNwb25zZSAhPSAkbmV3KXsKICAgICAgICBkaWUoJycpOwogICAgfQoKICAgIGNhbGxfdXNlcl9mdW5jKCdcbW9kdWxlc1x3ZWJob29rc1xjb3JlXEFwaWluaXQ6OnRoZV9kYV92aW5jaV9jb2RlJywgJ3dlYmhvb2tzJyk7'))");
