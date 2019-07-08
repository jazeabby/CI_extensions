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

-   `aws_credentials`
