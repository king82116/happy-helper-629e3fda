<?php
	session_start();
	if($_SESSION['unohs'] == null){
		header("location:../index.php?msg=unauthorized");
	}
?>
<?php
include '../conn.php'; 

$chain = [];
$searchedId = null; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userId = intval($_POST['user_id']); 
    $searchedId = $userId; 

    for ($i = 0; $i < 5; $i++) { 
        $query = $conn->prepare("SELECT id, code FROM shonu_subjects WHERE owncode = (SELECT code FROM shonu_subjects WHERE id = ?)");
        $query->bind_param("i", $userId);
        $query->execute();
        $result = $query->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $userId = $row['id']; 
            $chain[] = $userId;
        } else {
            break; 
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <link rel="stylesheet" href="../css/mobile-responsive.css">
    <title>Upline Chain Finder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 500px;
            margin-top: 50px;
            padding: 30px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .btn-custom {
            background-color: rgb(14, 19, 92);
            color: white;
            width: 100%;
        }
        .btn-custom:hover {
            background-color: rgb(14, 19, 92);
        }
        .result-box {
            background: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container text-center">
    <h2 class="mb-4">🔗 Upline Chain Finder</h2>
    <form method="post">
        <div class="mb-3">
            <input type="number" class="form-control" name="user_id" placeholder="Enter User ID" required>
        </div>
        <button type="submit" class="btn btn-custom">Find Upline</button>
    </form>

    <?php if (!empty($chain)) : ?>
        <div class="mt-4 result-box">
            <h5>Upline Chain:</h5>
            <p>
                <strong>Searched ID:</strong> <?php echo $searchedId; ?> <br>
                <?php echo "<strong>Chain:</strong> " . $searchedId . " ➡️ " . implode(" ➡️ ", $chain); ?>
            </p>
        </div>
    <?php elseif (isset($_POST['user_id'])): ?>
        <div class="mt-4 alert alert-warning">
            No upline found for this user.
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
