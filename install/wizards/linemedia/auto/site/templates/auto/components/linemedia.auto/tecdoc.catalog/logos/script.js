$(document).ready(function() {

  $("#lm-auto-tecdoc-navigation").treeview({
          animated: "false",
          collapsed: true,
          unique: false,
          persist: "cookie"
  });

  $('.manufacture_info').click(function() {
    var brand = $(this).html();
      // Отсылаем паметры
       $.ajax({
         type:"GET",
         url:"/bitrix/components/linemedia.auto/search.results/templates/.default/ajax/ajax.php?type=manufacture_info&brand=" + encodeURIComponent(brand),
         // Выводим то что вернул PHP
         success:function (html) {
           if (html == false) {
             return false;
           } else {
             $.fancybox(
               html,
               {
                 'autoDimensions':false,
                 'width':550,
                 'height':'auto',
                 'transitionIn':'none',
                 'transitionOut':'none'
               }
             );
           }
         },
         error:function (data) {
           alert("load error");
         }
       });
    });
});
