class signIn {
    constructor() {
        const dupUsername1 = 'Whoops, Looks like there is all ready an account registered with the Username';
        const dupUsername2 = 'Please select something different.';
        const usernameQuip = 'Usernames can contain upper and lower case letters, numbers and dashes only. Duplicate usernames are not allowed.';
        const dupEmail = 'Whoops, Looks like there is all ready an account registered with that Email Address.';
        const usernameReq = 'Your Account Username is required.';
        const passReq = 'Your Account Password is required.';
        const invalidSignin = 'Whoops, Invalid Sign In. Please check your Username and/or Password and try again.';
        const signinSuccess = 'Cheer! Sign In Successfull';
        const signinError = 'Uh oh, Looks like an unexpected error was encountered, and you were not Signed In.';
        const newusernameReq = 'Your New Account will need a Username.';
        const validEmailReq = 'Your New Account will need a valid Email Address.';
        const newpassReq = 'Your New Account will need a Password.';
        const newAccCreated = 'Your New Account has been successfully created.';
        const newAccError = 'Looks like an unexpected error was encountered, and your New Account was unable to be created.';
        const accountEmailReq = 'Your Account Email Address is required.';
        const passResetSuccess = 'Your Account Password has been reset, and an email has been sent with the new password.';
        const noAccError = 'Hmmm, An Account with that Email Address could not be found.';
        const resetPassError = 'Looks like an unexpected error was encountered, and your Account Password could not be reset.';


        document.querySelector('#newusername').blur(function () {
            const username = document.querySelector("#newusername").val();

            if (username !== '') {
                const post_data = {
                    'username': username,
                    'requestType': 'usercheck'
                };
                this.$.post('./api.php?signin', post_data, function (data) {
                    if (data === '1') {

                        Notifi.addNotification({
                            color: 'warning',
                            text: dupUsername1 + ' "' + username + '". ' + dupUsername2,
                            icon: '<i class="fa fa-warning"></i>',
                            timeout: 12000
                        });


                        document.querySelector("#newusername").val('');
                    }
                });
            }
        });

        document.querySelector('#newusername').focus(function () {
            if (this.focused === 0) {
                Notifi.addNotification({
                    color: 'info',
                    text: usernameQuip,
                    icon: '<i class="fa fa-info-circle"></i>',
                    timeout: 8000
                });
                this.focused++;
            }
        });

        document.querySelector('#newemail').blur(function () {
            const useremail = document.querySelector("#newemail").val();

            if (useremail !== '') {

                const post_data = {
                    'useremail': useremail,
                    'requestType': 'emailcheck'
                };
                this.$.post('./api.php?signin', post_data, function (data) {
                    if (data === '1') {

                        Notifi.addNotification({
                            color: 'warning',
                            text: dupEmail,
                            icon: '<i class="fa fa-warning"></i>',
                            timeout: 12000
                        });


                        document.querySelector("#newemail").val('');
                    }
                });
            }
        });

        document.querySelector('#signin-btn').addEventListener("click", function (e) {

            e.preventDefault();

            const username = document.querySelector("#username").val();
            const password = document.querySelector("#password").val();

            if (username === '') {
                Notifi.addNotification({
                    color: 'danger',
                    text: usernameReq,
                    icon: '<i class="fa fa-warning"></i>',
                    timeout: 10000
                });
                document.querySelector("#username").focus();
                return false;
            }

            if (password === '') {
                Notifi.addNotification({
                    color: 'danger',
                    text: passReq,
                    icon: '<i class="fa fa-warning"></i>',
                    timeout: 10000
                });
                document.querySelector("#password").focus();
                return false;
            }


            const post_data = {
                'username': username,
                'password': password,
                'requestType': 'signin'
            };
            this.$.post('./api.php?signin', post_data, function (resdata) {
                const datacheck = this.$.parseJSON(resdata).length;
                if (datacheck === 0) {

                    Notifi.addNotification({
                        color: 'warning',
                        text: invalidSignin,
                        icon: '<i class="fa fa-warning"></i>',
                        timeout: 12000
                    });


                    document.querySelector("#username, #password").val('');
                } else {
                    this.$.each(this.$.parseJSON(resdata), function (idx, obj) {
                        if (obj[0] !== '') {

                            Notifi.addNotification({
                                color: 'success',
                                text: signinSuccess,
                                icon: '<i class="fa fa-check"></i>',
                                timeout: 10000
                            });


                            document.querySelector("#username, #password").val('');


                            window.setTimeout(document.querySelector('html').addClass('login'), 5000);
                        } else {

                            Notifi.addNotification({
                                color: 'danger',
                                text: signinError,
                                icon: '<i class="fa fa-warning"></i>',
                                timeout: 12000
                            });
                        }
                    });
                }
            });
        });

        document.querySelector('#signup-btn').addEventListener("click", function (e) {
            e.preventDefault();

            const username = document.querySelector("#newusername").val();
            const useremail = document.querySelector("#newemail").val();
            const password = document.querySelector("#newpass").val();
            if (username === '') {
                Notifi.addNotification({
                    color: 'danger',
                    text: newusernameReq,
                    icon: '<i class="fa fa-warning"></i>',
                    timeout: 10000
                });
                document.querySelector("#newusername").focus();
                return false;
            }

            if (useremail === '') {
                Notifi.addNotification({
                    color: 'danger',
                    text: validEmailReq,
                    icon: '<i class="fa fa-warning"></i>',
                    timeout: 10000
                });
                document.querySelector("#newemail").focus();
                return false;
            }

            if (password === '') {
                Notifi.addNotification({
                    color: 'danger',
                    text: newpassReq,
                    icon: '<i class="fa fa-warning"></i>',
                    timeout: 10000
                });
                document.querySelector("#newpass").focus();
                return false;
            }


            const post_data = {
                'username': username,
                'useremail': useremail,
                'password': password,
                'requestType': 'signup'
            };
            this.$.post('./api.php?signin', post_data, function (data) {
                if (data === '1') {

                    Notifi.addNotification({
                        color: 'success',
                        text: newAccCreated,
                        icon: '<i class="fa fa-check"></i>',
                        timeout: 10000
                    });

                    setTimeout(Login, 1000);


                    document.querySelector("#newusername, #newemail, #newpass").val('');
                } else {

                    Notifi.addNotification({
                        color: 'danger',
                        text: newAccError,
                        icon: '<i class="fa fa-warning"></i>',
                        timeout: 12000
                    });
                }
            });
        });

        document.querySelector('#resetPass').addEventListener("click", function (e) {
            e.preventDefault();

            const useremail = document.querySelector("#accountEmail").val();

            if (useremail === '') {
                Notifi.addNotification({
                    color: 'danger',
                    text: accountEmailReq,
                    icon: '<i class="fa fa-warning"></i>',
                    timeout: 10000
                });
                document.querySelector("#newemail").focus();
                return false;
            }


            const post_data = {
                'useremail': useremail,
                'requestType': 'resetpass'
            };
            this.$.post('./api.php?signin', post_data, function (data) {
                if (data === '1') {

                    Notifi.addNotification({
                        color: 'success',
                        text: passResetSuccess,
                        icon: '<i class="fa fa-check"></i>',
                        timeout: 10000
                    });


                    document.querySelector("#accountEmail").val('');
                } else if (data === '0') {

                    Notifi.addNotification({
                        color: 'danger',
                        text: noAccError,
                        icon: '<i class="fa fa-warning"></i>',
                        timeout: 12000
                    });


                    document.querySelector("#accountEmail").val('');
                } else {

                    Notifi.addNotification({
                        color: 'danger',
                        text: resetPassError,
                        icon: '<i class="fa fa-warning"></i>',
                        timeout: 12000
                    });


                    document.querySelector("#accountEmail").val('');
                }
            });
        });
    }
}

new signIn();