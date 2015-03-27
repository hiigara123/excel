<?php
/**
 * Created by PhpStorm.
 * Time: 1:38
 */

class Xls extends CI_Model {
    function __construct()
    {
        ini_set('memory_limit', '-1');
    }

    function create_csv(){
        if(empty($_FILES) or $_FILES[key($_FILES)]['size'] == 0)
            return;

        $uploaddir = FCPATH.'archive/';
        $uploadfile = $uploaddir . basename($_FILES['userfile']['name']);

        if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
            echo "<div class='alert'>Файл корректен и был успешно загружен.\n</div>";
        } else {
            echo "Возможная атака с помощью файловой загрузки!\n";
        }

        $path = FCPATH.'archive/';

        error_reporting(E_ALL);
        set_time_limit(0);

        date_default_timezone_set('Europe/London');

        /** PHPExcel_IOFactory */
        include FCPATH.'/phpexcel/Classes/PHPExcel/IOFactory.php';
        $fileName1 = $path.$_FILES['userfile']['name'];
        $objPHPExcel = PHPExcel_IOFactory::load($fileName1);

        $objWriter = new PHPExcel_Writer_CSV($objPHPExcel, "CSV");
        $objWriter->setUseBOM(TRUE);
        $objWriter->save($path.'out.csv');
    }

    function read_csv(){
        $this->create_csv();

        $file = fopen(FCPATH.'archive/out.csv', 'r');

        $csv = "";

        while (($line = fgetcsv($file)) !== FALSE) {

            $rules = array(
                count($line) < 11,
                count(array_count_values($line)) < 4,
            );

            if(count(array_unique($rules)) === 1 && $rules[0] === false){
                $left_column = array($line[0],$line[1],$line[2],$line[3],str_replace(".", ",", $line[4]),str_replace(".", ",", $line[5]));
                $right_column = array($line[6],$line[7],$line[8],$line[9],str_replace(".", ",", $line[10]),str_replace(".", ",", $line[11]));

                foreach(array($left_column, $right_column) as $column){
                    $column[0] = str_replace(" ", "", $column[0]);
                    $column[2] = $this->validate_number($column[2]);
                    $column[3] = $this->validate_number($column[3]);
                    $column[4] = $this->validate_number($column[4], true);
                    $column[5] = $this->validate_number($column[5], true);

                    if(count(array_count_values($column)) > 4 && is_numeric($column[0])){
                        foreach($column as &$cell){
                            $cell = str_replace("'", "\\'", $cell);
                        }
                        $csv[] = $column;
                    }
                }

            }else{
                //
            }

        }

        return $csv;
    }

    function get_array()
    {
        $csv = $this->read_csv();

        return ($csv);
    }

    function validate_number($number, $type_price = false){
        $number = str_replace(array("0 00", "0 0", "0 "), "", $number);

        if($type_price){
            $number = explode(",", $number);

            if(count($number)>=2){
                if(strlen($number[1])>2){
                    $number[1] = substr($number[1], 0, 3)/10;
                    $number[1] = round($number[1], 0)*10;
                    $number[1] = substr($number[1], 0, 2);
                }

                $number = $number[0].",".$number[1];

            }else{
                $number = $number[0];
            }
        }

        return $number;
    }
}