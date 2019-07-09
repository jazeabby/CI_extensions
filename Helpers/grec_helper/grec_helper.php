<?php
/**
 * @author Abhishek Jain
 * @email abhishek@cteinternational.net
 * @create date 2019-07-02 12:08:44
 * @modify date 2019-07-02 12:08:44
 * @desc Helper to Implement Google Recaptcha V3
 */

 
/**
 * Loads the google Recaptcha v3 Library and the default function to return token from 
 * @param string form_id on which the captcha is to be included
 */
function grec_init($form_id = NULL)
{
    if($form_id != NULL){
        grec_create($form_id);
    }
    // initialise the google recaptcha code
    // Google site key 
    $key = '{your-google-captcha-client-key}';
    if(ENV == 'production'){
        $key = '';
    }else{
        $key = '';
    }


    
    echo '<script src="https://www.google.com/recaptcha/api.js?render='.$key.'"></script>';
    
    ?>
    <script type="text/javascript">
        grecaptcha.ready(function() {
            grecaptcha.execute('<?php echo $key;?>', {action: '<?php echo $form_id ?? "default";?>'}).then(function(token) {
                $("#GrecaptchaResponse").val(token);
                return true;
            });
        });
    </script>
    <form action="" method="post"></form>
    <?php
    
}


/**
 * To echo input field in html form.
 * @param string form_id optional, if provided: will add attribute 'form' for the input
 * @return void
 */
function grec_create($form_id = "")
{
    echo '<input type="hidden" name="g-recaptcha-response" id="GrecaptchaResponse" value="" form="'.$form_id.'">';
    // include the input before and append the following in the body
    // grec_init();
}

/**
 * The function checks the post value and returns true/false as per the post input
 * 
 */
function grec_verify()
{
    if(!isset($_POST['g-recaptcha-response'])){
        return false;
    }
    $token = $_POST['g-recaptcha-response'];
    
    // google server site secret
    $secretKey = '{your-google-captcha-server-secret}';
    if(ENV == 'production'){
        $secretKey = '';
    }else{
        $secretKey = '';
    }

    $data = array('secret' => $secretKey, 'response' => $token);
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://www.google.com/recaptcha/api/siteverify",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => http_build_query($data),
        CURLOPT_HTTPHEADER => array(
        "cache-control: no-cache"),
    ));
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
        log_message('error',"cURL Error #:" . $err);
    }

    $response = json_decode($response);
    
    if(isset($response->success) ) {
        return true;
    }
    return false;
}
