# Models


## E-Mail Model
This model can be included and used to send emails directly with certain built-in functions and configuration
---
### Usage/Installation
1. Just include the file in `applications/models/`
2. Load the Model using `$this->load->model('Email_model');`

---
### Configuration
Basic Settings required for sending email:
- Name
- From Email
- To Email
- Message
- Subject

Every setting in the configuration can be overided.
These are the public variables:
-   `$_mail_from` - From which email the mail is going; default *jazeabby@gmail.com*
-   `$_mail_to` - To whom the mail is going; default *jazeabby@gmail.com*
-   `$_site_name` - Shows from where the mail came (appended to most subjects); default *Website*
-   `$_subject` - Subject of the mail; default *Important: Mail from $this->_site_name*
-   `$_data` - The data to be sent in Mail View template; default *array()*
-   `$_documents` - Variable to be set if documents are available; default *NULL*
-   `$_view` - Mail View template which contains all the message; default *views/email/default_mail_template*
-   `$_debug` - Set this value TRUE to enable debugging, email will be sent to `$_debug_mail_id` below; default *false*
-   `$_hard_debug` - Set this value TRUE to enable debugging, block the emails too; default *false*
-   `$_testing` - This value is being set in Testing Controller for manual testing of emails in admin panel. Refer to `Email Management` in Developer Section; default *false*
-   `$_debug_mail_id` - Set this value according to your email id to test debugging in mail; default *jazeabby@gmail.com*
-   `$_user_table` - table from where users email is to be taken; default *users*
-	`$_user_id_column` - the column of above table which is the primary key; default *user_id*
-	`$_user_email_column`	=	the column of `email` which is to be taken; default *user_email*
-   `$_admin_email` - default email of admin; default *admin@admin.com*
-   `$_admin_table` - table from where users email is to be taken; default *admin*
-	`$_admin_id_column` - the column of above table which is the primary key; default *id*
-	`$_admin_email_column`	=	the column of `email` which is to be taken; default *email*
---
### Functions and Definitions
These are the private/protected functions available for use:
-   `_send` - Send Function takes the private variables already set and **sends** the email. The Email Server Configuration settings are stored in this function
    -   param *void*
    -   returns bool, *true* if the mail was sent
-   `_view` - Function to call view inside each function, the name of **view** template will be same as the function name itself.
    -   param `$custom_view_template` for over-riding the `__FUNCTION__` name as template. 
    -   Sets value of `$_view` after concatenating default view directory with template name
    -   returns *void*
-   `_to_user` - Custom Function to set `$_mail_to` from `$user_id`. Uses `$_user_table`, `$_user_id_column` and `$_user_email_column`
    -   param `$user_id`
    -   returns *void*
-   `_to_admin` - Custom Function to set `$_mail_to` from `$admin_id`. Uses `$_admin_table`, `$_admin_id_column` and `$_admin_email_column`
    -   param `$admin_id`
    -   returns *void*
-   `_to_developer` - Custom Function to set `$_mail_to` to developer while being in testing or development phase.
    -   param *void*
    -   returns *void*
-   `_to_enabled_admins` - Custom Function to send email to all admins who are enabled in the notifications panel. Pre-requisite 'Admin_notification System'
    -   param *void*
    -   returns *void*
-   `show` - Function to show any error or missing value encountered while testing
    -   param *string*
    -   returns *void*

---
### Usage in Functions
This is a custom function which can be made using above methods and variables

```
	public function contact_us($data){
		
		if($this->_testing){ 
            // testing mode is switched on, getting dummy entries
			$data = array(
				"name"	=> 'Test Name',
				"email"	=>	'email@example.com',
				"phone"	=>	"+919999999990",
				"message"=> "Lorem, ipsum dolor sit amet consectetur adipisicing elit. Reiciendis, ad.",
				"contact_msg_id"=> "1"
			);
		}

		if(!$data){
			$this->show("Data is wrong.");      // show if the data is wrong
			return FALSE;	    //  return false
		}

		$this->_view();         //  set the default view as `views/email/contact_us`


		$this->_to_admin();     //  this email will be sent to default admin_email

		//send email
		$this->_data 		=	array('user_info'=>$data);      //  set the data to be used in view
		$this->_subject 	=	"";                 //  set the subject of mail
		$email_send			=	$this->_send();     //  send the email
		return TRUE;
	}

```

To over-ride view:
```
$this->_view('custom_template');
//  set the default view as `views/email/custom_template`

$this->_view('user/custom_template');
//  set the default view as `views/email/user/custom_template`
```

To over-ride some email variables:
```
$this->_mail_to = "jazeabby@gmail.com";
$this->_mail_from = "jazeabby@gmail.com";
```


