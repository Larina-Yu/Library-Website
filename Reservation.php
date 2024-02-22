<?php
//Starting the session
session_start();

//Checking if the user is logged in
if (!isset($_SESSION['UserName'])) {
    //This will redirect the user to the login page if they are not logged in
    header("Location: Login.php");
    exit();
}

//This will retrieve the username from the session, so we know which user is using the web page and we can show their reserved books, etc.
$UserName = $_SESSION['UserName'];

//Connecting to the database
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'LabDb';

$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

if ($conn->connect_error) {
    die("Connection Failed:" . $conn->connect_error);
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
        <h1>Reserve a Book</h1>
    </header>

    <nav>    
        <ul>
            <li><a href="Homepage.php">Home Page</a></li>
            <li><a href="Reservation.php">Reservation</a></li>
            <li><a href="ReservedBooks.php">Your Reserved Books</a></li>
            <li><a href="Logout.php">Logout</a></li>
        </ul>
    </nav>

    <br>

    <!--This form makes a search bar on the web page which allows uses to search by author name or the book title -->
    <form method="get">
        <label for="search">Search for a book:</label>
        <input type="text" name="Search" id="search" value="<?php echo isset($_GET['Search']) ? $_GET['Search'] : ''; ?>">
        <input type="submit" value="Search">
    </form>

    <!--This form makes a drop down table where the category descriptions appear. They allow users to filter by these categories. -->
    <form method="get">
        <label for="Filter">Filter by Category:</label>
        <select name="Filter" id="Filter">
            <option value="">All Categories</option>
            <?php
            
            //Retrieving the categories from the CategoryTable and populating the dropdown for the filter
            $categorySql = "SELECT * FROM CategoryTable";
            $categoryResult = $conn->query($categorySql);

            //We are looping through every category to create the options for the drop down table
            while ($categoryRow = $categoryResult->fetch_assoc()) {
                
                //This checks if the current category is the category that has been chosen in the drop down table
                $selected = (isset($_GET['CategoryID']) && $_GET['CategoryDescription'] == $categoryRow['CategoryDescription']) ? 'selected' : '';
                
                //Displays all of the filters(category names) as options in the drop down table
                echo "<option value='{$categoryRow['CategoryDescription']}' $selected>{$categoryRow['CategoryDescription']}</option>";
            }
            ?>
        </select>
        
        <button type="submit">Filter</button>
    </form>

    <?php

    //Show existing data 
    $result = $conn-> query ("SELECT * FROM BooksTable");

    echo "<br>";

    //Pagination: Making sure that the table rows are broken up into pages of no more than 5 rows of data
    //This code tells us how many books per page. Setting the page default to 1. Calculates the offset for the query based on which page we are currently on
    $BooksPerPage = 5;
    $CurrentPage = isset($_GET['Page']) ? $_GET['Page'] : 1;
    $Offset = ($CurrentPage - 1) * $BooksPerPage;

    //Search Bar: Allows the user to search for an author or book title
    //We are creating the search topic using SQL query, which is based on the user input
    $Search = isset($_GET['Search']) ? $_GET['Search'] : "";
    $SearchTopic = $Search ? "AND BookTitle LIKE '%$Search%' OR Author LIKE '%$Search%'" : '';

    //Filter Drop Down Table: Allows the user to filter the books by their categories
    //Using SQl queries we can create the category topic
    $Category = isset($_GET['CategoryDescription']) ? $_GET['CategoryDescription'] : "";
    $CategoryTopic = $Category ? "AND Category = '$Category'" : '';

    //This query will retrieve the books by search and category
    //Result runs the SQL Queries
    $sql = "SELECT * FROM BooksTable WHERE 1 $SearchTopic $CategoryTopic LIMIT $Offset, $BooksPerPage";
    $result = $conn->query($sql);

    //This Sql query retrieves the categories for the filter drop down table
    //The result will run the query
    $sql1 = "SELECT * FROM CategoryTable";
    $result1 = $conn->query($sql1);   

    echo "<br>";     
   
    // Printing the books in a table form
    if ($result->num_rows > 0) {
        echo "<table>";
        echo "<tr>
                <th>ISBN</th>
                <th>Book Title</th>
                <th>Author</th>
                <th>Edition</th>
                <th>Year</th>
                <th>Category</th>
                <th>Reserved</th>
            </tr>";

        // We loop around the rows of the books until all relevant books are shown 
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>" . $row["ISBN"] . "</td>
                    <td>" . $row["BookTitle"] . "</td>
                    <td>" . $row["Author"] . "</td>
                    <td>" . $row["Edition"] . "</td>
                    <td>" . $row["Year"] . "</td>
                    <td>" . $row["CategoryID"] . "</td>
                    <td>"; // New table cell for the reserve button

            // This checks if the book is reserved or not (N OR Y)
            if (isset($row['Reserved']) && $row['Reserved'] == 'N') {
                // If the book is not reserved, a form is displayed to allow the user to reserve the book
                echo "<form action='Reservation.php' method='post'>
                        <input type='hidden' name='ISBN' value='" . $row["ISBN"] . "'>
                        <button type='submit' name='reserve'>Reserve</button>
                    </form>";
            } else {
                // If the book is reserved, the button won't be available, the word reserved will only be printed
                echo "Reserved";
            }

            echo "</td></tr>"; // Close the table cell and row
        }

        echo "</table>";
    }


        
    //This will check is the form has been submitted and the reserve button has been selected
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reserve'])) {
        //Retrieve the ISBN from the from that was submitted
        $ISBN = $conn->real_escape_string($_POST['ISBN']);
        
        //This will get the current date 
        $reservedDate = date('Y-m-d');
    
        //This will check if a book is available to be reserved
        $checkAvailabilitySql = "SELECT * FROM BooksTable WHERE ISBN = '$ISBN' AND Reserved = 'N'";
        $availabilityResult = $conn->query($checkAvailabilitySql);
    
        if ($availabilityResult->num_rows > 0) {
            //If the book is available we will continue with the reservation
            $reserveSql = "UPDATE BooksTable SET Reserved = 'Y' WHERE ISBN = '$ISBN'";

            if ($conn->query($reserveSql) === TRUE) {
                //This will insert the UserName, ISBN and ReservedDate into the ReservedBookTable
                $insertReservedSql = "INSERT INTO ReservedBookTable (UserName, ISBN, ReservedDate) VALUES ('$UserName', '$ISBN', '$reservedDate')";

                if ($conn->query($insertReservedSql) === TRUE) {
                    //If the reservation has been successful, the message will be printed
                    echo "<br>";
                    echo "Reservation Successful";
                } else {
                    //This message will be printed if the reservation was unsuccessful
                    echo "Error recording reservation: " . $conn->error;
                }
            } else {
                //This will be printed if the book was already reserved or unable to be found
                echo "Error reserving the book: " . $conn->error;
            }
        } else {
            // The book is already reserved or not found
            echo "Book is not available for reservation";
        }

    }


    //Calculates the total number of pages using the words that have been searched
    //Result executes the SQL query
    $totalRows = "SELECT COUNT(*) FROM BooksTable WHERE 1 $SearchTopic";
    $totalRowsResult = $conn->query($totalRows);

    //Error checking for the query
    if ($totalRowsResult) {
        //Retrieves how many rows from result
        //Calculates how many pages are needed based on how many rows we have
        $totalRows = $totalRowsResult->fetch_row()[0];
        $totalPages = ceil($totalRows / $BooksPerPage);
    } else {
        echo "Error calculating total number of pages: " . $conn->error;
    }

    // Display pagination links
    echo "<div class = 'Pagination'>";
    //For loop that is uses to create the pagination links, e.g page 1, 2, 3
    for($i =1; $i <= $totalPages; $i++){
        echo "<a href= '?Page=$i&Search=$Search'>$i\t\t</a>";
    }
    echo "</div>";

    // Close the database connection
    $conn->close();

    ?>

    <br>

    <!--This form allows the user to reserve a book using the books ISBN -->
    <form action="Reservation.php" method="post">
        <p>ISBN:
        <label for="ISBN"></label>
        <input type="text" id="ISBN" name="ISBN" required>
        <button type="submit" name="reserve">Reserve</button>
        <a href="Reservation.php">Cancel</a></p>
    </form>

    <br>

    <footer>
        <p>Larina's Library 2023</p>
    </footer>
    
</body>
</html>




