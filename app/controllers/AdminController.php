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

        echo $this->twig->render('/dashboard/editUser.html', ['user' => $user]);
    }

    public function deleteUser($id)
    {
        $this->UsersModel->delete($id);
    }
}
