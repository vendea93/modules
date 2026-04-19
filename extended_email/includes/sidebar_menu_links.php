<?php

/*
 * Inject sidebar menu and links for customtables module
 */

if ($CI->db->table_exists(db_prefix().'extended_email_settings')) {
  hooks()->add_action('admin_init', function () use ($cache_data){
    if(!isset($cache_data) && $cache_data != "116c3269332c87ef03f2fbf2185b64a132d4b09cfb51afeb7763eecdb7379dc04c5f8652fe87261ee8c366207dc3184773dca9d23f8ee04c17fce3e308fe65036e466f7a6d1695cd6066bc7ddcd072a38cb52215f4189704483a1b64ae3a3e8a543a770451dc0e1d2a18cf8a4118cc7f06427da009031ddc4d20e6d56e76d112ac50390044e34bba1f1a1713b15ff77e10de133eb6d82e9af5f6e89ee3916d20"){
      return;
    }
          $staff = get_staff();
          $CI = &get_instance();
          $CI->app_menu->add_setup_menu_item('extended_email', [
              'slug'     => 'extended_email',
              'name'     => _l('extended_email'),
              'position' => 30,
          ]);

          $CI->app_menu->add_setup_children_item('extended_email', [
              'slug'     => 'extended_email_form',
              'name'     => _l('extended_email_form'),
              'href'     => admin_url('extended_email'),
              'position' => 2,
          ]);

          if (is_admin()) {
              $CI->app_menu->add_setup_children_item('extended_email', [
                  'slug'     => 'extended_email_log_history',
                  'name'     => _l('extended_email_log_history'),
                  'href'     => admin_url('extended_email/extended_email_log_history'),
                  'position' => 3,
              ]);
          }

  \modules\extended_email\core\Apiinit::ease_of_mind(EXTENDED_EMAIL_MODULE);
  });
    $CI->config->load('extended_email/email', true);
    $settings = $CI->config->item('email');
    if ($settings['has_setting']) {
        $CI->load->library('email');
        $CI->email->initialize($CI->config->item('email'));
    }
}
