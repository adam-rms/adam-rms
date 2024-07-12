<?php
function social_authenticate_user(string $provider, string $userId, string $firstName, string $lastName, string $email, bool $emailVerified)
{
    require_once __DIR__ . '/../../common/head.php';
    $DBLIB->where("users_oauth_microsoftid", $userId);
    $DBLIB->where("users_deleted", 0);
    $user = $DBLIB->getOne("users", ["users.users_suspended", "users.users_userid", "users.users_hash", "users.users_email"]);
    if ($user) {
        if ($user['users_suspended'] != '0') {
            $PAGEDATA['ERROR'] = "Sorry, your user account is suspended";
            echo $TWIG->render('login/error.twig', $PAGEDATA);
            exit;
        }

        if ($user['users_emailVerified'] != 1 and $email_verified and strtolower($email) == strtolower($user['users_email'])) {
            // Update their email verification status
            $DBLIB->where("users_userid", $user['users_userid']);
            $DBLIB->update("users", ["users_emailVerified" => 1]);
        }

        $GLOBALS['AUTH']->generateToken($user['users_userid'], false, "Web - {$provider}", "web-session");
        header("Location: " . (isset($_SESSION['return']) ? $_SESSION['return'] : $CONFIG['ROOTURL']));
        return;
    } else {
        //See if an email is found, but not linked to provider. We don't want to auto-link them because its a good attack vector, so instead prompt a password login and then link in account settings.
        $DBLIB->where("users_email", strtolower($email));
        $user = $DBLIB->getOne("users", ["users.users_suspended", "users.users_userid", "users.users_hash"]);
        if ($user) {
            $PAGEDATA['ERROR'] = "An AdamRMS account associated with the email address you selected has been found. Please login again using your AdamRMS username & password to link your account to a Microsoft Account in AdamRMS account settings";
            echo $TWIG->render('login/error.twig', $PAGEDATA);
            return;
        }
    }

    //Okay we can't find them, so lets sign them up to an account
    $username = preg_replace("/[^a-zA-Z0-9]+/", "", $firstName . $lastName);
    while ($AUTH->usernameTaken($username)) {
        $username .= "1";
    }

    $data = array(
        'users_oauth_microsoftid' => $userId,
        'users_email' => $email,
        'users_emailVerified' => $emailVerified,
        'users_username' => $username,
        'users_name1' => $firstName,
        'users_name2' => $lastName,
        'users_hash' => $CONFIG['AUTH_NEXTHASH']
    );

    $newUser = $DBLIB->insert("users", $data);
    if (!$newUser) {
        $PAGEDATA['ERROR'] = "Sorry something went wrong trying to create a new user account";
        echo $TWIG->render('login/error.twig', $PAGEDATA);
        return;
    }
    $bCMS->auditLog("INSERT", "users", json_encode($data), null, $newUser);
    if (!$_SESSION['return'] and isset($_SESSION['app-oauth'])) {
        $PAGEDATA['ERROR'] = "Account created - please restart app and login again";
        echo $TWIG->render('login/error.twig', $PAGEDATA);
        return;
    } else {
        $GLOBALS['AUTH']->generateToken($newUser, false, "Web - {$provider}", "web-session");
        header("Location: " . (isset($_SESSION['return']) ? $_SESSION['return'] : $CONFIG['ROOTURL']));
        return;
    }
}

function get_available_auth_providers()
{
    require_once __DIR__ . '/oauth/providers.php';
    $hybridauth_providers = $hybridauth->getProviders();

    $providers = [];

    foreach ($hybridauth_providers as $provider_name) {
        $name_lower = strtolower($provider_name);
        $prov_details = [
            "short_name" => $provider_name,
            "icon_html" => "",
            "auth_path" => "/oauth?provider=$name_lower"
        ];
        $providers[] = $prov_details;
    }
    return $providers;
}
