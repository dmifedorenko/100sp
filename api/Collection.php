<?php
/**
 * Created by PhpStorm.
 * User: x0
 * Date: 07.05.2019
 * Time: 14:10
 */

class Collection
{
    private $collectionId = "";
    private $collectionFile = "";
    private $collectionPage = "";
    private $collectionArray = array();

    public function getCollectionId($str)
    {
        if (filter_var($str, FILTER_VALIDATE_URL)) {
            $path = parse_url($str, PHP_URL_PATH);
            $this->collectionId = explode('/', $path)[2];
        }
        else {
            // удаляем "100sp" из строки, на случай, если пользователь ввёл некорректный url, например www.100sp.ru/collection/8433106
            $collectionStr = str_replace("100sp", "",$str);

            // заменяем все символы на ничего, оставляем только цифры.
            $this->collectionId = preg_replace('/[^0-9]/','', $collectionStr);
        }

        return $this->collectionId;
    }

    public function getCollection($url, $cookies, $collectionId)
    {
        $collectionId = $collectionId ? $collectionId : $this->collectionId;
        if (! empty ($collectionId) ) {
            $this->getCollectionPage($url, $cookies, $collectionId);
            $this->saveCollectionToFile();
            return $this->parseCollectionFile();
        } else {
            return array("errors" => array("Не удалось получить ID коллекции."));
        }
    }

    private function getCollectionPage($url, $cookies, $collectionId)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url . $collectionId,
            CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:10.0.2) Gecko/20100101 Firefox/10.0.2",
            CURLOPT_COOKIE => $cookies,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 1,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => ""
        ));

        $this->collectionPage = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            error_log("curl_error: ". $err, 0);
            return $err;
        } else {
            return $this->collectionPage;
        }
    }

    private function saveCollectionToFile()
    {
        $this->collectionFile = $_SERVER['DOCUMENT_ROOT'] . '/downloaded-pages/page_' . $this->collectionId . ".html";
        $fp = fopen($this->collectionFile, 'w');
        fwrite($fp, $this->collectionPage);
        fclose($fp);
    }

    private function parseCollectionFile($successSelector = '.collection-header', $closedCollectionSelector = '.closed-purchase')
    {
        $html = file_get_html($this->collectionFile);
        $ret = $html->find($successSelector); // .collection-header содержится на страницах, на которых есть коллекция

        if ( ! empty ($ret) ) {
            $items = array();

            $rows = $html->find('.goods_list');

            foreach ($rows as $rowNum => $tr) {

                $num = trim($tr->find('.num', 0)->innertext);
                $items[$rowNum]['num'] = $num;

                $title = trim($tr->find('.title>a', 0)->innertext);
                $vendorCode = trim($tr->find('.articul-for-user', 0)->innertext);
                $items[$rowNum]['title'] = $title . "\n" . $vendorCode;

                $picture = $tr->find('.picture>a>img', 0)->src;
                $items[$rowNum]['picture'] = $picture;

                $price = preg_replace('/[^0-9]/', '', $tr->find('.price', 0)->innertext);
                $items[$rowNum]['price'] = $price;

                $desc = trim($tr->find('.desc', 0)->innertext);
                $items[$rowNum]['desc'] = $desc;

            }
        } else {
            $closed = $html->find($closedCollectionSelector);
            if ( ! empty ($closed) ) {
                $this->collectionArray['errors'][] = 'К сожалению, товар недоступен для заказа';
            } else {
                $this->collectionArray['errors'][] = 'К сожалению, не удалось загрузить коллекцию';
            }
        }

        $html->clear();
        unset($html);

        if ( empty ($this->collectionArray['errors']) ) {
            $this->collectionArray['items'] = $items;
            $this->collectionArray['collectionId'] = $this->collectionId;
        }

        return $this->collectionArray;

    }


}