<?php

    //Starting the session
    session_start();

    //This checks if the user wants to log out
    if (isset($_POST['logout'])) {
        //This will indicate the logout confirmation will be shown
        $_SESSION['show_logout_confirmation'] = true;
    }

    //This will check for the confirmation and redirect the user to index page
    if (isset($_POST['confirm_logout'])) {
        //To logout we need to destroy the session
        session_destroy();
        //This will redirect to the index page
        header("Location: Index.php");
        //This makes sure that no more code will be executed after the user is redirected 
        exit();
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
        <h1>Log Out</h1>
    </header>

    <nav>
        <ul>
            <li><a href="Homepage.php">Home Page</a></li>
            <li><a href="Reservation.php">Reservation</a></li>
            <li><a href="ReservedBooks.php">Your Reserved Books</a></li>
        </ul>   
    </nav>       
        
    <br>

        <!--Form for logging out -->
        <form method="post" action="">
            <button type="submit" name="logout">Logout</button>
        </form>
    
    

    <!-- Logout confirmation page -->
    <?php
        if (isset($_SESSION['show_logout_confirmation']) && $_SESSION['show_logout_confirmation']) {
            //Button which confirms logout with user
            echo "<p>Are you sure you want to logout?</p>";
            ?>
            <form method="post" action="">
                <button type="submit" name="confirm_logout">Yes, Logout</button>
            </form>
            <?php
            // Reset the session variable
            $_SESSION['show_logout_confirmation'] = false;
        }
    ?>

    <br>

    <footer>
        <p>Larina's Library 2023</p>
    </footer>

</body>
</html>
