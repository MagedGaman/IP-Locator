# IP-Locator

This Snipe allows you to convert an IP address into a full row of data including the address, connection type, ISP, and the ISP's complaint contact details by checking five IP allocation zones.

This Snipe can be used to notify your users about their logins or unauthorized login with rich data, and it does not require any paid API's.

Example of a practical usage:
------------------------------------------
```
New device signed in to your account:

Time: 2019-07-04 04:11:37 UTC
IP address: 00.00.00.00
Browser: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:67.0) Gecko/20100101 Firefox/67.0
Country: Malaysia
City: Kuala Lumpur
If you think this login is unauthorized, please reset your account's password immediately. 
You can also take further legal action by contacting the ISP of the attacker:
ISP name: ISP company
Abuse report email: example@example.com
Phone Number: +100 0000 000
```
Usage
------------------------------------------
```
<?php 
include 'IPL.php';
$get = LocateIP('Your_IP', 'normal');
echo "<pre>";
print_r($get);
echo "</pre>";
```
