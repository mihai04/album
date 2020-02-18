// add track button
let $addTagButton = $('<button type="button" style="width: 300px" class="_reusable add_track">Add a track</button>');

// default li
let $newLinkLi = $('<li></li>').append($addTagButton);

jQuery(document).ready(function() {

    // Get the ul that holds the collection of tags
    let $collectionHolder = $('ol.track');

    // // add a delete link to all of the existing tag form li elements
    $collectionHolder.find('li').each(function() {
        addTagFormDeleteLink($(this));
    });

    // add the "add a track" anchor and li to the tags ul
    $collectionHolder.append($newLinkLi);

    // count the current form inputs we have (e.g. 2), use that as the new
    // index when inserting a new item (e.g. 2)
    $collectionHolder.data('index', $collectionHolder.find(':input').length);

    jQuery('.add_track').on('click', function(e) {
        // add a new tag form
        addTagForm($collectionHolder, $newLinkLi);
    });
});

function addTagForm($collectionHolder, $newLinkLi) {
    // Get the data-prototype explained earlier
    let prototype = $collectionHolder.data('prototype');

    // get the new index
    let index = $collectionHolder.data('index');

    let newForm = prototype;
    // You need this only if yo67u didn't set 'label' => false in your tags field in TaskType
    // Replace '__name__label__' in the prototype's HTML to
    // instead be a number based on how many items we have
    // newForm = newForm.replace(/__name__label__/g, index);

    // Replace '__name__' in the prototype's HTML to
    // instead be a number based on how many items we have
    newForm = newForm.replace(/__name__/g, index);

    // increase the index with one for the next item
    $collectionHolder.data('index', index + 1);

    // Display the form in the page in an li, before the "Add a tag" link li
    let $newFormLi = $('<li></li>').append(newForm);
    $newLinkLi.before($newFormLi);
    addTagFormDeleteLink($newFormLi);
}

function addTagFormDeleteLink($tagFormLi) {
    var $removeFormButton = $('<button  style="margin-bottom: 5px; width: 300px;" class="btn btn-danger">' +
        'Remove track' +
        '</button>');
    $tagFormLi.append($removeFormButton);

    $removeFormButton.on('click', function(e) {
        // remove the li for the tag form
        $tagFormLi.remove();
    });
}


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