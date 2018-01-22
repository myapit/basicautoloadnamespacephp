# basicautoloadnamespacephp
Basic Folder and File Structure For PHP Application Using Autoloading and Namespace With Composer

Requirements
------------
-PHP 5.6 and above
-PDO extension
-composer

This strcutre will be using Autoloading and Namespace in PHP.
Composer to generate autoloading based on PSR-4

-any changes to /app/ folder, execute $composer dump-autoload -o

To install any packages, go to https://packagist.org/ and search for the package's name.
Edit your composer.json file, add the package's detail like below:

{
    "require": {
        "vendor/package": "1.3.2",
        "vendor/package2": "1.*",
        "vendor/package3": "^2.0.3"
    }
}

-execute #composer install 
