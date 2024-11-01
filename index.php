<?php
/*
Plugin Name: WP reCaptcha Login
Plugin URI: https://www.eastsidecode.com
Description: Plugin to enable reCaptcha V3 on the login page. This prevents brute-force attacks on your login page. 
Author: Louis Fico
Version: 1.0
Author URI: eastsidecode.com
*/

if ( ! defined( 'ABSPATH' ) ) exit;
 

/** 
 * Create the menu item
 */

add_action('admin_menu', function() {
	add_submenu_page( 'options-general.php', 'WP reCaptcha Login', 'reCaptcha Settings', 'manage_options', 'escode_wp_recaptcha_login_settings', 'escode_wp_recaptcha_settings_page' );
});
 
 
/**
 * Add settings
*/

add_action( 'admin_init', function() {
    register_setting( 'escode-wp-recaptcha-login-plugin-settings', 'escode_wp_recaptcha_login_site_key' );
    register_setting( 'escode-wp-recaptcha-login-plugin-settings', 'escode_wp_recaptcha_login_secret_key' );


});
 

/**
 * Add settings page
 */

 
function escode_wp_recaptcha_settings_page() {
  ?>
    <div class="wrap">
    	<h1>reCaptcha Settings</h1>
      <form action="options.php" method="post">
 
        <?php
          settings_fields( 'escode-wp-recaptcha-login-plugin-settings' );
          do_settings_sections( 'escode-wp-recaptcha-login-plugin-settings' );
        ?>
        
         <table>
             <tr>
                <td height="10" style="font-size:10px; line-height:10px;"></td>
            </tr>

             <tr>
                <td>reCaptcha Keys can be obtained from <a href="https://www.google.com/recaptcha" target="_blank">https://www.google.com/recaptcha</a></td>
            </tr>

        </table>

        <table>
             <tr>
                <td height="10" style="font-size:10px; line-height:10px;"></td>
            </tr>

            <tr>
                <th valign="top">Site Key:</th>
                <td><input type="text" placeholder="reCaptcha Site Key Here" name="escode_wp_recaptcha_login_site_key" value="<?php echo esc_attr( get_option('escode_wp_recaptcha_login_site_key') ); ?>" size="80" /></td>
            </tr>

            <tr>
				<td height="15" style="font-size:15px; line-height:15px;"></td>
            </tr>

              <tr>
                <th>Secret Key:</th>
                <td><input type="text" placeholder="reCaptcha Secret Key Here" name="escode_wp_recaptcha_login_secret_key" value="<?php echo esc_attr( get_option('escode_wp_recaptcha_login_secret_key') ); ?>" size="80" /></td>
            </tr>
            <tr>
                <td height="2" style="font-size:2px; line-height:2px;"></td>
            </tr>
 
            <tr>
                <td><?php submit_button(); ?></td>
            </tr>
 
        </table>
 
      </form>
    </div>
  <?php
}

/**
 * Add Captcha
 */

add_action('login_enqueue_scripts', 'escode_recaotcha_login_recaptcha_script');

function escode_recaotcha_login_recaptcha_script() {

    wp_register_script('recaptcha_login', 'https://www.google.com/recaptcha/api.js');

    wp_enqueue_script('recaptcha_login');

}



add_action( 'login_form', 'escode_display_recaptcha_on_login' );

function escode_display_recaptcha_on_login() {

$escodereCaptchaPublicKey = esc_attr(get_option('escode_wp_recaptcha_login_site_key'));
$escodereCaptchaSecretKey = esc_attr(get_option('escode_wp_recaptcha_login_secret_key'));


if (!isset($escodereCaptchaPublicKey)) {
    return;
}


if (empty($escodereCaptchaPublicKey)) {

    /**
     * Aint nobody got time fo dat
     */
    echo "<script>alert('Make sure to fill in your reCaptcha site key');</script>";
}



if (!isset($escodereCaptchaSecretKey)) {
    return;
}


if (empty($escodereCaptchaSecretKey)) {

    /**
     * Aint nobody got time fo dat
     */
    echo "<script>alert('Make sure to fill in your reCaptcha private key');</script>";
}


    /**
     * If neither key is empty
     */

    if (!empty($escodereCaptchaPublicKey) && !empty($escodereCaptchaSecretKey)) {

        echo "<script>
        function onSubmit(token) {
        document.getElementById('loginform').submit();
        }
        </script>
        <button class='g-recaptcha' data-sitekey='$escodereCaptchaPublicKey' data-callback='onSubmit' data-size='invisible' style='display:none;'>Submit</button>";


    }

}


/**
 * Add the login filter
 */


$escodereCaptchaPublicKey = esc_attr(get_option('escode_wp_recaptcha_login_site_key'));
$escodereCaptchaSecretKey = esc_attr(get_option('escode_wp_recaptcha_login_secret_key'));


if (!empty($escodereCaptchaPublicKey) && !empty($escodereCaptchaSecretKey)) {

add_filter('wp_authenticate_user', 'escode_verify_recaptcha_on_login', 10, 2);

}

function escode_verify_recaptcha_on_login($user, $password) {



$escodereCaptchaPublicKey = esc_attr(get_option('escode_wp_recaptcha_login_site_key'));
$escodereCaptchaSecretKey = esc_attr(get_option('escode_wp_recaptcha_login_secret_key'));



if (empty($escodereCaptchaPublicKey)) {

    /**
     * Aint nobody got time fo dat
     */
    echo "<script>alert('Make sure to fill in your reCaptcha site key');</script>";
}



if (empty($escodereCaptchaSecretKey)) {

    /**
     * Aint nobody got time fo dat
     */
    echo "<script>alert('Make sure to fill in your reCaptcha private key');</script>";
   
}


/**
 * If neither key is empty
 */

    if (!empty($escodereCaptchaPublicKey) && !empty($escodereCaptchaSecretKey)) {

    if (isset($_POST['g-recaptcha-response'])) {


        /** 
         * Santiize it
         */

        if (!preg_match('/^[\w-]*$/', $_POST['g-recaptcha-response'])) {
           return new WP_Error( 'Captcha Invalid', __('<strong>ERROR</strong>: Not a valid reCaptcha response.') );
        }


    $escodeWPLoginCaptcharesponse = wp_remote_get( "https://www.google.com/recaptcha/api/siteverify?secret=$escodereCaptchaSecretKey&response=" . $_POST['g-recaptcha-response'] );



    $escodeWPLoginCaptcharesponse = json_decode($escodeWPLoginCaptcharesponse['body'], true);

        if (true == $escodeWPLoginCaptcharesponse['success']) {

            return $user;

        } else {

            return new WP_Error( 'Captcha Invalid', __('<strong>ERROR</strong>: reCaptcha thinks you are a bot.') );

        }

        } else {

            return new WP_Error( 'Captcha Invalid', __('<strong>ERROR</strong>: No response from reCaptcha. Please make sure Javascript is enabled.') );

        }


    } // end if  keys are not empty

} // end function




