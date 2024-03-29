<?php

require_once 'connection.php';

/* Begin a transaction, turning off autocommit */
$dbh->beginTransaction();

/* Change the database schema */
$sth = $dbh->exec('DROP TABLE fruit');

/* Commit the changes */
$dbh->commit();

/* Database connection is now back in autocommit mode */

/*---------------------------*/

/* Begin a transaction, turning off autocommit */
$dbh->beginTransaction();

/* Change the database schema and data */
$sth = $dbh->exec('DROP TABLE fruit');
$sth = $dbh->exec("UPDATE dessert
    SET name = 'hamburger'");

/* Recognize mistake and roll back changes */
$dbh->rollBack();

/* Database connection is now back in autocommit mode */

?>
