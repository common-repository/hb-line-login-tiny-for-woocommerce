<?php
/*
 * Plugin Name: HB Line Login Tiny For Woocommerce
 * Plugin URI: https://piglet.me/hb-line-login-tiny-for-woocommerce
 * Description: A HB Hide Shipping Tiny For Woocommerce
 * Version: 0.1.1
 * Author: heiblack
 * Author URI: https://piglet.me
 * License:  GPL 3.0
 * Domain Path: /languages
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
*/


class hb_line_login_tiny_admin
{
    private $url,$protocol;
    public function __construct()
    {
        if (!defined('ABSPATH')) {
            http_response_code(404);
            die();
        }
        if (!function_exists('plugin_dir_url')) {
            return;
        }
        if (!function_exists('is_plugin_active')) {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }
        if (!is_plugin_active('woocommerce/woocommerce.php')) {
            return;
        }
        $this->init();
    }

    public function init()
    {
        $this->HBAddpluginlink();
        $this->HB_add_line_tiny_setting();
        $this->AddinputPhoneBox();

        if(get_option('users_can_register')=='1'){
            $this->LineLoginCallbackEvent();
            $this->AddLoginButton();
        }
        if(get_option('hb_line_login_tiny_no_phone_auto')=='yes') $this->autoInputPhone();
    }
    private function HBAddpluginlink(){
        add_filter('plugin_action_links_'.plugin_basename(__FILE__), function ( $links ) {
            $links[] = '<a href="' .
                admin_url( 'admin.php?page=wc-settings&tab=hb-line-login-tiny' ) .
                '">' . esc_html(__('Settings')) . '</a>';
            return $links;
        });
    }
    private function AddLoginButton(){
        require_once(dirname(__FILE__ ). '/line/class.manager.php');
        $manager = new hb_line_login_tiny_manager();
        $scope = str_replace(",","%20",'openid%20profile%20email');
        $include_url = home_url().'/heiblack/LineLogin';
        $this->url =   $manager->lineLogin().'&scope='.$scope.'&redirect_uri='.$include_url;

        if(get_option('hb_line_login_tiny_login')=='yes'){
            add_action('woocommerce_login_form',function (){
                echo '<a href="'.esc_url($this->url).'">'.'<img src="'.plugin_dir_url( __FILE__ ). 'assets/btn_login_base.png'.'"></a><br>';
            });
        }
        if(get_option('hb_line_login_tiny_register')=='yes') {
            add_action('woocommerce_register_form', function () {
                echo '<a href="' . esc_url($this->url) . '">' . '<img src="' . plugin_dir_url(__FILE__) . 'assets/btn_login_base.png' . '"></a><br>';
            });
        }
    }
    private function LineLoginCallbackEvent(){
        $this->GetHttpProtocol();
        add_action('template_include', function ($original_template){
            if(strtok($this->protocol.$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"], '?')==home_url().'/heiblack/LineLogin'){
                $this->SetLineLoginTitle();
                status_header(200);
                wp_enqueue_style( '_hb_line_login_tiny_css', plugin_dir_url( __FILE__ ) . 'assets/style.css' );
                return  dirname(__FILE__ ). '/line/class.callback.php';
            }
            return $original_template;
        });
    }
    private function GetHttpProtocol(){
        if (isset($_SERVER['HTTPS']) &&
            ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
            isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
            $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            $this->protocol = 'https://';
        }
        else {
            $this->protocol = 'http://';
        }
    }
    private function SetLineLoginTitle(){
        add_filter( 'document_title_parts', function (){
            $title_parts['title'] = __( 'Enter Your Email - ', 'hb-line-login-tiny-for-woocommerce' ).get_bloginfo();
            return $title_parts;
        } );
    }
    private function HB_add_line_tiny_setting(){
        add_filter( 'woocommerce_get_settings_pages', function ( $settings ) {
            $settings[] = require_once dirname(__FILE__) . '/page/HB-Settings.php';
            return $settings;
        } );
    }

    private  function AddinputPhoneBox(){

        add_action('woocommerce_edit_account_form', function () {

            if(get_user_meta(get_current_user_id(), '_heiblack_social_line_id')) {

                woocommerce_form_field(
                    'heiblack_phone_number',
                    array(
                        'type' => 'tel',
                        'required' => true, // remember, this doesn't make the field required, just adds an "*"
                        'label' => 'Phone number'
                    ),
                    get_user_meta(get_current_user_id(), 'shipping_phone', true) // get the data
                );

            }

        });

        add_action( 'woocommerce_save_account_details', function ( $user_id ) {
            if(@$_POST[ 'heiblack_phone_number' ]){
                update_user_meta( $user_id, 'shipping_phone', wc_clean( $_POST[ 'heiblack_phone_number' ] ) );
            }
        });

        add_filter( 'woocommerce_save_account_details_required_fields',   function ( $required_fields ){

            if(get_user_meta(get_current_user_id(), '_heiblack_social_line_id')) {
                $required_fields['heiblack_phone_number'] = 'Phone numbers';
                return $required_fields;
            }

        } );

    }

    private function  autoInputPhone(){
        add_filter( 'woocommerce_checkout_fields' ,  function ( $fields ) {
            $user_phone_num = get_user_meta(get_current_user_id(), 'shipping_phone');
            if(isset($user_phone_num[0])){
                $fields['billing']['billing_phone']['default'] = esc_textarea($user_phone_num[0]);
            }
            return $fields;
        } );
    }



}


new hb_line_login_tiny_admin();



