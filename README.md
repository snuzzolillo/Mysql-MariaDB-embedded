# Programmatic and Compound Statements (MariaDB) embedded in a Server Script (PHP)

###### Note: The code shown here is part of a larger project implementation called WEB APPLICATION FULLSTACK FRAMEWORK.

If you are a SQL database enthusiast and know about the use of database languages and know the database engine (RDBMS) power then this may be of interest to you. The versions before 10.1.1 of MariaDB did not allow Programmatic and Compound Statements outside of a procedure, functions or triggers (we usually call them "anonymous blocks"). From version 10.1.1 of MariaDB they are possible. This opens a door to the possibility of making full use of the maximum potential of the database engine (RDBMS) during the "data transformation" or performing of the "business rules". This potential also includes the control of transactions, that is, you deciding when to make a COMMIT or when to make a ROLLBACK. All within a "BEGIN ... END;" can be treated as one "transaction."

At the time of developing this code, I did not find any indication that the latest version of MySql ([MySQL 8.0.18](https://dev.mysql.com/doc/relnotes/mysql/8.0/en/), General Availability 2019-10-14) has this capability.

Let's start with a very simple example:
```php
	<?php
	##-----------------------------------------------------------------------------------
	## MARIADB Programmatic and Compound Statements
	##-----------------------------------------------------------------------------------
	
	##-- REQUIRED TO EXECUTE PARSE MariaDB (from v10.1.1) Programmatic and Compound Statements
	define("RelativePath", ".."); #-- RELATIVE TO ROOT OF CUURENT FILE
	require_once(RelativePath."/db_mariadbparser.php");
	##-----------------------------------------------------------------------------------
	
	#-----------------------------------------------------------------
	# Instance creation execute the precompiler
	#-----------------------------------------------------------------
	$parser = new clsDBParser("test");
	#-----------------------------------------------------------------
	
	## ---------------------------------------------------------------
	## CASE1 : SQL Programmatic and Compound Statements  EMBEDDED
	## a very simple example
	## ---------------------------------------------------------------
	
	/*<MARIADB ANONYMOUS CASE1>
	  SET :maria_db_version = @@version;
	  show variables   like 'auto%';
	  SHOW FULL PROCESSLIST;
	<END>*/
	
	$resultDataSet = $parser->doCode('CASE1');
	# Check for Error
	if ($___SQLCODE === 0 ) {
	  print 'DATABASE VERSION=' . $maria_db_version . PHP_EOL;
	
	  # Use $resultDataSet[0] because using SQL Procedure can get multiple DataSet Results
	  print('<pre>');
	  print_r((isset($resultDataSet[0]) ? $resultDataSet[0] : "no result DataSet"));
	  print('</pre>');
	} else {
	  print('<pre>'
		.'Error on CASE1 (USING GLOBALS ERROR VARIABLES): '
		.$___SQLCODE.' - '.$___SQLERRM.PHP_EOL
		.'</pre>');
	  # Which Statement
	  $parser->printForDebug('CASE1');
	}
	?>

#### PHP Code
```
What is this?:
>>>
```php
/*<MARIADB ANONYMOUS CASE1>
```
```sql
SET :maria_db_version = @@version;
show variables  like 'auto%';
SHOW FULL PROCESSLIST;
```
```php
<END>*/
```
It is an embedded code inside the PHP Script and is written in MariaDB sentences. This code was interpreted when we created the instance of the class "clsDBParser" in the php instruction:
```php
$parser = new clsDBParser("test");
```
The interpreted code is ready for use. In order to execute this specific code we use the "doCode" method of the class "clsDBParser"
```php
$resultDataSet = $parser->doCode('CASE1');
```
### Executing the PHP Script
When we execute the PHP script example above, we will get these the results shown here for each "print":
```php
print 'DATABASE VERSION=' . $maria_db_version . PHP_EOL;
```
```
OUTPUT: DATABASE VERSION=10.1.29-MariaDB
```
The *$maria_db_version* PHP variable took the value assigned to *:maria_db_version* inside the MariaDB code:

```sql
SET :maria_db_version = @@version;
```
*: maria_db_version* is what we call a Bind Variable. These variables establish a direct relationship between an anfritrion language variable (that is PHP) with an embedded language variable (that is  SQL), the correspondence is made by "variable name".

Then, in the PHP Script, the execution continues:
```php
# Use $resultDataSet[0] because using SQL Procedure can get multiple DataSet Results
print('<pre>');
print_r((isset($resultDataSet[0]) ? $resultDataSet[0] : "no result DataSet"));
print('</pre>');
```
```
OUTPUT: 
Array
(
	[0] => Array
		(
			[Variable_name] => auto_increment_increment
			[Value] => 2
		)

	[1] => Array
		(
			[Variable_name] => auto_increment_offset
			[Value] => 2
		)

	[2] => Array
		(
			[Variable_name] => autocommit
			[Value] => OFF
		)

	[3] => Array
		(
			[Variable_name] => automatic_sp_privileges
			[Value] => ON
		)

	[4] => Array
		(
			[Id] => 2
			[User] => system
			[Host] => localhost:60694
			[db] => 
			[Command] => Sleep
			[Time] => 1
			[State] => 
			[Info] => 
			[Progress] => 0.000
		)

	[5] => Array
		(
			[Id] => 58
			[User] => system
			[Host] => localhost:56370
			[db] => test
			[Command] => Query
			[Time] => 0
			[State] => Unlocking tables
			[Info] => SHOW FULL PROCESSLIST
			[Progress] => 0.000
		)
)
```
The "*doCode*" method returns a multi-dimensional array (3 dimensions) which in PHP mysqli are called "Results". The first dimension of the array has an index for each "Result" each of them we will call a DataSet, the second dimension of the array are rows of that DataSet and the third dimension are columns of those rows. Normally we will always get 1 Unique DataSet. The output shown is the content of the PHP *$resultDataSet[0]* variable. When a MariaDB statement generates an output of one or more rows, they will create a DataSet. For example, if you run "select * from table;" The output of the select can be retrieved as a DataSet.

The first 4 rows of the *$resultDataSet[0]* array (indexed by 0..3) contain the values resulting from the MariaDB statement:
```sql
show variables  like 'auto%';
```
The following rows (indexed by 4 and 5) are the result of the MariaDB statement:
```sql
SHOW FULL PROCESSLIST;
```

### Error Handling
During the execution of the embedded code, errors may occur, but these errors will not interrupt the execution of the host language (PHP). SQL errors, whether runtime or syntax errors, are captured and handled internally by the precompiler. In order for the host language to handle embedded language errors, error variables are used for the host language to handle.

In our example, the output will be shown if there was no error, otherwise we show the error, let's see.
```php
	if ($___SQLCODE === 0 ) {
	  print 'DATABASE VERSION=' . $maria_db_version . PHP_EOL;

	  # Use $resultDataSet[0] because using SQL Procedure can get multiple DataSet Results
	  print('<pre>');
	  print_r((isset($resultDataSet[0]) ? $resultDataSet[0] : "no result DataSet"));
	  print('</pre>');
	} else {
	  print('<pre>'
		.'Error on CASE1 (USING GLOBALS ERROR VARIABLES): '
		.$___SQLCODE.' - '.$___SQLERRM.PHP_EOL
		.'</pre>');
	  # Which Statement
	  $parser->printForDebug('CASE1');
	}
```
The "*doCode*" method handles 3 variable inside PHP's global context these are:

|Variable| Description|
|--|--|
|\$___SQLCODE|Contains the error code of the last SQL statement. If there was no error, the code is 0 (zero).  |
|\$___SQLERRM|Contains the error message of the last SQL statement. If there was no error the content is empty.|
|\$___LASTSQL|It contains the last SQL statement executed.|

##### What if an Error occurs
We will change the code to generate an error. We will put an invalid SQL statement. For example:
```
	/*<MARIADB ANONYMOUS CASE1>
	  SET :maria_db_version = @@version;
	  show variables   like 'auto%';
	  SHOW FULL PROCESSLIST;
	  @any_variable = 'any value';
	<END>*/
```
After execute the PHP scritpt, there will be an SQL syntax error, then this PHP code will be executed:
```php
	else {
	  print('<pre>'
		.'Error on CASE1 (USING GLOBALS ERROR VARIABLES): '
		.$___SQLCODE.' - '.$___SQLERRM.PHP_EOL
		.'</pre>');
	  # Which Statement
	  $parser->printForDebug('CASE1');
	}
```
After execute the PHP scritpt we will get this OUTPUT:
```
	OUTPUT:
	Error on CASE1 (USING GLOBALS ERROR VARIABLES): 1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '@any_variable = 'any value';
	-- -------------------------------------
	-- end emb' at line 15
	
	   1 BEGIN NOT ATOMIC
	   2    -- - Start Bind Variables 
	   3    	SET @MARIA_DB_VERSION = NULL;
	   4  
	   5    -- - End Bind Variables 
	   6    SET @___autocommit = @@autocommit;
	   7    SET @@autocommit = 0;
	   8    BEGIN 
	   9 -- -------------------------------------
	  10 -- start embedded code
	  11 -- -------------------------------------
	  12 SET @MARIA_DB_VERSION = @@version; 
	  13   show variables   like 'auto%'; 
	  14   SHOW FULL PROCESSLIST; 
	  15   @any_variable = 'any value';
	  16 -- -------------------------------------
	  17 -- end embedded code
	  18 -- -------------------------------------
	  19   
	  20 	SELECT 'OUTPUT BIND' as ___action___,@MARIA_DB_VERSION as maria_db_version ; 
	  21 END; 
	  22 COMMIT; 
	  23    SET @@autocommit = @___autocommit;
	  24 END; -- END OF BEGIN NOT ATOMIC 
```
