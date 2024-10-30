<?php
class hb_line_login_tiny_admin_setting extends WC_Settings_Page {
    public function __construct() {
        $this->id    = 'hb-line-login-tiny';
        $this->label = esc_html(__( 'HB Line Login', 'hb-line-login-tiny-for-woocommerce' ));
        parent::__construct();
    }
    public function get_sections() {
        $sections = array(
            ''              => esc_html(__( 'General', 'hb-line-login-tiny-for-woocommerce' )),
        );
        return apply_filters( 'woocommerce_get_sections_' . $this->id, $sections );
    }
    public function output() {
        global $current_section;
        echo  "<br>";
        if(get_option('users_can_register')!='1') {
            echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html(__('Important! You are not open for registration.', 'hb-line-login-tiny-for-woocommerce')) . '</p></div>';
        }
        echo  "<a target='_blank' href='https://piglet.me/hb-line-login-tiny-for-woocommerce'>".__('Have Bug or suggest','hb-line-login-tiny-for-woocommerce')."</a>";

        $settings = $this->get_settings( $current_section );
        WC_Admin_Settings::output_fields( $settings );
    }
    public function save() {
        global $current_section;
        $settings = $this->get_settings( $current_section );
        WC_Admin_Settings::save_fields( $settings );

        if ( $current_section ) {
            do_action( 'woocommerce_update_options_' . $this->id . '_' . $current_section );
        }
    }
    public function get_settings( $current_section = '' ) {
        global $wp_roles;
        $settings[] = [
            'name' => esc_html(__('Setting', 'hb-line-login-tiny-for-woocommerce')),
            'type' => 'title',
            'desc' => '',
        ];
        $settings[] = [
            'title' => 'Channel ID',
            'desc' => '',
            'id' => '_hb_line_login_tiny_channel_id',
            'type' => 'text',
        ];
        $settings[] = [
            'title' => 'Channel secret',
            'desc' => '',
            'id' => '_hb_line_login_tiny_channel_secret',
            'type' => 'text',
        ];
        $settings[] = [
            'title' => 'skip email input',
            'desc' => '',
            'id' => '_hb_line_login_tiny_no_email',
            'type' => 'checkbox',
            'default' => 'no',
        ];
        $settings[] = [
            'title' => 'skip phone input',
            'desc' => '',
            'id' => '_hb_line_login_tiny_no_phone',
            'type' => 'checkbox',
            'default' => 'yes',
        ];
        $settings[] = [
            'title' => '',
            'desc' => 'phone number length , 0 = not limited',
            'id' => '_hb_line_login_tiny_no_phone_num',
            'type' => 'number',
            'default' => '0',
            'custom_attributes' => array('min' => '0')
        ];
        $settings[] = [
            'title' => '',
            'desc' => 'phone auto input billing_phone (woo)',
            'id' => 'hb_line_login_tiny_no_phone_auto',
            'type' => 'checkbox',
            'default' => 'no',
        ];
        $settings[] = [
            'title' => 'In woocommerce login form',
            'desc' => '',
            'id' => 'hb_line_login_tiny_login',
            'type' => 'checkbox',
            'default' => 'no',
        ];
        $settings[] = [
            'title' => 'In woocommerce register form',
            'desc' => '',
            'id' => 'hb_line_login_tiny_register',
            'type' => 'checkbox',
            'default' => 'no',
        ];
        $arrays = [];
        foreach ( $wp_roles->roles as $key=>$value ){
            $arrays[esc_attr($key)] = esc_textarea($value['name']);
        }
        unset($arrays['administrator']);
        unset($arrays['shop_manager']);
        unset($arrays['editor']);
        unset($arrays['contributor']);
        unset($arrays['author']);

        $settings[] = [
            'title' => 'Registered Member Role',
            'desc' => '',
            'id' => 'hb_line_login_tiny_role',
            'type' => 'select',
            'default' => 'customer',
            'options'   => $arrays
        ];
        $settings[] = [
            'type'  => 'sectionend',
            'id'    => '_hb_line_login_tiny_tiny',
        ];
        return apply_filters( 'woocommerce_get_settings_' . $this->id, $settings, $current_section );
    }
}

new hb_line_login_tiny_admin_setting();