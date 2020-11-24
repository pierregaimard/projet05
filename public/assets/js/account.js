$(document).ready(function () {
    let $firstName = $("#firstName");
    let $lastName = $("#lastName");
    let $email = $("#email");
    let $newPassword = $("#newPassword");
    let $passwordConfirm = $("#passwordConfirm");

    let $nameButton = $("#nameButton");
    let $emailButton = $("#emailButton");
    let $passwordButton = $("#passwordButton");

    $nameButton.hide();
    $emailButton.hide();
    $passwordButton.hide();

    $firstName.on('keyup', function () {
        $nameButton.show();
    });
    $lastName.on('keyup', function () {
        $nameButton.show();
    });
    $email.on('keyup', function () {
        $emailButton.show();
    });
    $newPassword.on('keyup', function () {
        $passwordButton.show();
    });
    $passwordConfirm.on('keyup', function () {
        $passwordButton.show();
    });
});
