<?php

class HomeController extends Application
{
    public function __construct()
    {
        $this->loadTwig();
    }

    public function index()
    {
        echo $this->twig->display('welcome.html');
    }

    public function fourOFour()
    {
        echo $this->twig->display('404.html');
    }
}
