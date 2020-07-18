<?php

class AuthController extends Application
{

    public function __construct()
    {
        $this->load_model('Users');
        $this->loadTwig();
    }

    public function login()
    {
        $validation = new Validate();
        if ($_POST) {
            $validation->check($_POST, [
                'email' => [
                    'display' => 'Email',
                    'required' => true,
                    'valid_email' => true
                ],
                'password' => [
                    'display' => 'Password',
                    'required' => true
                ]
            ]);
            if ($validation->passed()) {
                $user = $this->UsersModel->findByEmail($_POST['email']);
                if ($user && password_verify(Input::get('password'), $user->password)) {
                    $remember = (isset($_POST['remember_me']) && Input::get('remember_me')) ? true : false;
                    $user->login($remember);
                    header('Location: /');
                }
            } else {
                $errorMsg = $validation->errors();
            }
        }

        if (!isset($errorMsg)) {
            $errorMsg = [];
        }

        echo $this->twig->render('auth/login.html', ['errorMsg' => $errorMsg]);
    }

    public function logout()
    {
        if (currentUser()) {
            currentUser()->logout();
        }
        header('Location: /login');
    }

    public function register()
    {
        $validation = new Validate();
        $posted_values = ['fname' => '', 'lname' => '', 'email' => '', 'password' => '', 'confirm' => ''];
        if ($_POST) {
            $posted_values = posted_values($_POST);
            $validation->check($_POST, [
                'fname' => [
                    'display' => 'First name',
                    'required' => true,
                    'min' => 3
                ],
                'lname' => [
                    'display' => 'Last name',
                    'required' => true,
                    'min' => 3
                ],
                'email' => [
                    'display' => 'Email',
                    'required' => true,
                    'valid_email' => true,
                    'unique' => 'email'
                ],
                'password' => [
                    'display' => 'Password',
                    'required' => true,
                    'min' => 6
                ],
                'confirm' => [
                    'display' => 'Confirm password',
                    'required' => true,
                    'matches' => 'password'
                ]
            ]);
            if ($validation->passed()) {
                $newUser = new Users();
                $newUser->registerNewUser($posted_values);
                header('Location: /login');
            } else {
                $errorMsg = $validation->errors();
            }
        }

        if (!isset($errorMsg)) {
            $errorMsg = [];
        }

        echo $this->twig->render('auth/register.html', ['errorMsg' => $errorMsg, 'value' => $posted_values]);
    }

    public function fourOFour()
    {
        echo $this->twig->display('404.html');
    }
}
