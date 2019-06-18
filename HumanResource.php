<!--Oracle/PHP test file for UBC CPSC 304.
  Created by Jiemin Zhang, 2011.
  Modified by Simona Radu, Raghav Thakur, Ed Knorr, and others.

  This file shows the very basics of how to execute PHP commands
  on Oracle.

  Specifically, it will drop a table, create a table, insert values,
  update values, and perform select queries.

  NOTE:  If you have a table called "tab1", it will be destroyed
         by this sample program.

  The script assumes you already have a server set up.
  All OCI commands are commands to the Oracle libraries.
  To get the file to work, you must place it somewhere where your
  Apache server can run it, and you must rename it to have a ".php"
  extension.  You must also change the username and password on the
  OCILogon below to be your own ORACLE username and password.

  Next, we have some sample HTML code that will appear when you run
  this script.
 -->

<p>If you wish to reset the table, press the reset button.
    If this is the first time that you're running this page,
    you MUST use reset.</p>

<form method="POST" action="HumanResource.php">
    <p><input type="submit" value="Reset" name="reset"></p>
</form>

<h1>Human Resource</h1>
<h4>Insert HR ID and Name into tab below:</h4>
<p>
    HR ID&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    Name
</p>
<form method="POST" action="HumanResource.php">
    <!-- refreshes page when submitted -->

    <p><input type="text" name="hrid" size="18">
        <input type="text" name="hrname" size="24">
        <!-- Define two variables to pass values. -->
        <input type="submit" value="insert" name="inserthr"></p>
</form>


<!-- Create a form to pass the values.
     See below for how to get the values. -->

<h4> Create offer below: </h4>
<p>
    HR ID&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    HM ID&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    Applicant ID&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    Offer ID&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    Job Details
</p>

<form method="POST" action="HumanResource.php">
    <!-- refreshes page when submitted -->

    <p><input type="text" name="ohrid" size="18">
        <input type="text" name="hmid" size="18">
        <input type="text" name="oaid" size="18">
        <input type="text" name="oid" size="18">
        <input type="text" name="jobdetails" size="72">
        <input type="submit" value="insert" name="insertoffer">
        <!-- Define two variables to pass values. -->

    </p>
</form>

<h4> Update offer below: </h4>
<p>
    Offer ID&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    Job Details
</p>
<form method="POST" action="HumanResource.php">
    <p>
        <input type="text" name="oid" size="18">
        <input type="text" name="newjobdetails" size="72">
        <input type="submit" value="update" name="updatesubmit">
    </p>
</form>


<h4> Delete offer below</h4>
<p>
        Offer ID&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
</p>
<form method="POST" action="HumanResource.php">
    <!-- refreshes page when submitted -->

    <p><input type="text" name="Oid" size="18">
        <!-- Define two variables to pass values. -->

        <input type="submit" value="delete" name="deleteOffer"></p>
    </p>
</form>


<html>
<style>
    table {
        width: 20%;
        border: 1px solid black;
    }

    th {
        font-family: Arial, Helvetica, sans-serif;
        font-size: .7em;
        background: #666;
        color: #FFF;
        padding: 2px 6px;
        border-collapse: separate;
        border: 1px solid #000;
    }

    td {
        font-family: Arial, Helvetica, sans-serif;
        font-size: .7em;
        border: 1px solid #DDD;
        color: black;
    }
</style>
</html>


<?php

/* This tells the system that it's no longer just parsing
   HTML; it's now parsing PHP. */

// keep track of errors so it redirects the page only if
// there are no errors
$success = True;
$db_conn = OCILogon("ora_darvey6", "a16444144",
    "dbhost.students.cs.ubc.ca:1522/stu");

function executePlainSQL($cmdstr)
{
    // Take a plain (no bound variables) SQL command and execute it.
    //echo "<br>running ".$cmdstr."<br>";
    global $db_conn, $success;
    $statement = OCIParse($db_conn, $cmdstr);
    // There is a set of comments at the end of the file that
    // describes some of the OCI specific functions and how they work.

    if (!$statement) {
        echo "<br>Cannot parse this command: " . $cmdstr . "<br>";
        $e = OCI_Error($db_conn);
        // For OCIParse errors, pass the connection handle.
        echo htmlentities($e['message']);
        $success = False;
    }

    $r = OCIExecute($statement, OCI_DEFAULT);
    if (!$r) {
        echo "<br>Cannot execute this command: " . $cmdstr . "<br>";
        $e = oci_error($statement);
        // For OCIExecute errors, pass the statement handle.
        echo htmlentities($e['message']);
        $success = False;
    } else {

    }
    return $statement;

}

function executeBoundSQL($cmdstr, $list)
{
    /* Sometimes the same statement will be executed several times.
        Only the value of variables need to be changed.
       In this case, you don't need to create the statement several
        times.  Using bind variables can make the statement be shared
        and just parsed once.
        This is also very useful in protecting against SQL injection
        attacks.  See the sample code below for how this function is
        used. */

    global $db_conn, $success;
    $statement = OCIParse($db_conn, $cmdstr);

    if (!$statement) {
        echo "<br>Cannot parse this command: " . $cmdstr . "<br>";
        $e = OCI_Error($db_conn);
        echo htmlentities($e['message']);
        $success = False;
    }

    foreach ($list as $tuple) {
        foreach ($tuple as $bind => $val) {
            //echo $val;
            //echo "<br>".$bind."<br>";
            OCIBindByName($statement, $bind, $val);
            unset ($val); // Make sure you do not remove this.
            // Otherwise, $val will remain in an
            // array object wrapper which will not
            // be recognized by Oracle as a proper
            // datatype.
        }
        $r = OCIExecute($statement, OCI_DEFAULT);
        if (!$r) {
            echo "<br>Cannot execute this command: " . $cmdstr . "<br>";
            $e = OCI_Error($statement);
            // For OCIExecute errors pass the statement handle
            echo htmlentities($e['message']);
            echo "<br>";
            $success = False;
        }
    }

}

function printResult($result)
{ //prints results from a select statement
    echo "<br>Got data from table tab1:<br>";
    echo "<table>";
    echo "<tr><th>ID</th><th>Name</th></tr>";

    while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
        echo "<tr><td>" . $row["NID"] . "</td><td>" . $row["NAME"] . "</td></tr>"; //or just use "echo $row[0]"
    }
    echo "</table>";
}


/*
Function printTable created by Raghav Thakur on 2018-11-15.

Input:  takes in a result returned from your SQL query and an array of
        strings of the column names
Output: prints an HTML table of the results returned from your SQL query.

printTable is an easy way to iteratively print the columns of a table,
instead of having to manually print out each column which can be
cumbersome and lead to duplicate code all over the place.

If you will be making calls to printTable multiple times and intend to
use it for multiple php files, please do the following:

Step 1) Create a new php file and copy the printTable function and the
        associated HTML styling code into the file you created, give
        this file a meaningful name such as 'print-table.php'.
        (Search for "style" above.)

Step 2) In whichever file you want to use the printTable function,
        assuming this file also contains the server code to communicate
        with the database:  Type in "include 'print-table.php'" without
        double quotes.  If the file in which you want to use printTable
        is not in the root directory, you'll need to specify the path of
        root directory where 'print-table.php' is.  As an example:
        "include '../print-table.php'" without double quotes.

Step 3) You can now make calls to the printTable function without
        needing to redeclare it in your current file.

Note:  You can move all the server code into a separate file called
       'server.php' in a similar way, except whichever file needs to
       use the server code needs to have "require 'server.php'" without
       double quotes.  So, you might have something like what's shown
       below in each file:

require 'server.php';
require 'print-table.php'

Using printTable as an example:

Note: PHP uses '$' to declare variables

$result = executePlainSQL("SELECT CUST_ID, NAME, PHONE_NUM FROM CUSTOMERS");

$columnNames = array("Customer ID", "Name", "Phone Number");
printTable($result, $columnNames); // this will print the table
                                   // in the current webpage

*/

function printTable($resultFromSQL, $namesOfColumnsArray)
{
    echo "<br>Here is the output, nicely formatted:<br>";
    echo "<table>";
    echo "<tr>";
    // iterate through the array and print the string contents
    foreach ($namesOfColumnsArray as $name) {
        echo "<th>$name</th>";
    }
    echo "</tr>";

    while ($row = OCI_Fetch_Array($resultFromSQL, OCI_BOTH)) {
        echo "<tr>";
        $string = "";

        // iterates through the results returned from SQL query and
        // creates the contents of the table
        for ($i = 0; $i < sizeof($namesOfColumnsArray); $i++) {
            $string .= "<td>" . $row["$i"] . "</td>";
        }
        echo $string;
        echo "</tr>";
    }
    echo "</table>";
}


// Connect Oracle...
if ($db_conn) {

    if (array_key_exists('reset', $_POST)) {
        // Drop old table...
        echo "<br> dropping table <br>";
        executePlainSQL("Drop table Offer");

        echo "<br> dropping table <br>";
        executePlainSQL("Drop table HR");


        // Create new table...
        echo "<br> creating new table <br>";
        executePlainSQL("create table HR (HRid number,
                                                 HRname varchar2(30), 
                                                 primary key (HRid))");
        OCICommit($db_conn);

        echo "<br> creating new table <br>";
        executePlainSQL("create table Offer (HRid number, 
                                                    HMid number,
                                                    Aid number,
                                                    Oid number,
                                                    Jobdetails varchar2(80), 
                                                    primary key (HRid, Oid),
                                                    foreign key (HRid) references HR on delete cascade)");
        OCICommit($db_conn);



    } else
        if (array_key_exists('inserthr', $_POST)) {
            // Get values from the user and insert data into
            // the table.
            $tuple = array(
                ":bind1" => $_POST['hrid'],
                ":bind2" => $_POST['hrname'],
            );
            $alltuples = array(
                $tuple
            );
            executeBoundSQL("insert into HR values (:bind1, :bind2)", $alltuples);
            OCICommit($db_conn);

        } else
            if (array_key_exists('updatesubmit', $_POST)) {
                // Update tuple using data from user
                $tuple = array(
                    ":bind1" => $_POST['oid'],
                    ":bind2" => $_POST['newjobdetails'],
                );
                $alltuples = array(
                    $tuple
                );
                executeBoundSQL("update Offer set Jobdetails=:bind2 where Oid=:bind1", $alltuples);

                OCICommit($db_conn);

            } else
                if (array_key_exists('deleteOffer', $_POST)) {
                    // Update tuple using data from user
                    $tuple = array(
                        ":bind1" => $_POST['Oid'],
                    );
                    $alltuples = array(
                        $tuple
                    );
                    executeBoundSQL("delete from Offer where Oid=:bind1", $alltuples);

                    OCICommit($db_conn);
                } else
                        if (array_key_exists('insertoffer', $_POST)) {
                            // Get values from the user and insert data into
                            // the table.
                            $tuple = array(
                                ":bind1" => $_POST['ohrid'],
                                ":bind2" => $_POST['hmid'],
                                ":bind3" => $_POST['oaid'],
                                ":bind4" => $_POST['oid'],
                                ":bind5" => $_POST['jobdetails'],
                            );
                            $alltuples = array(
                                $tuple
                            );
                            executeBoundSQL("insert into Offer values (:bind1, :bind2, :bind3, :bind4, :bind5)", $alltuples);
                            OCICommit($db_conn);

                        }


    if ($_POST && $success) {
        //POST-REDIRECT-GET -- See http://en.wikipedia.org/wiki/Post/Redirect/Get
        header("location: HumanResource.php");
    } else {
        // Select data...
        $result = executePlainSQL("select * from HR");
        /*printResult($result);*/
        /* next two lines from Raghav replace previous line */
        $columnNames = array("HR ID#", "HR Name");
        printTable($result, $columnNames);
        $result = executePlainSQL("select * from Offer");
        $columnNames = array("HR ID", "HM ID", "Applicant ID", "Offer ID", "Offer Details");
        printTable($result, $columnNames);
    }


    //Commit to save changes...
    OCILogoff($db_conn);
} else {
    echo "cannot connect";
    $e = OCI_Error(); // For OCILogon errors pass no handle
    echo htmlentities($e['message']);
}

/* OCILogon() allows you to log onto the Oracle database
     The three arguments are the username, password, and database.
     You will need to replace "username" and "password" for this to
     to work.
     all strings that start with "$" are variables; they are created
     implicitly by appearing on the left hand side of an assignment
     statement */
/* OCIParse() Prepares Oracle statement for execution
      The two arguments are the connection and SQL query. */
/* OCIExecute() executes a previously parsed statement
      The two arguments are the statement which is a valid OCI
      statement identifier, and the mode.
      default mode is OCI_COMMIT_ON_SUCCESS. Statement is
      automatically committed after OCIExecute() call when using this
      mode.
      Here we use OCI_DEFAULT. Statement is not committed
      automatically when using this mode. */
/* OCI_Fetch_Array() Returns the next row from the result data as an
     associative or numeric array, or both.
     The two arguments are a valid OCI statement identifier, and an
     optinal second parameter which can be any combination of the
     following constants:

     OCI_BOTH - return an array with both associative and numeric
     indices (the same as OCI_ASSOC + OCI_NUM). This is the default
     behavior.
     OCI_ASSOC - return an associative array (as OCI_Fetch_Assoc()
     works).
     OCI_NUM - return a numeric array, (as OCI_Fetch_Row() works).
     OCI_RETURN_NULLS - create empty elements for the NULL fields.
     OCI_RETURN_LOBS - return the value of a LOB of the descriptor.
     Default mode is OCI_BOTH.  */
?>
