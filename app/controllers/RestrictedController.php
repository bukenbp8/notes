<?php

class RestrictedController extends Application
{

    public function restricted()
    {
        echo $this->twig->display('/restricted/restricted.html');
    }
}
