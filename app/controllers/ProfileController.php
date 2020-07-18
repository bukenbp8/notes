<?php

class ProfileController extends Application
{
    public function __construct()
    {
        $this->loadTwig();
        $this->load_model('Users');

        if (!currentUser()) {
            header('Location: /restricted');
        }
    }

    public function profile()
    {
        $user = makeArray(currentUser());
        echo $this->twig->render('/profile/profile.html', ['user' => $user]);
    }

    public function deleteAccount($id)
    {
        $user = makeArray(currentUser());
        if ($user['id'] == $id) {
            $this->UsersModel->delete($id);
            currentUser()->logout();
            header('Location: /register');
        } else {
            header('Location: /restricted');
        }
    }
}
