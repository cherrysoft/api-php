# Ordr.in Php Library

A php library for the ordr.in API.
See full API documentation at http://hackfood.ordr.in

## Table of Contents

 - [Installation](#installation)
 - [Usage](#usage)
   - [Initialization](#initialization)
   
   - [Order Endpoints](#order)
     - [Guest Order](#guest-order)
     - [User Order](#user-order)
     
   - [Restaurant Endpoints](#restaurant)
     - [Delivery Check](#delivery-check)
     - [Delivery List](#delivery-list)
     - [Fee](#fee)
     - [Restaurant Details](#restaurant-details)
     
   - [User Endpoints](#user)
     - [Change Password](#change-password)
     - [Create Account](#create-account)
     - [Create Address](#create-address)
     - [Create Credit Card](#create-credit-card)
     - [Remove address](#remove-address)
     - [Remove Credit Card](#remove-credit-card)
     - [Get Account Information](#get-account-information)
     - [Get All Saved Addresses](#get-all-saved-addresses)
     - [Get all saved credit cards](#get-all-saved-credit-cards)
     - [Get an Order](#get-an-order)
     - [Get Order History](#get-order-history)
     - [Get a single saved address](#get-a-single-saved-address)
     - [Get a single saved credit card](#get-a-single-saved-credit-card)
     

## Installation

Just download the zip file of this repo and unzip it into your project directory.

## Usage

### Initialization

```php
use Ordrin;
$ordrin_api = new Ordrin::APIs(api_key, servers)
```

In the initializer, the second argument sets the servers that API requests will
be sent to, and must be set to either `Ordrin::APIs::PRODUCTION` or `Ordrin::APIs::TEST`
(defaults to `Ordrin::APIs::TEST`).


### Order Endpoints

#### Guest Order

    $ordrin_api->order_guest(args)

##### Arguments
- `args["rid"]` : Ordr.in's unique restaurant identifier for the restaurant.
- `args["em"]` : The customer's email address
- `args["tray"]` : Represents a tray of menu items in the format '[menu item id]/[qty],[option id],...,[option id]'
- `args["tip"]` : Tip amount in dollars and cents
- `args["first_name"]` : The customer's first name
- `args["last_name"]` : The customer's last name
- `args["phone"]` : The customer's phone number
- `args["zip"]` : The zip code part of the address
- `args["addr"]` : The street address
- `args["addr2"]` : The second part of the street address, if needed
- `args["city"]` : The city part of the address
- `args["state"]` : The state part of the address
- `args["card_name"]` : Full name as it appears on the credit card
- `args["card_number"]` : Credit card number
- `args["card_cvc"]` : 3 or 4 digit security code
- `args["card_expiry"]` : The credit card expiration date.
- `args["card_bill_addr"]` : The credit card's billing street address
- `args["card_bill_addr2"]` : The second part of the credit card's biling street address.
- `args["card_bill_city"]` : The credit card's billing city
- `args["card_bill_state"]` : The credit card's billing state
- `args["card_bill_zip"]` : The credit card's billing zip code
- `args["card_bill_phone"]` : The credit card's billing phone number


###### Either
- `args["delivery_date"]` : Delivery date
- `args["delivery_time"]` : Delivery time

###### Or
- `args["delivery_date"]` : Delivery date



#### User Order

    $ordrin_api->order_user(args)

##### Arguments
- `args["rid"]` : Ordr.in's unique restaurant identifier for the restaurant.
- `args["tray"]` : Represents a tray of menu items in the format '[menu item id]/[qty],[option id],...,[option id]'
- `args["tip"]` : Tip amount in dollars and cents
- `args["first_name"]` : The customer's first name
- `args["last_name"]` : The customer's last name
- `args["email"]` : The user's email address
- `args["current_password"]` : The user's current password

###### Either
- `args["phone"]` : The customer's phone number
- `args["zip"]` : The zip code part of the address
- `args["addr"]` : The street address
- `args["addr2"]` : The second part of the street address, if needed
- `args["city"]` : The city part of the address
- `args["state"]` : The state part of the address

###### Or
- `args["nick"]` : The delivery location nickname. (From the user's addresses)



###### Either
- `args["card_name"]` : Full name as it appears on the credit card
- `args["card_number"]` : Credit card number
- `args["card_cvc"]` : 3 or 4 digit security code
- `args["card_expiry"]` : The credit card expiration date.
- `args["card_bill_addr"]` : The credit card's billing street address
- `args["card_bill_addr2"]` : The second part of the credit card's biling street address.
- `args["card_bill_city"]` : The credit card's billing city
- `args["card_bill_state"]` : The credit card's billing state
- `args["card_bill_zip"]` : The credit card's billing zip code
- `args["card_bill_phone"]` : The credit card's billing phone number

###### Or
- `args["card_nick"]` : The credit card nickname. (From the user's credit cards)



###### Either
- `args["delivery_date"]` : Delivery date
- `args["delivery_time"]` : Delivery time

###### Or
- `args["delivery_date"]` : Delivery date




### Restaurant Endpoints

#### Delivery Check

    $ordrin_api->delivery_check(args)

##### Arguments
- `args["datetime"]` : Delivery date and time
- `args["rid"]` : Ordr.in's unique restaurant identifier for the restaurant.
- `args["addr"]` : Delivery location street address
- `args["city"]` : Delivery location city
- `args["zip"]` : The zip code part of the address


#### Delivery List

    $ordrin_api->delivery_list(args)

##### Arguments
- `args["datetime"]` : Delivery date and time
- `args["addr"]` : Delivery location street address
- `args["city"]` : Delivery location city
- `args["zip"]` : The zip code part of the address


#### Fee

    $ordrin_api->fee(args)

##### Arguments
- `args["datetime"]` : Delivery date and time
- `args["rid"]` : Ordr.in's unique restaurant identifier for the restaurant.
- `args["subtotal"]` : The cost of all items in the tray in dollars and cents.
- `args["tip"]` : The tip in dollars and cents.
- `args["addr"]` : Delivery location street address
- `args["city"]` : Delivery location city
- `args["zip"]` : The zip code part of the address


#### Restaurant Details

    $ordrin_api->restaurant_details(args)

##### Arguments
- `args["rid"]` : Ordr.in's unique restaurant identifier for the restaurant.



### User Endpoints

#### Change Password

    $ordrin_api->change_password(args)

##### Arguments
- `args["email"]` : The user's email address
- `args["password"]` : The user's new password
- `args["email"]` : The user's email address
- `args["current_password"]` : The user's current password

#### Create Account

    $ordrin_api->create_account(args)

##### Arguments
- `args["email"]` : The user's email address
- `args["pw"]` : The user's password
- `args["first_name"]` : The user's first name
- `args["last_name"]` : The user's last name


#### Create Address

    $ordrin_api->create_addr(args)

##### Arguments
- `args["email"]` : The user's email address
- `args["nick"]` : The nickname of this address
- `args["phone"]` : The customer's phone number
- `args["zip"]` : The zip code part of the address
- `args["addr"]` : The street address
- `args["addr2"]` : The second part of the street address, if needed
- `args["city"]` : The city part of the address
- `args["state"]` : The state part of the address
- `args["email"]` : The user's email address
- `args["current_password"]` : The user's current password

#### Create Credit Card

    $ordrin_api->create_cc(args)

##### Arguments
- `args["email"]` : The user's email address
- `args["nick"]` : The nickname of this address
- `args["card_number"]` : Credit card number
- `args["card_cvc"]` : 3 or 4 digit security code
- `args["card_expiry"]` : The credit card expiration date.
- `args["bill_addr"]` : The credit card's billing street address
- `args["bill_addr2"]` : The second part of the credit card's biling street address.
- `args["bill_city"]` : The credit card's billing city
- `args["bill_state"]` : The credit card's billing state
- `args["bill_zip"]` : The credit card's billing zip code
- `args["bill_phone"]` : The credit card's billing phone number
- `args["email"]` : The user's email address
- `args["current_password"]` : The user's current password

#### Remove address

    $ordrin_api->delete_addr(args)

##### Arguments
- `args["email"]` : The user's email address
- `args["nick"]` : The nickname of this address
- `args["email"]` : The user's email address
- `args["current_password"]` : The user's current password

#### Remove Credit Card

    $ordrin_api->delete_cc(args)

##### Arguments
- `args["email"]` : The user's email address
- `args["nick"]` : The nickname of this address
- `args["email"]` : The user's email address
- `args["current_password"]` : The user's current password

#### Get Account Information

    $ordrin_api->get_account_info(args)

##### Arguments
- `args["email"]` : The user's email address
- `args["email"]` : The user's email address
- `args["current_password"]` : The user's current password

#### Get All Saved Addresses

    $ordrin_api->get_all_saved_addrs(args)

##### Arguments
- `args["email"]` : The user's email address
- `args["email"]` : The user's email address
- `args["current_password"]` : The user's current password

#### Get all saved credit cards

    $ordrin_api->get_all_saved_ccs(args)

##### Arguments
- `args["email"]` : The user's email address
- `args["email"]` : The user's email address
- `args["current_password"]` : The user's current password

#### Get an Order

    $ordrin_api->get_order(args)

##### Arguments
- `args["email"]` : The user's email address
- `args["oid"]` : Ordr.in's unique order id number.
- `args["email"]` : The user's email address
- `args["current_password"]` : The user's current password

#### Get Order History

    $ordrin_api->get_order_history(args)

##### Arguments
- `args["email"]` : The user's email address
- `args["email"]` : The user's email address
- `args["current_password"]` : The user's current password

#### Get a single saved address

    $ordrin_api->get_saved_addr(args)

##### Arguments
- `args["email"]` : The user's email address
- `args["nick"]` : The nickname of this address
- `args["email"]` : The user's email address
- `args["current_password"]` : The user's current password

#### Get a single saved credit card

    $ordrin_api->get_saved_cc(args)

##### Arguments
- `args["email"]` : The user's email address
- `args["nick"]` : The nickname of this address
- `args["email"]` : The user's email address
- `args["current_password"]` : The user's current password

