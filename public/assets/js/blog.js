/*global List*/
/*eslint no-undef: "error"*/

$(document).ready(function () {
    let options = {
        valueNames: [ "title", "date" ]
    };
    let usersList = new List("blog-posts-list", options);
});