<?php

namespace Core;


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

            $userId = $user['id'];

            if ($user['role'] == 'Admin') {
                $admin = true;
            } else {
                $admin = false;
            }
        } else {
            $admin = false;

            $userId = false;
        }

        $loader = new \Twig\Loader\FilesystemLoader(
            [
                'app/views',
                'app/views/auth',
                'app/views/restricted',
                'app/views/inc',
                'app/views/notes',
                'app/views/dashboard',
                'app/views/profile'
            ]
        );
        $this->twig = new \Twig\Environment($loader);
        $this->twig->addGlobal('loggedIn', $loggedIn);
        $this->twig->addGlobal('userId', $userId);
        $this->twig->addGlobal('admin', $admin);
    }

    protected function load_model($model, $namespace = 'Models\\')
    {
        $modelPath = $namespace . $model;
        if (class_exists($modelPath)) {
            $this->{$model} = new $modelPath();
        }
    }
}
