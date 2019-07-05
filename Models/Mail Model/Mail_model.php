<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 
 * Naming convention for funtions
 * function a_b_c
 * a = action = (new/old/delete/accept/decline/update)
 * b = entity = (offer/order/password/setting/post)
 * c = reciever = (user/admin/owner)
 */

class Mail_model extends CI_Model {

	

    
    /**
     * The view path of all mails in mail_model
     *
     * @const string
     */
	const   MAIL_VIEW = "email";
    const   DEBUG_MAIL_VIEW = "email"; // can be in a different directory while testing/development environment is turned on
    
	
	
    /**
     * Active Status
	 * ## Value 1
     *
     * @var integer
     */
    private $_active_status 	=	1;
    
    /**
     * From which email the mail is going
     *
     * @var string
     */
    private $_mail_from         = "";
    
    /**
     * to whom the mail is going
     *
     * @var string
     */
    private $_mail_to          	= "";

    /**
     * site_name which shows from where the mail came
     *
     * @var string
     */
    private $_site_name     	= "";

    /**
     * Subject of the mail
     *
     * @var string
     */
    private $_subject          	= "";

    /**
     * The data to be sent in Mail template
     *
     * @var array
     */
	private $_data          	= array();
	
    /**
     * Variable to be set if documents are available
     *
     * @var mixed
     */
	private $_documents          	= NULL;
	

    /**
     * Mail View template which contains all the message
     *
     * @var string
     */
    private $_view          	= "";


    /**
     * Set this value TRUE to enable debugging
     *
     * @var boolean
     */
    
	private $_debug          	= FALSE;

	/**
     * Set this value TRUE to enable debugging
     *
     * @var boolean
     */
    private $_hard_debug       	= FALSE;

    /**
     * This value is being set in Testing Controller for manual testing of emails in admin panel
     *
     * @var boolean
     */
    
	public $_testing          	= FALSE;

    /**
     * Set this value according to your email id to test debugging in mail
     *
     * @var string
     */
    
     
	private $_debug_mail_id          = "jazeabby@gmail.com";

	
	/**
	 * DB Table settings being used for values being taken from `users` table
	 */
	private $_user_table		    =	'users';
	private $_user_id_column		=	'user_id';
	private $_user_email_column	    =	'user_email';


	/**
	 * DB Table settings being used for values being taken from `admin` table while sending mail to admin
	 */
	private $_admin_email		    =	'';
	private $_admin_table		    =	'admin';
	private $_admin_id_column	    =	'id';
	private $_admin_email_column	=	'email';


    /**
     * Constructor for the Mail Model
     *
     * @access public
     * @param void
     * @return void
     */
    function __construct(){
        parent::__construct();

        //initialise the private_variables
        $this->_mail_to     =	"admin@admin.com";
        $this->_admin_email =	"admin@admin.com";
        
        //by default value
		$this->_site_name   =	"Website";
        $this->_subject     =	"Important: Mail from ".$this->_site_name;

        //by default template for view
		$this->_view        =	"email/default_mail_template";
		
		if(ENV == 'production'){
			$this->_hard_debug	=	FALSE;
			$this->_debug		=	FALSE;
		}

    }



    /**
     * Send Function takes the private variables already set and send the email
     *
     * @access private
     * @param void
     * @return true
     */
    private function _send(){

		// dd($this->_view);

        $mdata['name']		=	$this->_site_name;
		$mdata['from']		=	$this->_mail_from;
		$mdata['to']		=	$this->_mail_to;
		
		
        $message	        =   $this->load->view( $this->_view, $this->_data, TRUE);
        $mdata['subject']   =   $this->_subject;


		//strictly for debugging purposes
		if(ENV != 'production'){
			if($this->_debug!=false){
				
                $mdata['to']		    =	$this->_debug_mail_id;
				$mdata['subject']		=	"Test Mail: ".$this->_subject;
			}

			if($this->_hard_debug){
				dd($this->_data,TRUE);
				echo $message;
				dd($mdata['subject'] );
			}
		}
		
		if($this->_testing){
			echo '<div class="col-md-12">';
			echo "<br>Subject: ".$this->_subject;
			echo "<hr>Message:<br>";
			echo $message;
			echo '</div>';
			exit();
        }
        
		$this->load->library('email');
		$this->email->clear();
        
        
		$uri_string = $this->uri->segment(2);

		$config['useragent']        =   $this->_site_name;
		$config['mailpath']         =   '/usr/sbin/sendmail';
		$config['protocol']         =   'smtp';
		$config['smtp_host']        =   'localhost';
		$config['smtp_port']        =   '25';
		
		$config['mailtype']			=	'html';
		$config['charset']			=	'utf-8';
		$config['dsn']				=	TRUE;
		$config['wordwrap']			=	TRUE;
		$config['newline']			=	"\r\n";
		$config['crlf'] 			= 	"\r\n";
			
		
		ini_set('sendmail_from', $this->_mail_from);

        $this->email->initialize($config);
        
		$this->email->to($this->_mail_to);
        $this->email->from($this->_mail_from, $this->_site_name);
        
		$this->email->subject($this->_subject);
		$this->email->message($message);
        
        $this->email->reply_to($this->_mail_from, $this->_site_name);
		

		if($this->_documents!=NULL){
			if(!is_array($this->_documents)){
				$this->email->attach(FCPATH.$this->_documents);
			}else{
				foreach($this->_documents as $key => $attach){
					$this->email->attach(FCPATH.$attach);
				}
			}
		}

		if($mail = $this->email->send()){
			if($mail){
				log_message('error','Message sent to :'.$data['to'].' | Subject : "'.$data['subject'].'"');
				return TRUE;
			}else{
				log_message('error','message not sent to :'.$data['to'].' Email Debugger info # '.implode(' | ',$this->email->print_debugger()));
				return FALSE;
				exit();
			}
			
        }
        

        return true;

    }
    

    /**
     * function to call view inside each function, the name of view will be same as the function name itself.
     *
     * @access private
     * @param string optional
     * @return void
     */
    private function _view($custom_view_template = NULL){
		// dd($custom_view_template);
		$view_path = self::MAIL_VIEW;
		if(ENV == 'development'){
			$view_path = $this->_testing ? self::DEBUG_MAIL_VIEW : self::MAIL_VIEW;
		}

        if($custom_view_template === NULL)
            $this->_view    =   $view_path.DIRECTORY_SEPARATOR.debug_backtrace()[1]['function'];
        else
            $this->_view    =   $view_path.DIRECTORY_SEPARATOR.$custom_view_template;
    }


    /**
     * function to set email id from user_id
     *
     * @access private
     * @param integer required user id of the email to set to
     * @return void
     */
    private function _to_user($user_id){
		if(!$user_id){
			return false;
		}

		$this->_mail_to 	= 	$this->lib->get_row( $this->_user_table, $this->_user_id_column, $user_id, $this->_user_email_column);
	}
	
	
    /**
     * function to set email id to admin, for any information related mail, not invloving contacting the user
     *
     * @access private
     * @param integer optional user_id of admin, from admin table
     * @return void
     */
    private function _to_admin($user_id = FALSE){
		if(!$user_id){
			return $this->_mail_to 	= 	$this->_admin_email;
			// return false;
		}
		
		$email			=	$this->lib->get_row( $this->_admin_table, $this->_admin_id_column, $user_id, $this->_admin_email_column);
		
		if(!$email){
			$this->_mail_to 	= 	$this->_admin_email;
		}else{
			$this->_mail_to 	= 	$email;
		}
	}


    /**
     * function to set email id to developer team, only for technical responses
     *
     * @access private
     * @return void
     */
	private function _to_developer()
	{
		$this->_mail_to 	= 	$this->_settings['developer_email'];
		
	}
	
	
    /**
	 * function to set email id to verification team, also admin, but this team will contact the user directly in any case
     *
	 * @access private
	 * @return void
     */
	private function _to_verification(){
		$this->_mail_to 	= 	$this->_settings['verification_mail'];
	}



    /**
	 * function to send email to all admins who are enabled in the notifications panel
     *
	 * @access private
	 * @return bool true after all mails are sent
     */
	private function _to_enabled_admins()
	{
		$notif_name = debug_backtrace()[1]['function'];

		$notif = $this->lib->get_row_array('admin_notifications', array('an_name'=>$notif_name));
		if(!$notif){
			$this->logs->add('The notification is not added in table: '.$notif_name);
			$this->_to_admin();
			return $this->_send();
		}

		$this->load->model('Admin_notification_model','admin_notif');
		$admin_emails = $this->admin_notif->get_admins($notif->an_id, 'email');
		if(count($admin_emails) < 1){
			$this->logs->add('No admins assigned to "'.$notif_name.'", sending email of '.$notif_name.' to main admin');
			$this->_to_admin();
			return $this->_send();
		}
		$count = 0;
		foreach ($admin_emails as $email ) {
			$count++;
			$this->_mail_to = $email;
			// dd($email, true);
			$this->_send();
		}

		$this->logs->add($count.' Mails sent to admins for "'.$notif_name.'"');
		return true;
	}
	

	/**
	 * Function to show any error or missing value encountered while testing
	 * @param string message to be shown
	 */
	private function show($msg)
	{
		if($this->_testing){
			dd($msg);
		}
	}

    
}