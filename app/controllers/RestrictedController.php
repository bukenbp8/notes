<?php

namespace Controllers;

use Core\Application;

class RestrictedController extends Application
{

    public function restricted()
    {
        echo $this->twig->display('/restricted/restricted.html');
    }
}
