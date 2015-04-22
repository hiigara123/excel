<?php
header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
//header('Content-Disposition: attachment; filename=data.csv');
?>
<link rel="stylesheet" type="text/css" href="/excel/css/style.css">
<link rel="stylesheet" type="text/css" href="/excel/css/bootstrap.css">
<script type="text/javascript" src="/excel/js/jquery.js"></script>
<script type="text/javascript" src="/excel/js/base.js"></script>
<script type="text/javascript" src="/excel/js/export.js"></script>
<script type="text/javascript" src="/excel/js/bootstrap.js"></script>
<script type="text/javascript" src="/excel/js/bootstrap_input.js"></script>
<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
<meta http-equiv="Cache-Control" content="no-cache">

<div id="header">
    <div>
        <span class="link"><a href="/excel/show">выбрать</a></span>
<!--        <span class="link"><a href="/excel/show/--><?php //echo $table_name; ?><!--"><span id="table_name">--><?php //echo $table_name; ?><!--</span></a></span>-->
        <span class="link"><a href="/excel/compare/<?php //echo $table_name; ?>">сравнение</a></span>
        <span class="link"><a href="/excel/new_products/show/<?php //echo $table_name; ?>">новые</a></span>
        <span class="link"><a href="/excel/missed_products/show/<?php //echo $table_name; ?>">отсутствующие</a></span>
        <span class="link"><a href="/excel/prices/<?php //echo $table_name; ?>">цены</a></span>
        <form class="file" enctype="multipart/form-data" action="/excel/" method="POST">
            <input type="hidden" name="MAX_FILE_SIZE" value="300000000" />
            <input name="userfile" class="btn btn-small" type="file" title="загрузить прайс-лист" data-filename-placement="inside"/>
            <input type="submit" class="btn btn-small send_file" value="отправить" />
        </form>
        <span class="link"><a href="/excel/delete">удалить таблицы</a></span>
    </div>
    <div class="loading_bar">В зависимости от количества изменений в таблицах может занять до 10 минут</div>
</div>

<div style="display: none;">
    <div id="cats"><?php echo $input_selects[0]; ?></div>
    <div id="subcats"><?php echo $input_selects[1]; ?></div>
    <div id="supps"><?php echo $input_selects[2]; ?></div>
</div>