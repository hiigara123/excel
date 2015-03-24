<?php
/**
 * Created by PhpStorm.
 * Time: 0:57
 */

class Ajax extends CI_Model {

    function __construct()
    {
        $this->mysqli = $GLOBALS['$mysqli'];

        $this->table = $_POST['table'];
        $this->id = $_POST['id'];
        $this->order = $_POST['order'];
        $this->reference = $_POST['reference'];
        $this->value = $_POST['value']==""?NULL:$_POST['value'];

        $this->prepare_sql();
    }

    function prepare_sql(){
        $relations = array(
            0 => "id",
            1 => "reference",
            2 => "name",
            3 => "unit",
            4 => "packaging",
            5 => "wholesale_price",
            6 => "price",
            7 => "category",
            8 => "subCategory",
            9 => "supplier",
            10 => "comment"
        );

        $relations_compare = array(
            0 => "reference",
            1 => false,
            2 => "name",
            3 => false,
            4 => "unit",
            5 => false,
            6 => "packaging",
            7 => false,
            8 => "wholesale_price",
            9 => false,
            10 => "price",
            11 => "category",
            12 => "subcategory",
            13 => "supplier",
            14 => "comment"
        );

        if($this->order > 6 && $this->id != "compare"){
            $target_table = "products";
        }elseif($this->order > 10 && $this->id == "compare"){
            $target_table = "products";
        }else{
            $target_table = "pricelist";
        }

        if($this->id == "compare"){
            $value_name = $relations_compare[$this->order];
        }else{
            $value_name = $relations[$this->order];
        }

        if($target_table == "products"){
            $sql = "INSERT INTO products (reference, $value_name)
                    VALUES ($this->reference, '$this->value')
                    ON DUPLICATE KEY
                    UPDATE $value_name = '$this->value' ";
        }elseif($target_table == "pricelist"){
            $sql = "UPDATE $this->table
                    SET $value_name = '$this->value'
                    WHERE reference = $this->reference;";
        }

        var_dump($sql);

        return $this->mysqli->query($sql);
    }
}