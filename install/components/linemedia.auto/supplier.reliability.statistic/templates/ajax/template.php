<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
if ($arResult['completed'] > 0 || $arResult['rejected']>0) {?>
<?  // Сохдание ссылки для ajax-запроса.
    CUtil::InitJSCore(array('window', 'ajax','popup'));
$APPLICATION->AddHeadScript('https://www.google.com/jsapi');
    $div_id = 'chart_'.rand();
?>
    <a href="javascript:void(0);" title="Статистика отказов поставщика" data-url="<?=$this->GetFolder().'/ajax.php?supplier_id='.$arParams['SUPPLIER_ID']?>" onclick="showRSRD(this);"><img src="<?=$this->GetFolder()?>/i/chart.png"></a>
<script type="text/javascript">
<?if (!defined('SUPPLIER_RELIABILITY_JS_LOADED')) {
    define('SUPPLIER_RELIABILITY_JS_LOADED', 42);
?>
    window.google_charts_en=false;
    google.load("visualization", "1", {packages:["corechart"]});
    google.setOnLoadCallback(function(){window.google_charts_en=true;});

    function showRSRD(link) {
        if (!window.google_charts_en) return;
        var popup = new BX.PopupWindow("rs_reliability", null, {
            titleBar: {content: BX.create("span", {html: '<b>Статистика отказов поставщика</b>'})},/*,
            closeIcon: {right: '12px', top: '10px'}*/
            buttons: [
            new BX.PopupWindowButton({
                text: "Закрыть",
                className: "webform-button-link-cancel",
                events: {click: function(){
                        $("<?=$div_id?>_pie").remove();
                        $("<?=$div_id?>_rely").remove();
                        this.popupWindow.close(); // закрытие окна
                        popup.destroy();
                    }}
                })
            ]
        });
            var html = '<div style="min-width:400px;width:400px;">'+
            '<div id="<?=$div_id?>_pie" style="display:block;width: 400px; height:250px;"></div>'+
            '<div id="<?=$div_id?>_rely" style="display:block;width:400px; height:250px;"></div></div>';
            popup.setContent(html);
            BX.ajax.loadJSON($(link).attr('data-url'), null, function(reply) {
            var data = google.visualization.arrayToDataTable(reply.pie);
            var chart = new google.visualization.PieChart(document.getElementById('<?=$div_id?>_pie'));
            chart.draw(data, reply.pie_opts);

            data = google.visualization.arrayToDataTable(reply.bars);
            var chart2 = new google.visualization.ColumnChart(document.getElementById('<?=$div_id?>_rely'));
            reply.bars_opts.bar = {groupWidth:'10px'};
//             reply.bars_opts.colors = ['red','silver'];
            reply.bars_opts.hAxis.direction = 1;
            reply.bars_opts.hAxis.minValue = 0;
            reply.bars_opts.vAxis.minValue = 0;
            reply.bars_opts.vAxis.maxValue = 100;
            reply.bars_opts.hAxis.viewWindow ={min:0};
            reply.bars_opts.hAxis.format = '#';
//             reply.bars_opts.width = 500;
//             reply.bars_opts.height = 250;
            reply.bars_opts.legend = {position:'none'};
            reply.bars_opts.hAxis.gridlines={count:reply.bars.length > 2?2/*reply.bars.length + 1*/:2};
            chart2.draw(data, reply.bars_opts);
            popup.show();
        });
    }
<?}?>
</script>
<?
}else {?>&nbsp;<?}
