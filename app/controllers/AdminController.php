<?php

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
        $users = $this->UsersModel->find();

        echo $this->twig->render('/dashboard/dashboard.html', ['users' => $users]);
    }

    public function editUser($id)
    {
        $user = $this->UsersModel->findById($id);

        $validation = new Validate();

        $posted_values = ['fname' => '', 'lname' => '', 'email' => ''];

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
                    'valid_email' => true
                ]
            ]);
            if ($validation->passed()) {
                $this->UsersModel->update($posted_values, $id);
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
        $this->UsersModel->delete($id);
        header('Location: /dashboard');
    }
}
