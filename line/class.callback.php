<?php

class hb_line_login_tiny_profiles{
    private $CLIENT_ID,$CLIENT_SECRET,$CLIENT_URL;
    public function __construct()
    {

        $this->CLIENT_ID        = sanitize_text_field(get_option('_hb_line_login_tiny_channel_id'));
        $this->CLIENT_SECRET    = sanitize_text_field(get_option('_hb_line_login_tiny_channel_secret'));
        $this->CLIENT_URL       = sanitize_url(home_url().'/heiblack/LineLogin');

    }
    public function getLineProfile_access_token($accessToken){

        // $headerData = [
        //     "content-type: application/x-www-form-urlencoded",
        //     "charset=UTF-8",
        //     'Authorization: Bearer ' . $accessToken,
        // ];
        // $ch = curl_init();
        // curl_setopt($ch, CURLOPT_HTTPHEADER, $headerData);
        // curl_setopt($ch, CURLOPT_URL, "https://api.line.me/v2/profile");
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // $result = curl_exec($ch);
        // curl_close($ch);
        // $result = json_decode($result);
        // return $result;

        $headers = array(  'Content-Type' => 'application/json', 'Authorization'=> 'Bearer ' .  $accessToken);

        $response = wp_remote_post("https://api.line.me/v2/profile", array(
                'method' => 'POST',
                'timeout' => 15,
                'redirection' => 5,
                'httpversion' => '1.0',
                'sslverify' => false,
                'headers' => $headers,
            )
        );

        $result = json_decode($response['body']);


        return $result;



    }
    public function getAccessToken($code){

        //$url = "https://api.line.me/oauth2/v2.1/token";

        $header = ['Content-Type: application/x-www-form-urlencoded'];

        $data = [
            "grant_type" => "authorization_code",
            "code" => $code,
            "redirect_uri" => esc_url($this->CLIENT_URL),
            "client_id" => esc_attr($this->CLIENT_ID),
            "client_secret" => esc_attr($this->CLIENT_SECRET)
        ];



        $response = wp_remote_post("https://api.line.me/oauth2/v2.1/token", array(
                'method' => 'POST',
                'timeout' => 15,
                'redirection' => 5,
                'httpversion' => '1.0',
                'sslverify' => false,
                'headers' => $header,
                'body'=>http_build_query($data)
            )
        );



        //     $request = curl_init();

        //     curl_setopt($request, CURLOPT_HTTPHEADER, $header);

        //     curl_setopt($request, CURLOPT_URL, $url);

        //     curl_setopt($request, CURLOPT_SSL_VERIFYHOST, false);

        //     curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);

        //     curl_setopt($request, CURLOPT_POST, TRUE);

        //       curl_setopt($request, CURLOPT_POSTFIELDS, http_build_query($data));

        //       curl_setopt($request, CURLOPT_FOLLOWLOCATION, 1);

        //       curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);

        //     $result = curl_exec($request);

        //     curl_close($request);



        $result = json_decode($response['body']);

        if(empty($result->error)){

            return $result->access_token;

        }


        return false;



    }

}



class  hb_line_login_tiny_verify{
    private $url,$access_token,$line_Class;
    public function __construct(){
        $this->url = get_permalink( wc_get_page_id( 'myaccount' ) );

        $this->init();
    }
    private function init(){
        $code   = sanitize_text_field(@$_GET['code']);
        if(!$code) $this->gotomyaccount();

        $this->line_Class   = new  hb_line_login_tiny_profiles();
        $this->access_token = $this->line_Class->getAccessToken($code);

        if($this->access_token == false && $_COOKIE['HB_Line_Login_Tiny_Access_Token'])  $this->emailVerify();
        if($this->access_token != false)  $this->accessTokenVerify();
        if($this->access_token == false && empty($_COOKIE['HB_Line_Login_Tiny_Access_Token']))  $this->gotomyaccount();
        //$this->gotomyaccount();

    }
    private function accessTokenVerify(){
        setcookie("HB_Line_Login_Tiny_Access_Token",$this->access_token);
        $user = $this->line_Class->getLineProfile_access_token($this->access_token);
        if(username_exists($user->userId)) $this->loginLineUser($user->userId);
        if(!username_exists($user->userId)) $this->newLineUser($user->userId,$user->displayName);
    }
    private function loginLineUser($userId){
        //exist
        $hasuser = get_user_by('login',$userId);

        wp_set_auth_cookie($hasuser->ID, true, false);

        wp_set_current_user($hasuser->ID);


        //jump

        $this->gotomyaccount();

        return;
    }

    private function newLineUser($userId,$lineUserName){

        if(empty($userId)) $this->gotomyaccount();

        $_hb_line_login_tiny_no_email = get_option('_hb_line_login_tiny_no_email');
        $_hb_line_login_tiny_no_phone = get_option('_hb_line_login_tiny_no_phone');

        if($_hb_line_login_tiny_no_email == 'yes' && $_hb_line_login_tiny_no_phone == 'yes' ) $this->skipEmailVerify($userId,$lineUserName);
        if($_hb_line_login_tiny_no_email == 'no' || $_hb_line_login_tiny_no_phone == 'no') $this->emailEnter(false);
    }

    private function skipEmailVerify($userId,$lineUserName){

        //skip email
        $lineUserId = $userId;

        if(empty($lineUserName)) $lineUserName=$lineUserId;

        $random_password = wp_generate_password(
            $length = 20,
            $include_standard_special_chars = false

        );

        $role = get_option('hb_line_login_tiny_role');


        $userdata     = array(
            'ID'         => '',
            'user_login' => esc_attr($lineUserId),
            'user_pass'  => $random_password,
            'user_email' => esc_html($lineUserId.'@mail.line.me'),
            'display_name'=>esc_html($lineUserName),
            'nickname'=>esc_html($lineUserName),
            'role' => esc_html($role)
        );

        $user_id = wp_insert_user($userdata);

        if(is_wp_error($user_id)) wp_die( __( 'Error' ) );

        $this->createNewUserData($user_id,$lineUserId);

    }

    private function  emailVerify(){

        //$_hb_line_login_tiny_no_email == 'yes'

        if(@$_POST['email'] || @$_POST['tel']){

            $_hb_line_login_tiny_no_email = get_option('_hb_line_login_tiny_no_email');
            $_hb_line_login_tiny_no_phone = get_option('_hb_line_login_tiny_no_phone');

            if($_hb_line_login_tiny_no_email == 'no'){
                $email = sanitize_email(@$_POST['email']);
            }

            if ($_hb_line_login_tiny_no_phone == 'no') {
                $tel = sanitize_text_field(@$_POST['tel']);
                preg_match_all("/[0-9]+$/", $tel, $mobiles);
                if(empty($mobiles[0][0])) $this->gotomyaccount();
            }

            if($_hb_line_login_tiny_no_email == 'no' && empty($email)) $this->gotomyaccount();
            if($_hb_line_login_tiny_no_phone == 'no' && empty($tel)) $this->gotomyaccount();


            $user = $this->line_Class->getLineProfile_access_token($_COOKIE['HB_Line_Login_Tiny_Access_Token']);

            $lineUserId  = $user->userId;
            $lineUserName  = $user->displayName;

            if($_hb_line_login_tiny_no_email == 'yes') $email = $lineUserId.'@mail.line.me';

            if(empty($lineUserId)) $this->gotomyaccount();

            if(empty($lineUserName)) $lineUserName=$lineUserId;

            if(email_exists($email)) $this->emailEnter(true);

            if(!email_exists($email)) $this->createNewUser($email,$lineUserId,$lineUserName,$tel);

            return;
        }else{

            $this->gotomyaccount();

        }

        //
    }

    private function createNewUser($email,$lineUserId,$lineUserName,$tel=false){

        $random_password = wp_generate_password(
            $length = 20,
            $include_standard_special_chars = false

        );

        $role = get_option('hb_line_login_tiny_role');

        $userdata     = array(
            'ID'         => '',
            'user_login' => esc_attr($lineUserId),
            'user_pass'  => $random_password,
            'user_email' => esc_html($email),
            'nickname'=>esc_html($lineUserName),
            'display_name'=>esc_html($lineUserName),
            'role' => esc_html($role)
        );

        $user_id = wp_insert_user($userdata);

        if(is_wp_error($user_id)) wp_die( __( 'Error' ) );


        if($tel != false && isset($tel)) update_user_meta( $user_id, 'shipping_phone', wc_clean( $tel ) );

        $this->createNewUserData($user_id,$lineUserId);




    }

    private function emailEnter($isExists){
        $emailExists = $isExists;
        require_once(dirname(dirname(__FILE__)) . '/page/HB-LineRegisterPage.php');
    }


    private function createNewUserData($user_id,$lineUserId){
        wp_set_auth_cookie($user_id, true, false);

        wp_set_current_user($user_id);

        update_user_meta( $user_id,'_heiblack_social_line_id', sanitize_text_field($lineUserId) );

        update_user_meta( $user_id,'_heiblack_social_line_data', sanitize_text_field($lineUserId) );


        unset($_COOKIE['HB_Line_Login_Tiny_Access_Token']);

        $this->gotomyaccount();

    }





    private function gotomyaccount(){

        unset($_COOKIE['HB_Line_Login_Tiny_Access_Token']);

        wp_redirect($this->url);

        exit();
    }




}


new hb_line_login_tiny_verify();























