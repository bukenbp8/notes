<?php

class ProfileController extends Application
{
    protected $_user;

    public function __construct()
    {
        $this->loadTwig();
        $this->load_model('Users');

        if (currentUser()) {
            $this->_user = makeArray(currentUser());
        } else {
            header('Location: /restricted');
        }
    }

    public function profile()
    {
        // change email
        if (isset($_POST['email'])) {

            $validation = new Validate();
            $posted_values = posted_values($_POST);
            $validation->check($_POST, [
                'email' => [
                    'display' => 'Email',
                    'required' => true,
                    'valid_email' => true,
                    'unique' => true,
                    'min' => 6
                ],
                'confirm' => [
                    'display' => 'Confirm Email',
                    'required' => true,
                    'min' => 6,
                    'matches' => 'email'
                ]
            ]);
            if ($validation->passed()) {
                $this->UsersModel->update(['email' => $posted_values['email']], $this->_user['id']);
                $emailMsg = 'Your email was changed';
            } else {
                $errorEmail = $validation->errors();
                $emailMsg = '';
            }
        }

        // change password
        if (isset($_POST['password'])) {

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
                $hashedPw = password_hash($posted_values['password'], PASSWORD_DEFAULT);
                $this->UsersModel->update(['password' => $hashedPw], $this->_user['id']);
                $pwMsg = 'Your password was changed';
            } else {
                $errorPw = $validation->errors();
                $pwMsg = '';
            }
        }

        if (!isset($errorEmail)) {
            $errorEmail = [];
        }

        if (!isset($errorPw)) {
            $errorPw = [];
        }

        if (!isset($emailMsg)) {
            $emailMsg = '';
        }

        if (!isset($pwMsg)) {
            $pwMsg = '';
        }

        echo $this->twig->render('/profile/profile.html', ['user' => $this->_user, 'errorEmail' => $errorEmail, 'errorPw' => $errorPw, 'emailMsg' => $emailMsg, 'pwMsg' => $pwMsg]);
    }


    public function deleteAccount($id)
    {
        if ($this->user['id'] == $id) {
            $this->UsersModel->delete($id);
            currentUser()->logout();
            header('Location: /register');
        } else {
            header('Location: /restricted');
        }
    }
}
