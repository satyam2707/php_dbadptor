<?php
   /* 
	* @class    : DbAdapter
	* @package  : DbAdapter
	* @author   : Satyam Kumawat
    * @version  : $id:1.0
    */

require_once 'DbAdapterInterface.php';
class DbAdapter  implements DbAdapterInterface{
	
	private $select = null;
	private $isConnected = false;
	public  $pdo = null;
	private $fields = array();
	private $table = null;
	private $where = null;
	private $orWhere = null;
	private $orderBy = null;
	private $groupBy = null;
	private $limit = null;
	private $fetchmode = self::FETCH_ASSOC;
	private $reflectionObj = null;
	private $statementObj = null;
	private $tblStructure = null;
	private $primaryKey = null;
	private $join = null;
	
	public function __construct(array $config)
	{
		$this->connect($config);
		
	}
	
	public function connect($config){
		
	  if(!$this->isConnected)
	  {
		  	try{
		  		 $connection = new PDO($config['dsn'],$config['username'],$config['password']);
				 
				 $connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,self::FETCH_ASSOC);
				 $connection->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
				 
				 $this->pdo = $connection;
			     $this->isConnected = true;
		  	} 
			catch (Exception $e){
				 $this->exceptionHandler($e);
				 exit;
		  	}
	 }
	  	 
	  
	  
	}
	public function buildSql(){
	
	  $sql   = $this->select;
	  $sql  .= $this->fields;
	  
	  if(is_null($this->table))
	  {
	  	  throw new AdapterException('Table is missing');
	  }
	  
	  $sql  .= $this->table;
	  
	  if(!is_null($this->join)){
	  	$sql .=$this->join;	
	  }
	  
	  if(!is_null($this->where)){
	 	 $sql  .= $this->where;
	  }
	  
	  if(!is_null($this->orWhere)){
	 	 $sql  .= $this->orWhere;
	  }
	  
	  if(!is_null($this->groupBy)){
	  	$sql  .= $this->groupBy;
	  }
	  
	   if(!is_null($this->orderBy)){
	  	$sql  .= $this->orderBy;
	  }
	   if(!is_null($this->limit)){
	  	$sql  .= $this->limit;
	  }
	  	
	  $this->_destruct();
	  
	  return $sql;
	}

	public function select($fields ='*'){
		
	
	  if(is_null($this->select))	
		$this->select  = 'SELECT ';
	  
	  if(is_array($fields))
	  {
	  	
		$fieldString ="";
		foreach($fields as $key)
		{
			$fieldString .= "`$key`,";
		}
		$fieldString=substr($fieldString,0,-1); 
		$this->fields = $fieldString;
	  }
	  else 
	  {
	  	$this->fields = $fields;
	  }
  
	  return $this;
	  
	}
	
	public function insert($table,$values,$autoInsert = false)
	{
	 	$this->table = $table;
		
		if(!is_array($values))
		{
           throw new AdapterException('INSERT query require Array  ('.gettype($values).') given','general');
           			
		}
		
		$sql  = 'INSERT INTO';
		$sql .= $this->_padString($this->table);
		$sql .= $this->_padString('SET');
		
		if($autoInsert)
		{
			$columns = $this->getTblStructure();
			foreach($values as $key =>$values)
			{
				if(in_array($key, $columns))
				$sql .= "`$key` ='".mysql_real_escape_string($values)."',";
			}
			
		}
		else 
		{
		   	foreach($values as $key =>$values)
			{
				$sql .= "`$key` ='".mysql_real_escape_string($values)."' ,";
			}
		}
		$sql=substr($sql,0,-1); 
		
		if($this->execute($sql))
		{
			return $this->pdo->lastInsertId();
		}
		
			
	}
	
	public function update($table,$values,$conditions = null)
	{
	    $this->table = $table;
		if(!is_array($values))
		{
           throw new AdapterException('Update query require Array  ('.gettype($values).') given','general');
           			
		}
		
		$sql  = 'UPDATE';
		$sql .= $this->_padString($table);
		$sql .= $this->_padString('SET');
		
		if(is_null($conditions))
		{
			$primaryKey = $this->getPrimaryKey();
			$columns = $this->getTblStructure();
			
			if(!($primaryValue = $values["$primaryKey"]))
			{
	           throw new AdapterException("Auto Update  Syntex  Require PRIMARY KEY  ($primaryKey)");
	           			
			}
			$keyCount = 0;
			foreach($values as $key =>$value)
			{
				$keyCount ++;
				
				if($key != $primaryKey && in_array($key, $columns))
				 {
				 	if($keyCount >1 )
					{
						$sql .= ",";
					}
				 	$sql .= "`$key`='".mysql_real_escape_string($value)."'";
				 }
			}
				
			$sql .= $this->_padString("WHERE `$primaryKey` =".$primaryValue);
			
		}
		else 
		{
			$keyCount =0;
		   	foreach($values as $key =>$values)
			{
				$keyCount ++;
				if($keyCount >1 )
				{
					$sql .= ",";
				}
				$sql .= "`$key` ='".mysql_real_escape_string($values)."'";
			}
			
			
			$sql .= $this->_padString("WHERE");
			
			if(is_array($conditions))
			{
				$count =0;
				foreach($conditions as $key =>$val)
				{
					$count ++;
					if($count >1 )
					{
						$sql .= " AND ";
					}
					$sql .= "`$key` ='$val'";
					
					
				}
		
			}
			else {
				$sql .= $condtions;
			}
			
		}
		return $this->execute($sql);
		
	}
	
	public function getPrimaryKey()
	{
	   $sql ="SHOW KEYS FROM ".$this->table." WHERE Key_name = 'PRIMARY'";
	   $keyData = $this->query($sql)->fetch(self::FETCH_ASSOC);
	   $this->primaryKey = $keyData['Column_name'];
	   return $this->primaryKey;
	}
	
	public function getTblStructure()
	{
		 //$this->table = 'book';
	     $sql = $this->_padString('DESCRIBE');
		 $sql .= $this->table;
		 $this->query($sql);	
		 $columns =   $this->query($sql)->fetchAll(PDO::FETCH_COLUMN);
		 $this->tblStructure = $columns;
		 
		 return $columns;
		 
	}
	
	public function from($table,$alias=null){
		
		$this->table = " FROM $table as ".(is_null($alias) ? $table: $alias);
		return $this;
	}
	
	public  function execute($sql){
		
		try{
	   	 return $this->pdo->exec($sql);
	   }
	   catch(PDOException $e)
	   {
	   	 $exception = new AdapterException($e,'query');
		 $exception->throwException();
			
	   }
	}
	
	public function fetch(){
		return  $this->query()->fetch();
	}
	
	public function fetchAll(){
		
		$data = $this->query()->fetchAll();
		return $data;
		
	}
	
	
	public function query($sql=null)
	{
		if(is_null($sql))
		 $sql = $this->buildSql();
	    
	   try{
	   	
	   	 // $this->statementObj =  $this->pdo->query($sql);
		  //return $this;
		 
		  return $this->pdo->query($sql);
		
	   }
	   catch(PDOException $e)
	   {
	   	 new AdapterException($e,'query');
		
			
	   }
	  
	}
	
	public function where($conditions =NULL)
	{
		if(is_null($conditions))
		{
		  return $this;	
		}
		
		$where = $this->_padString("WHERE ( ");
		
		$count = 0;
		if(is_array($conditions))
		{
			foreach($conditions as $key =>$value){
				$count++;
				if($count >1)
				{
					$where .=  $this->_padString("AND");
				}
				
			   if($char = $this->_searchSpecialchar($key))
				{
					$where .= "$key '$value'";
				}
				else{
					$where .= "$key= '$value'";
				}
			}
		}
		else 
		{
			$where .= $conditions;
		}
		$where .=  $this->_padString(")");;
		
		$this->where = $where;
		return $this;
	    
	}
	
	public function orWhere($conditions)
	{
		if(is_null($conditions))
		{
		  return $this;	
		}
		
		$onlyOR = false;
		if(is_null($this->where))
		{
			$where = $this->_padString("WHERE (");
			$onlyOR = true;
		}
		else {
			$where = $this->_padString("OR (");
		}
	    
		
		$count = 0;
		if(is_array($conditions))
		{
			foreach($conditions as $key =>$value)
			{
				$count++;
				$KeyWord = ($onlyOR) ? 'OR' :'AND';
				if($count >1)
				{
					$where .=  $this->_padString($KeyWord);
				}
				
				if($char = $this->_searchSpecialchar($key))
				{
					$where .= "$key '$value'";
				}
				else{
					$where .= "$key= '$value'";
				}
			
			}
		}
		else 
		{
			$where .= $conditions;
		}
		$where .=  $this->_padString(")");;
		
		$this->orWhere = $where;
		return $this;
	}
	
	public function _searchSpecialchar($string)
	{
		if(preg_match("/[<>>=<=!=]{2}|[><]{1}$/",trim($string),$match))
		{
			return $match[0];
		}
		else {
			return false;
		}
		
	}
	public function delete($table,$conditions=NULL)
	{
		return  $this->_delete($table,$conditions);
	   
	}
	
	private function _delete($table,$conditions)
	{
		
		  $sql   = $this->_padString("DELETE FROM",2,'right');
		  $sql  .= $table;
		  
		  if(!is_null($conditions)){
			  $sql  .=  $this->_padString("WHERE");
			  $sql  .= $conditions;
		  }
		 return $this->execute($sql);
	}
   
	public function setFetchMode($mode)
	{
		$this->hasConstant($mode);
		
		$this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,constant("self::$mode"));
		return $this;
	}
	public function join($type='left',$table=null,$joinCond=null)
	{
		if(is_null($table)){
	  	  throw new AdapterException('Join function required second arugment to be (String) table name (Null) given.');
		}
		if(is_null($table)){
			throw new AdapterException('Join function required third arugment to be (String) join conditions  (Null) given.');
		}
		
	   $joinTitle = $this->_padString( strtoupper($type) .' '.'JOIN');
	   $statement =  $this->_padString($joinTitle) .$table .' ON'.$this->_padString($joinCond);
	   if(is_null($this->join)){
	   	
		$this->join =  $statement ;
	   }
	   else {
		   $this->join .= $statement ;
	   }
		
			
		return $this;
	}
	
	public function orderBy($orderBy)
	{
		if(is_array($orderBy))
		{
			  $this->orderBy = $this->_padString('ORDER BY').implode(',',$orderBy);
		}
		else {
			
			$this->orderBy = $this->_padString('ORDER BY'). $orderBy;
		}
		return $this;
	}
	
	public function groupBy($groupBy)
	{
		if(is_array($groupBy))
		{
			$this->groupBy = $this->_padString('GROUP BY').implode(',',$groupBy);
		}
		else{
			
			$this->groupBy = $this->_padString('GROUP BY').$groupBy;
		}
		return $this;
	}
	
	public function limit($limit)
	{
		$this->limit = $this->_padString('limit') .$limit;
		return $this;
	}
	
	private function _padString($input,$pad_length=2,$type=null)
	{
		$pad_length = strlen($input) + $pad_length;
		
		switch ($type) 
		{
			case 'left':
				$string = str_pad($input,$pad_length," ",STR_PAD_LEFT);	
				break;
				
			case 'right':
			    $string = str_pad($input,$pad_length," ",STR_PAD_RIGHT);	
				break;
				
			default:
				 $string = str_pad($input,$pad_length," ",STR_PAD_BOTH);	
				break;
		}	
			
	  return $string;
		
	}
	
	public function setAttribute($attribute,$value)
	{
		if(!defined(@constant("PDO::$attribute")))
		{
			throw new Exception("Error Occured : PDO  attribute ($attribute) does not exists");
		}
		
		$this->pdo->setAttribute(constant("PDO::$attribute"),$value);
		return $this;
	}

	
	private function hasProperty($property)
	{
		if(!$this->reflectionObj)
		{
			$reflect = new ReflectionClass($this);
			$this->reflectionObj = $reflect;
		}
		
		if(!$this->reflectionObj->hasProperty($property))
		{
           throw new Exception(self::EXECEPTION .__CLASS__ ." does not have Property ($property) ");
		}	
		
	}
	private function hasConstant($constant)
	{
		if(!$this->reflectionObj)
		{
			$reflect = new ReflectionClass($this);
			$this->reflectionObj = $reflect;
		}
			
		if(!$this->reflectionObj->hasConstant($constant))
		{
			throw new Exception(self::EXECEPTION .__CLASS__ ." does not have Constant ($constant)");
		}	
		
	}
	private function exceptionHandler($expectionobj)
	{
		
		$message = $expectionobj->getMessage();
		$file    = $expectionobj->getFile();
		$line    = $expectionobj->getLine();
		
		echo self::EXECEPTION.' <br>'.' Message : ' .$message .'<br>'.'File : '.$file.'<br>'.'Line No : '.$line;
	}
	
    /* 
	* @function : __get
	* @use      : call everytime a property is access
    * @return   : property of a class if exists
    * @input    : property_name 
    */
	 
	public function __get($property)
	{
		if(property_exists($this, $property))
		{
			$reflect = new ReflectionProperty($this,$property);
			if($reflect->isPrivate())
			{
				throw new Exception("Propety ($property) is private and not accesible outside the class");
			}
			else{
				return   $this->{$property};
			}
		}else{
	      throw new Exception("Propety ($property) not available in the class");
		  
		}
	}
	public function __call($name,$args)
	{
		if(!method_exists($this, $name))
		{
			throw new Exception("Error Occured : function ($name) not exists");
		}
		else 
	    {
			//call_user_func_array($name, $arga);
		}
	}
	private function _destruct()
	{
	    $this->table  = null;
		$this->select = null;
	    $this->fields = null;
	    $this->where  = null;
	}
	
	


}
    /* 
	* @class    : AdapterException
	* @package  : DbAdapter
	* @author   : Satyam Kumawat
	* @use      : For exception handling 
    * @version  : $id:1.0
    */
	 
    class AdapterException 
	{
		const EXECP_HEADING= 'Error Occured';
		private $exceptionType;
		private $message;
		private $expectionObj;
		
		public function __construct($expectionObj,$type='general')
		{
		 
			$this->exceptionType = $type;
			
			if(is_object($expectionObj))
			{
				$this->expectionObj = $expectionObj;
				$this->prepareExeception()->throwException();;
				
			}
			else{
				
				$this->simpleException($expectionObj)->throwException();	
			}
			
		}
		public function throwException()
		{
			$html  = "<table width='800px' style='border:1px solid black;color:red;'><tr style='font-size:20px;'><td style='
			text-align:center'><b><u>".self::EXECP_HEADING."</u><b></td></tr>";
			
			foreach($this->message as $heading =>$data):
				$html .="<tr><td><b><u>".ucwords($heading)." : </u></b>".$data."</td></tr>";
			endforeach;
			
			$html .='</table>';
			echo $html;
			exit;
		}
		public function prepareExeception()
		{
		   $message = array();
			switch ($this->exceptionType) {
				case 'general':
				
					$message['message']  = $this->expectionObj->getMessage();
					$message['file']     =  $this->expectionObj->getFile();
					$message['line no']  = $this->expectionObj->getLine();
					
					break;
					
				case 'query':
					$message['message']  = $this->expectionObj->getMessage();
					$message['file']     =  $this->expectionObj->getFile();
					$message['line no']  = $this->expectionObj->getLine();
					$trace     = $this->expectionObj->getTrace();
					
					$message['query']  = $trace[0]['args'][0];
					break;
			
				default:
					$message['message']  = $this->expectionObj->getMessage();
					$message['file']     =  $this->expectionObj->getFile();
					$message['line no']  = $this->expectionObj->getLine();
					break;
			}
			
			$this->message = $message;
			return $this;
		}
		
		public function simpleException($msg)
		{
		       $message['message']  = $msg;
			   $this->message = $message;
			   return $this;
		}
		
	}

?>