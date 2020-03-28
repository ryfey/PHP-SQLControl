<?php
/**
 * SQLControl Class build 20180101
 * @category    Database Access
 * @package     SQLControl
 * @author      [Rifai Syaban] twitter: @RyFey <rifai_syaban@yahoo.co.id>
 * @copyright   Copyright (c) 2018
 * @license     http://opensource.org/licenses/gpl-3.0.html GNU Public License
 * @link        https://github.com/ryfey/PHP-SQLControl 
 * @version     0.0.1
 */

//check php version
$php_ver = phpversion();
//devine empty variabel
$php7 = null ;
$php56 = null ;
if (version_compare( '7.0.0', $php_ver, '<' ) ) {       
$php7 = TRUE ; 
$php56 = FAlSE ;
} else if (version_compare( '5.6.0', $php_ver, '<' ) ) {
$php7 = FAlSE ;
$php56 = TRUE ;
} else {
die( 
    sprintf( 'Your server is running PHP version %1$s but SQLControl requires at least PHP version %2$s.' , 
    $php_ver, '5.6.0' ));
}

class SQLControl {
    Private $SQLCon = null ;
    Private $IsConnected = 0 ;
    Private $SQLCmd = null ;
    Public $QtyFill = null  ;
    Public $QtyKolom = null  ;
    Public $QtyBaris = null  ;
    Public $QtyTbl = null  ;
    Public $QtyBrUbah = null  ;
    Public $Params = array() ;
    Public $ErrInfo = null ;
    // Private $ErrClass = "Sorry The <b>". __CLASS__ ." Class </b>  can't be call directly" ;
    Private $ErrToSRV = "Tidak terhubung ke Server Database" ;
    Private $ErrToDB = "Tidak dapat terhubung ke Database yang dipilih" ;

    public function __construct(){
        //$this->MakeConn();
    }

    public function __toString(){        
        return $this->ErrClass ;
    }


    public function MakeConn() {
        try {

        // 1. Try Connect ke Server
            $conn = @ new mysqli(DB_HOST.":".DB_PORT, DB_USER, DB_PASSWORD);        
            if ($conn->connect_error) 
                die ("<p>".$this->ErrToSRV .", info masalah : ".$conn->connect_error ."</p>" ); 
            $conn->close();

        // 2. Try Connect ke database
            $conn = @ new mysqli (DB_HOST.":".DB_PORT, DB_USER, DB_PASSWORD,DB_DB) ;        
            if ($conn->connect_error) 
                die ("<p>".$this->ErrToDB .", info masalah : ".$conn->connect_error ."</p>" );
        //$this->SQLCon = $conn ;
        //$conn->close();
            return $conn;         

        } catch (Exception $e) {   
            $this->ErrInfo = $e; 
            $conn->close();  
        }        
    }

    public function HasConn() {
        $conn = @ new mysqli(DB_HOST.":".DB_PORT, DB_USER, DB_PASSWORD) ;
        if ($conn->connect_error){
            $result = ($conn->connect_error);
            $this->ErrInfo = $result;
            if($conn){mysqli_close($conn);}
			//$conn->close() ;
            return FALSE ;
        }
        else{
            if($conn){mysqli_close($conn);}
            return TRUE ;
        }
    }

    public function HasConnDB() {
        $this->ErrInfo = null ;
        $conn = @ new mysqli(DB_HOST.":".DB_PORT, DB_USER, DB_PASSWORD,DB_DB) ;
        if ($conn->connect_error){
            $result = ($conn->connect_error);
            $this->ErrInfo = $result;       
            //$conn->close() ;     
            return FALSE ;
        }
        else{
            //$conn->close() ;
            return TRUE ;
        }
        //$conn->close() ;
    }

    public function HasConnTBL($sqlstr) {
        $this->ErrInfo = null ;
        if ($this->HasConn()==FALSE) {
            return FALSE ;
        }
        else {
            if ($this->HasConnDB()==FAlSE) {
                return FALSE ;
            }
            else {
                $conn = $this->MakeConn() ;
                $result = @ $conn->query($sqlstr);
                if ($result){                     
                        return true;
                    }                
                else{
                    return false; 
                }               
            }
        }        
    }

    public function prm($types, $values = array()) {
        $valCount = count($values);
        // $newParam = array() ;
        // array_push($newParam, $types, $values)  ;
        // $this->Params = new bind_param ;
        $this->Params = array(&$types);
        for ($i=0;$i > $valCount; $i++) {
            $this->Params [] = &$values[$i] ;
        }
    }

    public function tQue(string $sqlstr){
        $this->QtyFill = null ;
        $this->QtyKolom = null ;
        $this->QtyBaris = null ;
        $this->QtyTbl = null ;
        $this->QtyBrUbah = null ;        
        $this->ErrInfo = null ;
        $SQLCon = $this->MakeConn() ;
		if ($result = @ $SQLCon->query($sqlstr) === TRUE) { 
			$this->QtyBrUbah = $result->affected_rows;
			$this->QtyBaris= $result->num_rows ;
			$this->QtyKolom = $result->field_count ;
			$this->Params = null ;
			return $result ;	
		} else {
			$result = $SQLCon->error;
			$this->ErrInfo = $result;
			$this->Params = null ;
		}	
        $SQLCon->close() ;
        
    }

    public function uQue($sqlstr) {
        $this->QtyFill = null ;
        $this->QtyKolom = null ;
        $this->QtyBaris = null ;
        $this->QtyTbl = null ;
        $this->QtyBrUbah = null ;       
        $this->ErrInfo = null ;
        $SQLCon = $this->MakeConn() ; 
		if ($result = @ $SQLCon->query($sqlstr) === TRUE) { 
			$this->QtyBrUbah = $result->affected_rows;
		} else {
			$result = $SQLCon->error;
			$this->ErrInfo = $result;
		}
		$SQLCon->close();            
    }


    function set_params($stmt, $params = array()){
        if ($params != null){
            // Generate the Type String (eg: 'issisd')
            $types = '';
            foreach($params as $param) {
                if(is_int($param)) {
                    // Integer
                    $types .= 'i';
                } elseif (is_float($param)) {
                    // Double
                    $types .= 'd';
                } elseif (is_string($param)) {
                    // String
                    $types .= 's';
                } else {
                    // Blob and Unknown
                    $types .= 'b';
                }
            }
            // Add the Type String as the first Parameter
            $bind_name[] = $types;
            // Loop thru the given Parameters
            for ($i=0; $i<count($params); $i++) {
                // Create a variable Name
                //$bind_name = 'bind' . $i;
                // Add the Parameter to the variable Variable
                $bind_name[] = &$params[$i];                
                // Associate the Variable as an Element in the Array
                //array_push($bind_names, $bind_name) ;
                //$bind_names[] = &$params[$i];
            }
            print_r($bind_name);
            echo "<br>";            
            // Call the Function bind_param with dynamic Parameters
            call_user_func_array(array($stmt,'bind_param'), $bind_name);
        }
        return $stmt;
    }    


    public function rQue($sqlstr) {
        $this->QtyFill = null  ;
        $this->QtyKolom = null  ;
        $this->QtyBaris = null  ;
        $this->QtyTbl = null  ;
        $this->QtyBrUbah = null  ;
        $this->Params = null ;
        $this->ErrInfo = null ;
        
        try {
            $SQLCon = $this->MakeConn() ;        
            $result = @ $SQLCon->query($sqlstr);             
            $this->QtyBaris= $result->num_rows ;
            $this->QtyKolom = $result->field_count ;
            $SQLCon->close() ;
            if ( $result->num_rows > 0) {             
                $results = $result->fetch_assoc();
                return $results;
            }
            else {
                return null;
            }     
        } catch (Exception $e) {            
            $this->ErrInfo = $e;  
        }
    }

    public function sQue($sqlstr, string $types = null, $params = array()) {
        $this->QtyFill = null  ;
        $this->QtyKolom = null  ;
        $this->QtyBaris = null  ;
        $this->QtyTbl = null  ;
        $this->QtyBrUbah = null  ;
        $this->Params = null ;
        $this->ErrInfo = null ;
        
        try {
            $SQLCon = $this->MakeConn() ; 
            if (!$params) {
                $result = @ $SQLCon->query($sqlstr) ;            
                if ($result->num_rows > 0) {            
                    $this->QtyBaris= $result->num_rows ;
                    $this->QtyKolom = $result->field_count ;
                    $results = $result->fetch_row();
                    $data = $results [0] ;
                    $SQLCon->close();
                    return $data;         
                }
                else {
                    $SQLCon->close();
                    // $result = $SQLCon->error;
                    // $this->ErrInfo = $result;            
                    return null;
                }
            } else {
                $conn = $SQLCon;
                /* Prepare a statement */
                $stmt = $conn->prepare($sqlstr);

                /* Bind the parameters to the statement
                s = string
                d = double (float)
                */

                $typeString = $types;
                // $vals = $params ;
                $valCount = count($params);
                echo "<br>".$valCount;
                /* Populate args with references to values */
                $args = array(& $types);
                for ($i=0; $i > $valCount; $i++){
                    $args[] = & $params[$i];
                }

                /* call $stmt->bind_params() using $args as its parameter list */
                call_user_func_array( array($stmt, 'bind_param'), $args);
                
                $stmt->execute();

                /* The result of all savings accounts with a balance > 100.00 */
                $results = $stmt->get_result();
                $data = $results [0] ;
                $SQLCon->close();
                return $data;         
            }
                              
        } catch (Exception $e) {            
            $this->ErrInfo = $e;  
        }
    }

    public function QueryStat() {
        $links = @ new mysqli(DB_HOST.":".DB_PORT, DB_USER, DB_PASSWORD, DB_DB);
        
        /* check connection */
        if ($links->connect_error) {
        $result = ($links->connect_error); 
        return $result ;
        } else {
            $result = $links->stat();
            return $result ;
        }
	}

    Public function __destruct () {
    }

}

?>







