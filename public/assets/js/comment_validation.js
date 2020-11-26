/*global List*/
/*eslint no-undef: "error"*/

$(document).ready(function () {
    let options = {
        valueNames: [ "author", "post" ]
    };
    let usersList = new List("comments-list", options);

    let $validationText = $("#app-comment-action-confirm .text");
    let $action = $("#app-comment-action-confirm #action");
    let $listItemButton = $(".uk-list li a");

    $listItemButton.on("click", function () {
        let text = $(this).data("validation-text");
        let actionText = $(this).data("action-text");
        let actionLink = $(this).data("action-link");
        let actionClass = $(this).data("action-class");

        $validationText.html(text);
        $action.html(actionText);
        $action.attr("href", actionLink);
        $action.attr("class", actionClass);
    });
});