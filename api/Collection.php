<?php
/**
 * Created by PhpStorm.
 * User: x0
 * Date: 07.05.2019
 * Time: 14:10
 */

class Collection
{
    private $id = "";
    private $file = "";
    private $page = "";
    private $array = array();

    function __construct($str)
    {
        preg_match('~(\d+)$~', $str, $matches);
        $this->id = (int)$matches[1];
    }

    public function getCollection($url, $cookies)
    {
        if ($this->id) {
            $this->getCollectionPage($url, $cookies, $this->id);
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

        $this->page = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            error_log("curl_error: ". $err, 0);
            return $err;
        } else {
            return $this->page;
        }
    }

    private function saveCollectionToFile()
    {
        $this->file = $_SERVER['DOCUMENT_ROOT'] . '/downloaded-pages/page_' . $this->file . ".html";
        $fp = fopen($this->file, 'w');
        fwrite($fp, $this->page);
        fclose($fp);
    }

    private function parseCollectionFile($successSelector = '.collection-header', $closedCollectionSelector = '.closed-purchase')
    {
        $html = file_get_html($this->file);
        $ret = $html->find($successSelector); // .collection-header содержится на страницах, на которых есть коллекция

        $items = array();

        if ( ! empty ($ret) ) {

            $rows = $html->find('.goods_list');

            foreach ($rows as $rowNum => $tr) {
                /** @var $tr simple_html_dom_node */

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
                $this->array['errors'][] = 'К сожалению, товар недоступен для заказа';
            } else {
                $this->array['errors'][] = 'К сожалению, не удалось загрузить коллекцию';
            }
        }

        $html->clear();
        unset($html);

        if ( empty ($this->array['errors']) ) {
            $this->array['items'] = $items;
            $this->array['collectionId'] = $this->id;
        }

        return $this->array;

    }


}