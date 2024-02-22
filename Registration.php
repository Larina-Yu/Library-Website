<!DOCTYPE html>
<html>

<head>
   <title>Project</title>
   <link rel="stylesheet" href="Project.css">
</head>

<body>

    <header>
        <img src="libraryheader.jpg" alt="Library Header">
        <h1>Register An Account To Login</h1>
    </header>

    <nav>
        <ul>
            <li><a href="Index.php">Index</a></li>
            <li><a href="Login.php">Login</a></li>
        </ul>
    </nav>

    <br>

    <!--This form is for the user to register an account with the library. All fields are required and there is a confirm password
    input box, that will make sure the passwords match. -->
    <form method="post">
        <p>Username:
            <input type="text" name="UserName"></p>
        <p>Password:
            <input type="Password" name="Password" required></p>
        <p>Confirm Password:
            <input type="Password" name="ConfirmPassword" required></p>
        <p>First Name:
            <input type="text" name="FirstName" required></p>
        <p>Surname:
            <input type="text" name="Surname" required></p>
        <p>Address Line 1:
            <input type="text" name="AddressLine1" required></p>
        <p>Address Line 2:
            <input type="text" name="AddressLine2" required></p>
        <p>City:
            <input type="text" name="City" required></p>
        <p>Telephone Number:
            <input type="text" name="Telephone" required></p>
        <p>Mobile Number:
            <input type="text" name="Mobile" required></p>
        <p><input type="submit" value="Register"/></p>
    </form>

    <?php

    //Connecting to the database
    $dbHost = 'localhost';
    $dbUser = 'root';
    $dbPass = '';
    $dbName = 'LabDb';

    $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

    if ($conn->connect_error) {
        die("Connection Failed:" . $conn->connect_error);
    }

    //This code will do the error checking for the username, password and password length
    if (isset($_POST['UserName']) && isset($_POST['Password']) && isset($_POST['FirstName']) 
        && isset($_POST['Surname']) && isset($_POST['AddressLine1'])  && isset($_POST['AddressLine2'])
        && isset($_POST['City']) && isset($_POST['Telephone']) && isset($_POST['Mobile'])) {
        
        $UserName = $conn->real_escape_string($_POST['UserName']);
        $Password = $conn->real_escape_string($_POST['Password']);
        $ConfirmPassword = $conn->real_escape_string($_POST['ConfirmPassword']);
        $FirstName = $conn->real_escape_string($_POST['FirstName']);
        $Surname = $conn->real_escape_string($_POST['Surname']);
        $AddressLine1 = $conn->real_escape_string($_POST['AddressLine1']);
        $AddressLine2 = $conn->real_escape_string($_POST['AddressLine2']);
        $City = $conn->real_escape_string($_POST['City']);
        $Telephone = $conn->real_escape_string($_POST['Telephone']);
        $Mobile = $conn->real_escape_string($_POST['Mobile']);
        
        //Checks if the password is more than 6 character/numbers, if it is then the error will appear on the web page.
        if (strlen($Password) > 6) {
            echo "Error: Password should not exceed 6 characters/numbers.";
        } elseif ($Password != $ConfirmPassword) {
            //This checks if the passwords match, otherwise the error message will appear, prompting the user to try again.
            echo "Error: Passwords do not match.";
        } else {
            //This checks if the username is already taken, by checking the UsersTable and seeing if the username is taken. If it is the user will be asked to choose a different unique username.
            $checkUsernameSql = "SELECT * FROM UsersTable WHERE UserName = '$UserName'";
            $result = $conn->query($checkUsernameSql);
    
            if ($result->num_rows > 0) {
                echo "Error: Username already taken. Please choose a different username.";
            } else {

                //This inserts the user's data into the database UsersTable
                $insertSql = "INSERT INTO UsersTable (UserName, Password, FirstName, Surname, AddressLine1, AddressLine2, City, Telephone, Mobile) 
                            VALUES ('$UserName', '$Password', '$FirstName', '$Surname', '$AddressLine1', '$AddressLine2', '$City', '$Telephone', '$Mobile')";

                if ($conn->query($insertSql) === TRUE) {
                    echo "Registration Successful";
                    echo "<br>";
                    echo "<a href='Login.php'>Login</a>";
                    exit();
                    //This link will bring the user to the login page, where they will enter their details to access the library.
                } else {
                    echo "Error: " . $insertSql . "<br>" . $conn->error;
                }
            }

        }

    }

    //Shows existing data from UsersTable
    $selectSql = "SELECT * FROM UsersTable";
    $result = $conn->query($selectSql);

    echo "<br>";

    $conn->close();

    ?>

    <br>

    <footer>
        <p>Larina's Library 2023</p>
    </footer>

</body>

</html>
