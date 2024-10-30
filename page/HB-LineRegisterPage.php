<?php echo '<meta name="robots" content="noindex">'?>
<?php echo '<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">'?>
<?php wp_head(); ?>
<div class="wrap">
    <div class="container">
        <form  method="post" id="lineLogin">
            <div id="login">
                <img src="<?php echo esc_url(get_site_icon_url())?>" alt="">
            </div>
            <br>
            <p class="email-already-exists">
                <?php
                if(@$emailExists == true){
                    esc_html_e(__( 'Email Already Exists', 'hb-line-login-tiny-for-woocommerce' ));
                }
                ?>
            </p>
            <?php if(get_option('_hb_line_login_tiny_no_email')=='no'):?>
                <p><?php esc_html_e(__( 'Email', 'hb-line-login-tiny-for-woocommerce' ));?></p>
                <input type="email" id="user_login" name="email" class="input" value="" size="20" autocapitalize="off" >
            <?php endif;?>


            <?php if(get_option('_hb_line_login_tiny_no_phone')=='no'):?>
                <p><?php esc_html_e(__( 'Phone number ', 'hb-line-login-tiny-for-woocommerce' ));?></p>
                <input type="tel" id="user_login_phone" name="tel" class="input" value="" size="20" autocapitalize="off" >
                <?php
                $_hb_line_login_tiny_no_phone_num = get_option('_hb_line_login_tiny_no_phone_num');
                if(isset($_hb_line_login_tiny_no_phone_num) && $_hb_line_login_tiny_no_phone_num != '0' ){
                    echo '<div class="user_login_phone_num"><span id="user_login_phone_num">0</span>/'.esc_textarea($_hb_line_login_tiny_no_phone_num).'</div>';

                }



                ?>
            <?php endif;?>
            <input type="submit" id="user_login_submit" class="HB-btn HB-btn-primary" value="<?php esc_html_e(__( 'Register', 'hb-line-login-tiny-for-woocommerce' )); ?>">
        </form>
    </div>
</div>
<?php
$emailisempty   = __( 'Can not be empty', 'hb-line-login-tiny-for-woocommerce' );
$isnotemail     = __( 'This is not a legitimate email', 'hb-line-login-tiny-for-woocommerce' );
$phoneisempty   = __( 'Can not be empty', 'hb-line-login-tiny-for-woocommerce' );
$phoneisnotNumber     = __( 'This is not a legitimate phone', 'hb-line-login-tiny-for-woocommerce' );


if(empty($_hb_line_login_tiny_no_phone_num)) $_hb_line_login_tiny_no_phone_num=0;




echo "<script>const EmailIsEmpty = \" ".esc_html($emailisempty)."\"; const IsNotEmail = \" ".esc_html($isnotemail)."\";</script>";
echo "<script>const PhoneIsEmpty = \" ".esc_html($phoneisempty)."\"; const PhoneIsNotNumber = \" ".esc_html($phoneisnotNumber)."\";</script>";
?>
<?php wp_footer();?>

<script>
    window.addEventListener('load', (event) =>{


        var user_login_phone = document.getElementById('user_login_phone');

        var lineLogin = document.getElementById('lineLogin');

        lineLogin.addEventListener("submit", function(event) {

            var user_login = document.getElementById('user_login');

            if(user_login){

                if(!user_login.value){
                    event.preventDefault();
                    user_login.style.borderColor = 'red';
                    alert(EmailIsEmpty);
                    return;
                }

                if(!validateEmail(user_login.value)){
                    event.preventDefault();
                    user_login.style.borderColor = 'red';
                    alert(IsNotEmail);
                    return;
                }

            }


            if(user_login_phone){

                if(!user_login_phone.value){
                    event.preventDefault();
                    user_login_phone.style.borderColor = 'red';
                    alert(PhoneIsEmpty);
                    return;
                }

                if(!telephoneCheck(user_login_phone.value)){
                    event.preventDefault();
                    user_login_phone.style.borderColor = 'red';
                    alert(PhoneIsNotNumber);
                    return;
                }

                var phone_num_length = <?php echo esc_textarea($_hb_line_login_tiny_no_phone_num); ?>

                if(!(phone_num_length == 0 || user_login_phone.value.length == phone_num_length)){
                    event.preventDefault();
                    user_login_phone.style.borderColor = 'red';
                    return;
                }



            }






        });


        var user_login_phone_num = document.getElementById('user_login_phone_num');


        if(user_login_phone_num){

            user_login_phone.addEventListener('keyup', (event) => {

                user_login_phone_num.innerText = event.target.value.length;

                if (event.target.value.length > 10) {
                    user_login_phone.value = user_login_phone.value.substring(0, 10);
                    user_login_phone_num.innerText = event.target.value.length;
                }

            });

        }


        function telephoneCheck(str) {
            var phone = /[0-9]+$/.test(str);
            return phone;
        }


        const validateEmail = (email) => {
            return String(email)
                .toLowerCase()
                .match(
                    /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
                );
        };

    });


</script>