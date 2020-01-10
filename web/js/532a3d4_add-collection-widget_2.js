// setup an "add a tag" link


var $addTagButton = $('<button type="button" class="btn btn-primary add_tag_link">Add a track</button>');
var $newLinkLi = $('<li></li>').append($addTagButton);
jQuery(document).ready(function() {

    // Get the ul that holds the collection of tags
    let $collectionHolder = $('ol.track');

    // add the "add a track" anchor and li to the tags ul
    $collectionHolder.append($newLinkLi);

    // add a delete link to all of the existing tag form li elements
    // $collectionHolder.find('li').each(function() {
    //     addTagFormDeleteLink($(this));
    // });

    // count the current form inputs we have (e.g. 2), use that as the new
    // index when inserting a new item (e.g. 2)
    $collectionHolder.data('index', $collectionHolder.find(':input').length);

    jQuery('.add_tag_link').on('click', function(e) {
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
    // You need this only if you didn't set 'label' => false in your tags field in TaskType
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
    var $removeFormButton = $('<button type="button">Delete this tag</button>');
    $tagFormLi.append($removeFormButton);

    $removeFormButton.on('click', function(e) {
        // remove the li for the tag form
        $tagFormLi.remove();
    });
}

























// jQuery(document).ready(function () {
//     jQuery('.add-another-collection-widget').click(function (e) {
//         var list = jQuery(jQuery(this).attr('data-list-selector'));
//         // Try to find the counter of the list or use the length of the list
//         var counter = list.data('widget-counter') || list.children().length;
//
//         // grab the prototype template
//         var newWidget = list.attr('data-prototype');
//         // replace the "__name__" used in the id and name of the prototype
//         // with a number that's unique to your emails
//         // end name attribute looks like name="contact[emails][2]"
//         newWidget = newWidget.replace(/__name__/g, counter);
//         // Increase the counter
//         counter++;
//         // And store it, the length cannot be used if deleting widgets is allowed
//         list.data('widget-counter', counter);
//
//         // create a new list element and add it to the list
//         var newElem = jQuery(list.attr('data-widget-tags')).html(newWidget);
//         newElem.appendTo(list);
//     });
// });