# Introduction

This Library allows you to convert an IP address into a full row of data including the address, connection type, ISP, and the ISP's complaint contact details by checking five IP allocation zones.

Example of a practical usage:
------------------------------------------
**Login alerts:**

This Library can be used to notify your users about their logins or unauthorized login with rich data, and it does not require any paid API's.

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
**Identify languages:**

This Library can be used to Identify the Language of any IP address which will help you set your website's language based on the user's language.

Usage
------------------------------------------
- To get an array of wide range of metadata:
```
<?php 
include 'IPL.php';
$get = LocateIP('Your_IP');
echo "<pre>";
print_r($get);
echo "</pre>";
```
- To get a specific string such as the Country name:
```
<?php 
include 'IPL.php';
$country = LocateIP('Your_IP', 'country-name');
echo $country;
```
**List of Metadata:**

By default, the function will return them all as an array, however, if you wish to return a specific string, you may make use of the following:
| Name                                   | Description        |
| ----------------------------------------- | -------------------------------------------------------------- |    
| country                                   | No Description        |
| country-name                                   | No Description        |
| language                                   | No Description        |
| person                                   | No Description        |
| abuse-mailbox                                   | No Description        |
| phone                                   | No Description        |
| website                                   | No Description        |
| descr                                   | No Description        |
| e-mail                                   | No Description        |
| notify                                   | No Description        |
| remarks                                   | No Description        |
| netname                                   | No Description        |
| address                                   | No Description        |
| source                                   | No Description        |


**Have A Good Day!**
