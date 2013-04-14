
 Version : 1.0
 Author  :  Satyam Kumawat <satyam2707@gmail.com>
 ===================================================

 This program is free package
 This program is distributed in the hope that it will be useful,
 This package is based on PHP AND PDO (PHP DATA OBJECT)

 Please Send your suggestion and feedback at satyam2707@gmail.com

=======================
 GENERAL INTRUCTIONS 
=======================

There are three files in this package.
1. DbAdapter.php (class contain all the functions for database operations)
2. DbAdapterInterface.php (Interface having function which should be defined in DbAdapter class. All the function are commented for better understanding)
3. demo.php (It is a demo file which which illustrate how to use this adaptor)


Below are the steps which supposed to be followed for better understanding.
---------------------------------------------------------------------------

1. Include Adaptor Class : <?php require_once 'DbAdapter.php';?>
	

2. Creating Adaptor Object :

	    <?php
    			$connection = array ('dsn'  =>'mysql:dbname=databasename;host=localhost', 
		        'username'  => 'root',
		        'password'     => '',
					   
				);
   		       $dbObj = new DbAdapter($connection);
	    ?>

3. Perform Your First Query :
  
   
  	<?php  //for single row
   		 $dbObj->select(array(*)) 
         	       ->from($table) 
                      ->fetch(); 

         //for All row
          $dbObj->select(array(*)) 
         	       ->from($table) 
                    ->fetchAll(); 

        ?>
4. Please follow demo.php file for all posible operations.

