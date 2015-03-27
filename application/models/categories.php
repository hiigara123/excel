<?php
/**
 * Created by PhpStorm.
 * User: Тимур
 * Date: 17.09.14
 * Time: 14:43
 */

class Categories extends CI_Model {

    //reference, name
    private $categories = array(
        20 => 'Орешки',
        24 => 'Овощи',
        35 => 'Вода'
    );

    //id, (reference, (parent reference, name))
    private $subCategories = array(
        36 => array(20, 'sub1'),
        37 => array(20, 'sub2'),
        38 => array(34, 'sub3'),
        39 => array(34, 'sub4'),
        40 => array(35, 'sub5'),
        41 => array(35, 'sub6'),
    );

    //reference, name
    private $suppliers = array(
        1 => 'Fozzy',
        2 => 'АТБ'
    );

    function __construct()
    {
        $this->mysqli = $GLOBALS['$mysqli'];

        $this->create_tables();
    }

    function create_tables(){
        $this->mysqli->query("CREATE TABLE IF NOT EXISTS `products` (
          `reference` int(10) DEFAULT NULL,
          `name` varchar(1000) DEFAULT NULL,
          `category` int(2) DEFAULT NULL,
          `subCategory` int(2) DEFAULT NULL,
          `supplier` int(2) DEFAULT NULL,
          `comment` varchar(1000) DEFAULT NULL,
          PRIMARY KEY (`reference`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;");

        $this->mysqli->query("CREATE TABLE IF NOT EXISTS `categories` (
          `ID` int(11) NOT NULL AUTO_INCREMENT,
          `reference` varchar(1000) DEFAULT NULL,
          `name` varchar(1000) DEFAULT NULL,
          PRIMARY KEY (`ID`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;");

        $this->mysqli->query("CREATE TABLE IF NOT EXISTS `prices` (
          `reference` int(12) NOT NULL DEFAULT '0',
          `date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          `bezocheredi` varchar(1000) DEFAULT NULL,
          `productoff` varchar(1000) DEFAULT NULL,
          `ambar` varchar(1000) DEFAULT NULL,
          `wnog` varchar(1000) DEFAULT NULL,
          `citymarket` varchar(1000) DEFAULT NULL,
          `fozzy` varchar(1000) DEFAULT NULL,
          PRIMARY KEY (`reference`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $cats = $this->mysqli->query('select count(ID) from categories;');
        $cats = $cats->fetch_array();

        if(($cats[0])==0){
            $cat_values = "";

            foreach($this->categories as $ref => $name){
                $cat_values .= "('".$ref."', '".$name."'),";
            }

            $this->mysqli->query("INSERT INTO `categories` (`reference`, `name`) VALUES ".substr($cat_values, 0, -1));
        }

        $this->mysqli->query("CREATE TABLE IF NOT EXISTS `subcategories` (
          `ID` int(11) NOT NULL AUTO_INCREMENT,
          `parent_reference` varchar(1000) DEFAULT NULL,
          `reference` varchar(1000) DEFAULT NULL,
          `name` varchar(1000) DEFAULT NULL,
          PRIMARY KEY (`ID`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;");

        $subCats = $this->mysqli->query('select count(ID) from subcategories;');
        $subCats = $subCats->fetch_array();

        if(($subCats[0])==0){
            $subCats_values = "";

            foreach($this->subCategories as $ref => $arr){
                $subCats_values .= "('".$ref."', '".$arr[0]."', '".$arr[1]."'),";
            }

            $this->mysqli->query("INSERT INTO `subcategories` (`reference`, `parent_reference`, `name`) VALUES ".substr($subCats_values, 0, -1));
        }

        $this->mysqli->query("CREATE TABLE IF NOT EXISTS `suppliers` (
          `ID` int(11) NOT NULL AUTO_INCREMENT,
          `reference` varchar(1000) DEFAULT NULL,
          `name` varchar(1000) DEFAULT NULL,
          PRIMARY KEY (`ID`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;");

        $suppliers = $this->mysqli->query('select count(ID) from suppliers;');
        $suppliers = $suppliers->fetch_array();

        if(($suppliers[0])==0){
            $suppliers_values = "";

            foreach($this->suppliers as $ref => $name){
                $suppliers_values .= "('".$ref."', '".$name."'),";
            }

            $this->mysqli->query("INSERT INTO `suppliers` (`reference`, `name`) VALUES ".substr($suppliers_values, 0, -1));
        }
    }
} 