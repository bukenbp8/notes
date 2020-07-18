<?php

class Application
{
    public $model;

    public function __construct()
    {
        $this->loadTwig();
    }

    public function loadTwig()
    {

        $loggedIn = (currentUser()) ?  true : false;

        if (currentUser()) {
            $user = (array)currentUser();
            if ($user['role'] == 'Admin') {
                $admin = true;
            } else {
                $admin = false;
            }
        } else {
            $admin = false;
        }

        $loader = new \Twig\Loader\FilesystemLoader(
            [
                'app/views',
                'app/views/auth',
                'app/views/restricted',
                'app/views/inc',
                'app/views/notes',
                'app/views/dashboard'
            ]
        );
        $this->twig = new \Twig\Environment($loader);
        $this->twig->addGlobal('loggedIn', $loggedIn);
        $this->twig->addGlobal('admin', $admin);
    }

    protected function load_model($model)
    {
        if (class_exists($model)) {
            $this->{$model . 'Model'} = new $model(strtolower($model));
        }
    }
}
