<?php
/**
 * Created by PhpStorm.
 * User: Тимур
 * Date: 17.09.14
 * Time: 14:43
 */

header('Content-Type: text/html; charset=utf-8');

ini_set("default_socket_timeout", 2);

class Prices extends CI_Model {

    var $competitors = array(
        "bezocheredi" => "bezocheredi.kiev.ua",
        "productoff" => "productoff.com",
        "ambar" => "ambar.ua",
        "wnog" => "8nog.com.ua",
    );

    var $competitors_from_url;

    var $name;

    var $categories = 0;
    var $subcategories = 0;

    function __construct()
    {
        $this->mysqli = $GLOBALS['$mysqli'];

        $name = $this->basemodel->get_tables();
        $this->name = $name[0][0]; //

        $this->categories = isset($_GET['cats'])&&$_GET['cats']!=''?$_GET['cats']:$this->get_categories();
        $this->subcategories = isset($_GET['subcats'])&&$_GET['subcats']!=''?$_GET['subcats']:$this->get_subcategories();
        $this->limit = isset($_GET['subcats'])||isset($_GET['cats'])?"9999999999":"0";

        if(isset($_GET['competitors'])){
            $competitors = explode(',', $_GET['competitors']);
            foreach($competitors as $comp){
                $this->competitors_from_url[$comp] = true;
            }
        }else{
            $this->competitors_from_url = $this->competitors;
        }
    }

    function get_categories_total(){
        $categories = $this->mysqli->query('
            SELECT t1.reference, t1.name, t2.reference, t2.name
            FROM `categories` t1
            LEFT OUTER JOIN `subcategories` t2
            ON t2.parent_reference = t1.reference ORDER by t1.`reference`');
        $categories = $categories->fetch_all();

        return $categories;
    }

    function get_categories(){
        $categories = $this->mysqli->query('
            SELECT reference
            FROM `categories`');
        while ($row = $categories->fetch_row()){
            $arr[] = $row[0];
        }
        return implode(",",$arr);
    }

    function get_subcategories(){
        $categories = $this->mysqli->query('
            SELECT reference
            FROM `subcategories`
            ORDER by `parent_reference`');
        while ($row = $categories->fetch_row()){
            $arr[] = $row[0];
        }
        return implode(",",$arr);
    }

    function get_competitors(){
        return $this->competitors;
    }

    function update_price($ref, $name, $price){
        $date = new DateTime("NOW");

        $sql = "INSERT INTO prices (reference, $name)
                VALUES ($ref, '$price')
                ON DUPLICATE KEY
                UPDATE $name = '$price', date = '{$date->format('Y-m-d H:i:s')}' ";

        $this->mysqli->query($sql);
    }

    function get_table(){

        $sql = "
            SELECT t1.reference, t1.name, t4.name as catname, t5.name, t1.wholesale_price,
            t3.bezocheredi, t3.productoff, t3.ambar, t3.wnog
            FROM $this->name t1
            JOIN products t2
            ON t1.reference = t2.reference
            JOIN prices t3
            ON t1.reference = t3.reference
            JOIN categories t4
            ON t2.category = t4.reference
            JOIN subcategories t5
            ON t2.subcategory = t5.reference
            WHERE t2.category IN($this->categories) AND t2.subCategory IN($this->subcategories)
            GROUP BY t1.reference ORDER BY t2.category
            LIMIT $this->limit";

        $products = $this->mysqli->query($sql);

        $products = $products->fetch_all();

        $html = "";

        $html .= '<table id="prices" class="table">';
        $html .= '<tr>
        <td>Артикул</td><td>Название</td><td>Цена опт.</td>
        <td class="bezocheredi">bezocheredi</td><td class="productoff">productoff</td>
        <td class="ambar">ambar</td><td class="wnog">wnog</td>
        </tr>';

        $prev_category = '';

        foreach($products as $row){
            if($row[2]!=$prev_category){
                $html .= "<tr><td colspan='7'><h4>$row[2]</h4></td></tr>";
            }

            $prev_category = $row[2];

            $html .= "<tr>";
            $i=0;
            $main_price = false;
            foreach($row as $col){
                $class = "";
                $i++;
                if($i==3 or $i==4)
                    continue;
                if($i==5)
                    $main_price = $col;

                if($main_price and is_numeric($col)){
                    $class = $main_price<$col?"green":($main_price>$col?"red":"");
                }

                if($i>5){
                    $col = str_replace(".", ",", $col);
                }

                $html .= "<td class=$class>";
                $html .= $col;
                $html .= "</td>";
            }
            $html .= "</tr>";
        }

        $html .= '</table>';

        return $html;
    }

    function refresh(){

        $sql = "
            SELECT t1.reference
            FROM `products` t1
            JOIN $this->name t2
            ON t1.reference = t2.reference
			LEFT OUTER JOIN prices t3
            ON (t1.reference = t3.reference)
            WHERE t1.category IN($this->categories)
                AND t1.subCategory IN($this->subcategories)
                AND (DATE(t3.date) <> CURDATE() or t3.date is NULL)
            ORDER BY t3.date
            LIMIT 1";

        $refs = $this->mysqli->query($sql);
        $refs = $refs->fetch_all();

        if(!(boolean)$refs){
            echo "Все цены на выбранных товарах на сегодня обновлены.";
            return;
        }

        foreach($refs as $row){
            foreach($this->competitors_from_url as $name => $link){


                echo "Поиск цены товара ".$row[0]." на сайте <b>$name</b>...<br>";
                flush();
                ob_flush();
                try {
                    $price = $this->parse_prices($name, $row[0]);
                } catch (Exception $e) {
                    echo 'Ошибка: <span style="color:red">',  $e->getMessage(), "</span><br><br>";
                    $price = "—";
                }

                $this->update_price($row[0], $name, $price);
                flush();
                ob_flush();

            }
        }

        echo '<script>location.reload();</script>';
    }

    function parse_prices($site, $reference){
        libxml_use_internal_errors(true);

        switch ($site) {
            case 'productoff':
                $url = "http://www.produktoff.com/search.html?q=";
                $url .= $reference;
                $html = file_get_contents($url);
                $doc = new DOMDocument();
                $doc->loadHTML('<meta http-equiv="content-type" content="text/html; charset=utf-8">'.$html);
                $finder = new DomXPath($doc);
                $classname="pricetov";
                $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
                $html = $doc->saveHTML($nodes->item(0));
                $doc->loadHTML($html);
                $price = $doc->getElementsByTagName('span');
                $price = $price->item(1)->nodeValue;
                break;

            case 'bezocheredi':
                $url = "http://bezocheredi.kiev.ua/products?keyword=";
                $url .= $reference;
                $html = file_get_contents($url);
                $doc = new DOMDocument();
                $doc->loadHTML('<meta http-equiv="content-type" content="text/html; charset=utf-8">'.$html);
                $ref = $doc->getElementById('prodart');
                if(gettype($ref)!='object')
                    throw new Exception('товар не обнаружен.');
                $ref = $ref->childNodes->item(1)->nodeValue;
                if($ref and $ref!=$reference)
                    throw new Exception('товар не обнаружен.');
                $finder = new DomXPath($doc);
                $classname="price";
                $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
                $price = $doc->saveHTML($nodes->item(0));
                $doc->loadHTML($price);
                $price = $doc->getElementsByTagName('span');
                $price = $price->item(0)->nodeValue;
                $price = explode(" ", $price);
                $price = $price[0];
                break;

            case 'wnog':
                $url = "http://8nog.com.ua/kupit-kharkov/search/?q=";
                $url .= $reference;
                $html = file_get_contents($url);
                $doc = new DOMDocument();
                $doc->loadHTML('<meta http-equiv="content-type" content="text/html; charset=utf-8">'.$html);
                $finder = new DomXPath($doc);
                $classname="info";
                $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
                $html = $doc->saveHtml($nodes->item(0));
                $doc->loadHTML($html);
                $href = $doc->getElementsByTagName('a');
                if(gettype($href->item(0))!='object')
                    throw new Exception('товар не обнаружен.');
                $href = $href->item(0)->getAttribute('href');
                $ref_check = file_get_contents("http://8nog.com.ua".$href);
                $pattern = "/арт\\.: (.*?)<\\/p>/si";
                preg_match($pattern, $ref_check, $matches);
                if(!isset($matches[1]) or $matches[1]!=$reference)
                    throw new Exception('товар не обнаружен.');
                $pattern = "/price\">(.*?) грн/si";
                preg_match($pattern, $html, $matches);
                $price = $matches[1];
                break;

            case 'ambar':
                $reference = $this->get_ambar_reference($reference);
                if(!$reference)
                    throw new Exception('атрикул не обнаружен в файле ambar.xls.');
                $reference = explode("-", $reference);
                $reference = $reference[1];
                $url = "http://www.ambar.ua/ru/results/?q=";
                $url .= $reference;
                $html = file_get_contents($url);
                $pattern = "/product_price2[^>]*rel=\"(.*?)\">/i";
                preg_match_all($pattern, $html, $matches);
                if(!isset($matches[1]))
                    throw new Exception('товар не обнаружен.');
                $price = $matches[1][0];
                break;
        }

        if(!$price)
            throw new Exception('товар не обнаружен.');

        echo $price." <span style='color:green'>успешно</span><br><br>";
        return str_replace(",", ".", $price);
    }

    function get_ambar_reference($reference){
        $this->load->model('xls');
        return $this->xls->get_ambar_csv($reference);
    }
}