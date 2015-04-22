<?php
/**
 * Created by PhpStorm.
 * Time: 0:57
 */

class BaseModel extends CI_Model {

    var $title   = '';
    var $content = '';
    var $mysqli  = '';

    function __construct()
    {
        $this->mysqli = $GLOBALS['$mysqli'];

        $this->mysqli->name = "tprice".date("d_m_y_his");

        //pagination
        $this->limit = $this->config->item('pagination');

        $this->offset = $this->uri->uri_to_assoc(3);
        if(isset($this->offset['page']) && $this->offset['page'] == "all"){
            $this->limit = " LIMIT 999999999";
        }else{
            $this->offset = isset($this->offset['page'])&&$this->offset['page']?($this->offset['page']-1)*$this->limit:0;
            $this->limit = " LIMIT $this->offset, $this->limit";
        }
    }

    function create_db(){
        $this->mysqli->query("Create database ".EXCEL_DATABASE) or die($this->mysqli->error);
        echo "<a href='/excel/'>база создана</a>";
    }


    function select(){
        $table = $this->mysqli->query("SELECT * FROM ".$this->mysqli->name." LIMIT 1;");
        $table = $table->fetch_all();

        $count = $this->mysqli->query("SELECT COUNT(*) AS SUM FROM ".$this->mysqli->name.";");
        $count = $count->fetch_assoc();
    }

    function check_db(){
        $check = $this->mysqli->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '".EXCEL_DATABASE."';");
        if($check->fetch_row())
            return "<a href='./create_db'>Создать базу</a>";
    }

    //sort tables by date properly
    function adjust_tables($tables){
        foreach($tables as $key => &$table){
            if(strpos($table[0], "price")){
                $date = substr($table[0], 6, 15);
                $date = explode("_", $date);
                $date = $date[2].$date[1].$date[0].$date[3];
                $table['date'] = $date;
            }else{
                unset($tables[$key]);
            }
        }

        usort($tables, function($a, $b) {
            return $a['date'] - $b['date'];
        });

        return $tables;
    }

    function show_tables($type){
        $result = $this->mysqli->query("SELECT table_name, table_rows
            FROM INFORMATION_SCHEMA.TABLES
            WHERE TABLE_SCHEMA = '".EXCEL_DATABASE."';");

        $tables = $this->adjust_tables($result->fetch_all());

        $html = "";
        foreach($tables as $table){
            if(strpos($table[0], "price")){
                if($type=="select"){
                    $html .= "<span><input type='checkbox' value='".$table[0]."'/>".$table[0]."</span><br>";
                }else{
                    $html .= "<a href='/excel/delete/".$table[0]."'>Удалить ".$table[0]."</a><br>";
                }
            }
        }
        return $html;
    }

    function create_table(){
        $this->mysqli->query("CREATE TABLE ".$this->mysqli->name." (
         ID int NOT NULL AUTO_INCREMENT,
         reference VARCHAR(1000),
         name VARCHAR(1000),
         unit VARCHAR(1000),
         packaging VARCHAR(1000),
         wholesale_price VARCHAR(1000),
         price VARCHAR(1000),
         PRIMARY KEY (ID)
       ) CHARACTER SET=utf8;");
    }

    function delete(){
        $this->mysqli->query('SET foreign_key_checks = 0');
        if ($result = $this->mysqli->query("SHOW TABLES"))
        {
            while($row = $result->fetch_array(MYSQLI_NUM))
            {
                $this->mysqli->query('DROP TABLE IF EXISTS '.$row[0]);
            }
        }

        $this->mysqli->query('SET foreign_key_checks = 1');
    }

    function insert($data){
        $quantity = count($data);
        $max_rows = 10000;

        if($quantity > $max_rows){
            $iterations = (int)($quantity/$max_rows)+(($quantity%$max_rows==0)?0:1);
        }else{
            $iterations = 1;
        }

        for($i=0;$i<$iterations;$i++){
            $offset = 0;
            $cr = 0;
            $values = "";

            foreach($data as $key => $row){

                if($offset == $max_rows){
                    break;
                }

                $values .= "(";
                $cc = 0;

                foreach($row as $col){
                    $values .= "'".$col."',";
                    $cc++;
                }

                $values = substr($values, 0, -1);

                $values .= '),';

                $offset++;
                $cr++;

                unset($data[$key]);
            }

            $values = substr($values, 0, -1);

            $sql = "INSERT INTO ".$this->mysqli->name." (reference,name,unit,packaging,wholesale_price,price) VALUES $values";


            if (!$this->mysqli->query($sql)) {
                printf("Error message: %s\n", $this->mysqli->error);
            }
        }
    }

    function show_table($table = false, $id = false, $table_name = false, $count = false){
        if(!$table_name){
            $name = $this->get_tables();
            $name = $name[0][0];
        }else{
            $name = explode("-", $table_name);
            $name = $name[0];
        }

        if(!$table){
            $where = $this->get_where_clause("base");

            $sql = "
                SELECT t1.*, t3.name, t4.name, t5.name, t2.comment,
                IF(t2.category AND t2.subCategory AND t2.supplier, concat(t2.category, t2.subCategory, t2.supplier, t2.reference), NULL) as uniqueid
                FROM $name t1
                LEFT JOIN products t2
                ON t1.reference = t2.reference
                LEFT JOIN categories t3
                ON t3.reference = t2.category
                LEFT JOIN subCategories t4
                ON t4.reference = t2.subCategory
                LEFT JOIN suppliers t5
                ON t5.reference = t2.supplier $where";

            $count = $this->mysqli->query($sql);
            $count = count($count->fetch_all());

            $table = $this->mysqli->query($sql.$this->limit);
            $table = $table->fetch_all();
        }

        $html = "";

        $html .= $this->pagination($count);

        $html .= '<table id="'.$id.'" class="table">';
        $html .= '<tr>
            <td>ID</td><td>Артикул</td><td>Название</td><td>Ед. изм.</td>
            <td>Фасовка</td><td>Цена опт.</td><td>Цена розн.</td>
            <td>Категория</td><td>Подкатегория</td><td>Поставщик</td><td>Комментарий</td>
            <td>Уник. ID</td>
        </tr>';
        $html .= $this->get_filter("base");

        foreach($table as $row){
            $html .= "<tr>";
            $i = 0;
            foreach($row as $col){
                $class = "";
                if($i>1 && $i != 11)
                    $class = " editable";
                $html .= "<td class='".$class."'>";
                $html .= $col;
                $html .= "</td>";
                $i++;
            }
            $html .= "</tr>";
        }

        $html .= '</table>';

        $html .= $this->pagination($count);

        return $html;
    }

    function show_table_compare($table, $count){
        $html = "";

//        $html .= $this->pagination($count);

        $html .= '<table id="compare">';
        $html .= '<tr>
            <td>Артикул</td><td>Название</td><td>Название*</td>
            <td>Ед. изм.</td><td>Ед. изм.*</td><td>Фасовка</td>
            <td>Фасовка*</td><td>Цена опт.</td><td>Цена опт.*</td>
            <td>Цена розн.</td><td>Цена розн.*</td>
            <td>Категория</td><td>Подкатегория</td><td>Поставщик</td><td>Комментарий</td>
            <td>Уник. ID</td>
        </tr>';
        $html .= $this->get_filter("compare");

        foreach($table as $row_key => $row){
            $i = 0;

            $html .= "<tr>";
            foreach($row as $col_key => $col){
                $class = "";
                if($i>0)
                    $class .= " editable";
                if($i>1 and !($i & 1) and $col == $table[$row_key][$col_key-1]){
                    $col = "";
                }else{
                    if($i>1 and $i<11 and !($i & 1))
                        $class .= " missmatch";
                }

                $html .= "<td class='".$class."'>";
                $html .= $col;
                $html .= "</td>";
                $i++;
            }
            $html .= "</tr>";
        }

        $html .= '</table>';

//        $html .= $this->pagination($count);

        return $html;
    }

    function get_tables(){
        $result = $this->mysqli->query("SELECT table_name, table_rows
            FROM INFORMATION_SCHEMA.TABLES
            WHERE TABLE_SCHEMA = '".EXCEL_DATABASE."';");

        $tables = $this->adjust_tables($result->fetch_all());

        return array_reverse($tables);
    }

    function compare($uri = false){
        if(!$uri){
            $tables = $this->get_tables();
            $new_table = $tables[0][0];
            $old_table = $tables[1][0];
        }else{
            $tables = explode("-", $uri);
            $new_table = $tables[0];
            $old_table = $tables[1];
        }

        try{
            $result_new_table = $this->mysqli->query("
                SELECT reference, name, unit, packaging, wholesale_price, price
                FROM $new_table");

            if(!is_object($result_new_table)) throw new Exception('данные для выборки отсутствуют');
        }catch (Exception $e) {
            echo 'Ошибка: ',  $e->getMessage(),". ";
            echo '<a href="/excel/">Вернутся на главную</a>';
        }

        while($res = $result_new_table->fetch_row()){
            $new_products[] = serialize($res);
        }

        $result_old_table = $this->mysqli->query("
            SELECT reference, name, unit, packaging, wholesale_price, price
            FROM $old_table");

        while($res = $result_old_table->fetch_row()){
            $old_products[] = serialize($res);
        }

        $diff = array_diff($new_products, $old_products);

        $values = "";
        foreach($diff as $product){
            $product = unserialize($product);
            $values .= $product[0].",";
        }
        $values = substr($values, 0, -1);

        $where = $this->get_where_clause("new_products");

        $sql = "
            SELECT
            t1.reference,
            t1.name, t2.name,
            t1.unit, t2.unit,
            t1.packaging, t2.packaging,
            t1.wholesale_price, t2.wholesale_price,
            t1.price, t2.price,
            t4.name, t5.name,
            t6.name, t3.comment,
            IF(t3.category AND t3.subCategory AND t3.supplier, concat(t3.category, t3.subCategory, t3.supplier, t3.reference), NULL) as uniqueid
            FROM $old_table t1
            RIGHT JOIN $new_table t2 ON (t1.reference = t2.reference)
            LEFT JOIN products t3
            ON t1.reference = t3.reference
            LEFT JOIN categories t4
            ON t4.reference = t3.category
            LEFT JOIN subCategories t5
            ON t5.reference = t3.subCategory
            LEFT JOIN suppliers t6
            ON t6.reference = t3.supplier
            WHERE t1.reference IN ($values) $where";

        $count = $this->mysqli->query($sql);
        $count = count($count->fetch_all());

//        $result_compare = $this->mysqli->query($sql.$this->limit);
        $result_compare = $this->mysqli->query($sql);
        $compare = $result_compare->fetch_all();

        return $this->show_table_compare($compare, $count);
    }

    function new_products($uri = false){
        if(!$uri){
            $tables = $this->get_tables();
            $new_table = $tables[0][0];
            $old_table = $tables[1][0];
        }else{
            $tables = explode("-", $uri);
            $new_table = $tables[0];
            $old_table = $tables[1];
        }

        $result_new_table = $this->mysqli->query("
            SELECT reference
            FROM $new_table");

        while($res = $result_new_table->fetch_row()){
            $new_products[] = $res[0];
        }

        $result_old_table = $this->mysqli->query("
            SELECT reference
            FROM $old_table");

        while($res = $result_old_table->fetch_row()){
            $old_products[] = $res[0];
        }

        $new_products = array_diff($new_products, $old_products);

        if(empty($new_products))
            return;

        $values = "";
        foreach($new_products as $product){
            $values .= $product.",";
        }
        $values = substr($values, 0, -1);

        $where = $this->get_where_clause("new_products");

        $sql = "
            SELECT t1.*, t3.name, t4.name, t5.name, t2.comment,
            IF(t2.category AND t2.subCategory AND t2.supplier, concat(t2.category, t2.subCategory, t2.supplier, t2.reference), NULL) as uniqueid
            FROM $new_table t1
            LEFT JOIN products t2
            ON t1.reference = t2.reference
            LEFT JOIN categories t3
            ON t3.reference = t2.category
            LEFT JOIN subCategories t4
            ON t4.reference = t2.subCategory
            LEFT JOIN suppliers t5
            ON t5.reference = t2.supplier
            WHERE t1.reference IN ($values) $where";

        $count = $this->mysqli->query($sql);
        $count = count($count->fetch_all());

        $new_products = $this->mysqli->query($sql.$this->limit);
        $new_products = $new_products->fetch_all();

        if($new_products)
            return $this->show_table($new_products, "new_products", false, $count);
    }

    function zero_products($uri = false){
        if(!$uri){
            $tables = $this->get_tables();
            $new_table = $tables[1][0];
            $old_table = $tables[0][0];
        }else{
            $tables = explode("-", $uri);
            $new_table = $tables[1];
            $old_table = $tables[0];
        }

        $result_new_table = $this->mysqli->query("
            SELECT reference
            FROM $new_table");

        while($res = $result_new_table->fetch_row()){
            $new_products[] = $res[0];
        }

        $result_old_table = $this->mysqli->query("
            SELECT reference
            FROM $old_table");

        while($res = $result_old_table->fetch_row()){
            $old_products[] = $res[0];
        }

        $new_products = array_diff($new_products, $old_products);

        if(empty($new_products))
            return;

        $values = "";
        foreach($new_products as $product){
            $values .= $product.",";
        }
        $values = substr($values, 0, -1);

        $where = $this->get_where_clause("new_products");

        $sql = "
            SELECT t1.*, t3.name, t4.name, t5.name, t2.comment,
            IF(t2.category AND t2.subCategory AND t2.supplier, concat(t2.category, t2.subCategory, t2.supplier, t2.reference), NULL) as uniqueid
            FROM $new_table t1
            LEFT JOIN products t2
            ON t1.reference = t2.reference
            LEFT JOIN categories t3
            ON t3.reference = t2.category
            LEFT JOIN subCategories t4
            ON t4.reference = t2.subCategory
            LEFT JOIN suppliers t5
            ON t5.reference = t2.supplier
            WHERE t1.reference IN ($values) $where";

        $count = $this->mysqli->query($sql);
        $count = count($count->fetch_all());

        $new_products = $this->mysqli->query($sql.$this->limit);
        $new_products = $new_products->fetch_all();

        if($new_products)
            return $this->show_table($new_products, "zero_products", false, $count);
    }

    function get_table_name(){
//        $request = $this->get_tables();

        return $this->uri->segment(2);
    }

    function get_input_select(){
        $categories = $this->mysqli->query("SELECT reference, name FROM categories");
        $subCategories = $this->mysqli->query("SELECT reference, name FROM subCategories");
        $suppliers = $this->mysqli->query("SELECT reference, name FROM suppliers");

        $data = array();

        $data[] = $categories->fetch_all();
        $data[] = $subCategories->fetch_all();
        $data[] = $suppliers->fetch_all();

        $classes = array("t2.category", "t2.subCategory", "t2.supplier");

        $result = array();

        $i = 0;
        foreach($data as $type){
            $html = "<select name=$classes[$i]>";
            $html .= "<option value='null'>выбрать</option>";
            $html .= "<option value='empty'>   </option>";
            foreach($type as $row){
                $html .= "<option value=$row[0]>$row[1]</option>";
            }
            $html .= "</select>";
            $result[] = $html;
            $i++;
        }

        return $result;
    }

    function get_where_clause($type){
        if(count($this->uri->uri_to_assoc(3)) > 0){
            switch ($type) {
                case "base":
                    $where = "WHERE ";
                    break;
                case "new_products":
                    $where = " AND ";
                    break;
            }

            $uri = $this->uri->uri_to_assoc(3);

            foreach($uri as $name => $value){
                if($name == "t1.name" or $name == "t2.comment") {
                    $where .= $name . " LIKE '%" . urldecode($value) . "%' AND ";
                }elseif($name == "page"){
                    $where .= "1' AND";
                }else{
                    $where .= $name." = '".urldecode($value)."' AND ";
                }
            }

            return substr($where, 0, -5);
        }
    }

    function pagination($total){

        $limit = $this->config->item('pagination');

        $page = $this->uri->uri_to_assoc(3);
        $page = isset($page['page'])?$page['page']:false;

        $total = (ceil($total / $limit));

        $html = "<div><ul class='pagination pagination-sm'>";

        $url = implode(explode("/", $this->uri->uri_string().(!$page?"/page/1":""), -1), "/");

        for($i=1;$i<=$total;++$i){
            if($i==1 && !$page) {
                $class = " class='active'";
            }else{
                $class = $page==$i?" class='active'":"";
            }

            $html .= "<li$class><a href='/excel/$url/".$i."'>$i</a></li>";
        }

        $html .= "<li".($page == "all"?" class='active'":"")."><a href='/excel/$url/all'>все</a></li>";

        $html .= "</ul></div>";

        return $html;
    }

    function get_filter($type){
        if($type == "base"){
            $html = '<tr>';
            $html .= "<td class='filter'></td>
                  <td><input name='t1.reference' type='text' size='1'></td>";
            $html .= "<td><input name='t1.name' type='text' size='1'></td>
                  <td><input name='t1.unit' type='text' size='1'></td>";
            $html .= "<td><input name='t1.packaging' type='text' size='1'></td>
                  <td><input name='t1.wholesale_price' type='text' size='1'></td>";
            $html .= "<td><input name='t1.price' type='text' size='1'></td>";
            $html .= "<td class='f_cat_sel'></td>
                  <td class='f_subcat_sel'></td>
                  <td class='f_sup_sel'></td>";
            $html .= "<td><input name='t2.comment' type='text' size='1'></td>
                  <td></td>";
            $html .= "</tr>";
        }elseif($type == "compare"){
            $html = '<tr>';
            $html .= "<td><input name='t1.reference' type='text' size='1'></td><td></td>
                  <td><input name='t1.name' type='text' size='1'></td><td></td>";
            $html .= "<td><input name='t1.unit' type='text' size='1'></td><td></td>";
            $html .= "<td><input name='t1.packaging' type='text' size='1'></td><td></td>
                  <td><input name='t1.wholesale_price' type='text' size='1'></td><td></td>";
            $html .= "<td><input name='t1.price' type='text' size='1'></td>";
            $html .= "<td class='f_cat_sel'></td>
                  <td class='f_subcat_sel'></td>
                  <td class='f_sup_sel'></td>";
            $html .= "<td><input name='t2.comment' type='text' size='1'></td>
                  <td></td>";
            $html .= "</tr>";
        }


        return $html;
    }

    function delete_table($table){
        $this->mysqli->query('DROP TABLE IF EXISTS '.$table);
        echo "Таблица $table удалена.";
    }

}
