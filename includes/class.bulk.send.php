<?php

/**
 * ASANAK Bulk Send Class
 * 
 * @category  PLugins
 * @package   Wordpress
 * @author   Customized By Vahid Rezazadeh <vahid.rezazadeh1372@gmail.com>
 */
class WoocommerceIR_Bulk_SMS
{
    /**
     * Send Sms To Bulk
     *
     * @return void
     */
    public static function sendSmsToBulk()
    {
        if (isset($_POST['ps_sms_numbers'])) { ?>
            <div class="updated">
                <p><strong>تعداد مخاطبین با حذف شماره های تکراری </strong> => <?php echo count(explode(',', sanitize_text_field($_POST['ps_sms_numbers']))) . ' شماره ' ?></p>
            </div>
            <?php 
        } else if (isset($_GET['message']) && $_GET['message'] == 'error') { ?>
            <div class="error">
                <p><strong>خطا:</strong> وارد کردن شماره دریافت کننده الزامی است !</p>
            </div>
        <?php } elseif (isset($_GET['message']) && $_GET['message'] == 'sending_failed') { ?>
            <div class="error">
                <p><strong>خطا:</strong> ارسال پیام با مشکل مواجه گردید. لطفا شماره دریافت کننده یا تنظیمات سیستم پیام را بررسی کنید !</p>
            </div>
        <?php } else if (isset($_GET['message']) && $_GET['message'] == 'success') { ?>
            <div class="updated">
                <p>پیام ها با موفقیت به دریافت کننده ارسال گردیدند !</p>
            </div>
        <?php } ?>
        <script type="text/javascript">
            jQuery(document).ready(function(){
                jQuery("select#select_sender").change(function(){
                    var get_method = "";
                    jQuery("select#select_sender option:selected").each(
                        function(){
                            get_method += jQuery(this).attr('id');
                        }
                    );
                    if(get_method == 'wp_woo_numbers'){
                        jQuery("#wp_customer_club_contacts_desc").hide();
                        jQuery("#show_linenumber").fadeIn();
                        jQuery("#wp_woo_get_numbers").fadeIn();
                        jQuery("#persianwoosms_receiver_number").focus();
                    } 
                    if(get_method == 'wp_woo_customer_club_contacts'){
                        jQuery("#wp_woo_get_numbers").hide();
                        jQuery("#show_linenumber").hide();
                        jQuery("#wp_customer_club_contacts_desc").fadeIn();
                        jQuery("#persianwoosms_buyer_sms_body").focus();
                    } 
                });
            });
        </script>

        <?php
    }

    /**
     * Send Sms To Bulk Receiver
     *
     * @return void
     */
    public static function sendSmsToBulkReceiver()
    {
        if (isset($_POST['persianwoosms_send_sms'])) {
            if ($_POST['wp_woo_send_to'] == "wp_woo_customer_club_contacts") {
                if (empty($_POST['persianwoosms_buyer_sms_body'])) {
                    wp_redirect(add_query_arg(array('page'=> 'WoocommercePluginAsanak', 'send'=>'true', 'message' => 'error'), admin_url('admin.php')));
                    exit;
                } else {
                    $receiver_sms_data['sms_body'] = esc_textarea($_POST['persianwoosms_buyer_sms_body']);
                    $receiver_response_sms_club = WoocommerceIR_Gateways_SMS::init()->sendSMStoCustomerclubContacts($receiver_sms_data);
                    if ($receiver_response_sms_club) {
                        wp_redirect(add_query_arg(array('page'=> 'WoocommercePluginAsanak', 'send'=>'true', 'message' => 'success'), admin_url('admin.php')));
                        exit;
                    } else {
                        wp_redirect(add_query_arg(array('page'=> 'WoocommercePluginAsanak', 'send'=>'true', 'message' => 'sending_failed'), admin_url('admin.php')));
                        exit;
                    }
                }
            } else {
                if (empty($_POST['persianwoosms_receiver_number'])) {
                    wp_redirect(add_query_arg(array('page'=> 'WoocommercePluginAsanak', 'send'=>'true', 'message' => 'error'), admin_url('admin.php')));
                    exit;
                } else {
                    $receiver_sms_data['number']   = isset($_POST['persianwoosms_receiver_number']) ? explode(',', sanitize_text_field($_POST['persianwoosms_receiver_number'])) : '';
                    $receiver_sms_data['number'] = fa_en_mobile_woo_sms($receiver_sms_data['number']);
                    $receiver_sms_data['sms_body'] = esc_textarea($_POST['persianwoosms_buyer_sms_body']);  
                    $receiver_response_sms = WoocommerceIR_Gateways_SMS::init()->sendAsanak($receiver_sms_data);

                    if ($receiver_response_sms) {
                        wp_redirect(add_query_arg(array('page'=> 'WoocommercePluginAsanak', 'send'=>'true', 'message' => 'success' ), admin_url('admin.php')));
                        exit;
                    } else {
                        wp_redirect(add_query_arg(array('page'=> 'WoocommercePluginAsanak', 'send'=>'true', 'message' => 'sending_failed' ), admin_url('admin.php')));
                        exit;
                    }
                }
            }
        }
    }

    /**
     * Admin Footer Bulk
     *
     * @return void
     */
    public static function bulkAdminFooterPsSms()
    {
        if (ps_sms_options('enable_plugins', 'sms_main_settings', 'off') == 'off')
            return;
        global $post_type;
        if ('shop_order' == $post_type) {
            ?>
            <script type="text/javascript">
                jQuery(function() {
                    jQuery('<option>').val('send_sms').text('<?php _e('ارسال پیامک دسته جمعی', 'woocommerce')?>').appendTo("select[name='action']");
                    jQuery('<option>').val('send_sms').text('<?php _e('ارسال پیامک دسته جمعی', 'woocommerce')?>').appendTo("select[name='action2']");
                });
            </script>
            <?php
        }
    }

    /**
     * Admin Bulk Action
     *
     * @return void
     */
    public static function bulkActionPsSms()
    {
        if (ps_sms_options('enable_plugins', 'sms_main_settings', 'off') == 'off')
            return;
        $wp_list_table = _get_list_table('WP_Posts_List_Table');
        $action        = $wp_list_table->current_action();
        if ($action != 'send_sms') {
            return;
        }
        $post_ids = array_map('absint', (array) $_REQUEST['post']);
        $numbers = array();
        foreach ($post_ids as $post_id) {
            $numbers[] = get_post_meta($post_id, '_billing_phone', true);
        }
        $numbers = implode(',', array_unique($numbers));
        echo '<form method="POST" name="ps_sms_post_form" action="'.admin_url('admin.php?page=WoocommercePluginAsanak&send=true').'">
		<input type="hidden" value="'.$numbers.'" name="ps_sms_numbers" />
		</form><script language="javascript">document.ps_sms_post_form.submit(); </script>';
        exit();
    }
}