Ordr.in PHP API
======================

A PHP wrapper for the Restaurant, User, and Order APIs provided by Ordr.in.

Usage
-----
	require_once('Ordrin.php');
	
    $dt = new dT();
    $dt->asap();

    $api = new Ordrin('sLclVFC54BGXcjGku8bTaA', 'https://r-test.ordr.in');

    $m = new Money(100.23);
    $a = new Address('123 main st','cedar rapids','52404','Suite 200', 'ia','1234567890','test');

    $subT = new Money(100);
    $tip = new Money(15);

    $r = new Restaurant();
    $r->deliveryList($dt, $a);
    $r->deliveryCheck("142", $dt, $a);

    $r->deliveryFee("142", $subT, $tip, $dt, $a);
    $r->details("142");

    $api->_url = 'http://localhost'; // Test User API
    $u = new User();
    $u->makeAcct('test@test.com', 'pass', 'John','Doe');
    $api->setCurrAcct('test@test.com', 'pass');

    $u->updateAddress($a);
    $u->getAddress('home');
    $u->deleteAddress('home');

    $u->updateCard('personal', 'John Doe', '4111111111111111','444','02','12',$a);

    $u->getCard('personal');
    $u->deleteCard('personal');

    $u->orderHistory('12');

    $u->updatePassword('newPassword');

    $api->_url = 'http://localhost'; // Test Order API

    $o = new Order();
    $tray = '';
    $o->submit('142', $tray, $tip, $dt, 'test@testing.com', 'John','Doe',$a, 'John Doe', '4111111111111111', '444', '0212', $a);


Notes
----- 
API docs available at http://www.ordr.in/developers/api.