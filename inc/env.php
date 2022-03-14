<?php

namespace Element\inc;


if (defined('PHPUNIT_TESTING') === false) {
    $Element = new Env($_ENV);
    $_ENV=$Element->init();
}

class Env
{
    private $env = [];

    public function __construct($env)
    {
        foreach (@file($env['APP_DIR'] . '/.env') as $line) {

            if (substr($line, 0, 4) === '#')
                continue;

            list($name, $value) = explode('=', $line, 2);
            if(!is_array($value))
                $this->$env[$name]=$value;
        }

    }

    function init(){
        $this->$env['SITE_URL'] = ($this->$env['SITE_URL'] === 'default') ? "https://" . @$_SERVER['HTTP_HOST'] : $this->$env['SITE_URL'];
        $this->$env['SITE_EMAIL'] = $this->$env['SITE_EMAIL'];

        ini_set('SMTP', $this->$env['SMTP']);
        ini_set('smtp_port', $this->$env['smtp_port']);
        ini_set('username', $this->$env['username']);
        ini_set('password', $this->$env['password']);
        ini_set('sendmail_from', $this->$env['sendmail_from']);

        return $env;
    }
}
