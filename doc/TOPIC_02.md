### The precompiler.
The server script *"db_mariadbparser.php"* is a piece of PHP code that does the precompile function, it also contains what is necessary to connect to the database and execute the embedded SQL commands. That is, it reads itself to search for specific tags to find statements in the "embedded language". The foreing language is embedded within a block comment into the host language so do not generate error.

#### Concepts to consider when using embedded code.

|             |	|
|----------------|-------------------------------|
|Host Language|It refers to a language that runs as a host (for example PHP) and will have a foreing language code embedded.|
|PreCompiler|Piece of code in host language that transforms the content of the embedded code into host language code.|
|Bind Variable|Host language variable that will be referenced in the embedded code. For general use, the PL / SQL convention will be used, that is, host language variables will be represented as ":variable_name" (the variable name preceded by the colon)|

The server script *"db_mariadbparser.php"* is a piece of PHP code that does the precompile function, so it must be included into the PHP Script with the embedded SQL code.

```php
	##-- REQUIRED TO EXECUTE PARSE MariaDB (from v10.1.1) Programmatic and Compound Statements
	define("RelativePath", ".."); #-- RELATIVE TO ROOT OF CUURENT FILE
	require_once(RelativePath."/db_mariadbparser.php");
	##-----------------------------------------------------------------------------------
```
The precompiler is invoked when an instance of clsDBParser is created. Just one time for all embedded codes:
```php
	$parser = new clsDBParser("test");
```
The "clsDBParser" function is part of *"db_mariadbparser.php"*. This function runs through the script (that is, "to itself") looking for comment blocks enclosed within two specific tags.

The "test" parameter is a reference to a DB-CONNECTOR. It is defined into *"db_mariadbparser.php"*. Yo must config this to your needs.
#### The Tags descripctions
|TAG             |Descripcion                          |
|----------------|-------------------------------|
|Start with|/*<DATABASE_ENGINE ANONYMOUS BLOCK_NAME\>|
|Ends with|\<END>*/|

>The texts of the tags must be in UPPERCASE.

The call to the precompiler must be in the beginning, in this way the embedded codes will be interpreted and constructed before continuing to execute the original script.

#### The Embedded code
Is considered "embedded code" what is enclosed between the tags 
```
/*<……> and <END>*/
```
Here an example:
```sql
/*<MARIADB ANONYMOUS CASE2>

-- --------------------------------------------------------------------
-- Testing with MARIADB, version 10.1.29
-- --------------------------------------------------------------------

  DECLARE in_date date DEFAULT null;
  set in_date = DATE_ADD(:var_date, INTERVAL 30 DAY);

  if in_date > '2018-03-30' then
    set @text_error = concat('Error Managed by User:',cast(in_date as CHAR),' Exceeded Date limit');
    SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = 31001, MESSAGE_TEXT = @text_error;
  end if;

  -- -----------------------------------------------------
  -- This command without INTO will fill DataSet result --
  -- -----------------------------------------------------
  SELECT d_date, d_time, open
  FROM test.tmp_forex
  where d_date > in_date
  limit 10;
  -- -----------------------------------------------------

  -- -----------------------------------------------------
  -- This command with INTO will change Bind Variables  --
  -- -----------------------------------------------------
  SELECT d_date, d_time, open
  into :d_date, :d_time, :open
  FROM test.tmp_forex
  where d_date = in_date
  limit 1;
  -- -----------------------------------------------------

  -- -----------------------------------------------------
  -- This SET will change the value of Bind Variables ----
  -- -----------------------------------------------------
  set :var_date = in_date;

<END>*/
```
The first tag provides us with additional information, in our case:
```
/*<MARIADB ANONYMOUS CASE2>
```
The parser will interpret this as follows:

|             ||
|----------|--------------------------|
|MARIADB|SQL language or dialect or Database engine.|
|ANONYMOUS|Indicates that it is an anonymous block. Used for future implementation, for now it is not relevant but the term "ANONYMOUS" must be present.|
|CASE2|It is a unique name. It is the identification of the block that refers to the SQL code that ends when finding the <END>*\/ tag. During the precompilation process, the embedded code will be extracted and stored in a PHP array usin the name as index and to allow its subsequent invocation. It must be unique into a Script Server.|


The precompiler creates an array called plsqlParsed, whose elements are indexed by the code name identified by the "BLOCK_NAME" on the start tag.
	
The precompiler builds several variables referenced by:
```
	plsqlParsed["BLOCK_NAME"]
```
Those that are of interest to us:
```
	plsqlParsed["BLOCK_NAME"]->LASTSQL
	plsqlParsed["BLOCK_NAME"]->SQLCODE
	plsqlParsed["BLOCK_NAME"]->SQLERRM
```

#### About the db_mariadbparser.php file
This script contains the precompiler logic in addition to everything needed to connect to a MySql (MARIADB) database with "mysqli" drivers. It is the summary within a single file of a group of multiple files that make up a "Generic DB Adapters", which has been simplified for the purpose of testing this idea. This idea is experimental, in the testing phase and possibly will be part of the integral development associated with… .. FRAMEWORK…

In this file you will find references to PL / SQL or ORACLE, from which the original idea started and was tested in the same way as this. At the time of testing with ORACLE, Mysql did not have the concept of executing instructions outside of procedure or function. Mysql also does not have the concept of "Bind Variables" as ORACLE does, the closest implementation is the association by positioning and not by name of variables. Mysql (and MARIADB) have the concept of User-Defined Variables which are used to simulate the "Binded Variables", if you notice, the generated code replaces the variables preceded by ":" to variables preceded by "@" within the embedded code.
