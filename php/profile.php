<?php
// Assuming you have a session started and the user is logged in
session_start();
$conn = new mysqli("localhost", "root", "", "fyp");

$userId = $_SESSION['UserID'] ?? 0;

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user details
$userID = $_SESSION['UserID'];
$sql = "SELECT Name, Email FROM users WHERE UserID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "No user found.";
    exit;
}
$stmt->close();

// Dashboard stats
// Total papers viewed
$result = $conn->query("SELECT COUNT(*) as total FROM viewhistory WHERE UserID = $userId");
$viewedCount = $result->fetch_assoc()['total'] ?? 0;

// Total saved papers
$result = $conn->query("SELECT COUNT(*) as total FROM savedpapers WHERE UserID = $userId");
$savedCount = $result->fetch_assoc()['total'] ?? 0;

// Most searched topics
$topTopics = [];
$result = $conn->query("SELECT Keyword, COUNT(*) as total FROM search_logs WHERE UserID = $userId GROUP BY Keyword ORDER BY total DESC LIMIT 5");
while ($row = $result->fetch_assoc()) {
    $topTopics[] = $row;
}

// Topic trend data by month
$trendData = [];
$result = $conn->query("SELECT Keyword, COUNT(*) as total, DATE_FORMAT(SearchedAt, '%Y-%m') as month FROM search_logs WHERE UserID = $userId GROUP BY Keyword, month ORDER BY month ASC");
while ($row = $result->fetch_assoc()) {
    $trendData[] = $row;
}

$chartLabels = [];
$datasets = [];
$keywordMap = [];

foreach ($trendData as $row) {
    $month = $row['month'];
    $keyword = $row['Keyword'];
    $total = (int)$row['total'];

    if (!in_array($month, $chartLabels)) {
        $chartLabels[] = $month;
    }

    if (!isset($keywordMap[$keyword])) {
        $keywordMap[$keyword] = [];
    }
    $keywordMap[$keyword][$month] = $total;
}

$datasetsJS = [];
foreach ($keywordMap as $keyword => $monthCounts) {
    $data = [];
    foreach ($chartLabels as $month) {
        $data[] = $monthCounts[$month] ?? 0;
    }
    $datasetsJS[] = [
        'label' => $keyword,
        'data' => $data,
        'backgroundColor' => sprintf('#%06X', mt_rand(0, 0xFFFFFF))
    ];
}
$result = $conn->query("SELECT COUNT(DISTINCT DATE(ViewedAt)) as active FROM viewhistory WHERE UserID = $userId");
$activeDays = $result->fetch_assoc()['active'] ?? 0;
// Suggested papers based on top topic
$suggestedPapers = [];
if (!empty($topTopics)) {
    $firstTopic = urlencode($topTopics[0]['Keyword']);
    $api_url = "http://127.0.0.1:5000/aggregate?q=$firstTopic";
    $response = file_get_contents($api_url);
    if ($response !== false) {
        $suggestedPapers = json_decode($response, true);
        $suggestedPapers = array_slice($suggestedPapers, 0, 5); // Show top 5
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="../css/navbar.css">
    <link rel="stylesheet" href="../css/Prototype.css">
    <link rel="stylesheet" href="../css/profile.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<?php include 'nav-bar.php' ?>
<h1>&#128100;Profile</h1>
<div class="container">
    <div class="profile-details">
        <p><strong>Username:</strong> <?php echo htmlspecialchars($user['Name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['Email']); ?></p>
        <a href="update_profile.php" class="update-button">Update Profile</a>
    </div>

    <h2 style="margin-top: 2rem;">📊 Your Dashboard</h2>
    <div class="stats-grid">
        <div class="card">
            <h3>Total Views</h3>
            <p><?= $viewedCount ?></p>
        </div>
        <div class="card">
            <h3>Saved Papers</h3>
            <p><?= $savedCount ?></p>
        </div>
        <div class="card">
            <h3>Top Topic</h3>
            <p><?= $topTopics[0]['Keyword'] ?? 'N/A' ?></p>
        </div>
        <div class="card">
            <h3>Active Days</h3>
            <p><?= $activeDays ?></p>
        </div>
    <div class="chart-box">
        <h3>📈 Topic Trends Over Time</h3>
        <canvas id="topicTrendChart"></canvas>
    </div>
</div>

<script>
const ctx = document.getElementById('topicTrendChart');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($chartLabels) ?>,
        datasets: <?= json_encode($datasetsJS) ?>
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top'
            },
            title: {
                display: true,
                text: 'Search Keywords Over Time'
            }
        },
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Month'
                },
                beginAtZero: true
            },
            y: {
                title: {
                    display: true,
                    text: 'Number of Searches'
                },
                beginAtZero: true,
                ticks: {
                    stepSize: 1,
                    precision: 0
                }
            }
        }
    }
});
</script>
</body>
</html>
