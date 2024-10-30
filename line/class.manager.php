<?php
class hb_line_login_tiny_manager
{
    private $CLIENT_ID,$CLIENT_SECRET,$CLIENT_STATE;
    public function __construct()
    {
        $state=sha1(time());
        $this->CLIENT_STATE     = $state;
        $this->CLIENT_ID        = get_option('_hb_line_login_tiny_channel_id');
        $this->CLIENT_SECRET    = get_option('_hb_line_login_tiny_channel_secret');
    }
    public function lineLogin(){
        $parameter = [
            'response_type' => 'code',
            'client_id'     => $this->CLIENT_ID,
            'state'         => $this->CLIENT_STATE,
        ];
        $url = 'https://access.line.me/oauth2/v2.1/authorize?'.http_build_query($parameter);
        return $url;
    }
}

new hb_line_login_tiny_manager();