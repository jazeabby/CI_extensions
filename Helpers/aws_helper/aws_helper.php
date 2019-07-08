<?php
  require FCPATH . 'vendor/autoload.php';


    function aws_credentials(){
        $params = array(
            'credentials' => array(
                'key'   =>  '',
                'secret' => ''
            ),
            'region' => 'us-east-2', // < your aws from SNS Topic region
            'version' => 'latest'
        );
        return $params;
    }

    //		old credentials were not working
    
	//   'key' 		=> '',
	//   'secret' 	=> '',

    /**
     * Retrieve list of object from given path on s3 bucket
     * Ref : https://docs.aws.amazon.com/AmazonS3/latest/dev/ListingObjectKeysUsingPHP.html
     * @param path strong
     * @return file_list array
     * 
     *  */

    function s3_list_object($path='',$obj=null){
        $params = aws_credentials();
        $s3     = new \Aws\S3\S3Client($params); 

        $results = $s3->getPaginator('ListObjects', [
            'Bucket' => bucket_name(),
            'Prefix' => $path
        ]);
        
        if($obj!=null){
            return $results;
        }

        foreach ($results as $result) {
            foreach ($result['Contents'] as $object) {
                $file_list[]    =   $object['Key'];
            }
        }

        return array_reverse($file_list);
    }

    /**
     * Retrieve object content from s3 bucket
     * Ref : https://docs.aws.amazon.com/AmazonS3/latest/dev/RetrieveObjSingleOpPHP.html
     * @param path string
     * @return result string
     * 
     ***/
    function s3_read_object($path=''){
        $params     =   aws_credentials();
        $s3         =   new \Aws\S3\S3Client($params);

        $result = $s3->getObject([
            'Bucket' => bucket_name(),
            'Key'    => $path
        ]);

        return $result;
        


    }

    function s3_upload_file($path,$sourceFile){
        if(!file_exists($sourceFile)){
            log_message('error','ERROR unable to upload file to S3 as file '.$sourceFile.' does not exists');
            return false;
        }
        $params     =   aws_credentials();
        $s3         =   new \Aws\S3\S3Client($params); 
        $fileSize   =   filesize($sourceFile);
        $fileType   =   mime_content_type($sourceFile);
        
        try {
            $result =   $s3->putObject([
                'Bucket'		=> bucket_name(),
                'Key'    		=> $path,
                'SourceFile'	=> $sourceFile,
                'ContentLength' => $fileSize,
                'ContentType' 	=> $fileType,
            ]);
            $response =   array(
                'status'    =>  true,
                'value'     =>  $path
            );
            return $response;
        } catch (S3Exception $e) {
            // Catch an S3 specific exception.
            $response =   array(
                'status'    =>  false,
                'value'     =>  $e->getMessage()
            );
            return $response;
        }
    }

    function s3_is_exist($location){

        $server = $_SERVER['SERVER_NAME'];
        $allowed_server =   array('');
       
        if(!in_array($server,$allowed_server)){
            return true;
        }
        
        if(!is_string($location) || !is_string(bucket_name())){
            return false;
        }
        
        $params = aws_credentials();
        $s3     = new \Aws\S3\S3Client($params); 
        $result = $s3->doesObjectExist(bucket_name(), $location);
        
        if (!$result) {
            return FALSE;
        }
        return TRUE;
    }

    function check_s3_valid($url){
        $file = $url;
        
		$file_headers = @get_headers($file);
		
		if(!$file_headers || $file_headers[0] != 'HTTP/1.1 200 OK') {
				return $exists = false;
		}
		else {
				return $exists = true;
		}
    }

    function s3_del_file($location){

        $params = aws_credentials();
        $s3     = new \Aws\S3\S3Client($params); 
        try {
            $result = $s3->deleteObject([
                'Bucket' => bucket_name(),
                'Key'    => $location
            ]);

            $response =   array(
                'status'    =>  true,
                'value'     =>  'delted successfully'
            );

            return $response;
        } catch (S3Exception $e) {
            // Catch an S3 specific exception.
            $response =   array(
                'status'    =>  false,
                'value'     =>  $e->getMessage()
            );
            return $response;
        }

    }

    function aws_sms($msg,$mobile){
        $params = aws_credentials();
        $sns    = new \Aws\Sns\SnsClient($params); 
        
        $args = array(
            'MessageAttributes' => [
                'AWS.SNS.SMS.SenderID' => [
                    'DataType' => 'String',
                    'StringValue' => 'WEBSITE'
                ]
            ],
            "SMSType" => "Transational",
            "DefaultSenderID" => "WEBSITE",
            "SenderID" => "WEBSITE",
            "PhoneNumber" => $mobile,
            "Message" => $msg
        );
        $result = $sns->publish($args);
        $msgId  =   $result['MessageId'];

        if(!$msgId){
            return FALSE;
        }
        return $msgId;
        
    }

    


    function send_ses($data,$attachment=NULL){
    // Instantiate a new PHPMailer 

    $mail = new PHPMailer\PHPMailer\PHPMailer;

    // Tell PHPMailer to use SMTP
    $mail->isSMTP();

    // Replace sender@example.com with your "From" address. 
    // This address must be verified with Amazon SES.

    $mail->setFrom(strtolower($data['from']), $data['name']);

    // Replace recipient@example.com with a "To" address. If your account 
    // is still in the sandbox, this address must be verified.
    // Also note that you can include several addAddress() lines to send
    // email to multiple recipients.

    $mail->addAddress($data['to'], 'You');

    // Replace smtp_username with your Amazon SES SMTP user name.
    $mail->Username = '';

    // Replace smtp_password with your Amazon SES SMTP password.
    $mail->Password = '';
        
    // Specify a configuration set. If you do not want to use a configuration
    // set, comment or remove the next line.

    //$mail->addCustomHeader('X-SES-CONFIGURATION-SET', 'ConfigSet');
    
    // If you're using Amazon SES in a region other than US West (Oregon), 
    // replace email-smtp.us-east-2.amazonaws.com with the Amazon SES SMTP  
    // endpoint in the appropriate region.
    $mail->Host = '';

    // The subject line of the email
    $mail->Subject = $data['subject'];

    // The HTML-formatted body of the email
    $mail->Body = $data['message'];

    //Add attachments one by one
    if($attachment!=NULL){
			if(!is_array($attachment)){
                if(file_exists($attachment)):
                    $mail->addStringAttachment(file_get_contents($attachment), basename($attachment));
                endif;
			}else{
				foreach($attachment as $key => $attach){
                    if(file_exists($attach)):
                        $mail->addStringAttachment(file_get_contents($attach), basename($attach));
                    endif;
				}
			}
		}
    
    // Tells PHPMailer to use SMTP authentication
    
    $mail->SMTPOptions = array(
        'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
        )
        );
    
    $mail->SMTPDebug = 0; 

    $mail->SMTPAuth = true;

    // Enable TLS encryption over port 587

    $mail->SMTPSecure = 'tsl';
    $mail->Port = 587;
    
    //$mail->SMTPSecure = 'ssl';
    //$mail->Port = 465;    
    //465; 
    // Tells PHPMailer to send HTML-formatted email
    $mail->isHTML(true);

    // The alternative email body; this is only displayed when a recipient
    // opens the email in a non-HTML email client. The \r\n represents a 
    // line break.

    // $mail->AltBody = "Email Test\r\nThis email was sent through the 
    //     Amazon SES SMTP interface using the PHPMailer class.";
    // var_dump($mail->send());


    if(!$mail->send()) {
        return $mail->ErrorInfo;
    } else {
        return "sent";
    }
  }