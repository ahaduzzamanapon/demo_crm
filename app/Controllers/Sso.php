<?php

namespace App\Controllers;

class Sso extends App_Controller {

    function __construct() {
        parent::__construct();
    }

    public function login() {

        $config = new \Config\App();
        $shared_secret = $config->ssoSharedSecret;

        $hash = $this->request->getPost('hash');
        $user_data = $this->request->getPost();
        unset($user_data['hash']);

        $calculated_hash = hash_hmac('sha256', http_build_query($user_data), $shared_secret);

        if ($hash !== $calculated_hash) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Invalid hash.']);
        }

        $email = $this->request->getPost('email');
        $first_name = $this->request->getPost('first_name');
        $last_name = $this->request->getPost('last_name');

        if (!$email) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Email is required.']);
        }

        $user = $this->Users_model->get_one_where(array('email' => $email, 'status' => 'active', 'deleted' => 0));

        if ($user) {
            // User exists, log them in
            $this->session->set('user_id', $user->id);
            return $this->response->setJSON(['success' => true, 'message' => 'Login successful.']);
        } else {
            // User does not exist, create a new user
            $password = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 8);
            $user_data = array(
                'email' => $email,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'user_type' => 'client',
                'is_admin' => 0,
                'status' => 'active',
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'created_at' => get_current_utc_time()
            );

            $new_user_id = $this->Users_model->ci_save($user_data);

            if ($new_user_id) {
                $this->session->set('user_id', $new_user_id);
                return $this->response->setJSON(['success' => true, 'message' => 'User created and logged in successfully.']);
            } else {
                return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Could not create user.']);
            }
        }
    }
}
