<?php
include('conn.php');

// Show all columns in the table
$query = "SHOW COLUMNS FROM shonu_kaichila";
$result = mysqli_query($conn, $query);

echo "<h2>Columns in shonu_kaichila table:</h2>";
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "Column name: " . $row['Field'] . "<br>";
    }
} else {
    echo "Error: " . mysqli_error($conn);
}

// Show sample data
echo "<h2>Sample data:</h2>";
$sample = "SELECT * FROM shonu_kaichila LIMIT 1";
$sample_result = mysqli_query($conn, $sample);

if ($sample_result) {
    $row = mysqli_fetch_assoc($sample_result);
    foreach ($row as $key => $value) {
        echo "$key: $value<br>";
    }
} else {
    echo "Error: " . mysqli_error($conn);
}
?> 