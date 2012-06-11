Ordr.in Python API
==================

A PHP wrapper for the Restaurant, User, and Order APIs provided by Ordr.in. The main API documentation can be found at http://ordr.in/developers.


Data Structures
---------------

```python
ordrin.data.Address(addr, city, state, zip, phone, addr2='')

ordrin.data.CreditCard(name, expiry_month, expiry_year, bill_address, number, cvc)

ordrin.data.UserLogin(email, password)

ordrin.data.TrayItem(item_id, quantity, *options)

ordrin.data.Tray(*items)
```

Exceptions
----------

```python
ordrin.errors.ApiError(msg, text)

ordrin.errors.ApiInvalidResponseError(msg)

ordrin.errors.BadValueError(msg)
```

API Initialization
------------------

There are two key ways to initialize the OrdrinApi. First you can initialize it using the standard test or demo servers like so.
```php
// initialize with ordr.in test servers
$ordrin = new OrdrinApi(API_KEY,OrdrinApi::TEST_SERVERS);

// initialize for production
$ordrin = new OrdrinApi(API_KEY,OrdrinApi::PROD_SERVERS);
```

Second, you can initialize using your own custom server urls like this:
```php
$ordrin = new OrdrinApi(API_KEY,OrdrinApi::CUSTOM_SERVERS,restaurant_test_server,user_test_server,order_test_server);
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
$ordrin->restaurant->deliveryFee(restaurant_id,subtota.,tip,date_time,address);

// Provides restaurant details to allow display of a restaurant's menu page
$ordrin->restaurant->details(restaurant_id)
```

User API Functions
------------------
You can use the follow methods to interact with the Ordr.in User API:

```php
// Set the user credentials you'll be using
$ordrin->user->authenticate(email,sha256encodedpw);

// Get Account Information
$ordrin->user->getAccountInfo();

// Create New Account
$ordrin->user->create(email,sha256encodedpw,first_name,last_name)

// Get all saved addresses
$ordrin->user->getAddress();

// Get a single saved address
$ordrin->user->getAddress(address_nickame);

// Add/Edit a single saved address
$ordrin->user->setAddress(address_nickname,address);

// Remove an address from the user's saved addresses
$ordrin->user->deleteAddress(address_nickname);

// Get all saved credit cards
$ordrin->user->getCard();

// Get a single saved credit card
$ordrin->user->getCard(card_nickname);

// Add/Edit a single saved credit card
$ordrin->user->setCard(card_nick,cardholder_name,card_number,ccv,expire_month,expire_year,address);

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

```python
api.order.order(restaurant_id, tray, tip, delivery_date_time, first_name, last_name, address, credit_card, email=None, login=None)

order_create_user(restaurant_id, tray, tip, delivery_date_time, first_name, last_name, address, credit_card, email, password)
```
