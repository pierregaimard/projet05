# projet05
Study blog project

## Project code quality
-   Blog project:  
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/fb086120a32c48b898f0c3b0a967c5cc)](https://www.codacy.com/gh/pierregaimard/projet05/dashboard?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=pierregaimard/projet05&amp;utm_campaign=Badge_Grade)
[![Maintainability](https://api.codeclimate.com/v1/badges/b3dfb0450ca8b4ec8ba2/maintainability)](https://codeclimate.com/github/pierregaimard/projet05/maintainability)

-   Blog Framework project (Climb):  
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/74ddf10f3de442518d2a08eb637a4c2c)](https://www.codacy.com/gh/pierregaimard/climb/dashboard?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=pierregaimard/climb&amp;utm_campaign=Badge_Grade)
[![Maintainability](https://api.codeclimate.com/v1/badges/515362bf623805575542/maintainability)](https://codeclimate.com/github/pierregaimard/climb/maintainability)

## Installation
### Step one: Get project code
At first: you will need to retrieve the blog code and to put it in your server document root.

If you use git, get a terminal, go into your web server document root and
executes the following command:  
`git clone https://github.com/pierregaimard/projet05.git`

To install git see the [official documentation](https://git-scm.com/book/en/v2/Getting-Started-Installing-Git)

If you don't use git, you can download source code from this repository.
Download the code and put it into your web server document root.

Then, in a Terminal, go into to the project directory:  
`cd projet05`

### Step two: Install PHP dependencies
Next, you need to install project php dependencies.  
For that, you will need **Composer**.

If **Composer** is installed globally, uses the following command:  
`composer update`

If you have installed **Composer** locally, uses the following command:  
`php composer.phar update`

If **Composer** is not installed, you will need to install it.
See the [official documentation](https://getcomposer.org/download/)

This will install project dependencies and the Composer autoload.

### Step three: Sets .env file configuration
Then, rename `.env.example` to `.env` and changes the example information
by your server configuration.

#### List of variables to set in .env file
##### Project Base Dir

VAR | DETAIL
--- | ---
`BASE_DIR` | Put here the path of the project document root

##### Database Configuration
_At this time the ORM only supports MySql database server._
 
VAR | DETAIL
--- | ---
`APP_DB_HOST` | MySql server host
`APP_DB_PORT` | MySql server port (3306 by default)
`APP_DB_DBNAME` | MySql database name
`APP_DB_USERNAME` | MySql database username
`APP_DB_PASSWORD` | MySql database password

##### Email server configuration

VAR | DETAIL
--- | ---
`APP_EMAIL_SERVER` | smtp server
`APP_EMAIL_PORT` | smtp server port
`APP_EMAIL_PROTOCOLE` | smtp server protocole (ssl is recommended)
`APP_EMAIL_USERNAME` | email account username
`APP_EMAIL_PASSWORD` | email account password
`APP_EMAIL_ADDRESS` | email account address

### Step four: Changes your admin account information
Finally, in a web browser, goto the blog address.

The blog will automatically execute the database structure and flush a generic
admin user.

Then, you will be redirect to the admin account page, and you will be
asked to change your admin account information.

**Done!**
