<?php
/*
Plugin Name: INSIDE Integration
Version: 0.4
Plugin URI: http://www.inside.tm/
Description: Integrates with the INSIDE platform
Author: INSIDE
*/

if (!defined('WP_CONTENT_URL'))
    define('WP_CONTENT_URL', get_option('siteurl').'/wp-content');
if (!defined('WP_CONTENT_DIR'))
    define('WP_CONTENT_DIR', ABSPATH.'wp-content');
if (!defined('WP_PLUGIN_URL'))
    define('WP_PLUGIN_URL', WP_CONTENT_URL.'/plugins');
if (!defined('WP_PLUGIN_DIR'))
    define('WP_PLUGIN_DIR', WP_CONTENT_DIR.'/plugins');

function activate_inside() {
    add_option('inside_accountkey', '');
}

function deactive_inside() {
    delete_option('inside_accountkey');
}

function admin_init_inside() {
    register_setting('inside', 'inside_accountkey');
}

function admin_menu_inside() {
    add_options_page('INSIDE Integration', 'INSIDE Integration', 8, 'inside', 'options_page_inside');
}

function options_page_inside() {
    include(WP_PLUGIN_DIR.'/wp-inside/options.php');  
}

function inside_head () {
    $inside_accountkey = get_option('inside_accountkey');
    if ($inside_accountkey != "")
    {
        $accountkey = $inside_accountkey;
        global $post,$pagenow;
        $thePostID = $post->ID;

        $_remoteAddress = str_replace('.','',$_SERVER['REMOTE_ADDR']);
        $_remoteAddrLocalHost = '';
        $_serverName = $_SERVER['SERVER_NAME'];
        if(gethostbyname($_serverName) != $_serverName){
        $_remoteAddrLocalHost = str_replace('.','',gethostbyname($_serverName)); 
        } else {
        $_remoteAddrLocalHost = str_replace('.','',$_serverName); 
        }

        $_custEmail = '';
        $_custID = 0;
        if(is_user_logged_in()){
        $current_user = wp_get_current_user();
        $_custEmail = $current_user->user_email;
        $_custID = $current_user->ID;
        }
        $_INSIDE_OrderID = $_custID.$_remoteAddress.$_remoteAddrLocalHost;


        if (is_404()) {
            $_INSIDE_trackView_Type = 'pagenotfound';
            $_INSIDE_trackView_Name = '404/Page Not Found';
        } else if (get_search_query() != "") {
            $_INSIDE_trackView_Type = 'search';
            $_INSIDE_trackView_Name = 'Search "'.get_search_query().'" Result Page';
        }
        elseif (is_home()) {
            $_INSIDE_trackView_Type = 'homepage';
            $_INSIDE_trackView_Name = 'Home Page';
        } else if (in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'))){

        switch($_GET['action']){

          case 'register':
            $_INSIDE_trackView_Type = 'login';
            $_INSIDE_trackView_Name = 'Register Page';
          break;

          case 'lostpassword':
            $_INSIDE_trackView_Type = 'login';
            $_INSIDE_trackView_Name = 'Forgot Password Page';
          break;

          default:
            $_INSIDE_trackView_Type = 'login';
            $_INSIDE_trackView_Name = 'Log-in Page';
        }

        } else if(is_page() && get_post_type($post->ID) == 'page'){
            $_INSIDE_trackView_Type = 'article';
            /*$_INSIDE_trackView_Name = trim(wp_title("",false)).' | Content Page';*/
            $_INSIDE_trackView_Name = get_the_title().' | Content Page';
        } else if(get_post_type($post->ID) == 'post'){
            $_INSIDE_trackView_Type = 'other';
            if(get_the_title()) {
                $_INSIDE_trackView_Name = get_the_title().' | Post Page';
            } else {
                $_INSIDE_trackView_Name = 'Other [Post relevant] Page';    
            }            
        } else {
            $_INSIDE_trackView_Type = 'other';
            $_INSIDE_trackView_Name = 'Other Page';
        }
        ?>
<script type="text/javascript">
var _inside = _inside || [];
_inside.push({
'action':'getTracker',
'account':'<?php echo $accountkey; ?>'<?php if(is_user_logged_in()){?>,
'visitorId':'<?php echo md5($_custEmail); ?>'<?php }?>
});
// Track the page view
_inside.push({
'action':'trackView',
'type':'<?php echo $_INSIDE_trackView_Type;?>',
'name':'<?php echo $_INSIDE_trackView_Name;?>',
'id' : '<?php echo $_INSIDE_OrderID; ?>',
'orderId': '<?php echo $_INSIDE_OrderID; ?>',
});
(function() {
  var inside = document.createElement('script'); inside.type = 'text/javascript'; inside.async = true;
  inside.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'inside-graph.com/ig.js?hn=' + encodeURIComponent(document.location.hostname) + '&_=' + Math.random();
  var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(inside, s);
})();
</script>
        <?php
    }
}

function init_sessions() {
    if (!session_id()) {
        session_start();
    }
}

register_activation_hook(__FILE__, 'activate_inside');
register_deactivation_hook(__FILE__, 'deactive_inside');

if (is_admin()) {
    add_action('admin_init', 'admin_init_inside');
    add_action('admin_menu', 'admin_menu_inside');
} else {
    add_action('wp_head', 'inside_head');
    if (!session_id())
        add_action('init', 'init_sessions');
}

add_action('login_head', 'inside_head');
?>
