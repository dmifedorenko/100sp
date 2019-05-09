<?php
/**
 * Created by PhpStorm.
 * User: x0
 * Date: 08.05.2019
 * Time: 17:52
 */

class CsvGenerator
{
    public function generate($collectionArray)
    {
        $headers = array('Номер', 'Товар', 'Фото', 'Цена', 'Описание');

        $fileName = '/downloaded-pages/'. $_POST['collectionId'] .'.csv';
        $fileSystemPath = $_SERVER['DOCUMENT_ROOT'] . $fileName;
        $fp = fopen($fileSystemPath, 'w');

        fputs($fp, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF))); //BOM-хак для ms excell
        fputcsv($fp, $headers, ";");
        foreach ($collectionArray as $fields) {
            $row = array();

            if( is_object($fields) ) {
                $fields = (array) $fields;
            }

            foreach ($fields as $key => $field) {
                if ( is_string ($field) ) {
                    $field = trim($field);
                    // $row[$key] = substr($field, 0, 1) === '=' ? '' : $field; // Вырезаем формулы
                    $row[$key] = $field[0] === "=" ? '' : $field; // Вырезаем формулы - так быстрее
                }
            }

            fputcsv($fp, $row,";");
        }

        fclose($fp);
        return $fileName;
    }
}