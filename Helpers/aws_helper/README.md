# Helpers


## AWS Helper
This helper contains core-php functions to be used while integrating AWS libraries. It can be used only after AWS SDK is installed and Setup from AWS Console.

---
### Usage/Installation
1. Install the AWS SDK. Use [this guide](https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/getting-started_installation.html) to install.
2. Just include the file in `applications/helpers/`
3. Load the Helper using `$this->load->helper('aws');`

---
### Requirements
1. In order to completely use this helper, an IAM user must be created on IAM user management. Refer [AWS Docs](https://docs.aws.amazon.com/IAM/latest/UserGuide/id_users_create.html) to accomplish this.


---
### Configuration
These configuration settings must be made before using aws_helper is ready to use.

---
### Functions and Definitions
These are the public functions available for use:

-   `aws_credentials` internally called to set aws credential access parameter
-   `s3_list_object` Retrieve list of objects from given path on bucket
-   `s3_read_object` Retrieve object content from s3 bucket
-   `s3_upload_file` To upload files to S3 bucket
-   `s3_is_exist` To check if the file exists on S3 bucket
-   `check_s3_valid` To check a valis response from AWS bucket
-   `s3_del_file` To delete file from S3 bucket
-   `aws_sms` To use AWS SNS Functionality, to send a message to a number
-   `send_ses` To use AWS SES Functionality to send emails
