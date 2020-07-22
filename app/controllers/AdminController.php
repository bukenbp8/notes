<?php

namespace Controllers;

use Core\Application;
use Core\Validate;

class AdminController extends Application
{
    public function __construct()
    {
        parent::__construct();

        $this->load_model('Users');

        $user = (array)currentUser();

        if ($user['role'] != 'Admin') {
            header('Location: /restricted');
        }
    }

    public function dashboard()
    {
        // grabs all users
        $users = $this->Users->find();

        echo $this->twig->render('/dashboard/dashboard.html', ['users' => $users]);
    }

    public function editUser($id)
    {
        $user = $this->Users->findById($id);

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
                    'unique_email' => 'email'
                ]
            ]);
            if ($validation->passed()) {
                $this->Users->update($posted_values, $id);
                header('Location: /dashboard');
            } else {
                $errorMsg = $validation->errors();
            }
        }

        if (!isset($errorMsg)) {
            $errorMsg = [];
        }

        echo $this->twig->render('/dashboard/editUser.html', ['user' => $user, 'errorMsg' => $errorMsg]);
    }

    public function deleteUser($id)
    {
        $this->Users->delete($id);
        header('Location: /dashboard');
    }
}
