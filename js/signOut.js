class signOut {
    constructor() {
        var signoutNotification = null;
        document.querySelector('#signout').addEventListener("click", function (e) {
            e.preventDefault();
            if (Notifi.isDismissed(signoutNotification)) {
                signoutNotification = Notifi.addNotification({
                    color: 'default',
                    title: accountSignOutTitle,
                    text: accountSignOutText,
                    icon: '<i class="fa fa-sign-out fa-lg"></i>',
                    button: '<a href="api.php?action=signout" class="btn btn-success btn-notifi">' + yesOption + '</a> <span id="cancel-signout" class="btn btn-warning btn-notifi btn-close-notification">' + cancelOption + '</span>',
                    timeout: null
                });
            }
        });
    }
}

