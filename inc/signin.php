<?php

namespace Element\inc;


class signin
{
    public function __construct()
    {
        /*
        $db = new \txtDB();
        $db->datadir = $_ENV['DBDIR'];

        $mode = $_POST['requestType'];
        switch ($mode) {
            case
            'signin':
                $this->sign($db, $_POST['username'], $_POST['password']);
                break;
            case 'signup':
                $this->signup($db, $_POST['username'], $_POST['useremail'], $_POST['password']);
                break;
            case 'resetpass':
                $this->resetpass($db, $_POST['username']);
                break;
            case 'usercheck':
                $this->usercheck($db, $_POST['username']);
                break;
            case 'emailcheck':
                $this->emailcheck($db, $_POST['useremail']);
                break;

        }
        */
    }


    function sign($db, $username, $password)
    {
        /*
                $table = new Store('USERS', $_ENV['DBDIR']);

                $userdata = $table->findOneBy([$_ENV['USERNAME'], '=', htmlspecialchars($username)]);

                //$userdata = $db->selectWhere('USERS', $compClause, 1);

                foreach ($userdata as $item => $row) {
                    $_SESSION['st']['userId'] = $row[0];
                    $_SESSION['st']['userName'] = $row[1];
                    $_SESSION['st']['userEmail'] = $row[3];
                }

                echo json_encode($userdata);
            }

            function signup($db, $username, $useremail, $password)
            {
                $userdata = '';

                $usrname = htmlspecialchars(functions::alphaNum($username));
                $usremail = htmlspecialchars($useremail);
                $pass = functions::encodeIt($password);
                $dateCreated = date('Y-m-d H:i:s');


                $randomHash = uniqid(rand());

                $randHash = substr($randomHash, 0, 8);


                $newuser[$_ENV['USER_ID']] = $randHash;
                $newuser[$_ENV['USERNAME']] = $usrname;
                $newuser[$_ENV['PASSWORD']] = $pass;
                $newuser[$_ENV['USER_EMAIL']] = $usremail;
                $newuser[$_ENV['DATE_CREATED']] = $dateCreated;

                $new_user = $db->insert(
                    'USERS',
                    $newuser
                );

                $userinfo[$_ENV['USER_ID']] = $randHash;
                $userinfo[$_ENV['USERNAME']] = $usrname;
                $userinfo[$_ENV['DATE_CREATED']] = $dateCreated;


                $userFile = $usrname . '-' . $randHash;


                $new_user = $db->insert(
                    $userFile,
                    $userinfo
                );


                $subject = $_ENV['SITE_NAME'] . ' New Account Created';

                $message = '<html><body>';
                $message .= '<h3>' . $subject . '</h3>';
                $message .= '<p>';
                $message .= 'Your new account has been successfully created, and you can now sign in.<br />';
                $message .= '<a href="' . $_ENV['SITE_URL'] . 'sign-in.php">Sign In</a>';
                $message .= '</p>';
                $message .= '<p>Username: ' . $usrname . '<br />Password: The password you signed up with.</p>';
                $message .= '<hr />';
                $message .= '<p>Thank You,<br>' . $_ENV['SITE_NAME'] . '</p>';
                $message .= '</body></html>';

                $headers = 'From: ' . $_ENV['SITE_NAME'] . ' <' . $_ENV['SITE_EMAIL'] . ">\r\n";
                $headers .= 'Reply-To: ' . $_ENV['SITE_EMAIL'] . "\r\n";
                $headers .= "MIME-BUILD: 1.0\r\n";
                $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

                mail($usremail, $subject, $message, $headers);


                $checkFile = $_ENV['APP_DIR'] . '/data/' . $userFile;

                if (is_dir($checkFile)) {
                    echo '1';
                } else {
                    echo '0';
                }
        */
    }

    function resetpass($db, $useremail)
    {
        /*
        $userdata = '';
        $useremail = htmlspecialchars($useremail);

        $userdata = $db->selectWhere(
            'USERS',
            new SimpleWhereClause($_ENV['USER_EMAIL'], '=', $useremail)
        );

        if (empty($userdata)) {
            echo '0';
            exit;
        } else {
            global $uid;
            global $uname;
            foreach ($userdata as $item => $row) {
                $uid = $row[0];
                $uname = $row[1];
            }

            $userfile = $db->selectAll($uname . '-' . $uid);

            $randomHash = uniqid(rand());
            $randHash = substr($randomHash, 0, 8);

            $newpass = encodeIt($randHash);

            $db->updateSetWhere(
                'USERS', [
                $_ENV['PASSWORD'] => $newpass,
            ],
                new SimpleWhereClause(
                    USER_ID, '=', $uid
                )
            );

            $subject = $_ENV['SITE_NAME'] . ' Account Password Reset';

            $message = '<html><body>';
            $message .= '<h3>' . $subject . '</h3>';
            $message .= '<p>';
            $message .= 'Your Account Password has been Reset<br />';
            $message .= 'Temporary Password: ' . $randHash;
            $message .= '</p>';
            $message .= '<p>Once you have signed in, please take the time to update your account password to something you can easily remember.</p>';
            $message .= '<hr />';
            $message .= '<p>Thank You,<br>' . $_ENV['SITE_NAME'] . '</p>';
            $message .= '</body></html>';

            $headers = 'From: ' . $_ENV['SITE_NAME'] . ' <' . $_ENV['SITE_EMAIL'] . ">\r\n";
            $headers .= 'Reply-To: ' . $_ENV['SITE_EMAIL'] . "\r\n";
            $headers .= "MIME-BUILD: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

            mail($useremail, $subject, $message, $headers);

            echo '1';
        }
        */
    }

    function usercheck($db, $username)
    {
        /*
        $userdata = '';

        $usrname = htmlspecialchars(functions::alphaNum($username));

        $userdata = $db->selectWhere(
            'USERS',
            new SimpleWhereClause($_ENV['USERNAME'], '=', $usrname, $_ENV['DEFAULT_COMPARISON'])
        );

        echo (empty($userdata)) ? '0' : '1';
        */
    }

    function emailcheck($db, $useremail)
    {
        /*
        $userdata = '';

        $useremail = htmlspecialchars($useremail);

        $userdata = $db->selectWhere(
            'USERS',
            new SimpleWhereClause($_ENV['USER_EMAIL'], '=', $useremail, $_ENV['DEFAULT_COMPARISON'])
        );

        echo empty($userdata) ? '0' : '1';
        */
    }
}
