# GCM-Laravel-Simple-Push-Message
Simple classes to send GCM to Android devices in Laravel 5, using all the devices registered in the database. 

Based on https://github.com/mattg888/GCM-PHP-Server-Push-Message

Configuration
-------------------

* Copy all the files to your Laravel 5.* folder.
* Insert in your routes.php file, the call to the controller that is going to register the devices in your database.
  + `Route::post('gcm', 'GCMController@registerGcm');`
* Run the migrations to create the table to store the devices_id
  + `php artisan migrate`

Sending to all the devices
--------------------------

To send the a message to all the registered devices:

```php
$apiKey = "YOUR GOOGLE API SERVER KEY";
$message = "The message to send";

$gcpm = new GCMPushMessage($apiKey);
$response = $gcpm->sendFromDB($message);
```

Sending to a specific set of users_id
--------------------------------------
```php
$apiKey = "YOUR GOOGLE API SERVER KEY";
$message = "The message to send";

$gcpm = new GCMPushMessage($apiKey);
$response = $gcpm->sendFromDB($message);
```

Using a queue to send the message
---------------------------------

Dispatch the message to the queue from any of your Controllers
```php
$apiKey = "YOUR GOOGLE API SERVER KEY";
$message = "The message to send";

$this->dispatch(new Push($message, $apiKey));
```

# More Information

How to obtain a Google Server API Key
-----------------------
-	Go to the Google Console https://console.developers.google.com
-	Create a new project / open project
-	Click on 'APIs & Auth' on the left
-	Find the 'Google Cloud Messaging for Android' option, and press off to turn it on
-	Go to the creditials tab on the left
-	Go the 'Public API access' section and click 'Create new key'
-	Choose 'Server key' and click 'Create'
-	The API key is now shown under the section 'Key for server applications'

