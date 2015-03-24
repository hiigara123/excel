$(document).ready(function(){
    $(".table table td").click(function(){
        if(!$(this).hasClass("editable")){
            return;
        }
        if(!$(this).hasClass("editing")){
            var length = $(this).html().length==0?1:$(this).html().length;

            var html = "<input type='text' value='"+$(this).text()+"' size="+length+">";

            if($(this).closest("table").attr("id") != "compare" && $(this).index() == 7){
                html = $("#cats").html();
            }

            if($(this).closest("table").attr("id") != "compare" && $(this).index() == 8){
                html = $("#subcats").html();
            }

            if($(this).closest("table").attr("id") != "compare" && $(this).index() == 9){
                html = $("#supps").html();
            }

            if($(this).closest("table").attr("id") == "compare" && $(this).index() == 11){
                html = $("#cats").html();
            }

            if($(this).closest("table").attr("id") == "compare" && $(this).index() == 12){
                html = $("#subcats").html();
            }

            if($(this).closest("table").attr("id") == "compare" && $(this).index() == 13){
                html = $("#supps").html();
            }

            $(this).html(html);
            $(this).find("input").focus();
            $(this).find("select").focus();
        }
        $(this).addClass("editing");
    });

    $(".table table").on('blur', 'td', function(){
        if(!$(this).hasClass("editable")){
            return;
        }
        var table_name = $("#table_name").text().split('-')[0];
        var value = $(this).find("input").val();
        var table = $(this).closest("table").attr("id");
        var order = $(this).index();
        var reference = $(this).parent().find("td:nth-child(2)").text();

        if(table=="compare")
            reference = $(this).parent().find("td:first-child").text();

        $(this).html(value);

        if(table != "compare" && order > 6 && order < 10){
            value = $(this).find("select option:selected").val();
            var text = $(this).find("select option:selected").text()
            $(this).html(text=="выбрать"?null:text);
        }

        if(table == "compare" && order > 10 && order < 14){
            value = $(this).find("select option:selected").val();
            var text = $(this).find("select option:selected").text()
            $(this).html(text=="выбрать"?null:text);
        }

        $(this).removeClass("editing");

        update(table_name, table, order, reference, value);
    });

    $(".table table td").keypress(function(e){
        if(e.which == 13){
            $(this).blur();
        }
    });

    $(".export").click(function(){
        //var id = $(this).parent().find("table.table").attr("id");
        var name = $("#table_name").text();
        //$table = $(this).parent().find("table");
        //var lenght = $($table).find('tr:first-child td').length;
        //if(lenght>11){
        //    var arr = [6,7,8,9,10];
        //}else{
        //    var arr = [5,6];
        //}
        //$($table).find("tr").each(function(){
        //    $(this).find("td").each(function(){
        //        if($.inArray($(this).index(), arr) != "-1"){
        //            $(this).html( $(this).html().replace(/\./g,","));
        //        }
        //    });
        //});
        ExcellentExport.excel(this, "base", name);
    });

    setTimeout(function(){
        $(".alert").fadeOut(1000)
    }, 5000);

    $(".tables_show_list input[type=checkbox]").change(function(){
        var checks = 0;
        var status = "";
        $(".tables_show_list input[type=checkbox]").each(function(){
            if($(this).is(':checked')){
                checks++;
                status += $(this).parent().text()+" => ";
            }
        });

        if(checks > 2 || checks == 0){
            $("button.show").prop( "disabled", true );
        }else{
            $("button.show").prop( "disabled", false );
        }

        if(checks == 2){
            $("button.swap").prop( "disabled", false );
        }else{
            $("button.swap").prop( "disabled", true );
        }

        $(".show_url").html(status.substring(0, status.length - 3));
    });

    $("button.swap").click(function(){
        var data = $(".show_url").html().split(' =&gt; ');
        $(".show_url").html(data[1]+' => '+data[0]);
    });

    $("button.show").click(function(){
        var data = $(".show_url").html().split(' =&gt; ');

        $(".loading_bar").show();

        if(data.length == 1){
            var url = "/excel/show/"+data[0];

        }else{
            var url = "/excel/compare/"+data[0]+"-"+data[1];
        }

        window.location.href = url.replace(" ", "");
    });

    $(".send_file").click(function(){
        $(".loading_bar").show();
    });

    $(".f_cat_sel").html($("#cats"));
    $(".f_subcat_sel").html($("#subcats"));
    $(".f_sup_sel").html($("#supps"));

    $(".filter").click(function(){
        var values = '';

        $(this).parent().find("input").each(function(){
            if($(this).val() != ''){
                values += $(this).attr("name")+"/"+$(this).val()+"/";
            }
        });

        $(this).parent().find("select").each(function(){
            if($(this).val() != 'null'){
                values += $(this).attr("name")+"/"+$(this).val()+"/";
            }
        });

        //var page = $(".pagination").find(".active a").html();

        var table_name = $("#table_name").text().split('-')[0];
        var controller = $(location).attr('href').split("/")[4];
        var url = "/excel/"+controller+"/"+table_name+"/"+values+"page/1";
        window.location.href = url;
    });

    $(".filter_toggle").click(function(){
        $(".categories_container").slideToggle();
    });

    $(".show_prices").click(function(){
        var params = get_prices_params();
        console.log(params);
        if(!params){
            alert("Выберите категории.")
            return false;
        }
        var url = "/excel/prices/?"+params;
        window.location.href = url;
    });

    $(".refresh_prices").click(function(){
        var params = get_prices_params();
        console.log(params);
        if(!params){
            alert("Выберите категории.")
            return false;
        }
        var url = "/excel/refresh/?"+params;
        window.location.href = url;
    });

    $('input[type=file]').bootstrapFileInput();

});

function update(table_name, table, order, reference, value){

    $.ajax({
        url: "/excel/ajax",
        type: "POST",
        data: {
            table : table_name,
            id : table,
            order : order,
            reference: reference,
            value : value
        }
    });
}

function get_prices_params(qwe){
    var cats = '';
    var subcats = '';

    $(".categories input[type='checkbox']:checked").each(function(){
        var val = $(this).val();
        if($(this).hasClass("subcat")){
            val = val.split("_")[1];
            subcats += val+",";
        }else{
            cats += val+",";
        }
    });
    cats = cats.substring(0, cats.length - 1);
    subcats = subcats.substring(0, subcats.length - 1);
    if(!cats && !subcats)
        return false;
    var params_cats = "cats="+cats+"&subcats="+subcats;

    var compets = '';

    $(".competitors input[type='checkbox']:checked").each(function(){
        var val = $(this).val();
        compets += val+",";
    });
    compets = compets.substring(0, compets.length - 1);

    var params_compets = "&competitors="+compets;

    if(!compets)
        params_compets = '';

    return params_cats+params_compets;
}