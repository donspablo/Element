class online {
    constructor() {
        e.preventDefault();

        var validEmailReq = 'A valid Email Address is required.';
        var passNoMatch = 'New Account Passwords do not match';
        var accUpdated = 'Cheer! Your Account Profile has been updated.';
        var errorText = 'Whoops, looks like an unexpected error was encountered, and your Account Profile could not be updated at this time.';


        var userEmail = document.querySelector("#userEmail").val();
        var password1 = document.querySelector("#password1").val();
        var password2 = document.querySelector("#password2").val();
        var old = document.querySelector("#old").val();
        var now = document.querySelector("#now").val();

        if (userEmail === '') {
            Notifi.addNotification({
                color: 'warning',
                text: validEmailReq,
                icon: '<i class="fa fa-warning"></i>',
                timeout: 10000
            });
            document.querySelector("#userEmail").focus();
            return false;
        }

        if (password1 !== password2) {
            Notifi.addNotification({
                color: 'warning',
                text: passNoMatch,
                icon: '<i class="fa fa-warning"></i>',
                timeout: 10000
            });
            document.querySelector("#password1, #password2").val('')
            return false;
        }


        var post_data = {
            'userEmail': userEmail,
            'password1': password1,
            'password2': password2,
            'old': old,
            'now': now
        };
        $.post('./api.php?profile', post_data, function (data) {
            if (data === '1') {

                Notifi.addNotification({
                    color: 'success',
                    text: accUpdated,
                    icon: '<i class="fa fa-check"></i>',
                    timeout: 12000
                });
                document.querySelector("#password1, #password2").val('')
            } else {

                Notifi.addNotification({
                    color: 'danger',
                    text: errorText,
                    icon: '<i class="fa fa-warning"></i>',
                    timeout: 12000
                });
                document.querySelector("#password1, #password2").val('')
            }
        });

    }


}

