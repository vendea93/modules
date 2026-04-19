<?php

defined('BASEPATH') or exit('No direct script access allowed');
header('Content-Type: text/html; charset=utf-8');

class Otpless extends App_Controller
{
    public function __construct()
    {
        parent::__construct();

        if ($this->app->is_db_upgrade_required()) {
            redirect(admin_url());
        }

        load_admin_language();
        $this->load->model('Authentication_model');

        hooks()->do_action('admin_auth_init');
    }

    /**
     * Entry point for login.
     */
    public function login()
    {
        if (get_option('otpless_functionality') == '1') {
            $this->admin();
        } else {
            set_alert('danger', _l('Invalid login credentials.'));
            redirect(admin_url());
        }
    }

    /**
     * Show the custom login page.
     */
    public function admin()
    {
        if (is_staff_logged_in()) {
            redirect(admin_url());
        }

        $data['title'] = _l('admin_auth_login_heading');
        $this->app_scripts->add('otpless-js', module_dir_url('otpless', 'assets/js/jquery.min.js'), 'admin', ['app-js']);

        $this->load->view('otpless_login_admin', $data);
    }

    /**
     * Handle the OTP-less admin login logic.
     */
    public function admin_login()
    {
        $data = $this->input->post('data');
        $loginData = json_decode($data, true);

        if (!$loginData || !isset($loginData['status']) || $loginData['status'] !== 'SUCCESS') {
            set_alert('danger', _l('Login failed. Invalid or incomplete data.'));
            redirect(admin_url('otpless/admin'));
            return;
        }

        $identity = $loginData['identities'][0] ?? [];
        $loginType = $identity['identityType'] ?? '';
        $identityValue = $identity['identityValue'] ?? '';

        if (empty($loginType) || empty($identityValue)) {
            set_alert('danger', _l('Invalid login credentials.'));
            redirect(admin_url('otpless/admin'));
            return;
        }

        $isEmail = ($loginType === 'EMAIL');
        $column = $isEmail ? 'email' : 'phonenumber';

        $this->db->where($column, $identityValue);
        $user = $this->db->get(db_prefix() . 'staff')->row();

        if (!$user) {
            $identityLabel = $isEmail ? 'Email' : 'Phone';
            log_activity("Non Existing User Tried to Login [{$identityLabel}: {$identityValue}, Is Staff Member: Yes, IP: {$this->input->ip_address()}]");
            set_alert('danger', _l($isEmail ? 'Invalid email.' : 'Invalid Mobile no.'));
            redirect(admin_url('otpless/admin'));
            return;
        }

        if ((int) $user->active === 0) {
            log_activity("Inactive User Tried to Login [Email: {$user->email}, Is Staff Member: Yes, IP: {$this->input->ip_address()}]");
            set_alert('danger', _l('admin_auth_inactive_account'));
            redirect(admin_url('otpless/admin'));
            return;
        }

        hooks()->do_action('before_staff_login', [
            'email'  => $user->email,
            'userid' => $user->staffid,
        ]);

        $this->session->set_userdata([
            'staff_user_id'   => $user->staffid,
            'staff_logged_in' => true,
        ]);

        $this->create_autologin($user->staffid, true);
        $this->update_login_info($user->staffid, true);

        $this->load->model('announcements_model');
        $this->announcements_model->set_announcements_as_read_except_last_one(get_staff_user_id(), true);

        maybe_redirect_to_previous_url();

        hooks()->do_action('after_staff_login');
        redirect(admin_url());
    }

    /**
     * Creates autologin cookie.
     *
     * @param int  $user_id
     * @param bool $staff
     *
     * @return bool
     */
    private function create_autologin($user_id, $staff)
    {
        $this->load->helper('cookie');

        $key = substr(md5(uniqid(rand() . get_cookie($this->config->item('sess_cookie_name')))), 0, 16);

        $this->user_autologin->delete($user_id, $key, $staff);

        if ($this->user_autologin->set($user_id, md5($key), $staff)) {
            set_cookie([
                'name'   => 'autologin',
                'value'  => serialize(['user_id' => $user_id, 'key' => $key]),
                'expire' => 60 * 60 * 24 * 31 * 2, // 2 months
            ]);

            return true;
        }

        return false;
    }

    /**
     * Updates login metadata.
     *
     * @param int  $user_id
     * @param bool $staff
     */
    private function update_login_info($user_id, $staff)
    {
        $table = $staff ? db_prefix() . 'staff' : db_prefix() . 'contacts';
        $idKey = $staff ? 'staffid' : 'id';

        $this->db->set('last_ip', $this->input->ip_address());
        $this->db->set('last_login', date('Y-m-d H:i:s'));
        $this->db->where($idKey, $user_id);
        $this->db->update($table);

        log_activity("User Successfully Logged In [User Id: {$user_id}, Is Staff Member: " . ($staff ? 'Yes' : 'No') . ", IP: {$this->input->ip_address()}]");
    }
}
