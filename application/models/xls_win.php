<?php
/**
 * Created by PhpStorm.
 * Time: 1:38
 */
set_time_limit(3000);

class Xls extends CI_Model {
    function __construct()
    {
    }

    function create_csv(){
        if(empty($_FILES))
            return;

        $uploaddir = FCPATH.'archive/';
        $uploadfile = $uploaddir . "temp.xls";

        if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
            echo "<div class='alert'>Файл корректен и был успешно загружен.\n</div>";
        } else {
            echo "Возможная атака с помощью файловой загрузки!\n";
        }

        $path = FCPATH.'archive/';
        $filename = $path."temp.xls";

        exec(FCPATH.'application/libraries/'.'xlHtml.exe -xp:0 -csv '.$filename.' > '.$path.'out.csv');
    }

    function read_csv(){
        $this->create_csv();

        $file = fopen(FCPATH.'archive/out.csv', 'r');

        while (($line = fgetcsv($file)) !== FALSE) {
            // привести все строки из 
            if(count($line) == 7)
                $line = array_merge(array_fill(0, 5, 0), $line);

            $rules = array(
                count($line) < 11,
                count(array_count_values($line)) < 4,
            );

            if(count(array_unique($rules)) === 1 && $rules[0] === false){
                $left_column = array($line[0],$line[1],$line[2],$line[3],str_replace(".", ",", $line[4]),str_replace(".", ",", $line[5]));
                $right_column = array($line[6],$line[7],$line[8],$line[9],str_replace(".", ",", $line[10]),str_replace(".", ",", $line[11]));

                foreach(array($left_column, $right_column) as $column){
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

    function get_ambar_csv($reference){
        $path = FCPATH.'archive/';
        $filename = $path."ambar_xls.xls";

        exec(FCPATH.'application/libraries/'.'xlHtml.exe -xp:0 -csv '.$filename.' > '.$path.'ambar.csv');

        $file = fopen(FCPATH.'archive/ambar.csv', 'r');

        while (($line = fgetcsv($file)) !== FALSE) {
            if($line[2]==$reference){
                return $line[1];
            }
        }

        return false;
    }
}

