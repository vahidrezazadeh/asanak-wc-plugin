<?php
/**
 * Plugins Main File
 * 
 * PHP version 5.6.23 | 7.2.19
 * 
 * @category  PLugins
 * @package   Wordpress
 * @author   Customized By Vahid Rezazadeh <vahid.rezazadeh1372@gmail.com>
 */

/*
Plugin Name: افزونه ارسال پیامک ووکامرس
Version: 1.1
Plugin URI: https://asanak.com/
Description: افزونه ارسال پیامک در سیستم ووکامرس برای وردپرس
Author: Asanak - Vahid Rezazadeh
Developer EMAIL: vahid.rezazadeh1372@gmail.com
Author URI: https://asanak.com/
Contributors: asanak.com
*/

if (!defined('ABSPATH')) { 
   die('Access Denied');
}

if (!defined('PS_WOO_SMS_VERSION'))
    define('PS_WOO_SMS_VERSION', '1.1');

if (!defined('PS_WOO_SMS_PLUGIN_PATH'))
    define('PS_WOO_SMS_PLUGIN_PATH', plugins_url('', __FILE__));

if (!defined('PS_WOO_SMS_PLUGIN_LIB_PATH'))
    define('PS_WOO_SMS_PLUGIN_LIB_PATH', dirname(__FILE__). '/includes');

/**
 * Uninstall Function
 *
 * @return void
 */
function woocommercePersianUninstall()
{
    update_option('redirect_to_woo_sms_about_page', 'no');
    update_option('redirect_to_woo_sms_about_page_check', 'no');
}
register_activation_hook(__FILE__, 'woocommercePersianUninstall');
register_deactivation_hook(__FILE__, 'woocommercePersianUninstall');

/**
 * Adding File Resources
 *
 * @return void
 */
function wocommercePersianAsanak()
{
    global $persianwoosms;
    include_once PS_WOO_SMS_PLUGIN_LIB_PATH. '/requirement.php';
    include_once PS_WOO_SMS_PLUGIN_LIB_PATH. '/class.settings.php';
    include_once PS_WOO_SMS_PLUGIN_LIB_PATH. '/class.gateways.php';
    include_once PS_WOO_SMS_PLUGIN_LIB_PATH. '/class.bulk.send.php';
    $persianwoosms = WoocommerceIR_Settings_SMS::init();
}

add_action('plugins_loaded', 'wocommercePersianAsanak');