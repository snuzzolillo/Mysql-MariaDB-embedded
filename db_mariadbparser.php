<?php
/* TODO: Is PHP 7 complaint? */

#-- BEGIN PARSER
//DB MariaDB Ver 10.1 o superior

define("ccsInteger", 1);
define("ccsFloat", 2);
define("ccsSingle", ccsFloat); //alias
define("ccsText", 3);
define("ccsDate", 4);
define("ccsBoolean", 5);
define("ccsMemo", 6);

define("ccsGet", 1);
define("ccsPost", 2);

define("ccsTimestamp", 0);
define("ccsYear", 1);
define("ccsMonth", 2);
define("ccsDay", 3);
define("ccsHour", 4);
define("ccsMinute", 5);
define("ccsSecond", 6);
define("ccsMilliSecond", 7);
define("ccsAmPm", 8);
define("ccsShortMonth", 9);
define("ccsFullMonth", 10);
define("ccsWeek", 11);
define("ccsGMT", 12);
define("ccsAppropriateYear", 13);

define("CCS_ENCRYPTION_KEY_FOR_COOKIE", '55BOr3x0g77H2866');
define("CCS_EXPIRATION_DATE", 30 * 24 * 3600);
define("CCS_SLIDING_EXPIRATION", false);

//CCGetSession @0-A9848448
function CCGetSession($parameter_name, $default_value = "")
{
    return isset($_SESSION[$parameter_name]) ? $_SESSION[$parameter_name] : $default_value;
}

//Connection Settings
$CCConnectionSettings = array (
    "test" => array(
        "Database" => "test",
        "Host" => "localhost",
        "Port" => "0",
        "User" => "system",
        "Password" => "manager",
        # Change only if you know what is for
        "Type" => "MySQL",
        "DBLib" => "MySQLi",
        "Encoding" => array("", "utf8"),
        "Persistent" => false,
        "DateFormat" => array("yyyy", "-", "mm", "-", "dd", " ", "HH", ":", "nn", ":", "ss"),
        "BooleanFormat" => array(1, 0, ""),
        "Uppercase" => false
    )
);
//End Connection Settings

function error_manager($msg, $code=3, $type=false, $status = 400, $exception = false){

    //$msg = utf8_decode($msg);
    if (strlen($msg) != strlen(utf8_encode($msg))){
        $msg = utf8_encode($msg);
    }
    global $sourceName;
    global $CCConnectionSettings;
    global $Charset;

    # #######################################
    #$ContentType    = "application/json";
    #$Charset        = $Charset ? $Charset : "utf-8";
    #
    #if ($Charset) {
    #    header("Content-Type: " . $ContentType . "; charset=" . $Charset);
    #} else {
    #    header("Content-Type: " . $ContentType);
    #}
    # #######################################

    $status = (!$status) ? 400 : intval($status);
    $type = (!$type) ? "API" : $type;
    $exception = (!$exception) ? "OTHER" : $exception;
    #if (!$type) $type = 'API';
    $r = new stdClass();
    if ($status==200) {
        $r->{'ERROR'} = new stdClass();
    }
    #$e->{'ERROR'} = new stdClass();
    $e = $r;
    http_response_code($status);

    if ($msg == '5') {
        $e->{'CODE'} = $code;
        $e->{'MESSAGE'} = "BAD REQUEST $msg";
    } else {
        $e->{'CODE'} = $code;
        $e->{'MESSAGE'} = $msg;
        $e->{'USERID'} = CCGetSession("USERID");
    }

    $e->{'TYPE'} = $type;
    if ($sourceName and isset($CCConnectionSettings[$sourceName]["Type"])) {
        $e->{'DB_TYPE'} = strtoupper($CCConnectionSettings[$sourceName]["Type"]);
    } else {
        $e->{'DB_TYPE'} = "";
    }
    #if ($type=="DB") {
    #    $exceptions = new sqlExceptions();
    #    $e->{'EXCEPTION'} = $exceptions->getException($code,$e->{'DB_TYPE'});
    #} else {
    #    $e->{'EXCEPTION'} = $exception;
    #}

    $e = json_encode($e);
    if (json_last_error()) {
        error_manager('error_manager bad json '.json_last_error_msg(), -20101) ;
    }

    #die(json_encode($e));
}
/****
 * USE mysql as Connection Settings in Connection Settings array
 */
#$sourceName = 'mysql';

class DB_MySQLi {

    /* public: connection parameters */
    public $DBHost     = "";
    public $DBPort     = 0;
    public $DBSocket   = "";
    public $DBDatabase = "";
    public $DBUser     = "";
    public $DBPassword = "";
    public $Persistent = false;
    public $Case       = CASE_LOWER;
    /* Case puede ser CASE_LOWER CASE_UPPER o false */

    /* public: configuration parameters */
    public $Auto_Free     = 1;     ## Set to 1 for automatic mysqli_free_result()
    public $Debug         = 0;     ## Set to 1 for debugging messages.
    public $Seq_Table     = "db_sequence";

    /* public: result array and current row number */
    public $Record   = array();
    public $Row;

    /* public: result array DataSet */
    public $DataSet   = array();
    public $DataSetNumbers   = 0;

    /* public: current error number and error text */
    public $Errno    = 0;
    public $Error    = "";
    public $SqlState = "";

    /* public: this is an api revision, not a CVS revision. */
    public $type     = "mysql";
    public $revision = "1.2";

    /* private: link and query handles */
    public $Link_ID  = 0;
    public $Query_ID = 0;
    public $Connected = false;

    public $Encoding = "";

    /* public: constructor */
    function DB_Sql($query = "") {
        $this->query($query);
    }

    /* public: some trivial reporting */
    function link_id() {
        return $this->Link_ID;
    }

    function query_id() {
        return $this->Query_ID;
    }

    function try_connect($DBDatabase = "", $DBHost = "", $DBPort = 0, $DBSocket = "", $DBUser = "", $DBPassword = "") {
        $this->Query_ID  = 0;
        /* Handle defaults */
        if ("" == $DBDatabase)   $DBDatabase = $this->DBDatabase;
        if (0 == $DBPort)        $DBPort     = $this->DBPort;
        if ("" == $DBSocket)     $DBSocket   = $this->DBSocket;
        if ("" == $DBHost)       $DBHost     = $this->DBHost;
        if ("" == $DBUser)       $DBUser     = $this->DBUser;
        if ("" == $DBPassword)   $DBPassword = $this->DBPassword;

        restore_error_handler();

        $this->Link_ID = @mysqli_connect($DBHost, $DBUser, $DBPassword, $DBDatabase, $DBPort, $DBSocket);
        $this->Connected = $this->Link_ID ? true : false;

        if (mysqli_connect_errno()) {
            $msg["code"] = mysqli_connect_errno() ;
            $msg["message"] = mysqli_connect_error();
            $this->halt($msg, '');
        }

        // Reestablece el error handler
        set_error_handler("all_errors_handler", E_ALL);

        return $this->Connected;
    }

    /* public: connection management */
    function connect($DBDatabase = "", $DBHost = "", $DBPort = 0, $DBSocket="", $DBUser = "", $DBPassword = "") {
        /* Handle defaults */
        if ("" == $DBDatabase)   $DBDatabase = $this->DBDatabase;
        if (0 == $DBPort)        $DBPort     = $this->DBPort;
        if ("" == $DBSocket)     $DBSocket   = $this->DBSocket;
        if ("" == $DBHost)       $DBHost     = $this->DBHost;
        if ("" == $DBUser)       $DBUser     = $this->DBUser;
        if ("" == $DBPassword)   $DBPassword = $this->DBPassword;


        /* establish connection, select database */
        if (!$this->Connected) {

            restore_error_handler();

            $this->Query_ID  = 0;
            $this->Link_ID = @mysqli_connect($DBHost, $DBUser, $DBPassword, $DBDatabase, $DBPort, $DBSocket);

            if (mysqli_connect_errno()) {
                $msg["code"] = mysqli_connect_errno() ;
                $msg["message"] = mysqli_connect_error();
                $this->halt($msg, '');
            }

            if (!$this->Link_ID) {
                $msg["code"] =  "2";
                #$msg["message"] = mysqli_error($this->Link_ID);

                $msg["message"] = "mysqli_connect($DBHost, $DBUser, \$DBPassword, $DBDatabase, $DBPort, $DBSocket) failed.";
                $this->halt($msg, '');

                #$this->halt("mysqli_connect($DBHost, $DBUser, \$DBPassword, $DBDatabase, $DBPort, $DBSocket) failed.");
                return 0;
            }
            @set_error_handler("all_errors_handler", E_ALL);

            $server_info = @mysqli_get_server_info($this->Link_ID);
            preg_match("/\d+\.\d+(\.\d+)?/", $server_info, $matches);
            $version_str = $matches[0];
            $version = explode(".", $version_str);
            if ($version[0] >= 4) {
                if (($version[0] > 4 || $version[1] >= 1) && is_array($this->Encoding) && $this->Encoding[1])
                    @mysqli_query($this->Link_ID, "set character set '" . $this->Encoding[1] . "'");
                elseif (is_array($this->Encoding) && $this->Encoding[0])
                    @mysqli_query($this->Link_ID, "set character set '" . $this->Encoding[0] . "'");
            }

            ## SETAR EL LENGUAJE SEGUN CONFIG
            #@mysqli_query($this->Link_ID, "SET lc_messages = 'es_ES'");
            #@mysqli_query($this->Link_ID, "SET lc_time_names = 'es_ES';");
            $this->Connected = true;
        }

        return $this->Link_ID;
    }



    /* public: discard the query result */
    function free_result() {
        if (is_resource($this->Query_ID)) {
            @mysqli_free_result($this->Query_ID);
        }
        $this->Query_ID = 0;
    }

    /* public: perform a query */
    function query($Query_String) {
        /* No empty queries, please, since PHP4 chokes on them. */
        if ($Query_String == "")
            /* The empty query string is passed on from the constructor,
             * when calling the class without a query, e.g. in situations
             * like these: '$db = new DB_Sql_Subclass;'
             */
            return 0;

        if (!$this->connect()) {
            return 0; /* we already complained in connect() about that. */
        };

        # New query, discard previous result.
        if ($this->Query_ID) {
            $this->free_result();
        }

        if ($this->Debug)
            printf("Debug: query = %s<br>\n", $Query_String);

        $this->Query_ID = @mysqli_query($this->Link_ID, $Query_String);
        $this->Row   = 0;
        $this->Errno = mysqli_errno($this->Link_ID);
        $this->Error = mysqli_error($this->Link_ID);

        if (!$this->Query_ID) {
            $msg["code"]    = $this->Errno;
            $msg["message"] = $this->Error;
            $this->halt($msg, $Query_String );
            $this->Errors->addError("Database Error: " . mysqli_error($this->Link_ID));
        } else {
            if ($this->Link_ID->affected_rows == 0 and $this->Link_ID->warning_count > 0) {
                $e = $this->Link_ID->get_warnings();
                if ($e->errno == 1305) {
                    # casos como warning durante un drop if exists no es relevante.
                } else {
                    $msg["code"] = 1329;
                    $msg["code"] = $e->errno;
                    $msg["message"] = $e->message.($msg["code"] == 1329 ? "" : " No data - zero rows fetched, selected, or processed ");
                    $this->Errors->addError("Database Error: " . $msg["message"]);
                    $this->halt($msg, $Query_String);
                }
            }
        }
        # Will return nada if it fails. That's fine.
        return $this->Query_ID;
    }

    /* public: perform a query */
    function multi_query($Query_String) {
        /* No empty queries, please, since PHP4 chokes on them. */
        if ($Query_String == "")
            /* The empty query string is passed on from the constructor,
             * when calling the class without a query, e.g. in situations
             * like these: '$db = new DB_Sql_Subclass;'
             */
            return 0;

        if (!$this->connect()) {
            return 0; /* we already complained in connect() about that. */
        };

        # New query, discard previous result.
        if ($this->Query_ID) {
            $this->free_result();
        }

        if ($this->Debug)
            printf("Debug: query = %s<br>\n", $Query_String);

        $this->Query_ID = @mysqli_multi_query($this->Link_ID, $Query_String);
        $this->Row   = 0;
        $this->Errno = mysqli_errno($this->Link_ID);
        $this->Error = mysqli_error($this->Link_ID);

        if (!$this->Query_ID) {
            $msg["code"]    = $this->Errno;
            $msg["message"] = $this->Error;
            $this->halt($msg, $Query_String );
            $this->Errors->addError("Database Error: " . mysqli_error($this->Link_ID));
        } else {
            if ($this->Link_ID->affected_rows == 0 and $this->Link_ID->warning_count > 0) {
                $e = $this->Link_ID->get_warnings();
                if ($e->errno == 1305) {
                    # casos como warning durante un drop if exists no es relevante.
                } else {
                    $msg["code"] = 1329;
                    $msg["code"] = $e->errno;
                    $msg["message"] = $e->message.($msg["code"] == 1329 ? "" : " No data - zero rows fetched, selected, or processed ");
                    $this->Errors->addError("Database Error: " . $msg["message"]);
                    $this->halt($msg, $Query_String);
                }
            }
        }

        $this->DataSet = array();
        if ($this->Query_ID) do {
            // Lets work with the first result set
            if ($result = mysqli_use_result($this->Link_ID))
            {
                #print_r("Result ".PHP_EOL);
                #print_r($result);

                $DatasetNumber = 0;

                // Loop the first result set, reading it into an array
                #while ($row = $result->fetch_array(MYSQLI_ASSOC))
                while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
                {
                    #print_r("Result Row ".PHP_EOL);
                    #print_r("Result Row ".PHP_EOL);
                    #print_r($row);
                    #print_r(PHP_EOL);
                    $this->DataSet[$DatasetNumber][] = $row;
                }
                $this->DataSetNumbers = ++$DatasetNumber;

                // Close the result set
                mysqli_free_result ($result);

            }
        } while (mysqli_next_result($this->Link_ID)) ;

        #echo "ORIGINAL RESULT DATASET".PHP_EOL;
        #var_dump($this->DataSet);
        #echo PHP_EOL;

        # Get Last row of the last DataSet
        if (count($this->DataSet)) {
            $this->Record = array_pop($this->DataSet[count($this->DataSet) - 1]);
            $this->Row = 1;
        }

        # Will return nada if it fails. That's fine.
        return $this->Query_ID;
    }

    /* public: walk result set */
    function next_record() {
        if (!$this->Query_ID)
            return 0;

        $this->Record = @mysqli_fetch_array($this->Query_ID, MYSQLI_BOTH);

        if ($this->Case !== false) {
            $this->Record = is_array($this->Record) ? array_change_key_case($this->Record, $this->Case) : $this->Record;
        }

        $this->Row   += 1;
        $this->Errno  = mysqli_errno($this->Link_ID);
        $this->Error  = mysqli_error($this->Link_ID);

        $stat = is_array($this->Record);
        if (!$stat && $this->Auto_Free) {
            $this->free_result();
        }
        return $stat;
    }

    /* public: position in result set */
    function seek($pos = 0) {
        $status = @mysqli_data_seek($this->Query_ID, $pos);
        if ($status) {
            $this->Row = $pos;
        } else {
            $this->Errors->addError("Database error: seek($pos) failed -  result has ".$this->num_rows()." rows");

            /* half assed attempt to save the day,
             * but do not consider this documented or even
             * desireable behaviour.
             */
            @mysqli_data_seek($this->Query_ID, $this->num_rows());
            $this->Row = $this->num_rows();
        }
        return true;
    }

    /* public: table locking */
    function lock($table, $mode="write") {
        $this->connect();

        $query="lock tables ";
        if (is_array($table)) {
            while (list($key,$value)=each($table)) {
                if ($key=="read" && $key!=0) {
                    $query.="$value read, ";
                } else {
                    $query.="$value $mode, ";
                }
            }
            $query=substr($query,0,-2);
        } else {
            $query.="$table $mode";
        }
        $res = @mysqli_query($this->Link_ID, $query);
        if (!$res) {
            $this->Errors->addError("Database error: Cannot lock tables - " . mysqli_error($this->Link_ID));
            return 0;
        }
        return $res;
    }

    function unlock() {
        $this->connect();

        $res = @mysqli_query("unlock tables");
        if (!$res) {
            $this->Errors->addError("Database error: cannot unlock tables - " . mysqli_error($this->Link_ID));
            return 0;
        }
        return $res;
    }


    /* public: evaluate the result (size, width) */
    function affected_rows() {
        return @mysqli_affected_rows($this->Link_ID);
    }

    function num_rows() {
        return @mysqli_num_rows($this->Query_ID);
    }

    function num_fields() {
        return @mysqli_num_fields($this->Query_ID);
    }

    /* public: shorthand notation */
    function nf() {
        return $this->num_rows();
    }

    function np() {
        print $this->num_rows();
    }

    function f($Name) {
        return $this->Record && array_key_exists($Name, $this->Record) ? $this->Record[$Name] : "";
    }

    function p($Name) {
        print $this->Record[$Name];
    }

    /* public: sequence numbers */
    function nextid($seq_name) {
        $this->connect();

        if ($this->lock($this->Seq_Table)) {
            /* get sequence number (locked) and increment */
            $q  = sprintf("select nextid from %s where seq_name = '%s' LIMIT 1",
                $this->Seq_Table,
                $seq_name);
            $id  = @mysqli_query($this->Link_ID, $q);
            $res = @mysqli_fetch_array($id);

            /* No current value, make one */
            if (!is_array($res)) {
                $currentid = 0;
                $q = sprintf("insert into %s values('%s', %s)",
                    $this->Seq_Table,
                    $seq_name,
                    $currentid);
                $id = @mysqli_query($this->Link_ID, $q);
            } else {
                $currentid = $res["nextid"];
            }
            $nextid = $currentid + 1;
            $q = sprintf("update %s set nextid = '%s' where seq_name = '%s'",
                $this->Seq_Table,
                $nextid,
                $seq_name);
            $id = @mysqli_query($this->Link_ID, $q);
            $this->unlock();
        } else {
            $this->Errors->addError("Database Error: " . mysqli_error($this->Link_ID));
            return 0;
        }
        return $nextid;
    }

    function close()
    {
        if ($this->Query_ID) {
            $this->free_result();
        }
        if ($this->Connected && !$this->Persistent) {
            mysqli_close($this->Link_ID);
            $this->Connected = false;
        }
    }

    /* private: error handling */
    function halt($msg, $query) {
        $msg["message"] = str_replace(array("\\", '"', "/", "\n" , "\r", "\t", "\b"), array("\\\\", '\"', '\/', '\\n', '', '\t', '\b'), $msg["message"]);
        #error_manager($msg, $code=3, $type=false, $status = 400, $exception = false)
        error_manager(
            $msg["message"] . ' '//.$query
            , $msg["code"]
            , 'DB');
    }

    function table_names() {
        $this->query("SHOW TABLES");
        $i=0;
        while ($info=mysqli_fetch_row($this->Query_ID))
        {
            $return[$i]["table_name"]= $info[0];
            $return[$i]["tablespace_name"]=$this->DBDatabase;
            $return[$i]["database"]=$this->DBDatabase;
            $i++;
        }
        return $return;
    }

    function esc($value) {
        if ($this->Connected) {
            return mysqli_real_escape_string($this->Link_ID, $value);
        } elseif (function_exists("mysql_escape_string")) {
            return mysql_escape_string($value);
        } else {
            return addslashes($value);
        }
    }

}

//End DB MySQLi Class


//clsErrors Class
class clsErrors
{
    public $Errors;
    public $ErrorsCount;
    public $ErrorDelimiter;

    function clsErrors()
    {
        global $CCSIsXHTML;
        $this->Errors = array();
        $this->ErrorsCount = 0;
        $this->ErrorDelimiter = $CCSIsXHTML ? "<br />" : "<BR>";
    }

    function addError($Description)
    {
        if (strlen($Description))
        {
            $this->Errors[$this->ErrorsCount] = $Description;
            $this->ErrorsCount++;
        }
    }

    function AddErrors($Errors)
    {
        for($i = 0; $i < $Errors->Count(); $i++)
            $this->addError($Errors->Errors[$i]);
    }

    function Clear()
    {
        $this->Errors = array();
        $this->ErrorsCount = 0;
    }

    function Count()
    {
        return $this->ErrorsCount;
    }

    function ToString()
    {

        if(sizeof($this->Errors) > 0)
            return join($this->ErrorDelimiter, $this->Errors);
        else
            return "";
    }

}
//End clsErrors Class

//DB Adapter Class
class DBAdapter
{
    public $DateFormat;
    public $BooleanFormat;
    public $LastSQL;
    public $Errors;

    public $RecordsCount;
    public $RecordNumber;
    public $PageSize;
    public $AbsolutePage;

    public $SQL = "";
    public $Where = "";
    public $Order = "";

    public $Parameters;
    public $wp;

    public $NextRecord = array();

    public $Provider;

    public $Link_ID;
    public $Query_ID;
    public $DBHost;

    public $DBPort;
    public $DBDatabase;
    public $DBUser;
    public $DBPassword;
    public $Persistent;

    public $Auto_Free;
    public $Debug;

    public $Record;
    public $Row;

    public $DataSet;
    public $DataSetNumbers;

    public $Errno;
    public $Error;

    public $DateLeftDelimiter = "'";
    public $DateRightDelimiter = "'";

    function Initialize($sourceName) {
        $this->LastSQL = "";
        $this->RecordsCount = 0;
        $this->RecordNumber = 0;
        $this->AbsolutePage = 0;
        $this->PageSize = 0;
    }

    function SetProvider($Configuration = array()) {
        $DBLib = "DB_" . $Configuration["DBLib"];
        $DBLibFile = RelativePath . "/" . strtolower($DBLib) . ".php";
        #echo $DBLibFile;
        #include_once($DBLibFile);
        $this->Provider = new $DBLib;

        $this->Link_ID = & $this->Provider->Link_ID;
        $this->Query_ID = & $this->Provider->Query_ID;
        $this->Record = & $this->Provider->Record;
        $this->DataSet = & $this->Provider->DataSet;
        $this->DataSetNumbers = & $this->Provider->DataSetNumbers;
        $this->DBDatabase = & $this->Provider->DBDatabase;
        $this->DBHost = & $this->Provider->DBHost;
        $this->DBPort = & $this->Provider->DBPort;
        $this->DBUser = & $this->Provider->DBUser;
        $this->DBPassword = & $this->Provider->DBPassword;
        $this->Persistent = & $this->Provider->Persistent;
        $this->Uppercase = & $this->Provider->Uppercase;
        $this->Provider->Errors = new clsErrors();
        $this->Errors = & $this->Provider->Errors;

        $this->Errno = & $this->Provider->Errno;
        $this->Error = & $this->Provider->Error;

        if (isset($Configuration["DBLib"]))
            $this->DB = $Configuration["DBLib"];
        if (isset($Configuration["Type"]))
            $this->Type = $Configuration["Type"];
        if (isset($Configuration["Database"]))
            $this->DBDatabase = $Configuration["Database"];
        if (isset($Configuration["Host"]))
            $this->DBHost = $Configuration["Host"];
        if (isset($Configuration["Port"]))
            $this->DBPort = $Configuration["Port"];
        if (isset($Configuration["User"]))
            $this->DBUser = $Configuration["User"];
        if (isset($Configuration["Password"]))
            $this->DBPassword = $Configuration["Password"];
        if (isset($Configuration["UseODBCCursor"]))
            $this->UseODBCCursor = $Configuration["UseODBCCursor"];
        if (isset($Configuration["Options"]))
            $this->Options = $Configuration["Options"];
        if (isset($Configuration["Encoding"]))
            $this->Provider->Encoding = $Configuration["Encoding"];
        if (isset($Configuration["Persistent"]))
            $this->Persistent = $Configuration["Persistent"];
        if (isset($Configuration["DateFormat"]))
            $this->DateFormat = $Configuration["DateFormat"];
        if (isset($Configuration["BooleanFormat"]))
            $this->BooleanFormat = $Configuration["BooleanFormat"];
        if (isset($Configuration["Uppercase"]))
            $this->Uppercase = $Configuration["Uppercase"];
    }

    function MoveToPage($Page) {
        global $CCSLocales;
        if($this->RecordNumber == 0 && $this->PageSize != 0 && $Page != 0 && $Page != 1)
            if( !$this->seek(($Page-1) * $this->PageSize)) {
                $this->Errors->addError($CCSLocales->GetText('CCS_CannotSeek'));
                $this->RecordNumber = $this->Row;
            } else {
                $this->RecordNumber = ($Page-1) * $this->PageSize;
            }
    }

    function PageCount() {
        return $this->PageSize && $this->RecordsCount != "CCS not counted" ? ceil($this->RecordsCount / $this->PageSize) : 1;
    }

    function query($SQL) {
        $this->LastSQL = $SQL;
        $this->NextRecord = array();
        return $this->Provider->query($SQL);
    }

    function execute($Procedure, $RS = 0) {
        $this->Provider->execute($Procedure, $RS);
    }

    function has_next_record() {
        if (method_exists($this->Provider, "has_next_row"))
            return $this->Provider->has_next_row();
        if (count($this->NextRecord))
            return true;
        $Record = $this->Record;
        $result = $this->Provider->next_record();
        if ($result)
            $this->NextRecord = $this->Record;
        $this->Record = $Record;
        return $result;
    }

    function next_record() {
        if (method_exists($this->Provider, "has_next_row"))
            return $this->Provider->next_record();
        if (count($this->NextRecord)){
            $this->Record = $this->NextRecord;
            $this->NextRecord = array();
            return true;
        }
        return $this->Provider->next_record();
    }

    function seek($Num) {
        return $this->Provider->seek($Num);
    }

    function f($Field) {
        return $this->Provider->f($Field);
    }

    function close() {
        return $this->Provider->close();
    }

    function num_rows() {
        return $this->Provider->num_rows();
    }

    function esc($Text) {
        if (method_exists($this->Provider, "esc"))
            return $this->Provider->esc($Text);
        return addslashes($Text);
    }

    function affected_rows() {
        return $this->Provider->affected_rows();
    }

    function num_fields() {
        return $this->Provider->num_fields();
    }

    function nf() {
        return $this->num_rows();
    }

    function np() {
        return $this->num_rows();
    }

    function p($Name) {
        $this->Provider->p($Name);
    }

    function nextid($seq_name) {
        return $this->Provider->nextid($seq_name);
    }
    function ToSQL($Value, $ValueType, $List = false) {
        $RealValue = $Value;
        if (is_array($Value) && $List) {
            $Values = array();
            foreach ($Value as $Val)
                $Values[] = $this->ToSQL($Val, $ValueType);
            return $Values;
        } elseif (is_array($Value) && !$List) {
            $Value = count($Value) ? $Value[0] : null;
        }
        if (($ValueType == ccsDate && is_array($RealValue)) || strlen($Value) || ($ValueType == ccsBoolean && is_bool($Value)))
        {
            if($ValueType == ccsInteger || $ValueType == ccsFloat)
            {
                return doubleval(str_replace(",", ".", $Value));
            }
            else if($ValueType == ccsDate)
            {
                if (is_array($RealValue)) {
                    $Value = CCFormatDate($RealValue, $this->DateFormat);
                }
                return $this->DateLeftDelimiter . $this->esc($Value) . $this->DateRightDelimiter;
            }
            else if($ValueType == ccsBoolean)
            {
                if(is_bool($Value))
                    $Value = CCFormatBoolean($Value, $this->BooleanFormat);
                else if(is_numeric($Value))
                    $Value = intval($Value);
                else if(strtoupper($Value) == "TRUE" || strtoupper($Value) == "FALSE")
                    $Value = strtoupper($Value);
                else
                    $Value = "'" . $this->esc($Value) . "'";
                return $Value;
            }
            else
            {
                return "'" . $this->esc($Value) . "'";
            }
        }
        else
        {
            return "NULL";
        }
    }

    function SQLValue($Value, $ValueType)
    {
        if ($ValueType == ccsDate && is_array($Value)) {
            $Value = CCFormatDate($Value, $this->DateFormat);
        }
        if (is_array($Value))
            $Value = count($Value) ? $Value[0] : "";
        if(!strlen($Value))
        {
            return "";
        }
        else
        {
            if($ValueType == ccsInteger || $ValueType == ccsFloat)
            {
                return doubleval(str_replace(",", ".", $Value));
            }
            else if($ValueType == ccsBoolean)
            {
                if(is_bool($Value))
                    $Value = CCFormatBoolean($Value, $this->BooleanFormat);
                else if(is_numeric($Value))
                    $Value = intval($Value);
                else if(strtoupper($Value) == "TRUE" || strtoupper($Value) == "FALSE")
                    $Value = strtoupper($Value);
                else
                    $Value = $this->esc($Value);
                return $Value;
            }
            else
            {
                return $this->esc($Value);
            }
        }
    }

    function bind($Par1, $Par2, $Par3, $Par4 = null, $Par5 = null) {
        print_r('BIND FUNCTION Type = '.strtoupper($this->Type));
        if (strtoupper($this->Type) == 'MYSQL') {
            return;
        }
        if (is_null($Par4)) {
            return $this->Provider->bind($Par1, $Par2, $Par3);
        }
        if (is_null($Par5)) {
            return $this->Provider->bind($Par1, $Par2, $Par3, $Par4);
        }
        return $this->Provider->bind($Par1, $Par2, $Par3, $Par4, $Par5);
    }

    function __call($Method, $Params) {
        return call_user_func_array(array($this->Provider, $Method), $Params);
    }

    function link_id() {
        return $this->Provider->Link_ID;
    }

    function query_id() {
        return $this->Provider->Query_ID;
    }
}
//End DB Adapter Class


class clsDBconnector extends DBAdapter
{
    function __construct($sourceName, $user = false, $password = false)
    {

        global $CCConnectionSettings;

        $this->SetProvider($CCConnectionSettings[$sourceName]);
        if ($user) {
            $this->Provider->DBUser = $user;
            $this->DBUser = $user;
        }
        if ($password) {
            $this->Provider->DBPassword = $password;
            $this->DBPassword = $password;
        }
        $this->Initialize($sourceName);
    }

    function clsDBconnector($sourceName, $user = false, $password = false)
    {
        self::__construct($sourceName, $user, $password);
    }

    function Initialize($sourceName)
    {
        global $CCConnectionSettings;

        parent::Initialize($sourceName);
        $this->DateLeftDelimiter  = "\'";
        $this->DateRightDelimiter = "\'";
        if ($CCConnectionSettings[$sourceName]["Type"] == "Oracle") {
            $this->query("ALTER SESSION SET NLS_DATE_FORMAT = 'YYYY-MM-DD HH24:MI:SS'");
        }
    }

    function OptimizeSQL($SQL)
    {
        // TODO: es importante limitar el resultado, un resultado de mas de 10.000 linea pudiera traer problema con el buffer de PHP y enviar un resultado parcial e Invalido
        if (strtoupper($this->PageSize) == 'ALL') {
            return $SQL;
        }
        $PageSize = (int) $this->PageSize;
        if (!$PageSize) return $SQL;
        $Page = $this->AbsolutePage ? (int) $this->AbsolutePage : 1;

        ## En caso de Oracle

        if (strtoupper($this->Type) == "ORACLE") {
            $SQL = "SELECT a.*, rownum a_count_rows FROM (".$SQL.") a where rownum <= ".(($Page) * $PageSize);
            $SQL = "SELECT * from (".$SQL.") where a_count_rows > ".(($Page - 1) * $PageSize)."";

        } else if (strtoupper($this->Type) == "MYSQL") {
            if (strcmp($this->RecordsCount, "CCS not counted")) {
                $SQL = $SQL. (" LIMIT " . (($Page - 1) * $PageSize) . "," . $PageSize);
            } else {;
                $SQL = $SQL. (" LIMIT " . (($Page - 1) * $PageSize) . "," . $PageSize);
            }
        }
        return $SQL;
    }
}
//End Connection Class

class clsDBParser
{

    public $plsqlParsed = array();
    public $db = "";
    public $sourceName = "";
    public $currentScriptName = "";
    private $dbName = "";
    private $dbVersion = "";
    private $bindStrictMode = false; // When true Bind only if host variable exists, if not then do Stop Error!!

    function __construct($sourceName, $currentfile=null)
    {
        $this->sourceName = $sourceName;
        $this->currentScriptName = is_null($currentfile) ? $_SERVER['SCRIPT_FILENAME'] : $currentfile;
        # TODO: create a Method to allow include more embedded code parsed from another file
        # TODO: create a Method to allow include more embedded code parsed from string
        $this->db = new clsDBconnector($sourceName);
        ## Assuming MariaDB 10.1.1 or higher
        {
            $this->db->query("select @@version");
            $dbVersion = $this->db->next_record() ? $this->db->f(0) : "";
            #parsing Version
            $versionError = false;
            $dbVersion = explode('-', $dbVersion);
            if (isset($dbVersion[1])) {
                $dbName = $dbVersion[1];
                $dbVersion = $dbVersion[0];
                $dbVersionNumber = explode('.', $dbVersion);
                # Have 3 numbers?
                if (isset($dbVersionNumber[2])) {
                    $dbVersionNumber = (int)str_pad($dbVersionNumber[0], '3', '0', STR_PAD_LEFT)
                        . str_pad($dbVersionNumber[1], '3', '0', STR_PAD_LEFT)
                        . str_pad((!isset($dbVersionNumber[2]) ? '0' : $dbVersionNumber[2]), '3', '0', STR_PAD_LEFT);
                } else $versionError = 2; // Error Version Number
            } else $versionError = 1; // Error Version Number

            if (!($dbVersionNumber > 10001000)) $versionError = 3; // Error Version not allowed

            if (($dbName != 'MariaDB') or $versionError) {
                die('This script is only valid for MariaDB 10.1.1 or higher. Your Current database is ' . $dbName . ' ' . $dbVersion);
            }
            #print_r($dbName . ' ' . $dbVersion . ' ' . $dbVersionNumber . ' ' . '9001001');

            $this->dbName = $dbName;
            $this->dbVersion = $dbVersion;
        }
        # END Assuming MariaDB 10.1.1 or higher
        $this->sqlParser($this->currentScriptName, $sourceName); # call the Precompiler
    }

    function clsDBParser($sourceName, $currentfile)
    {

        #self::__construct($sourceName, $user, $password);
        $this->__construct($sourceName, $currentfile);
    }

    function setBindStrictMode($mode) {
        $this->bindStrictMode = $mode;
    }

    function doBind($codeName,$sourceName = null) {

        is_null($sourceName) ? $this->sourceName : $sourceName;

        $arr = $this->plsqlParsed[$codeName]->bind;
        $___BIND___ = "";

        #print(nl2br("DO BIND START".PHP_EOL));
        #$db = new clsDBconnector($sourceName);
        foreach($arr as $toBind) {
            $t = trim(str_replace(',','',$toBind));
            if (!isset($GLOBALS[$t]) and $this->bindStrictMode){
                die("Strict mode Bind Variable, require GLOBAL CONTEXT VARIABLE \"$".$t."\" to be declared");
            }
            else {
                if (!isset($GLOBALS[$t]))  $GLOBALS[$t] = "";
            }
            $___BIND___ .= "\t".'SET @'.strtoupper($t).' = '.$this->db->ToSQL($GLOBALS[$t],ccsText).";\n";
            #print(nl2br("$t = ".$GLOBALS[$t].PHP_EOL));
        }
        #print(nl2br("DO BIND END".PHP_EOL));
        return $___BIND___;

        #$db->close();

    }

    function printForDebug($codeName) {
        #
        #$queryString = $this->buildQueryString($codeName);
        $queryString = $this->plsqlParsed[$codeName]->LASTSQL;
        # Print pre-format query string qith line number
        $strinByLine = explode("\n", $queryString);
        $i = 1;
        print('<pre>');
        foreach($strinByLine as $lineNumber => $line) {
            print(str_pad($lineNumber+1, 4, ' ', STR_PAD_LEFT).' '.$line.PHP_EOL);
        }
        print('</pre>');
    }

    function buildQueryString($codeName, $sourceName = null) {
        $sourceName = is_null($sourceName) ? $this->sourceName : $sourceName;
        $queryString = $this->plsqlParsed[$codeName]->queryString;

        ### --------- BIND  LINE ---------------
        $___BIND___ = $this->doBind($codeName,$sourceName);
        $queryString = str_replace('### BIND',$___BIND___,$queryString);
        ### ------------------------------------

        ### --------- BUILD RESULT LINE ------
        $getResult2 = "\n\t"."SELECT 'OUTPUT BIND' as ___action___,";
        $arr = $this->plsqlParsed[$codeName]->bind;
        foreach($arr as $toBind) {
            $t = trim(str_replace(',','',$toBind));
            $getResult2 .= '@'.strtoupper($t).' as '.$t.',';
        }
        $getResult2 = substr($getResult2,0,-1).' ;';
        $queryString = str_replace('### RESULT',$getResult2,$queryString);
        ### ------------------------------------
        return $queryString;
    }

    function doCode($codeName, $sourceName = null) {

        $sourceName = is_null($sourceName) ? $this->sourceName : $sourceName;

        $queryString = $this->buildQueryString($codeName, $sourceName);
        #print_r($queryString);

        $this->plsqlParsed[$codeName]->SQLCODE = 0;
        $this->plsqlParsed[$codeName]->SQLEERM = "";

        $this->db->multi_query($queryString);

        # Error Handling
        $this->plsqlParsed[$codeName]->LASTSQL = $queryString;
        $this->plsqlParsed[$codeName]->SQLCODE = $this->db->Errno;
        $this->plsqlParsed[$codeName]->SQLERRM = $this->db->Error;

        $this->LAST_SQLCODE = $this->db->Errno;
        $this->LAST_SQLERRM = $this->db->Error;

        if (is_array($this->db->Record)) {
            #var_dump($this->db->Record);
            foreach($this->plsqlParsed[$codeName]->bind as $toBind) {
                $t = trim(str_replace(',','',$toBind));
                if (isset($this->db->Record[$t]) and $this->LAST_SQLCODE === 0)
                    $GLOBALS[$toBind] =  $this->db->Record[$t];
            }
        }

        #Set Error Handling GLOBALS Variables
        if (!isset($GLOBALS['___SQLCODE'])) $GLOBALS['___SQLCODE'] = 0;
        if (!isset($GLOBALS['___SQLERRM'])) $GLOBALS['___SQLERRM'] = "";
        if (!isset($GLOBALS['___LASTSQL'])) $GLOBALS['___LASTSQL'] = "";
        $GLOBALS['___SQLCODE'] =  $this->plsqlParsed[$codeName]->SQLCODE;
        $GLOBALS['___SQLERRM'] =  $this->plsqlParsed[$codeName]->SQLERRM;
        $GLOBALS['___LASTSQL'] =  $this->plsqlParsed[$codeName]->LASTSQL;

        return $this->db->DataSet;
    }


    function sqlParser($currentfile = "", $sourceName = null ){

        # TODO: Allow INCLUDE others embedded code by name
        is_null($sourceName) ? $this->sourceName : $sourceName;

        if (!$currentfile) {
            #$currentfile = RelativePath.PathToCurrentPage.FileName;
            $currentfile = __FILE__;
        }

        $lump = file_get_contents ($currentfile);
        $start_tag = '/*<';
        $end_tag = '>*/';

        // method 2 (faster)
        $n = 0;
        $endpos = strpos($lump, "#-- END PARSER") + 1;
        $startpos = strpos($lump, $start_tag, $endpos) + strlen($start_tag);
        while ($startpos !== false and $endpos !== false) {
            $endpos = strpos($lump, $end_tag, $startpos);
            if ($endpos !== false) {
                $codes[] = substr($lump, $startpos, $endpos - $startpos);
                $endpos = $endpos + strlen($end_tag);
                $startpos = strpos($lump, $start_tag, $endpos) === false ? false : strpos($lump, $start_tag, $endpos) + strlen($start_tag);
            } else {
                $startpos = false;
            }
            $n++;
        }

        if (count($codes) === 0) {
            return;
        }

        foreach($codes as $ind => $code) {

            $head = substr($code, 0, strpos($code, '>'));
            $body = trim(substr($code, strpos($code, ">")+1, strpos($code, '<')-(strpos($code, ">")+1)));

            $s = strtoupper($head);
            $s = explode( ' ', $s);

            $scope = array();
            $scope[0] = $s[0]; #-- Lang ej: PLSQL
            if (isset($s[1])) {
                $scope[1] = $s[1]; #-- tipo
                if ($scope[1] == 'TRIGGER') {
                    $t = explode( ':', $s[2]);
                    #var_dump($t);
                    if (!isset($t[1])) {
                        $scope[2] = 'FORM';
                        $scope[3] = $s[3];
                        $name = $s[3]  ;
                    } else {
                        $block = explode('.', $t[1] );
                        if (!isset($block[1])) {
                            $scope[2] = 'BLOCK';
                            $scope[3]  = $block[0];
                            $scope[4]  = $s[3];
                            $name = $s[3];
                        } else {
                            $scope[2] = 'ITEM';
                            $scope[3] = $t[1];
                            $scope[4] = $s[3];
                            $name = $s[3];
                        }
                    }
                } else if ($scope[1] == 'ANONYMOUS') {
                    $scope[2] = $s[2];
                    #$scope[3] = $s[2];
                    $name = $s[2] ;
                } else if ($scope[1] != 'ANONYMOUS') {
                    $scope[1] = 'ANONYMOUS';
                    $scope[2] = $s[1];
                    $name = $s[1]  ;
                }
            }

            $this->plsqlParsed[$name]= new stdClass();
            $this->plsqlParsed[$name]->SQLCODE = 0;
            $this->plsqlParsed[$name]->SQLERRM = "";
            $this->plsqlParsed[$name]->lastSQL = "";
            $this->plsqlParsed[$name]->originalCode = $start_tag.$code.$end_tag;

            # Admite variables que empiecen por letras y  numeros
            #preg_match_all("/\\:([a-zA-Z0-9_.\:]+)/ise", $body, $arr);

            # Admite variables que empiecen por letras y  numeros pero permite el : como caracter para capturar valores en caso de formatos de horas
            #preg_match_all("/\\:([a-zA-Z0-9_.\:]+)/ise", $body, $arr);

            # No admite variables que empiecen por numeros
            #preg_match_all("/\\:[a-zA-Z_]([a-zA-Z0-9_.]+)/ise", $body, $arr);

            # Ignore bind variables into SQL comments repleacing line comments to ''
            $patron = '~(?:#|\-\- )[^\r\n]*|/\*[\s\S]*?\*/~';
            $tmp_body =preg_replace($patron, '', $body);
            #echo $tmp_body; die;

            # En este caso, en arr[0] aparecen las que comienzan con : pero si estan entre ' ', debe ser omitida
            preg_match_all("/('(?:\\.|[^'])*\\')|\\:([a-zA-Z0-9_.]+)/ise", $tmp_body, $arr);


            if (isset($arr[1])) {
                foreach ($arr[0] as $key => $element) {
                    if (substr($element, 0,1) !== ':') {
                        unset($arr[0][$key]);
                        unset($arr[1][$key]);
                    } else {
                        $arr[1][$key] = substr($arr[0][$key],1);
                    }
                }
                foreach ($arr[1] as $key => $element) {
                    if (strpos($element, ':') !== false) {
                        unset($arr[0][$key]);
                        unset($arr[1][$key]);
                    }
                }
                $arr = array_unique($arr[1]);
                $arr = array_values($arr);
            } else $arr = array();
            #print_r($arr); die;

            $body = str_replace('*\/','*/',$body);
            $body = str_replace('"','\"',$body);
            $this->plsqlParsed[$name]->scope = $scope;
            $this->plsqlParsed[$name]->body = $body;
            $this->plsqlParsed[$name]->bind = $arr;
            #$this->plsqlParsed[$name]->phpCodeToEval = "";

            ## Hay Parsed varibles?

            #------ CREATE EL CODIGO PARA EVAL --------
            $esto = array(chr(10), chr(9), chr(13));
            $porEsto = array("\n","\t"," ");
            $body = trim(str_replace($esto, $porEsto, $body)) ;

            # REGLA:    las variables Bind o Hosting Variables o Parametros, deben ser majejados internamentente
            #           dentro del BLOQUE ANONIMO y es "case-insesitive"
            #

            foreach($arr as $toBind) {
                $t = trim(str_replace(',','',$toBind));
                $body = str_replace(':'.$t,'@'.strtoupper($t),$body);
            }

            $beginText = "BEGIN NOT ATOMIC"."\n"
                #."DECLARE EXIT HANDLER FOR SQLEXCEPTION"."\n"
                #."BEGIN"."\n"
                #."GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE,"."\n"
                #."@errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;"."\n"
                ##."SET @full_error = CONCAT('ERROR ', @errno, ' (', @sqlstate, '): ', @text);"."\n"
                #."SELECT @full_error as ___SQLCODE, @text as ___SQLERRM;"."\n"
                #."END;"."\n"
                ."   -- - Start Bind Variables \n"
                ."   ### BIND \n"
                ."   -- - End Bind Variables \n"
                ."   SET @___autocommit = @@autocommit;\n"
                ."   SET @@autocommit = 0;\n"
                ."   BEGIN \n"
                ."-- -------------------------------------\n"
                ."-- start embedded code\n"
                ."-- -------------------------------------\n";

            $endText = "\n-- -------------------------------------\n"
                ."-- end embedded code\n"
                ."-- -------------------------------------\n"
                ."  ### RESULT \n"
                .   "END; \n"
                .   "COMMIT; \n"
                ."   SET @@autocommit = @___autocommit;\n"
                ."END; -- END OF BEGIN NOT ATOMIC ";
            #    ."-- _STOP-ANONYMOUS_"."\n";

            $queryString = $beginText.$body.$endText;

            $this->plsqlParsed[$name]->queryString = $queryString;

        }

        ## AHORA EL ARRAY SCOPE TIENE TODOS LOS CODIGOS SQL
        ##

    }

    /*** TEST ****
    function multi_query($query) {
        // Change the params to you own here...
        $mysql = new mysqli('localhost', 'system', 'manager', 'test');
        $rs = array();
        if (mysqli_connect_errno())
        {
            die(printf('MySQL Server connection failed: %s', mysqli_connect_error()));
        }

        // Check our query results
        if ($mysql->multi_query($query))
        {
            $show_results = true;
            do {
                // Lets work with the first result set
                if ($result = $mysql->use_result())
                {
                    // Loop the first result set, reading it into an array
                    while ($row = $result->fetch_array(MYSQLI_ASSOC))
                    {
                        $rs[] = $row;
                    }

                    // Close the result set
                    $result->close();
                }
            } while ($mysql->next_result());
        }
        else
        {
            echo '<p>There were problems with your query [' . $query . ']:<br /><strong>Error Code ' . $mysql->errno . ' :: Error Message ' . $mysql->error . '</strong></p>';
        }

        $mysql->close();
        var_dump($rs);

    }
    *****/
}
//End -- END PARSER Class

?>
