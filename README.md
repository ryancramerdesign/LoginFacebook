# Login for Facebook

- Module for ProcessWire 3.x (requires PW 3.0.42 or newer).
- Enables Facebook login/authentication for PW and use of Facebook data.
- Developed by Ryan Cramer, made for and sponsored by Michael Barón.


## How it works

This module creates a new page /login-facebook/, feel free to move or rename it as needed.
When a user accesses this page, it asks them to login to Facebook. If they are already logged
in to Facebook, it will ask them to approve it. Upon approval, they are redirected back to your
site and now logged in to ProcessWire with a user having the role “login-facebook”. If the user
has not previously done this before, a new account will be created for them. The created account 
uses the name of their Facebook account. 


## Installation

1. Unzip and copy the module’s files to /site/modules/LoginFacebook/.

2. Copy the included login-facebook.php to your /site/templates/ folder (if not already present).

3. Create a new app from Facebook Developers site: <https://developers.facebook.com/apps/>.
   You will need to obtain a Facebook App ID and App Secret. Facebook will also ask for some
   other information, such as the following:
   
   - Display Name: Enter whatever you'd like, i.e. “Login for my site”.
     
   - Namespace: I left this one blank. 
     
   - App domains: Enter your web site’s domain name(s). Note that if “localhost” is needed, 
     Facebook won't take it until you've completed the “Website” first (see next item).
     
   - Click the “Add Platform” at the bottom, and add “Website”, along with the full http URL
     to it. If testing on localhost, enter “http://localhost". In my case using MAMP on 
     localhost, it was “http://localhost:8888”. 
       
   - For the rest of the fields, you can leave them blank or populate them as you see fit.  

4. Configure the module in the ProcessWire admin (Modules > Site > Login Facebook). Add the 
   Facebook App ID and App Secret you obtained in the last step, to the indicated fields. 
   
5. While still in the module configuration, configure the “Page where user is redirected to 
   after successful login”. Leave the rest of the configuration as-is with the defaults for now.
   You can come back later to configure and adjust things as needed, but it's a good idea to 
   get the module functional with the defaults first. 
   
5. Now lets test things out. Log out of ProcessWire (if you are logged in). Then access the 
   /login-facebook/ URL on your website where the module is installed. It should redirect to
   Facebook and ask you to login, or ask you to approve the request if you are already logged in.
   Upon login/approval, Facebook will redirect to your designated page, a new user account will
   be created in ProcessWire (if not already created), and the user will be logged in. 
   

## Usage

When a user is logged in from Facebook, you can access properties from their Facebook information
via the ProcessWire API. The fields that you want to work with are configured from the module
configuration screen. As an example, lets say that you wanted to output a welcome message that
contains the user's first name and picture, as stored in the Facebook data:

    $facebook = $modules->get('LoginFacebook');  
    echo "<h2>Welcome $facebook->first_name</h2>";
    if($facebook->picture_url) {
      echo "<p><img src='$facebook->picture_url'></p>";
    }
   
If you want to retrieve all user data, use the getAll() method, which returns an associative
array of all Facebook data that you've configured for the module to retrieve. 

    $facebook = $modules->get('LoginFacebook');  
    $data = $facebook->getAll(); 

In either case, any strings in the returned data is automatically entity encoded when the 
current page’s output formatting state is enabled. 

When making API calls like above, if the user is not logged-in via Facebook, then it will 
automatically redirect to Facebook to log them in, and return to your page (where the API call
exists) once logged in. If you would instead rather check if they are logged in via Facebook
before outputting data, use the isLoggedIn() method: 

    $facebook = $modules->get('LoginFacebook');  
    if($facebook->isLoggedIn()) {
      // user is logged in and Facebook data available
      echo "<h2>Welcome $facebook->first_name</h2>";
    } else {
      // user not logged in via Facebook, ask them to login
      echo "<a href='$facebook->url'>Login with Facebook</a>";
    }

This module also enables you to mirror data from Facebook to ProcessWire fields. This can add
convenience, especially in cases where the user might be able to login through either ProcessWire
or through Facebook. This ensures the Facebook data is stored in their ProcessWire user profile, 
even when not logged in via Facebook. This is useful for fields like email and names. 

------------

Copyright 2017 by Ryan Cramer
