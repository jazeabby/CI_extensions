# Helpers


## Grec Helper
GREC (Google ReCaptcha) Helper is used to implement and verify Google Recaptcha V3 on the site. Can be used as a core-php file library too. You can find out more about it on [Google Docs](https://developers.google.com/recaptcha/docs/v3).

reCAPTCHA protects you against spam and other types of automated abuse. Here, we explain how to add reCAPTCHA to your site or application.

---
### Usage/Installation
1. Just include the file in `applications/helpers/`
2. Load the Helper using `$this->load->helper('grec');`

---
### Requirements
1. To start using reCAPTCHA, you need to [sign up for an API key pair](http://www.google.com/recaptcha/admin) for your site. 


---
### Configuration
These configuration settings must be made before using grec_helper is ready to use:

1.  Client Side Key: Use this site key in the HTML code your site serves to users. Place **SITE KEY** in place of `{your-google-captcha-client-key}`.
2. Server Side Key: Use this secret key for communication between your site and reCAPTCHA. Place **SECRET KEY** in place of `{your-google-captcha-server-secret}`.


---
### Functions and Definitions
These are the public functions available for use:

-   `grec_init` internally called to set aws credential access parameter. 
-   `grec_create` Retrieve list of objects from given path on bucket
-   `grec_verify` The function checks the post value and returns true/false as per the post input

---
### Usage
If you're using CodeIgniter, remove the `require` line from the code below.

This will be the php file where you want to include the google recaptcha code on Client-side/ View:

```

<?php
    require('grec_helper.php');
?>

<!-- HTML -->
<form action="" method="post" id="my_form">
    ...
</form>

<?php
    //  this will initialise the form input field and also include the script of google reCaptcha
    grec_init("my_form");
?>

```
On Server-side, you can simply use the verification function to verify the reCaptcha code/ Controller:
```
<?php 
    require('grec_helper.php');

    if(!grec_verify()){
        //  Google reCaptcha code was not verified
    }

    //  continue as it was verified by google
?>
```

---
### Source

-   [Google](https://developers.google.com/recaptcha/intro)