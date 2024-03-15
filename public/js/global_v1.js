Dcat.ready(function () {
    //解決ios選取方塊css異常問題
    $('.select-all').click(function(){
        if ($(this).is(":checked")) {
            $('.vs-checkbox--check').addClass('vs-checkbox-show');
        }
        else{
            $('.vs-checkbox--check').removeClass('vs-checkbox-show');
        }
    });

    $('.grid-row-checkbox').click(function(){
        if ($(this).is(":checked")) {
            $(this).next().find('.vs-checkbox--check').addClass('vs-checkbox-show');
        }
        else{
            $(this).next().find('.vs-checkbox--check').removeClass('vs-checkbox-show');
        }
    });
});

$(document).on('pjax:complete', function() {
    if ($("body").hasClass("sidebar-open")) {
        $("body").removeClass("sidebar-open");
        $("body").addClass("sidebar-closed sidebar-collapse");
    }

    //解決ios選取方塊css異常問題
    $('.select-all').click(function(){
        if ($(this).is(":checked")) {
            $('.vs-checkbox--check').addClass('vs-checkbox-show');
        }
        else{
            $('.vs-checkbox--check').removeClass('vs-checkbox-show');
        }
    });

    $('.grid-row-checkbox').click(function(){
        if ($(this).is(":checked")) {
            $(this).next().find('.vs-checkbox--check').addClass('vs-checkbox-show');
        }
        else{
            $(this).next().find('.vs-checkbox--check').removeClass('vs-checkbox-show');
        }
    });
});