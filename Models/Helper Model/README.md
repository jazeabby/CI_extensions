# Models


## Helper Model
This model can be included and used for multiple purposes in relation with database with certain built-in functions and a small configuration
---
### Usage/Installation
1. Just include the file in `applications/models/`
2. Load the Model using `$this->load->model('Helper_model');`

---
### Requirements
1. In order to completely use this model, configure a `config` table in database for settings
2. If you want to use aws ses_mail services, include the `aws_helper.php` which is in helpers of this repo.

---
### Configuration
Every setting in the configuration can be overided.
These are the protected variables:
-   `$config_table` - the name of config table in database; default: *config*
-   `$resize_image` - If you want to resize image while uploading, set it as true; default: *true*
-   `$resize_h` - resize height after uploading image, $resize_image must be true; default: *1000*
-   `$resize_w` - resize width after uploading image, $resize_image must be true; default: *1000*
-   `$_settings` - array containing the settings of website, from config table; default: *array of settings*
-   `$_mail_switch` - bool value switch to send emails through aws or default email; default: *true: will send emails through aws*

---
### Functions and Definitions
These are the public functions available for use:

-   `cli_only` - Checks if a request is made from cli or web, returns not_found() if called from web
    -   usage: `$this->helper_model->cli_only();`

-   `settings` - Fetching data from config table of database
    -   usage: `$this->helper_model->settings('sitename');`


-   `config_list` - returns the configuration table from the database as an associative array
    -   usage: `$settings = $this->helper_model->config_list();`


-   `upload_file_s3` - uploads a file to amazon s3 bucket, requires `aws_helper.php`
    -   usage: 
	```
	$path = 'relative\path\for\upload\after\bucket';
	$name = 'file'; 		//	the input name from html
	$key = null;			//	as the file is not an array
	$image = false;			//	do not resize the image
	
	$upload = $this->helper_model->upload_file_s3($path, $name, $key, $image);
	if($upload){
		echo "file uploaded successfully";
	}else{
		echo "file could not be uploaded";
	}

	```


-   `upload_file` - uploads a file to your server
    -   usage: 
	```
	$path = 'relative\path\for\upload\after\base_url';
	$name = 'file'; 		//	the input name from html
	$key = null;			//	as the file is not an array
	
	$upload = $this->helper_model->upload_file($path, $name, $key, $image);
	if($upload){
		echo "file uploaded successfully";
	}else{
		echo "file could not be uploaded";
	}

	```


-   `alert` - to display alert if available in the flashdata, place this function where you want to show alert messages, in html;  *Is used while redirecting with a message*
    -   usage: `$this->helper_model->alert();`


-   `display_alert` - to display alert in html with the parameters passed, uses bootstrap for styling;
    -   usage: 
	```
	$message	=	"Unauthorised Access";
	$type		=	"danger";
	$icon		=	"exclamation-triangle";
	
	$this->helper_model->display_alert( $message, $type, $icon);
	```

-   `redirect_msg` - redirect to a 'url' with a alert with the parameters passed
    -   usage: 
	```
	$message	=	"Unauthorised Access";
	$type		=	"danger";
	$url		=	"login";
	
	$this->helper_model->redirect_msg( $message, $type, $url);
	
	// Will redirect to www.example.com/login, with an alert in the alert
	```


-   `send_formatted_mail` - public function to send mail, called from send_fromatted mail
    -   usage: 
	```
	$data['name']		=	"Abhishek Jain";
	$data['subject']	=	"Thank you for using helper_model";
	$data['to']			=	"jazeabby@gmail.com";
	$data['from']		=	"jazeabby@gmail.com";
	$data['message']	=	"Hi, Nice to meet you";
	
	$this->helper_model->send_formatted_mail( $data);
	
	```


-   `image_resize` - public function to send mail, called from send_fromatted mail
    -   usage: 
	```
	$upload_path		=	'path/to/the/image.jpg';
	$width				=	640;
	
	$this->helper_model->image_resize( $upload_path, $width);
	
	```



