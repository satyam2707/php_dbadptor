<?php
require_once 'DbAdapter.php';
$connection = array ('dsn'            => 'mysql:dbname=databasename;host=localhost', //dsn: data source name
		        	 'username'       => 'root',
		             'password'       => '',
				   
		);

$dbObj = new DbAdapter($connection); //First of all creating an object 
$table = 'book';

/* DEMO EXAMPLES
 *  
 *           //SIMPLE SELECT
 *      1.    $dbObj->select() //or select('*') || select("'field1',field2") || select(array('field1','field2'))
		     ->from($table)   // pass second argument as alias
 *           ->fetch();  will select only one row || fetchAll () for all rows
 * 
 * 
 *            //SELECT WITH WHERE
 *      2.    $dbObj->select() 
		     ->from($table)
 *           ->where("field = 'value'") // or  where(array('field'=>'value')) // Always put ANd between multiple condtions if array is passed
 *           ->fetchAll();
 *     
 *            //SELECT WITH FETCH MODE
 *      3.    $dbObj->select(array('field1','field2')) 
		     ->from($table)
 *           ->setFetchMode('FETCH_OBJ') // set fetch mode for current operation see all AVAILABLE FETCH MODES  in (INTERFACE DbAdapterInterface)
 *           ->where(array('field'=>'value'))
 *           ->fetchAll();
 *     
 *            //INSERT A RECORD MANUALLY
 *      4.    $dbObj->insert($table,array('field'=>'field_value'));
 *     
 *            //INSERT A RECORD AUTOMATICALLY
 *      5.    $dbObj->insert($table,$_POST,TRUE); // THIRD ARGUMENT SET TO TRUE FOR AUTO INSERT
 * 
 *            //UPDATE A RECORD AUTOMATICALLY
 *      6.    $dbObj->update($table,$_POST); // IF COMPLETE FORM IS POSTED WITH HIDDEN PRIMARY KEY THEN IT WILL AUTO UPDATE
 *   
 *      7.    //UPDATE A RECORD MANUALLY
 *            $dbObj->update($table,array('field'=>'field_value'),array('condition'=>'value')); 
 *      
 *      8.   //DELETE RECORS
 *           $dbObj->delete($table,array('key'=>'value'));
 * 
 *          //Query
 *      9.    $dbObj->query("SELECT * FROM $table")->fetch();
 * 
 * 
 *          //GROUP BY,ORDER BY ,LIMIT
 *      10.   $dbObj->select() 
		     ->from($table)
 *           ->orderBy('field1,field1')  // or  orderBy(array('field1','field2'))
			 ->groupBy('field1')        // or  groupBy(array('field1','field2'))
 *           ->limit('1') or limit('0,10')
 *           ->fetchAll();
 * 
 *           //JOIN (join type,table,join conditions)
 *      11.   $dbObj->select(array('field1','field2')) 
		     ->from(table1,'tbl1')
			 ->join('left','table2 as tbl2','tbl1.id = tbl2.someid')
			 ->join('left','table3 as tbl3','tbl2.someid = tbl3.someid')
 *           ->fetchAll();
 * 
 *         
 *      12.  //OR WHERE
 *            $dbObj->select('*') 
		     		->from(table1,'tbl1')
 *                  ->orWhere(array('field1'=>'value','field2'=>'value'))  // Always put OR between multiple condtions if array is passed
 *           		->fetchAll();
 *          
 *      13.  //MIX OF ORWHERE AND WHERE
 *             $dbObj->select('*') 
		     		->from(table1,'tbl1')
 *                  ->where(array('field1'=>'value','field2'=>'value'))
 *                  ->orWhere(array('field1'=>'value','field2'=>'value'))  // ALWAYS SHOULD BE USED AFTER WHERE OR STATND ALONE
 *           		->fetchAll();
 * 
 *            if orWhere is used with where then it will just put OR between two statement 
 *            The syntex will create syntex below :
 *            SELECT * FROM {table} where (table.field1 ='value' AND table.field1 ='value' ) OR (table.field1 ='value' AND table.field1 ='value' )
 *     
 *       14. OPERATOR USE
 *          	(A) <> OR != :- NOT EQUAL
 *          	(B) <  :- LESS THAN
 *          	(C) >  :- GREATOR THAN
 *          	(D) >= : GREATOR THAN EQUAL
 *          	(E) <= : LESS THAN EQUAL 
 *         
 * 
 * 
 */ 		
 

   $data =   $dbObj->select() 
		     		->from($table)
				   ->fetchAll();
            
   echo '<prE>';
   print_r($data);
?>
