/*global List*/
/*eslint no-undef: "error"*/

$(document).ready(function () {
    let options = {
        valueNames: [ "name", "email", "status" ]
    };
    let usersList = new List("users-list", options);

    let $name = $("#name");
    let $validationText = $("#validation");
    let $action = $("#action");
    let $listItemButton = $(".uk-list li a");

    $listItemButton.on("click", function () {
        let text = $(this).data("validation-text");
        let name = $(this).data("name");
        let actionText = $(this).data("action-text");
        let actionLink = $(this).data("action-link");
        let actionClass = $(this).data("action-class");

        $name.html(name);
        $validationText.html(text);
        $action.html(actionText);
        $action.attr("href", actionLink);
        $action.attr("class", actionClass);
    });
});
