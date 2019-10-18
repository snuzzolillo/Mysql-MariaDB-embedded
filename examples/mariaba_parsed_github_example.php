<?php
##-----------------------------------------------------------------------------------
## MARIADB Programmatic and Compound Statements
##-----------------------------------------------------------------------------------

##-- REQUIRED TO EXECUTE PARSE MariaDB Programmatic and Compound Statements ---------
define("RelativePath", ".."); #-- RELATIVE TO ROOT OF CUURENT FILE
include_once(RelativePath."/db_mariadbparser.php");
##-----------------------------------------------------------------------------------

sqlParser("test",__FILE__); # Precompiler
global $plsqlParsed;

## Before…. Setting values to Binded Variables
$var_date = '2018/01/01'; # Used as Bind Variable

##
## ---------------------------------------------------------------
/*<MARIADB ANONYMOUS CASE2>

-- --------------------------------------------------------------------
-- Testing with MARIADB, version 10.1.29
-- --------------------------------------------------------------------
  # please Check this. Block comments, internal embedded code, must use escape character
  /* Inside comments will not intefiering *\/
  DECLARE in_date date DEFAULT null;
  set in_date = DATE_ADD(:var_date, INTERVAL 30 DAY);

  if in_date > '2018-03-30' then
    set @text_error = concat('Error Managed by User:',cast(in_date as CHAR),' Exceeded Date limit');
    SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = 31001, MESSAGE_TEXT = @text_error;
  end if;

  SELECT d_date, d_time, open
  into :d_date, :d_time, :open
  FROM test.tmp_forex
  where d_date = in_date
  limit 1;
  set :var_date = in_date;
-- --------------------------------------------------------------------

<END>*/
## ---------------------------------------------------------------
#

# Execute 1 time
eval($plsqlParsed["CASE2"]->listToBind);
eval($plsqlParsed["CASE2"]->phpCodeToEval);
echo "RESULT =  $var_date, $d_date, $d_time, $open<br>\n" ;

# Execute 2 times
eval($plsqlParsed["CASE2"]->listToBind);
eval($plsqlParsed["CASE2"]->phpCodeToEval);
echo "RESULT =  $var_date, $d_date, $d_time, $open<br>\n" ;

# Execute 3 times
eval($plsqlParsed["CASE2"]->listToBind);
eval($plsqlParsed["CASE2"]->phpCodeToEval);

echo "RESULT =  $var_date, $d_date, $d_time, $open<br>\n" ;

?>
