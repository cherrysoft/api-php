Ordr.in PHP API
==================

A PHP wrapper for the Restaurant, User, and Order APIs provided by Ordr.in. The main API documentation can be found at http://ordr.in/developers.

Installation
-------------------
You can install the Ordrin PHP API library by [downloading the source (.zip)](https://github.com/ordrin/api-php/zipball/master).

After you download the library, move the Ordrin folder to your project and include the OrdrinApi file:

```php
require 'Ordrin/OrdrinApi.php';
````

Demo
-------------------
Included with the library is a basic demo page to help you see how interaction with the Ordr.in API works. To run the demo you'll need to [sign up for an API key](http://ordr.in/developers/signup) and add it to the API initalization in  Ordrin/demo//submit.php. Access the demo by browsing to Ordrin/demo. When using the wrapper in production you should remove the demo folder.

Data Structures
---------------

```php
$ordrin::address(address, city, state, zip, phone, address2);

$ordrin::creditCard(name, expiration_month, expiration_year, number, cvc, address);

$ordrin::trayItem(item_id, quantity, *options);

$ordrin::tray(*items);
```

Exceptions
----------
The library will throw exceptions on 3 diferent types of errors: API error, API invalid response and bad value. You'll want to be sure to catch these exceptions if you want to fail gracefully.

```php
// Exception thrown when OrdrinApi returns erorr data in json
OrdrinExceptionApiError(msg);

// Exception thrown when OrdrinAPI returns an invalid HTTP response
OrdrinExceptionApiInvalidResponse(msg);

// Exception thrown when data passed is invalid
OrdrinExceptionBadValue(msg);
```

API Initialization
------------------

There are two key ways to initialize the OrdrinApi. First, you can initialize it using the standard test or demo servers:
```php
// initialize with ordr.in test servers
$ordrin = new OrdrinApi(API_KEY, OrdrinApi::TEST_SERVERS);

// initialize for production
$ordrin = new OrdrinApi(API_KEY, OrdrinApi::PROD_SERVERS);
```

Second, you can initialize using your own custom server urls like this:
```php
$ordrin = new OrdrinApi(API_KEY, OrdrinApi::CUSTOM_SERVERS, restaurant_url, user_url, order_url);
```

Restaurant API Functions
------------------------
You can use the following functions to interact with the Ordr.in Restaurant API:

```php
// Get list of restaurants that deliver to a particular address
$ordrin->restaurant->getDeliveryList(date_time, address);

// Check to see if a particular restaurant delivers to an address at the specified times
$ordrin->restaurant->deliveryCheck(restaurant_id, date_time, address);

// Caluclates all fees for a given subtotal and delivery address
$ordrin->restaurant->deliveryFee(restaurant_id, subtotal, tip, date_time, address);

// Provides restaurant details to allow display of a restaurant's menu page
$ordrin->restaurant->details(restaurant_id);
```

User API Functions
------------------
You can use the follow methods to interact with the Ordr.in User API:

```php
// Set the user credentials you'll be using
$ordrin->user->authenticate(email, sha256encodedpw);

// Get Account Information
$ordrin->user->getAccountInfo();

// Create New Account
$ordrin->user->create(email, sha256encodedpw, first_name, last_name);

// Get all saved addresses
$ordrin->user->getAddress();

// Get a single saved address
$ordrin->user->getAddress(address_nickame);

// Add/Edit a single saved address
$ordrin->user->setAddress(address_nickname, address);

// Remove an address from the user's saved addresses
$ordrin->user->deleteAddress(address_nickname);

// Get all saved credit cards
$ordrin->user->getCard();

// Get a single saved credit card
$ordrin->user->getCard(card_nickname);

// Add/Edit a single saved credit card
$ordrin->user->setCard(card_nick, cardholder_name, card_number, ccv, expire_month, expire_year, address);

// Remove a credit card from the user's saved cards
$ordrin->user->deleteCard(card_nick);

// Get a list of previous orders
$ordrin->user->getOrderHistory();

// Get details of a specific order
$ordrin->user->getOrderHistory(order_id);

// Update the user's password
$ordrin->user->updatePassword(sha256encodedpw));
```

Order API Functions
-------------------
You can use the following methods to interact with the Ordr.in Order API

```php
// submit an anonymous order
$ordrin->order->submit(restaurant_id, tray, tip, delivery_date_time, email, '', first_name, last_name, address, credit_card);

// submit an order and create an account
$ordrin->order->submit(restaurant_id, tray, tip, delivery_date_time, email, password, first_name, last_name, address, credit_card);

// submit an order for an authenticated user
$ordrin->order->submit(restaurant_id, tray, tip, delivery_date_time, '', '', first_name, last_name, address, credit_card, true);
```

Prerequisites
-------------------
* PHP >= 5.2.1
* The PHP JSON extension

Providing Feedback
-------------------
Using the API and want to give feedback? Notify of us of issues with the [Github Issue Tracker](https://github.com/ordrin/api-php/issues) or join the discussion in our [Google Group](https://groups.google.com/forum/?fromgroups#!forum/ordrin-api).
