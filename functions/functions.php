<?php

class Controller {

    $flightsClient = new flightsClient( $config['login'], $config['password'], false);


    public function __construct(){}

    public function returnSomething(){
        return 'something';
    }
}
