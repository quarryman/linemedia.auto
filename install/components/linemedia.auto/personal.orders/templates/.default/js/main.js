$(document).ready(function() {

  // Фильтр по полям таблицы
  var oTable = $('#orders_table_id').dataTable({
    'bPaginate':false,
    'bFilter':true,
    'bSort':false,
    'bSearchable':false,
    'bInfo':false,
    'bProcessing':false,
    'bLengthChange':false,
    'sZeroRecords':"Ничего не найдено"
  });

  // Установка фильтров по столбцам таблицы
 $('th input').keyup(function() {
    oTable.fnFilter(this.value, $('#orders_table_id th').index($(this).parent('th')));
 });

 // Скрываем постоянное поле "Search"
 $('#orders_table_id_filter').html('&nbsp;');

 // Переход к оплате заказа
 $('a.paylink').click(function() {
    var payform = $(this).siblings('.payblock').find('form');
    if ($(payform).attr('action') != undefined) {
       $(payform).submit();
    }
    return false;
 });

 // Установка диалогов


 // Всплывающее окно с описанием заказа
 $('.showpaydialog').dialog({
    autoOpen: false,
    title: 'Описание заказа'
 });

 $('a.showpaylink').click(function() {
    $('#dialog_' + $(this).attr('id')).dialog("open");
    return false;
 });
});


function GetMsgOrder(ItemID){
 if(ItemID){
    $("#MsgOrderForm").html('Ждите, идет загрузка...').load('/bitrix/components/linemedia/autoportal.sale.personal.order.list/ajax_msg_order.php?a=getForm&ItemID=' + ItemID).dialog({width:460});
 }
}

function SendMsgOrder(ItemID){
 if(ItemID){
    var bSendForm = true;
    $('#OrMsgError').empty();
    if($.trim($('#OrMsgBody').val()) == ''){
       $('#OrMsgError').append('Не указан текст вопроса<br />');
       bSendForm = false;
    }
    if($.trim($('#OrMsgFromEmail').val()) == ''){
       $('#OrMsgError').append('Не указан обратный email');
       bSendForm = false;
    }
    if(bSendForm == true){
       var FromEmail = $('#OrMsgFromEmail').val();
       var Body = $('#OrMsgBody').val();
       $("#MsgOrderForm").html('Идет отправление вопроса...');
       $.post('/bitrix/components/linemedia/autoportal.sale.personal.order.list/ajax_msg_order.php?a=sendForm&ItemID=' + ItemID,
          { FromEmail: FromEmail, Body: Body },
          function(data){
             $("#MsgOrderForm").html(data);
          }
       );
    }
 }
}

function ShowCancelItemOrder(ItemID){
 if(ItemID){
    //$('#CancelOrderForm').empty();
    var sBody = '';
    if($('#OrItemTitleID_' + ItemID)){
       $('#COF_NAME').html($('#OrItemTitleID_' + ItemID).html());
    }
    if($('#OrItemQuantityID_' + ItemID)){
       $('#COF_QUANTITY').val($('#OrItemQuantityID_' + ItemID).html());
    }
    $('#COF_ID').val(ItemID);
    $('#COF_MSG').empty();
    $("#CancelOrderForm").dialog({width:460});
 }
}

function SendCancelItemOrder(){
 $('#COF_MSG').empty();
    var iItemQuant = parseInt($('#COF_QUANTITY').val());
    var ItemID = $('#COF_ID').val();
    if(iItemQuant > 0 && iItemQuant <= parseInt($('#OrItemQuantityID_' + ItemID).html())){
        $("#COF_SUBMIT").attr("disabled","disabled");
        $('#COF_FORM').submit();
    }else{
        $('#COF_MSG').html('Указано не корректное количество');
    }
}