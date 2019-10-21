### The precompiler.
The pre compiler is invoked when an instance of clsDBParser is created:
```php
	$parser = new clsDBParser("test");
```
The "clsDBParser" function is part of db_mariadbparser.php. This function runs through the script (that is, "to itself") looking for comment blocks enclosed within two specific tags.

|TAG             |descripcion                          |
|----------------|-------------------------------|
|Start with|/*<DATABASE_ENGINE ANONYMOUS BLOCK_NAME\>|
|Ends with|\<END>*/|

>The texts of the tags must be in UPPERCASE.

The call to the precompiler must be in the beginning, in this way the embedded codes will be interpreted and constructed before continuing to execute the original script.

The precompiler creates an array called plsqlParsed, whose elements are indexed by the code name identified by the "BLOCK_NAME" on the start tag.
	
The pre compiler builds several variables referenced by:
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
