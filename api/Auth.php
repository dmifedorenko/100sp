<?php
/**
 * Created by PhpStorm.
 * User: x0
 * Date: 07.05.2019
 * Time: 13:52
 */

class Auth
{
    public function login($username, $password) {
        // TODO: Авторизация
        // Отправляем логин и пароль, получаем cookies
    }

    public function setCookies($auth, $ring, $uid) {
        return "auth=" . $auth . ";ring=" . $ring . ";uid=" . $uid . ";";
    }
}
