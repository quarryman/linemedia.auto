$(document).ready(function(){
    $(".lm-auto-transactions").tablesorter({
        headers: {
            0: { sorter: 'digit'},
            3: { sorter: 'digit'}
        },
        textExtraction: function(node) {
            if ($('span[title]', node).length > 0) {
                return $('span[title]', node).eq(0).attr('title');
            } else {
                var txt = $(node).text();
                return txt === '-' ? '-0.000001' : txt;
            }
        }
    });
});