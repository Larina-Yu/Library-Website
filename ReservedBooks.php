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
        <h1>Your Reserved Books</h1>
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


    //This code will delete a reservation in the ReservedBookTable
    if(isset($_POST['ISBN'])) {
        //This will retrieve the ISBN that was entered into the form
        $ISBN = $conn->real_escape_string($_POST['ISBN']);

        //This will take the reservation details from the ReservedBookTable using the ISBN
        $selectSql = "SELECT * FROM ReservedBookTable WHERE ISBN='$ISBN'";
        $result = $conn->query($selectSql);

        if($result->num_rows > 0) {
            //This will find the reservation and retrieve the additional information, ReservedDate and UserName
            $reservedDate = $result->fetch_assoc();
            $UserName = $reservedDate['UserName'];
            $ReservedDate = $reservedDate['ReservedDate'];

            //This deletes the reservation from the ReservedBookTable
            $deleteSql = "DELETE FROM ReservedBookTable WHERE ISBN='$ISBN'";
            
            if($conn->query($deleteSql) === TRUE) {
                //If the reservation deletion was successful, this message will print
                echo "<br>";
                echo "Reservation deleted successfully";
                echo "<br>";
                
                //This will update the BooksTable to indicate that the book has not been reserved, N
                $updateBooksSql = "UPDATE BooksTable SET Reserved = 'N' WHERE ISBN = '$ISBN'";
                if ($conn->query($updateBooksSql) === TRUE) {
                    echo "<br>";
                } else {
                    echo "<br>Error updating book status: " . $updateBooksSql. "<br>" . $conn->error;
                }

                echo "<br>";
            } else {
                //This message will appear if the reservation could not be deleted or if there was an error in deleting it
                echo "Error: " . $deleteSql. "<br>" . $conn->error;
            }
        } else {
            //This message will appear if the ISBN does not match one found in the BooksTable
            echo "<br>";
            echo "No Reservation Found";
        }
    }

    //This will show the reserved books to the user, their books
    $UserName = $conn->real_escape_string($UserName);
    $sqlReservedBooks = "SELECT rbt.ISBN, bt.BookTitle, bt.Author, bt.Edition, bt.Year, ct.CategoryDescription, rbt.ReservedDate 
    FROM ReservedBookTable rbt 
    JOIN BooksTable bt ON rbt.ISBN = bt.ISBN 
    LEFT JOIN CategoryTable ct ON bt.CategoryID = ct.CategoryID 
    WHERE rbt.UserName = '$UserName'";

    //Result will execute the SQL query used to show the reserved books
    $resultReservedBooks = $conn->query($sqlReservedBooks);

    echo "<br>";

    //This checks if the SQL query was successful and if the user has any reserved books in the ReservedBookTable
    if ($resultReservedBooks !== false && $resultReservedBooks->num_rows > 0) {
        //Printing books in table form
        echo "<table>";
        echo "<tr>
                <th>ISBN</th>
                <th>Book Title</th>
                <th>Author</th>
                <th>Edition</th>
                <th>Year</th>
                <th>Category</th>
                <th>Reserved Date</th>
            </tr>";

        // //We loop around the rows of the books until all relevant books are shown 
        while ($rowReserved = $resultReservedBooks->fetch_assoc()) {
            echo "<tr>
                    <td>" . $rowReserved["ISBN"] . "</td>
                    <td>" . $rowReserved["BookTitle"] . "</td>
                    <td>" . $rowReserved["Author"] . "</td>
                    <td>" . $rowReserved["Edition"] . "</td>
                    <td>" . $rowReserved["Year"] . "</td>
                    <td>" . $rowReserved["CategoryDescription"] . "</td>
                    <td>" . $rowReserved["ReservedDate"] . "</td>
                </tr>";
        }

        echo "</table>";

    } else {
        echo "<br>";
        //This message will appear if the user has not reserved any books yet
        echo "No reserved books found for the current user.";
    }

    // Show existing data 
    $selectSql = "SELECT * FROM BooksTable";
    $result = $conn->query($selectSql);

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

    <!--This form allows the user to delete a reservation once the ISBN has been entered -->
    <form method="post">
        <p> Delete Reservation:
            <input type="text" id="ISBN" name="ISBN"></p>
        <input type="hidden" name="UserName" value="<?php echo $UserName; ?>">
        <button type="submit">Delete</button>
        <a href="Reservation.php">Cancel</a></p>
    </form>


    <br>
    
    <footer>
        <p>Larina's Library 2023</p>
    </footer>

</body>
</html>
