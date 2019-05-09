<?php

class Config
{
    //Если версия PHP ниже 5.6, то необходимо переписать на define, либо переписать массив AUTH_COOKIES в разные переменные.
    const
        URL = "https://www.100sp.ru/collection/",
        LOGIN = "",
        PASSWORD = "",
        AUTH_COOKIES = [
        "auth" => "",
        "ring" => "",
        "uid"  => ""
    ];
}