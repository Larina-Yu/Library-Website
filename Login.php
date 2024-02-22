<?php

//Starting the session
session_start();

//Connecting to the database
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'LabDb';

$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

//
if (isset($_SESSION['UserName'])) {
    header("Location: Homepage.php");
    exit();
}

//Checking if the user is part of the UsersTable, otherwise user is asked to register
if (isset($_POST['UserName'])) {
    $UserName = $_POST['UserName'];
    $Password = $_POST['Password'];

    //Select statement checks if username or password match the ones in the database
    $sql = "SELECT UserName FROM UsersTable WHERE UserName = '$UserName' AND Password = '$Password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['UserName'] = $row['UserName'];
        //If login is successful user will be brought directly to the library homepage
        header("Location: Homepage.php");
        exit();
    } else {
        //Prints invalid username or password if they don't match or are not stored in the UsersTable
        $error = "Invalid username or password";
    }
}


?>

<!DOCTYPE html>
<html>
<head>
    <title>Project</title>
    <link rel="stylesheet" href="Project.css">
</head>
<body> 
    
    <header>
        <img src="libraryheader.jpg" alt="Library Header">
        <h1>Login</h1>
    </header>

    <nav>
        <ul>
            <li><a href="Registration.php">Register</a></li>
            <li><a href="Homepage.php">Home Page</a></li>
            <li><a href="Reservation.php">Reservation</a></li>
            <li><a href="ReservedBooks.php">Your Reserved Books</a></li>
            <li><a href="Logout.php">Logout</a></li>
        </ul>
    </nav>

    <br>
    
    <!--Form for user to input username and password to login to library-->
    <form method="post" action="Login.php">
        <label for="Username">Username:</label>
        <input type="text" name="UserName" required><br>

        <label for="Password">Password:</label>
        <input type="Password" name="Password" required><br>

        <input type="submit" name="login" value="Login">
    </form>

    <br>

    <footer>
        <p>Larina's Library 2023</p>
    </footer>

</body>
</html>
