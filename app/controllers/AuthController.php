<?php

class AuthController extends Application
{

    public function __construct()
    {
        $this->load_model('Users');
        $this->load_model('Email');
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
                $u = makeArray($user);
                if ($user && password_verify(Input::get('password'), $user->password) && $u['email_verified'] == 1) {
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
                $u = makeArray($this->UsersModel->getLastUser());
                $this->EmailModel->registrationEmail($u[0]['email'], $u[0]['fname'], $u[0]['lname'], $u[0]['id'], $u[0]['token']);
                header('Location: /regComplete');
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

    public function verify($id, $key)
    {
        $user = $this->UsersModel->findById($id);
        $u = makeArray($user);

        if ($key == $u['token']) {
            $this->UsersModel->update(['email_verified' => true], $id);
            echo $this->twig->display('auth/verify.html');
        } else {
            header('Location: /restricted');
        }
    }

    public function retrieve($id, $key)
    {
        $user = $this->UsersModel->findById($id);
        $u = makeArray($user);

        if ($key == $u['token']) {

            $validation = new Validate();
            $posted_values = ['password' => '', 'confirm' => ''];

            if ($_POST) {
                $posted_values = posted_values($_POST);

                $validation->check($_POST, [
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
                    $this->UsersModel->update(['password' => $posted_values['password']], $id);
                    // new token makes retrival link useless
                    $newToken = substr(md5(mt_rand()), 0, 64);
                    $this->UsersModel->update(['token' => $newToken], $id);
                    header('Location: /login');
                } else {
                    $errorMsg = $validation->errors();
                }
            }

            if (!isset($errorMsg)) {
                $errorMsg = [];
            }


            echo $this->twig->display('auth/retrieve.html', ['errorMsg' => $errorMsg]);
        } else {
            header('Location: /restricted');
        }
    }

    public function registerinfo()
    {
        $title = 'Registration success!';
        $info = 'We sent you an email with a verification link. Please click on it to be able to login.';
        echo $this->twig->display('auth/registerinfo.html', ['title' => $title, 'info' => $info]);
    }

    public function pwinfo()
    {
        echo $this->twig->display('auth/pwinfo.html');
    }

    public function forgottenpw()
    {
        $validation = new Validate();
        $posted_values = ['email' => ''];

        if ($_POST) {
            $posted_values = posted_values($_POST);
            $validation->check($_POST, [
                'email' => [
                    'display' => 'Email',
                    'required' => true,
                    'valid_email' => true,
                ]
            ]);
            if ($validation->passed()) {
                $user = makeArray($this->UsersModel->findByEmail($posted_values['email']));
                $this->EmailModel->retrievePW($user['email'], $user['fname'], $user['lname'], $user['id'], $user['token']);
                header('Location: /pwinfo');
            } else {
                $errorMsg = $validation->errors();
            }
        }

        if (!isset($errorMsg)) {
            $errorMsg = [];
        }

        echo $this->twig->display('auth/forgottenpw.html', ['errorMsg' => $errorMsg]);
    }
}
