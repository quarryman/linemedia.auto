$(document).ready(function() {

    /**
     * Инициализируем переменную для выбранного в селекте авто (не пустое значение, чтобы при обновлении текущего
     * списка деталей не убирались только что созданные (еще не сохраненные) поля
     */


    var selected_auto = '     ';
    var table = $(".lm-auto-notepad");



    /**
     * Сохранение при нажатии на enter при редактировании полей,
     * перенос строки на shift+enter
     */
    $('.title-new, .brand-new, .article-new, .auto-new, .comments-new, .quantity-new ').keypress(function(event) {
        if(event.shiftKey && event.keyCode == 13){
            form.submit();
        } else if(event.keyCode==13){
            event.preventDefault();
            onEnterSave(this);
        }
    });



    /**
     * При двойном клике появляется область редактирования
     */
    $('.title, .brand, .article, .auto, .comments, .quantity ').dblclick(function(event) {
        onDblClick(this);
    });




    /**
     * Действие при нажатии на кнопку "добавить": добавляется в начало новая пустая строка,
     * показываются кнопки "сохранить", "удалить"
     * + скрытые поля для редактирования
     */
    table.on("click", ".add",function(event) {

              $('.lm-auto-notepad > tbody:first').prepend('' +
            '<tr id="">' +
            '                <td>' +
            '                    <img class="change" src="'+path_to_images+'change_new.png" title="'+lang_change+'" alt="'+lang_change+'"/>' +
            '                    <img class="save" src="'+path_to_images+'save.png" title="'+lang_save+'" alt="'+lang_save+'"/>' +
            '                    <img class="cancel" src="'+path_to_images+'cancel.png" title="'+lang_cancel+'" alt="'+lang_cancel+'"/>' +
            '                </td>' +
            '                <td class="title">' +
            '                    <input type="hidden" id="notepad_part_id" name="id" value="0">' +
            '                   <input class="title-new" type="text" value=""> ' +
            '                   <p class="title-old"></p>' +
            '                </td>' +
            '                <td class="brand">' +
            '                    <input class="brand-new" type="text" value="">' +
            '                    <p class="brand-old"></p>' +
            '                </td>' +
            '                <td class="article">' +
            '                    <input class="article-new" type="text" value="">' +
            '                    <p class="article-old"></p>' +
            '                </td>' +
            '                <td class="auto">' +
            '                    <input class="auto-new" type="text" value="">' +
            '                    <p class="auto-old"></p>' +
            '                </td>' +
            '                <td class="comments">' +
            '                    <textarea class="comments-new" rows="2" cols="20"></textarea>' +
            '                    <p class="comments-old"></p>' +
            '                </td>' +
            '                <td class="quantity">' +
            '                    <input class="quantity-new" type="text" value="">' +
            '                    <p class="quantity-old"></p>' +
            '                </td>' +
            '                <td class="price">' +
            '                </td>' +
            '                <td class="delete-column">' +
            '                    <img class="delete" src="'+path_to_images+'delete.png" title="'+lang_delete+'" alt="'+lang_delete+'"/>' +
            '                </td>' +
            '            </tr>');

        var tr = $(".change:first").closest("tr");


        /**
         * Становятся доступными поля для редактирования и скрываются поля для
         * отображения
         */

        tr.find(".title-old").hide();
        tr.find(".brand-old").hide();
        tr.find(".article-old").hide();
        tr.find(".auto-old").hide();
        tr.find(".quantity-old").hide();
        tr.find(".comments-old").hide();

        tr.find(".save").show();
        tr.find(".cancel").show();

        tr.find(".change").hide();

        tr.find(".title-new").show();
        tr.find(".brand-new").show();
        tr.find(".article-new").show();
        tr.find(".auto-new").show();
        tr.find(".comments-new").show();
        tr.find(".quantity-new").show();
        event.preventDefault();
       });



    /**
     * Действие при нажатии на кнопку "изменить": показываются кнопки "сохранить", "отменить", "удалить"
     * + скрытые поля для редактирования
     */
    table.on("click", ".change",function() {
        var tr = $(this).closest("tr");

        /**
         * Становятся доступными поля для редактирования и скрываются поля для
         * отображения
         */

        tr.find(".change").hide();

        tr.find(".title-old").hide();
        tr.find(".brand-old").hide();
        tr.find(".article-old").hide();
        tr.find(".auto-old").hide();
        tr.find(".quantity-old").hide();
        tr.find(".comments-old").hide();

        tr.find(".change").hide();

        tr.find(".save").show();
        tr.find(".cancel").show();

        tr.find(".title-new").show();
        tr.find(".brand-new").show();
        tr.find(".article-new").show();
        tr.find(".auto-new").show();
        tr.find(".comments-new").show();
        tr.find(".quantity-new").show();
    });



    /**
     * Функция аналогичная той, что вызывается при нажатии на кнопку изменить, только
     * применяется при двойном клике на элемент
     */

    function onDblClick(this_) {

        var tr = $(this_).closest("tr");

        tr.find(".change").hide();

        /**
         * Становятся доступными поля для редактирования и скрываются поля для
         * отображения
         */

        tr.find(".title-old").hide();
        tr.find(".brand-old").hide();
        tr.find(".article-old").hide();
        tr.find(".auto-old").hide();
        tr.find(".quantity-old").hide();
        tr.find(".comments-old").hide();

        tr.find(".change").hide();

        tr.find(".save").show();
        tr.find(".cancel").show();

        tr.find(".title-new").show();
        tr.find(".brand-new").show();
        tr.find(".article-new").show();
        tr.find(".auto-new").show();
        tr.find(".comments-new").show();
        tr.find(".quantity-new").show();

    }




    /**
     * Действие при нажатии на кнопку "сохранить": показывается кнопка "изменить", скрываются
     * "сохранить", "отменить", "удалить" + скрытые поля для редактирования
     * Отсылается post запрос на изменение, добавление данных
     */
    table.on("click", ".save", onClickSave);



    /**
     * Функция для схранения при нажатии на enter
     */
    function onEnterSave(_this) {
        var tr = $(_this).closest("tr");
        var id = +tr.children('td:first-child').next().children('input').val();

        var title = tr.find(".title-new").val();
        var brand_title = tr.find(".brand-new").val();
        var article = tr.find(".article-new").val();
        var auto = tr.find(".auto-new").val();
        var comment = tr.find(".comments-new").val();
        var quantity = tr.find(".quantity-new").val();
        if (article == 0) {
            alert(lang_empty_article);
            return false;
        }


        var status = '';
        if (id == 0) {
            status = 'add';
        } else {
            status = 'update';
        }

        $.post(path_to_ajax, { notepad: status, id: id, title: title, brand_title: brand_title, article: article, auto: auto, notes: comment, quantity: quantity,
            sessid: sessid})
            .done( function(data,status) {

                // получаем данные от ajax.php о цене и об id добавленной детали
                var php_data = JSON.parse(data);

                // формируем html в поле "Цена"
                if (php_data.max == php_data.min && php_data.max != false && parseInt(php_data.max) != 0) {
                    $(tr).find(".price").html('<a href="'+php_data.findurl+'" target="_blank">'+php_data.min+'</a>')
                } else if (parseInt(php_data.max) > 0 && php_data.max != false){
                    $(tr).find(".price").html('<a href="'+php_data.findurl+'" target="_blank">'+php_data.min+' - '+php_data.max+'</a>')
                } else {
                    $(tr).find(".price").html('<a href="'+php_data.findurl+'" target="_blank">'+lang_price+'</a>')
                }

                // если добавляем новую деталь, то получаем ее id и вписываем в html
                if (php_data.newid != '0') {
                    $(tr).find("#notepad_part_id").val(php_data.newid);
                    $(tr).attr("id","detail_id_"+php_data.newid);
                }
            });

        /**
         * Скрываются поля для редактирования и становятся доступными поля для
         * отображения, а также запоминаются отредактированные значения
         */
        tr.find(".title-old").text($(_this).closest("tr").find(".title-new").val());
        tr.find(".brand-old").text($(_this).closest("tr").find(".brand-new").val());
        tr.find(".article-old").text($(_this).closest("tr").find(".article-new").val());
        tr.find(".auto-old").text($(_this).closest("tr").find(".auto-new").val());
        tr.find(".comments-old").text($(_this).closest("tr").find(".comments-new").val());
        tr.find(".quantity-old").text($(_this).closest("tr").find(".quantity-new").val());

        tr.find(".save").hide();
        tr.find(".cancel").hide();

        tr.find(".title-new").hide();
        tr.find(".brand-new").hide();
        tr.find(".article-new").hide();
        tr.find(".auto-new").hide();
        tr.find(".comments-new").hide();
        tr.find(".quantity-new").hide();

        tr.find(".change").show();

        tr.find(".title-old").show();
        tr.find(".brand-old").show();
        tr.find(".article-old").show();
        tr.find(".auto-old").show();
        tr.find(".quantity-old").show();
        tr.find(".comments-old").show();





        // обновляем список авто и выбираем уже выбранное ранее авто как selected
        autoSort();
        autoCurrentSort();
        return true;
    }




    /**
     * Функция для сохранения изменений при редактировании, создании новой записи,
     * отправляетс данные на ajax.php для сохранения в бд
     */
    function onClickSave() {

        var tr = $(this).closest("tr");

        var id = +tr.children('td:first-child').next().children('input').val();

        var title = tr.find(".title-new").val();
        var brand_title = tr.find(".brand-new").val();
        var article = tr.find(".article-new").val();
        var auto = tr.find(".auto-new").val();
        var comment = tr.find(".comments-new").val();
        var quantity = tr.find(".quantity-new").val();
        if (article == 0) {
            alert(lang_empty_article);
            return false;
        }


        var status = '';
        if (id == 0) {
            status = 'add';
        } else {
            status = 'update';
        }

        $.post(path_to_ajax, { notepad: status, id: id, title: title, brand_title: brand_title, article: article, auto: auto, notes: comment, quantity: quantity,
            sessid: sessid})
            .done( function(data,status) {

                // получаем данные от ajax.php о цене и об id добавленной детали
                var php_data = JSON.parse(data);

                // формируем html в поле "Цена"
                if (php_data.max == php_data.min && php_data.max != 0) {
                    $(tr).find(".price").html('<a href="'+php_data.findurl+'" target="_blank">'+php_data.min+'</a>')
                }
                if (php_data.max != php_data.min ){
                    $(tr).find(".price").html('<a href="'+php_data.findurl+'" target="_blank">'+php_data.min+' - '+php_data.max+'</a>')
                }
                if (php_data.max == php_data.min && php_data.max == 0) {
                    $(tr).find(".price").html('<a href="'+php_data.findurl+'" target="_blank">'+lang_price+'</a>')
                }

                // если добавляем новую деталь, то получаем ее id и вписываем в html
                if (php_data.newid != '0') {
                    $(tr).find("#notepad_part_id").val(php_data.newid);
                    $(tr).attr("id","detail_id_"+php_data.newid);
                }
            });

        /**
         * Скрываются поля для редактирования и становятся доступными поля для
         * отображения, а также запоминаются отредактированные значения
         */
        tr.find(".title-old").text($(this).closest("tr").find(".title-new").val());
        tr.find(".brand-old").text($(this).closest("tr").find(".brand-new").val());
        tr.find(".article-old").text($(this).closest("tr").find(".article-new").val());
        tr.find(".auto-old").text($(this).closest("tr").find(".auto-new").val());
        tr.find(".comments-old").text($(this).closest("tr").find(".comments-new").val());
        tr.find(".quantity-old").text($(this).closest("tr").find(".quantity-new").val());

        tr.find(".title-new").hide();
        tr.find(".brand-new").hide();
        tr.find(".article-new").hide();
        tr.find(".auto-new").hide();
        tr.find(".comments-new").hide();
        tr.find(".quantity-new").hide();

        tr.find(".title-old").show();
        tr.find(".brand-old").show();
        tr.find(".article-old").show();
        tr.find(".auto-old").show();
        tr.find(".quantity-old").show();
        tr.find(".comments-old").show();


        tr.find(".save").hide();

        tr.find(".change").show();
        tr.find(".cancel").hide();



        // обновляем список авто и выбираем уже выбранное ранее авто как selected
        autoSort();
        autoCurrentSort();
        return true;
    }



    /**
     * При нажатии на кнопку "отменить" изменения не сохраняются и состояние
     * возвращяется в нередактируемое
     */
    table.on("click", ".cancel",function() {

        var tr = $(this).closest("tr");
        var id = +tr.children('td:first-child').next().children('input').val();

        if ( id === 0 ) {
            tr.html(" ");
        }
        /**
         * Скрываются поля для редактирования и становятся доступными поля для
         * отображения, не запоминаются отредактированные значения
         */
        tr.find(".title-new").val($(this).closest("tr").find(".title-old").text());
        tr.find(".brand-new").val($(this).closest("tr").find(".brand-old").text());
        tr.find(".article-new").val($(this).closest("tr").find(".article-old").text());
        tr.find(".auto-new").val($(this).closest("tr").find(".auto-old").text());
        tr.find(".comments-new").val($(this).closest("tr").find(".comments-old").text());
        tr.find(".quantity-new").val($(this).closest("tr").find(".quantity-old").text());

        tr.find(".cancel").hide();
        tr.find(".save").hide();


        tr.find(".title-new").hide();
        tr.find(".brand-new").hide();
        tr.find(".article-new").hide();
        tr.find(".auto-new").hide();
        tr.find(".comments-new").hide();
        tr.find(".quantity-new").hide();


        tr.find(".change").show();

        tr.find(".title-old").show();
        tr.find(".brand-old").show();
        tr.find(".article-old").show();
        tr.find(".auto-old").show();
        tr.find(".quantity-old").show();
        tr.find(".comments-old").show();
    });



    /**
     * Удаление записи при нажатии "удалить"
     */
    table.on("click", ".delete",function() {
        if (confirm(lang_delete_confirm)) {
            var tr = $(this).closest("tr");
            var id = +tr.children('td:first-child').next().children('input').val();

            // отправляем запрос на ajax.php
            if (id != 0) {
                $.post(path_to_ajax, { notepad: 'delete', id: id, sessid: sessid});
            }

            // убираем html содержимое удаленной строки
            tr.html(" ");


            // обновляем список авто и выбираем уже выбранное ранее авто как selected
            autoSort();
            autoCurrentSort();

        }
        return true;
    });



    /**
    * Выборка по автомобилю
    */

    // добавляем select перед таблицей
    table.before("" +
       "<select id='auto-select'>" +
       "</select>");

    autoSort();



    /**
     * Функция формирования списка авто для выбора (берутся данные из таблицы на экране,
     * в том числе и из скрытых запчастей)
     */
    function autoSort() {

        var auto_arr = []; //массив с названиями авто

        // делаем выборку авто из таблицы
        table.find(".auto").each(function () {
            if ($.trim($(this).text()) != '') {
                auto_arr.push($.trim($(this).text()));
            }
        });

        // убираем повторяющиеся названия и сортируем массив

        auto_arr = unique(auto_arr);


        auto_arr.sort();

        var auto_select = $("#auto-select");

        //удаляем предыдущий список
        auto_select.html(" ");

        // добавляем "Все автомобили" в select
        auto_select.append(
            "<option>"+lang_all_auto+"</option>"
        );

        // добавляем список авто в select
        $.each(auto_arr, function(key,value) {
            var is_selected = " ";

            if (selected_auto == value) {
                is_selected = "selected";
            }
            $("#auto-select").append(
                "<option "+is_selected+">"+value+"</option>"
            );
        });

        // Функция отрисовки таблицы при выборке авто
        auto_select.change( function () {
            $("#auto-select option:selected").each( function() {
                selected_auto = $.trim($(this).text());
                table.find(".auto").each(function () {
                    if (selected_auto == lang_all_auto) {
                        $(this).closest("tr").show();
                    } else if ($.trim($(this).text()) != selected_auto ) {
                        $(this).closest("tr").hide();
                    }  else {
                        $(this).closest("tr").show();
                    }
                });
            });
        });
        return true;
    }



    /**
     * Функция отрисовки при редактировании, удалении, на основе уже выбранного ранее авто.
     */
    function autoCurrentSort() {
        $("#auto-select option:selected").each( function() {
            var selected_auto = $.trim($(this).text());

            table.find(".auto").each(function () {
                var tr = $(this).closest("tr");
                var id = +tr.children('td:first-child').next().children('input').val();

                if (selected_auto == lang_all_auto) {
                    $(this).closest("tr").show();
                } else if ($.trim($(this).text()) != selected_auto && id !=0 ) {
                    $(this).closest("tr").hide();
                }  else {
                    $(this).closest("tr").show();
                }
            });
        });
    }



    /**
     * Функция для выборки уникальных элементов массива
     */
    function unique(arr) {
        var obj = {};
        for(var i=0; i<arr.length; i++) {
            var str = arr[i];
            obj[str] = true; // запомнить строку в виде свойства объекта
        }
        return Object.keys(obj);
    }

});