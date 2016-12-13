# PHP UserKit

## Summary
A UserKit client library for PHP.

## Installation

Clone or download the repository from github:
```
git clone git@github.com:workpail/userkit-php
```
Then copy or symlink the `userkit-php/userkit` sub-directory into
your project.

## Documentation

For full examples and docs checkout [UserKit documentation](https://docs.userkit.io/).

## Example usage

```php

require_once("UserKit.php");

$uk = new UserKit('<YOUR_APP_SECRET_KEY>');

// Create a user
$user = $uk->users->createUser(['email' => 'jane.smith@example.com',
                            'password' => 'secretpass']);

// Get a user
$user = $uk->users->getUser("<USER_ID>");

// Update a user
$user = $uk->users->updateUser("<USER_ID>", ['name' => 'Jane Smith']);

// Login a user
$session = $uk->users->loginUser('jane.smith@example.com', 'secretpass');

// Get a logged in user by their session-token
$user = $uk->users->getCurrentUser($session->token);
if ($user != null)
{
    print 'User is logged in:';
    print $user;
}
else
{
    print 'No logged in user, invalid session token';
}
```

## Test

To run unit tests you need to create a test-app.

You also need to install PHPUNIT.

If you have the Composer package manager installed on your system there is a composer.json file in the `/php` folder which can be used to install PHPUNIT.

```
cd php

composer update
```

Otherwise, click [PHPUnit Install](https://phpunit.de) to visit their website and install the unit test framework.

Once you have PHPUNIT installed on your system, open a command window and change to the php/test folder.

```
cd php/test
```

Set the `USERKIT_KEY` environment variable to your test app key, followed by the PHPUNIT command with a dot for the current folder:


```
USERKIT_KEY=<YOUR_APP_SECRET_KEY> phpunit .
```


