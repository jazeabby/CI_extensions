# CI_extensions
This repository contains the helpers/models/directories which can be implemented independently or with depencies

## Helpers
 - aws_helper.php
 - grec_helper.php
 - default_helper.php
 - image_helper.php
 - notifier_helper.php
 - validation_helper.php

## Modules

These modules will contain an independent MVC to be integrated directly
-   Developer Module for CI
    -   Email Management (What emails are going through a single module)
    -   Language Management for APIs (See all the languages implemented in Project)
    -   Log Management in help with in-built CI Log library
    -   Event Management (Independent Log System rather than in-built CI Library)
    -   Job Management System (Implementation of Queues in CI for certain jobs)
    -   Admin Notification System (Notify/ Send emails to only selected managed admins/moderators)
-   Blog Management System
-   Limit Management for Front-end Users in API/Web (control the limitations dynamically)
-   User Modules
    -   Contact Us Module
    -   Mail Module
    -   Report Module


## Core
 - Web_Controller.php
 - Api_Controller.php

## Models
-   E-Mail Model
-   Helper Model

