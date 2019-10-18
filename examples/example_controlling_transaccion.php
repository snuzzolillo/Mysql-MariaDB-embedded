<?php
##-----------------------------------------------------------------------------------
## MARIADB Programmatic and Compound Statements
##-----------------------------------------------------------------------------------

##-- REQUIRED TO EXECUTE PARSE MariaDB Programmatic and Compound Statements ---------
define("RelativePath", ".."); #-- RELATIVE TO ROOT OF CUURENT FILE
include_once(RelativePath."/db_mariadbparser.php");
##-----------------------------------------------------------------------------------

#-----------------------------------------------------------------
sqlParser("test",__FILE__); # Precompiler
#-----------------------------------------------------------------

## inicialice… Binded Variables
$autocommit = "";
## ---------------------------------------------------------------
## Example: Controlling transaction with rollback or commit.
## an automatic "SET AUTOCOMMIT = OFF" will be executeted before 
## start the embedded code.
##
## ---------------------------------------------------------
## --- Skeleton of the generated code : --------------------
## ---------------------------------------------------------
# BEGIN NOT ATOMIC
#	-- - Start Binded Variables 
#	.
#	...... <SET VALUES TO @VARIABLES> UESD AS BINDED VARIABLES
#	.
#	-- - End Binded Variables 
#    SET autocommit = 0;
#   BEGIN 
# -- -------------------------------------
# -- start embedded code
# -- -------------------------------------
#	.
#	.
#	.
#	..... EMBEDDED CODE
#	.
#	.
#	.
# -- -------------------------------------
# -- end embedded code
# -- -------------------------------------
#  SELECT <LIST OF @VARIABLES> USED AS BINDED VARIABLES; 
# END; 
# COMMIT; 
# END; -- END OF BEGIN NOT ATOMIC 
#
## ---------------------------------------------------------
## A COMMIT statement will be automatically executed at the <END>*/ tag
## if An error occurs, all transactions, excepts DDL, will be rolled back;
## all transactions after a DDL, will be commited;
## ---------------------------------------------------------------

##----------------------------------------------------------------
## HERE START THE EMBEDDED CODE
##----------------------------------------------------------------
/*<MARIADB ANONYMOUS TRANSACCION1>
SET :autocommit = @@autocommit;
BEGIN
	declare text_error varchar(1000);
	CREATE TABLE if not exists test.his_forex (
	  PAIR varchar(20) DEFAULT NULL,
	  D_DATE datetime DEFAULT NULL,
	  OPEN decimal(28,10) DEFAULT NULL,
	  HIGH decimal(28,10) DEFAULT NULL,
	  LOW decimal(28,10) DEFAULT NULL,
	  CLOSE decimal(28,10) DEFAULT NULL,
	  NADA int(11) DEFAULT NULL,
	  UNIQUE KEY D_DATE_2 (D_DATE,PAIR),
	  KEY PAIR (PAIR),
	  KEY D_DATE (D_DATE)
	) ENGINE=InnoDB DEFAULT CHARSET=latin1;


	insert into test.his_forex(PAIR,D_DATE,OPEN,HIGH,LOW,CLOSE,NADA) values ('EURUSD','2018-01-01 17:01:00',1.2008300000,1.2009500000,1.2001700000,1.2003000000,0);
	insert into test.his_forex(PAIR,D_DATE,OPEN,HIGH,LOW,CLOSE,NADA) values ('EURUSD','2018-01-01 17:02:00',1.2003500000,1.2004300000,1.2003500000,1.2004300000,0);
	insert into test.his_forex(PAIR,D_DATE,OPEN,HIGH,LOW,CLOSE,NADA) values ('EURUSD','2018-01-01 17:03:00',1.2004100000,1.2005000000,1.2003100000,1.2004600000,0);
	insert into test.his_forex(PAIR,D_DATE,OPEN,HIGH,LOW,CLOSE,NADA) values ('EURUSD','2018-01-01 17:04:00',1.2004900000,1.2004900000,1.2004600000,1.2004800000,0);
	insert into test.his_forex(PAIR,D_DATE,OPEN,HIGH,LOW,CLOSE,NADA) values ('EURUSD','2018-01-01 17:05:00',1.2005000000,1.2005000000,1.2004800000,1.2004800000,0);
	
	
	/*
	*
	* The "* /" block comment closing symbol may invalidate the PHP script 
	* or cause problems to the precompiler. 
	* You MUST use the escape character "* \ /"
	* 
	*\/
	
	
	/* 
	* A succesfull DDL statement will cause an automatic COMMIT for pending transaction 
	*\/
	-- ALTER TABLE test.his_forex ADD INDEX INDEX1 (PAIR);

	/* 
	* if an error ocurrs, an automatic rollback will be executed 
	*\/
	-- set text_error = concat('Error Managed by User: Check if rollback',' Autocommit was ',:autocommit );
    -- SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = 31001, MESSAGE_TEXT = text_error;
	
END;
<END>*/
## ---------------------------------------------------------------
#

global $plsqlParsed;
# Execute 1 time
print_r($plsqlParsed["TRANSACCION1"]->listToBind); 
eval($plsqlParsed["TRANSACCION1"]->listToBind);
print_r($plsqlParsed["TRANSACCION1"]->phpCodeToEval); 
eval($plsqlParsed["TRANSACCION1"]->phpCodeToEval);
echo "Auto Commit = $autocommit";

?>
