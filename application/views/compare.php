<div class="content">

<?php if(isset($compare_table)) { ?>
<div id="compare" class="table">
    <h2>Изменения в позициях</h2>
    <a class="export" download="<?php echo date("d_m_y_his"); ?>_compare.xls" href="#" ></a>
    <span class="filter filter_compare"></span>
    <br>* - изменения в новой таблице
    <?php echo $compare_table == "equal"?"<h1>Таблицы одинаковы</h1>":$compare_table; ?>
</div>
<?php } ?>

<?php if(isset($new_positions)) { ?>
<div id="new_positions" class="table">
    <h2>Новые позиции</h2>
    <a class="export" download="<?php echo date("d_m_y_his"); ?>_new_positions.xls" href="#" ></a>
    <?php echo $new_positions; ?>
</div>
<?php } ?>

<?php if(isset($zero_positions)) { ?>
<div id="old_positions" class="table">
    <h2>Отсутствующие позиции</h2>
    <a class="export" download="<?php echo date("d_m_y_his"); ?>_zero_positions.xls" href="#" ></a>
    <?php echo $zero_positions; ?>
</div>
<?php } ?>

    {elapsed_time}
</div>
