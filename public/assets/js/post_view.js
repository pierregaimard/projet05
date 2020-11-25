/*global hljs*/
/*eslint no-undef: "error"*/

hljs.initHighlightingOnLoad();
$(document).ready(function () {
    let $action = $(".app-comment-actions a");
    let $confirmText = $("#post-comment-action-confirm .app-confirm-action");
    let $confirmAction = $("#post-comment-action-confirm a");

    $action.on("click", function () {
        let text = $(this).data("text");
        let actionText = $(this).data("action");
        let actionHref = $(this).data("action-href");
        let actionClass = $(this).data("action-class");

        $confirmText.html(text);
        $confirmAction.attr("href", actionHref);
        $confirmAction.attr("class", actionClass);
        $confirmAction.html(actionText);
    });
})
