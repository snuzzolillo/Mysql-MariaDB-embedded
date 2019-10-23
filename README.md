# Programmatic and Compound Statements (MariaDB) embedded in a Server Script (PHP)

>###### Note: The code shown here is part of a larger project implementation called [WEB APPLICATION FULLSTACK FRAMEWORK](https://github.com/snuzzolillo/FULL-STACK-FRAMEWORK/blob/master/README.md).

If you are a SQL database enthusiast and know about the use of database languages and know about the database engine (RDBMS) power then this may be of interest to you. The versions before 10.1.1 of MariaDB did not allow Programmatic and Compound Statements outside of a procedure, functions or triggers (we usually call them "anonymous blocks"). From version 10.1.1 of MariaDB they are possible. This opens a door to the possibility of making full use of the maximum potential of the database engine (RDBMS) during "data transformation" or performing "business rules". This potential also includes the control of transactions, that is, you deciding when to make a COMMIT or when to make a ROLLBACK. All within a "BEGIN ... END;" can be treated as one "transaction."

At the time of developing this code, I did not find any indication that the latest version of MySql ([MySQL 8.0.18](https://dev.mysql.com/doc/relnotes/mysql/8.0/en/), General Availability 2019-10-14) has this capability.

### Purpose
Allows to embed simple or complex SQL instructions and / or "Programmatic and Compound Statements" (in this case MariaDB Version 10.1.1 or higher) into a Server Script statements sequence (in this case PHP).
### Installation
It does not require installation. Just place the *db_mariadbparser.php* file in an accessible path to be included in any PHP Script. For example:
```php
<?php
require_once('db_mariadbparser.php');
$parser = new clsDBParser("test");
/*<MARIADB ANONYMOUS CASE1>
	SET :maria_db_version = @@version;
<END>*/
$parser->doCode('CASE1');
if(!$___SQLCODE){
	print('DATABASE VERSION = '.$maria_db_version.PHP_EOL);
} else {
	die('Error '.$___SQLCODE.'-'.$___SQLERRM);
}
?>
```
### The *cldDBParser* class

#### Attributes
|Name|Description|
|--|--|
|db|DBAdapter Object|

#### Methods

|Name|Description|
|--|--|
|doCode(block_name)|Execute embedded code |


### Others Topics

 - [Let's start with a very simple example](doc/TOPIC_01.md)
 - [The Precompiler](doc/TOPIC_02.md)
 - [The Bind Variables](doc/TOPIC_03.md)

<!--stackedit_data:
eyJoaXN0b3J5IjpbMTc4OTQ3MzgzMiwtOTYwMTY4MTUsMTk3Mj
I2OTMwNl19
-->