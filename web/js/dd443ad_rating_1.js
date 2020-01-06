$(function(){
    let starClicked = function starClicked(){
        let input = $(this).siblings().filter('input');
        if (input.attr('disabled')) {
            return;
        }
        input.val($(this).attr('data-value'));
    };

    let fullStar = function fullStar(){
        if ($(this).find('input').attr('disabled')) {
            return;
        }
        let labels = $(this).find('div');
        labels.removeClass();
        labels.addClass('far fa-star');
    };

    let emptyStar = function emptyStar(){
        let rating = parseInt($(this).find('input').val());
        if (rating > 0) {
            let selectedStar = $(this).children().filter('#rating_star_'+rating)
            let prevLabels = $(selectedStar).nextAll();
            prevLabels.removeClass();
            prevLabels.addClass('fas fa-star');
            selectedStar.removeClass();
            selectedStar.addClass('fas fa-star');
        }
    };

    $('.fas, .fa-star, .rating-well').click(starClicked);
    $('.rating-well').each(emptyStar);
    $('.rating-well').hover(fullStar,emptyStar);

});

/* https://codepen.io/WeeHorse/pen/PQydzW */