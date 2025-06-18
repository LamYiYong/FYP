<?php
session_start();
$conn = new mysqli("localhost", "root", "", "fyp");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: Login.php");
    exit();
}

if (!isset($_SESSION['UserID'])) {
    header("Location: Login.php");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pdf_url']) && isset($_POST['question'])) {
    $pdfUrl = escapeshellarg($_POST['pdf_url']);
    $question = $_POST['question'];
    $command = "python3 extract_pdf_text.py $pdfUrl";
    $output = shell_exec($command);
    $data = json_decode($output, true);

    if (isset($data['content'])) {
        echo json_encode([
            "prompt" => "PDF:\n" . $data['content'] . "\n\nQuestion:\n" . $question
        ]);
    } else {
        echo json_encode(["error" => "Failed to extract text."]);
    }
    exit;
}

// Page & limit for pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

// 🔎 Search logic
$output = null;
$papers = [];
$sort_by = $_POST['sort_by'] ?? '';
$sort_order = $_POST['sort_order'] ?? 'desc';
$search_query = $_POST['search'] ?? $_GET['search'] ?? '';

if (!empty($search_query)) {
    $escaped = escapeshellarg($search_query);
    $command = "python aggregator.py $escaped 30";
    $output = shell_exec($command);

    if ($output !== null) {
        $papers = json_decode($output, true);

        if (!empty($papers) && is_array($papers)) {
            // ✅ Deduplicate by lowercase title
            $seenTitles = [];
            $papers = array_filter($papers, function($paper) use (&$seenTitles) {
                $titleKey = strtolower(trim($paper['title']));
                if (in_array($titleKey, $seenTitles)) {
                    return false;
                }
                $seenTitles[] = $titleKey;
                return true;
            });

            // ✅ Reindex array
            $papers = array_values($papers);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Home</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/Prototype.css">
  <link rel="stylesheet" href="../css/navbar.css">
</head>
<body>
<?php include 'nav-bar.php' ?>
<div class="container">
  <h1>AI-Driven Research Paper System</h1>
  <div class="about">
    <p>Welcome! This system leverages AI to suggest and summarize research papers based on your interests.</p>
  </div>

  <!-- 🔀 Search Form -->
  <form method="post" class="input-section">
    <input type="text" name="search" placeholder="Enter research topic..." value="<?= htmlspecialchars($search_query) ?>" required>
    <button type="submit">Search</button>
  </form>

  <!-- 📋 Search Results -->
  <div class="papers-list">
    <?php if (!empty($search_query)): ?>
      <?php if (isset($papers['error'])): ?>
        <p style="color:red;">Error: <?= htmlspecialchars($papers['error']) ?></p>
      <?php elseif (empty($papers)): ?>
        <p>No papers found.</p>
      <?php else: ?>
        <h2><?= count($papers) ?> research paper<?= count($papers) > 1 ? 's' : '' ?> found for "<?= htmlspecialchars($search_query) ?>"
        </h2>
      <?php foreach ($papers as $paper): ?>
<div class="paper-item">
  <div class="paper-title"><?= htmlspecialchars($paper['title']) ?></div>
  <div class="paper-details">
    <strong>Authors:</strong> <?= htmlspecialchars(is_array($paper['authors']) ? implode(', ', $paper['authors']) : $paper['authors']) ?><br>
    <strong>Year:</strong> <?= htmlspecialchars($paper['year']) ?><br>
    <strong>Citations:</strong> <?= htmlspecialchars($paper['num_citations'] ?? 0) ?><br><br>
    <strong>Source:</strong> <?= htmlspecialchars($paper['source'] ?? 'N/A') ?><br><br>
    <?php if (!empty($paper['url'])): ?>
      <a href="<?= htmlspecialchars($paper['url']) ?>" target="_blank" class="view-btn">View Paper</a>
    <?php endif; ?>
  </div>
</div>
        <?php endforeach; ?>

        <!-- Pagination -->
        <div style="text-align:center; margin-top: 20px;">
          <?php if ($page > 1): ?>
            <a href="?search=<?= urlencode($search_query) ?>&sort_by=<?= $sort_by ?>&sort_order=<?= $sort_order ?>&page=<?= $page - 1 ?>" class="summarize-btn">← Prev</a>
          <?php endif; ?>
          <a href="?search=<?= urlencode($search_query) ?>&sort_by=<?= $sort_by ?>&sort_order=<?= $sort_order ?>&page=<?= $page + 1 ?>" class="summarize-btn">Next →</a>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
