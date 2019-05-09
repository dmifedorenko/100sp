<?php
include './Config.php';
include './Auth.php';
include './Collection.php';
include './CsvGenerator.php';

include './vendor/simple_html_dom.php';

header("Content-type: application/json; charset=utf-8");

if ( ($_POST['type'] === 'collection-send') && ( ! empty ($_POST['value']) ) )  {
    $auth = new Auth();

    $cookies = $auth->setCookies(Config::AUTH_COOKIES["auth"], Config::AUTH_COOKIES["ring"], Config::AUTH_COOKIES["uid"]);

    $collection = new Collection($_POST['value']);
    $collectionArray = $collection->getCollection(Config::URL, $cookies);
    echo json_encode($collectionArray);
}

if ( ($_POST['type'] === 'save-to-csv') && ( ! empty ($_POST['value']) ) )  {
    $collectionArray = json_decode($_POST['value']);
    $csv = new CsvGenerator();
    $fileName = $csv->generate(json_decode($_POST['value']));

    echo json_encode( array('url' => $fileName ) );
}
