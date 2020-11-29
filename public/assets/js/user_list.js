/*global List*/
/*eslint no-undef: "error"*/

$(document).ready(function () {
    let options = {
        valueNames: [ "name", "email", "status" ]
    };
    let usersList = new List("users-list", options);
});
