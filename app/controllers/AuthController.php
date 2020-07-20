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
        if ($_POST) {
            $validation = new Validate();
            $validation->check($_POST, [
                'email' => [
                    'display' => 'Email',
                    'required' => true,
                    'valid_email' => true,
                    'must_exist' => 'email'
                ],
                'password' => [
                    'display' => 'Password',
                    'required' => true,
                    'wrongPw' => 'password'
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
        if ($_POST) {
            $validation = new Validate();
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
                header('Location: /registerinfo');
            } else {
                $errorMsg = $validation->errors();
            }
        }

        if (!isset($errorMsg)) {
            $errorMsg = [];
        }

        echo $this->twig->render('auth/register.html', ['errorMsg' => $errorMsg, 'value' => $posted_values]);
    }

    public function registerinfo()
    {
        echo $this->twig->display('auth/registerinfo.html');
    }

    public function fourOFour()
    {
        echo $this->twig->display('404.html');
    }

    // user email verification 
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

    public function resetPassword($id, $key)
    {
        $user = $this->UsersModel->findById($id);
        $u = makeArray($user);

        // token from email must match token from the database && the email can't be older than an hour
        if ($key == $u['token'] && !($u['retrieval_time'] >= ($u['retrieval_time']) + 3600)) {

            if ($_POST) {
                $validation = new Validate();
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
                    $newPW = password_hash($posted_values['password'], PASSWORD_DEFAULT);
                    // new token makes retrival link useless after giving a new pw
                    $newToken = bin2hex(random_bytes(16));
                    $this->UsersModel->update(['password' => $newPW, 'token' => $newToken], $id);

                    header('Location: /login');
                } else {
                    $errorMsg = $validation->errors();
                }
            }

            if (!isset($errorMsg)) {
                $errorMsg = [];
            }
            echo $this->twig->display('auth/reset.html', ['errorMsg' => $errorMsg]);
        } else {
            header('Location: /restricted');
        }
    }

    public function pwinfo()
    {
        echo $this->twig->display('auth/pwinfo.html');
    }

    public function forgottenpw()
    {
        if ($_POST) {
            $validation = new Validate();
            $posted_values = posted_values($_POST);
            $validation->check($_POST, [
                'email' => [
                    'display' => 'Email',
                    'required' => true,
                    'valid_email' => true,
                    'must_exist' => 'email'
                ]
            ]);
            if ($validation->passed()) {
                $user = $this->UsersModel->findByEmail($posted_values['email']);
                $u = makeArray($user);
                $user->update(['retrieval_time' => time()], $u['id']);
                $this->EmailModel->resetPW($u['email'], $u['fname'], $u['lname'], $u['id'], $u['token']);
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
