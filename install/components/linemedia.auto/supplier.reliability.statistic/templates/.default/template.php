<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
    если ajax, то надо отдать данные дл€ диаграмм и всЄ.
*/
if ($arParams['AJAX'] == 'Y') {
    include dirname(__FILE__).'/json.php';
    return;//больше ничего делать не надо.
}

/**
    статистики нет -- выведем пробел, чтобы табличку в »≈ нормально нарисовало.
*/
if ( $arResult['STAT']['completed'] <= 0 && $arResult['STAT']['rejected'] <= 0) {
    echo '&nbsp;';
    return;
}
    /**
    * если не указано, то в некоторых браузерах всплывающее окно разносит вдребезги.
    */
    if (empty($arParams['WIDTH'])) {
        $arParams['WIDTH'] = '400px';
    }

    if (empty($arParams['HEIGHT'])) {
        $arParams['HEIGHT'] = '200px';
    }

    CUtil::InitJSCore(array('window', 'ajax','popup')); //дл€ всплыв. диалога битрикса
    $APPLICATION->AddHeadScript('https://www.google.com/jsapi'); // диаграммы

    $div_id = 'chart_'.rand();//ID контейнера с телом диалога с диаграммами
?>

<a href="javascript:void(0);" title="<?=GetMessage('LM_AUTO_SUPP_RELY_LINK_TITILE')?>" data-url="<?=$this->GetFolder().'/ajax.php?supplier_id='.$arParams['SUPPLIER_ID']?>" onclick="showRSRD(this);">
    <img src="<?=$this->GetFolder()?>/i/chart.png">
</a>
<?if (!defined('SUPPLIER_RELIABILITY_JS_LOADED')) {
    define('SUPPLIER_RELIABILITY_JS_LOADED', 42);//не важно, чему это равно, мы провер€ем на defined(), потому что этот js нам нужен один раз.
    //сомнительно, что кому-то потребуетс€ делать разного размера диаграммы дл€ разных поставщиков в пределах одной страницы
?>
    <script type="text/javascript">
        window.google_charts_en=false;
        google.load("visualization", "1", {packages:["corechart"]});
        google.setOnLoadCallback(function(){window.google_charts_en=true;});
        var lm_auto_supplier_reliability_stat_dlg = false;
        function showRSRD(link) {
            if (!window.google_charts_en) {
                return;
            }
            if (lm_auto_supplier_reliability_stat_dlg) {
                lm_auto_supplier_reliability_stat_dlg.close();
                lm_auto_supplier_reliability_stat_dlg.destroy();
            }
            BX.showWait();
            lm_auto_supplier_reliability_stat_dlg = new BX.PopupWindow("rs_reliability", null, {
                titleBar: {content: BX.create("span", {html: '<b><?=GetMessage('LM_AUTO_SUPP_RELY_CHART_TITLE')?></b>'})},
                buttons: [
                new BX.PopupWindowButton({
                    text: "<?=GetMessage('LM_AUTO_SUPP_RELY_DLG_CLOSE')?>",
                    className: "webform-button-link-cancel",
                    events: {click: function(){
                            $("<?=$div_id?>_pie").remove();
                            $("<?=$div_id?>_rely").remove();
                            this.popupWindow.close();
                            lm_auto_supplier_reliability_stat_dlg.destroy();
                            lm_auto_supplier_reliability_stat_dlg = false;
                        }}
                    })
                ]
            });
                var html = '<div style="min-width:<?=$arParams['WIDTH']?>;width:<?=$arParams['WIDTH']?>;">'+
                '<div id="<?=$div_id?>_pie" style="display:block;width: <?=$arParams['WIDTH']?>; height:<?=$arParams['HEIGHT']?>;"></div>'+
                '<div id="<?=$div_id?>_rely" style="display:block;width:<?=$arParams['WIDTH']?>; height:<?=$arParams['HEIGHT']?>;"></div></div>';
                lm_auto_supplier_reliability_stat_dlg.setContent(html);
                BX.ajax.loadJSON($(link).attr('data-url'),
                                    null,
                                    function(reply) {
                                        var data = google.visualization.arrayToDataTable(reply.pie);
                                        var chart = new google.visualization.PieChart(document.getElementById('<?=$div_id?>_pie'));
                                        chart.draw(data, reply.pie_opts);
                                        if (reply.bars_exists) {
                                            data = google.visualization.arrayToDataTable(reply.bars);
                                            var chart2 = new google.visualization.ColumnChart(document.getElementById('<?=$div_id?>_rely'));
                                            if(!reply.bars_opts) reply.bars_opts = {};
                                            reply.bars_opts.bar = {groupWidth:'10px'};
<?
    /**
        мен€ем те настройки по умолчанию, которые нас не устраивают. Ќапример,
        нам не нужны дробные оси-отбивки и отрицательна€ часть диаграммы
    */
?>
                                            reply.bars_opts.hAxis.gridlines={count:2};
                                            reply.bars_opts.hAxis.direction = 1;
                                            reply.bars_opts.hAxis.minValue = 0;
                                            reply.bars_opts.hAxis.viewWindow ={min:0};
                                            reply.bars_opts.hAxis.format = '#';
                                            reply.bars_opts.vAxis.minValue = 0;
                                            reply.bars_opts.vAxis.maxValue = 100;
                                            reply.bars_opts.legend = {position:'none'};
                                            chart2.draw(data, reply.bars_opts);
                                        } else {
                                            $('<?=$div_id?>_rely').html('<?=GetMessage('LM_AUTO_SUPP_RELY_CHART_NO_DATA')?>');
                                        }
                                        BX.closeWait();
                                        lm_auto_supplier_reliability_stat_dlg.show();
                                        });
                    BX.closeWait();
        }
    </script>
<?}