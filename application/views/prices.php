<?php header( 'Content-type: text/html; charset=utf-8' ); ?>
<div class="content prices">
    <button class="btn filter_toggle">Показать/скрыть фильтр</button>
    <button class="btn show_prices">Показать цены</button>
    <button class="btn refresh_prices">Обновить цены</button>
    <div class="categories_container">
        <div class="categories">
            <?php foreach($categories as $cat): ?>
                <?php $current = $cat[1]; ?>

                <?php if(!isset($previous) || ($current != $previous)): ?>
                    <label for="cat<?php echo $cat[0]; ?>"><?php echo $cat[1]; ?></label>
                    <input id="cat<?php echo $cat[0]; ?>" type="checkbox" value="<?php echo $cat[0]; ?>"><br>
                <?php endif; ?>

                <label class="subcat" for="cat-<?php echo $cat[0]; ?>_<?php echo $cat[2]; ?>"><?php echo $cat[3]; ?></label>
                <input class="subcat" id="cat-<?php echo $cat[0]; ?>_<?php echo $cat[2]; ?>" type="checkbox" value="<?php echo $cat[0]; ?>_<?php echo $cat[2]; ?>"><br>

                <?php $previous = $cat[1]; ?>
            <?php endforeach; ?>
        </div>
        <div class="competitors">
            <?php foreach($competitors as $comp => $name): ?>
                <label for="cat<?php echo $name; ?>"><?php echo $name; ?></label>
                <input id="cat<?php echo $name; ?>" type="checkbox" value="<?php echo $comp; ?>"><br>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="prices_table">
        <h2>Сравнение цен с конкурентами</h2>
        <a class="export" download="<?php echo date("d_m_y_his"); ?>_price_list.xls" href="#" ></a>
        <?php echo $table; ?>
    </div>

{elapsed_time}
</div>
<?php if(isset($_GET['competitors']) and $_GET['competitors']!=''){ ?>
<script>
    $("#prices.table td:nth-child(n+4)").hide();

    var params = '<?php echo $_GET['competitors']; ?>';
    params = params.split(",");

    params.forEach(function(e){
        var index = ($("#prices.table").find("."+e).index())+1;
        $("#prices.table td:nth-child("+index+")").show();
    });
</script>
<?php } ?>