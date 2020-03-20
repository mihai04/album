$(function(){
    var labelWasClicked = function labelWasClicked(){
        var input = $(this).siblings().filter('input');
        if (input.attr('disabled')) {
            return;
        }
        input.val($(this).attr('data-value'));
    }

    var turnToStar = function turnToStar(){
        if ($(this).find('input').attr('disabled')) {
            return;
        }
        var labels = $(this).find('div');
        labels.removeClass();
        labels.addClass('far fa-star');
    }

    var turnStarBack = function turnStarBack(){
        var rating = parseInt($(this).find('input').val());
        if (rating > 0) {
            var selectedStar = $(this).children().filter('#rating_star_'+rating)
            var prevLabels = $(selectedStar).nextAll();
            prevLabels.removeClass();
            prevLabels.addClass('fas fa-star');
            selectedStar.removeClass();
            selectedStar.addClass('fas fa-star');
        }
    }

    $('.fas, .fa-star, .rating-well').click(labelWasClicked);
    $('.rating-well').each(turnStarBack);
    $('.rating-well').hover(turnToStar,turnStarBack);

});

// $(function(){
//     var labelWasClicked = function labelWasClicked(){
//         var input = $(this).siblings().filter('input');
//         if (input.attr('disabled')) {
//             return;
//         }
//         input.val($(this).attr('data-value'));
//     }
//
//     var turnToStar = function turnToStar(){
//         if ($(this).find('input').attr('disabled')) {
//             return;
//         }
//         var labels = $(this).find('div');
//         labels.removeClass();
//         labels.addClass('star');
//     }
//
//     var turnStarBack = function turnStarBack(){
//         var rating = parseInt($(this).find('input').val());
//         if (rating > 0) {
//             var selectedStar = $(this).children().filter('#rating_star_'+rating)
//             var prevLabels = $(selectedStar).nextAll();
//             prevLabels.removeClass();
//             prevLabels.addClass('star-full');
//             selectedStar.removeClass();
//             selectedStar.addClass('star-full');
//         }
//     }
//
//     $('.star, .rating-well').click(labelWasClicked);
//     $('.rating-well').each(turnStarBack);
//     $('.rating-well').hover(turnToStar,turnStarBack);
//
// });