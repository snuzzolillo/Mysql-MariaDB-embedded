### The Bind Variables
Bind Variables are used to exchange data between the host language and the embedded language. For the purposes of this pre-compiler, it will make a direct relationship between variables in embedded language (those preceded with ":") and variables (of global context) within the host language with a correlation by "name of variable", that is, a variable called ": var_date" in the embedded language the value will be associated with a variable called "$ var_date" in the host language.

Let's look at this example of embedded SQL code:
```php
	/*<MARIADB ANONYMOUS CASE2>
	-- --------------------------------------------------------------------
	-- Testing with MARIADB, version 10.1.29
	-- --------------------------------------------------------------------
	
	  DECLARE in_date date DEFAULT null;
	  set in_date = DATE_ADD(:var_date, INTERVAL 30 DAY);
	  
	  set :var_test = 'any value';
	  set :var_date = in_date;
	<END>*/
```

In this example there are two Bind Variable, called ":var_date" and ":var_test". The precompiler assumes that they will be related to global context php variables called "$var_date" and "$var_test" respectively. Additionally, the values ​​of ":var_date" and ":var_test" are modified by assignment statements.

The handling of the Bind Variables has two phases:
 1. Assignment from the host language to the embedded language as an initialization.
 2. Reassignment of the value from the embedded
    language to the host language.
To do this the precompiler generates the necessary instructions that we will see later.

To execute the embedded code of the example from php, we would follow these steps:
```
## Setting values to Bind Variables
$var_date = '2018/01/01'; # Used as Bind Variable

## Execute generated code
$parser->doCode('CASE2');

echo 'var_date='.$var_date.'<br>';
echo 'var_test='.$var_test;
```
OUTPUT:
```
var_date=2018-01-31
var_test=any value
```
Note that $ var_test was not initialized. The precompiler creates the php variable if it has not been created yet, the creation always occurs within the global context. For these variables created by the precompiler the initial value is "" for php, NULL for SQL.
