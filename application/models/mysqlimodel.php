<?php
/**
 * Created by PhpStorm.
 * Time: 14:48
 */

class MysqliModel extends CI_Model {
    function __construct()
    {
        $this->mysqli = new mysqli(EXCEL_HOST, EXCEL_USER, EXCEL_PASSWORD);

        if ($this->mysqli->connect_error) {
            die('Connect Error: ' . $this->mysqli->connect_error);
        }

        $this->mysqli->set_charset("utf8");

        $this->mysqli->select_db(EXCEL_DATABASE);
    }

    function initialise(){
        return $this->mysqli;
    }

}