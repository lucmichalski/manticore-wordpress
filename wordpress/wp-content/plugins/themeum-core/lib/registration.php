<?php
ob_start();
function custom_registration_function() {

    $username = $password = $email = $website = $first_name = $last_name = $nickname = $bio = '';

    if (isset($_POST['submit'])) {
        registration_validation(
            sanitize_user($_POST['username']),
            esc_attr($_POST['password']),
            sanitize_email($_POST['email']),
            esc_url($_POST['website']),
            sanitize_text_field($_POST['fname']),
            sanitize_text_field($_POST['lname']),
            sanitize_text_field($_POST['nickname']),
            esc_textarea($_POST['bio'])
        );
        
        // sanitize user form input
        global $username, $password, $email, $website, $first_name, $last_name, $nickname, $bio;
        $username   =   sanitize_user($_POST['username']);
        $password   =   esc_attr($_POST['password']);
        $email      =   sanitize_email($_POST['email']);
        $website    =   esc_url($_POST['website']);
        $first_name =   sanitize_text_field($_POST['fname']);
        $last_name  =   sanitize_text_field($_POST['lname']);
        $nickname   =   sanitize_text_field($_POST['nickname']);
        $bio        =   esc_textarea($_POST['bio']);

        // call @function complete_registration to create the user
        // only when no WP_error is found
        complete_registration(
        $username,
        $password,
        $email,
        $website,
        $first_name,
        $last_name,
        $nickname,
        $bio
        );
    }

    registration_form(
        $username,
        $password,
        $email,
        $website,
        $first_name,
        $last_name,
        $nickname,
        $bio
        );
}



function registration_form( $username, $password, $email, $website, $first_name, $last_name, $nickname, $bio ) {
    $new_logo = moview_options('reg_logo');
    echo '
    <style>
        .center-image{margin: 0 auto 10px;} .alert{padding-left: 10px; margin-bottom: 5px; margin-top: 5px; }
    </style>
    <div class="col-sm-4 col-sm-offset-4 text-center">
        <form action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="post">
            <a href="'.esc_url(get_site_url()).'">
                    <div class="themeum-reg"><img class="img-responsive center-image" src="'.$new_logo['url'].'" alt="">
            </div></a>
            <p class="lead">'.__("Register New Account","moview").'</p>
            <div class="form-group">
                <input type="text" autocomplete="off" class="required form-control"  placeholder="'.__("Username *","moview").'" name="username" value="' . (isset($_POST['username']) ? $username : null) . '">
            </div>
            <div class="form-group">
                <input type="password" class="required form-control"  placeholder="'.__("Password *","moview").'" name="password" value="' . (isset($_POST['password']) ? $password : null) . '">
            </div>
            <div class="form-group">
                <input type="text" autocomplete="off" class="required form-control" placeholder="'.__("Email *","moview").'" name="email" value="' . (isset($_POST['email']) ? $email : null) . '">
            </div>
            <div class="form-group">
                <input type="text" autocomplete="off" class="form-control" placeholder="'.__("Website","moview").'" name="website" value="' . (isset($_POST['website']) ? $website : null) . '">
            </div>
            <div class="form-group">
                <input type="text" autocomplete="off" class="form-control" placeholder="'.__("First Name","moview").'" name="fname" value="' . (isset($_POST['fname']) ? $first_name : null) . '">
            </div>
            <div class="form-group">
                <input type="text" autocomplete="off" class="form-control" placeholder="'.__("Last Name","moview").'" name="lname" value="' . (isset($_POST['lname']) ? $last_name : null) . '">
            </div>
            <div class="form-group">
                <input type="text" autocomplete="off" class="form-control" placeholder="'.__("Nickname","moview").'" name="nickname" value="' . (isset($_POST['nickname']) ? $nickname : null) . '">
            </div>
            <div class="form-group">
                <textarea autocomplete="off" style="resize:none" class="form-control" placeholder="'.__("About / Bio","moview").'" name="bio">' . (isset($_POST['bio']) ? $bio : null) . '</textarea>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-success btn-lg btn-block themeum-register" name="submit" value="'.__("Register","moview").'"/>
            </div>
        </form>
    </div>';
}



function registration_validation( $username, $password, $email, $website, $first_name, $last_name, $nickname, $bio )  {
    global $reg_errors;
    $reg_errors = new WP_Error;

    if ( empty( $username ) || empty( $password ) || empty( $email ) ) {
        $reg_errors->add('field', __('Required form field is missing','moview'));
    }

    if ( strlen( $username ) < 4 ) {
        $reg_errors->add('username_length', __('Username too short. At least 4 characters is required','moview'));
    }

    if ( username_exists( $username ) )
        $reg_errors->add('user_name', __('Sorry, that username already exists!','moview'));

    if ( !validate_username( $username ) ) {
        $reg_errors->add('username_invalid', __('Sorry, the username you entered is not valid','moview'));
    }

    if ( strlen( $password ) < 5 ) {
        $reg_errors->add('password', __('Password length must be greater than 5','moview'));
    }

    if ( !is_email( $email ) ) {
        $reg_errors->add('email_invalid', __('Email is not valid','moview'));
    }

    if ( email_exists( $email ) ) {
        $reg_errors->add('email', __('Email Already in use','moview'));
    }
    
    if ( !empty( $website ) ) {
        if ( !filter_var($website, FILTER_VALIDATE_URL) ) {
            $reg_errors->add('website', __('Website is not a valid URL','moview'));
        }
    }

    if ( is_wp_error( $reg_errors ) ) {

        foreach ( $reg_errors->get_error_messages() as $error ) {
            echo '<div class="col-sm-4 col-sm-offset-4 text-center"><div class="alert alert-danger" role="alert"><strong>'.__('ERROR','moview').'</strong>:'.$error.'</div></div>';
        }
    }
}


function complete_registration() {
    global $reg_errors, $username, $password, $email, $website, $first_name, $last_name, $nickname, $bio;
    if ( count($reg_errors->get_error_messages()) < 1 ) {
        $userdata = array(
        'user_login'    =>  $username,
        'user_email'    =>  $email,
        'user_pass'     =>  $password,
        'user_url'      =>  $website,
        'first_name'    =>  $first_name,
        'last_name'     =>  $last_name,
        'nickname'      =>  $nickname,
        'description'   =>  $bio,
        );
        $user = wp_insert_user( $userdata );
        echo '<div class="col-sm-4 col-sm-offset-4 text-center"><div class="alert alert-success" role="alert">'.__("Registration complete.","moview").'</div></div>';   
    }
}

// Register a new shortcode: [custom_registration]
add_shortcode('custom_registration', 'custom_registration_shortcode');

// The callback function that will replace [book]
function custom_registration_shortcode() {
    ob_start();
    custom_registration_function();
    return ob_get_clean();
}
