<?php
   /* 
	* @interface    : DbAdapterInterface
	* @package      : DbAdapter
	* @author       : Satyam Kumawat
    * @version      : $id:1.0
    */


interface DbAdapterInterface {

    const EXECEPTION = 'Error Occured ';
	//const AUTO_INSERT = true;
	
	/* FETCH TYPE*/
	const FETCH_LAZY   =1;
	const FETCH_ASSOC = 2;
	const FETCH_NUM   = 3;
	const FETCH_BOTH  = 4;
	const FETCH_OBJ   = 5;
	//const FETCH_BOUND = 6;
	/** FETCH TYPE **/
	
	/*
	 @function  :connect
	 @params    :array config parameters as taken by PDO object
	 @return    :PDO connection object
	*/
	public function connect($config);
	
	/*
	 @function  :select
	 @params    :Array of fields| String of fields | if none give then (*) is set
	 @return    :append select statement in query
	*/
	public function select();
	
	/*
	 @function  :delete
	 @params    :String table name,Array condtions| String conditions
	 @return    :no of affected row by given query
	*/
	public function delete($table,$conditions=NULL);
	
	/*
	 @function  :insert
	 @params    :String table name,Array fields to be inserted,
	             autoInsert(if set to true then direct pass [FORM VALUES ($_REQUEST)] and function will insert required column according to the table structure automatically)
	 @return    :last insert id
	*/
	public function insert($table,$values,$autoInsert=false);
	
	/*
	 @function  :update
	 @params    :String table name,Array fields to be inserted, Array| String of condtions (if condtions in not given then it will search primary key in the given array values and updated the record automatically)
	            
	 @return    :last update record  id
	*/
	public function update($table,$values,$conditions = null);
	
	/*
	 @function  :query
	 @params    :Sting query (any sql statement)
	*/
	public function query();
	
	/*
	 @function  :fetch
	 @params    :null
	 @return    :first affected row
	*/
	public function fetch();
	
	
	/*
	 @function  :fetchAll
	 @params    :null
	 @return    :all affected row
	*/
	public function fetchAll();
	
	/*
	 @function  :where
	 @params    :Array of conditions | String of prepared condtions
	*/
	public function where($cond=null);
	
	/*
	 @function  :orWhere
	 @params    :Array of conditions | String of prepared condtions
	*/
	public function orWhere($cond);
	
	/*
	 @function  :where
	 @params    :Array of orderBy | String orderBy
	*/
	public function orderBy($orderBy);
	
	/*
	 @function  :groupBy
	 @params    :Array of groupBy | String of groupBy 
	*/
	public function groupBy($groupBy);
	
	/*
	 @function  :where
	 @params    :String limit
	*/
	public function limit($limit);
	
}

?>
