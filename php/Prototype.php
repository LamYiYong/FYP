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

// 🔎 Search logic
$output = null;
$papers = [];
$sort_by = $_POST['sort_by'] ?? '';
$sort_order = $_POST['sort_order'] ?? 'desc';
$search_query = $_GET['search'] ?? '';
$relatedTopics = [];


function scorePaper($paper, $keywords) {
    $score = 0;
    $text = strtolower($paper['title'] . ' ' . ($paper['abstract'] ?? ''));
    foreach ($keywords as $word) {
        if (strpos($text, $word) !== false) {
            $score++;
        }
    }
    return $score;
}

function getRelatedTopicsAI($query) {
    $escaped = escapeshellarg($query);
    $command = "python3 related_topics.py $escaped";
    $output = shell_exec($command);
    return json_decode($output, true) ?? [];
}

if (!empty($search_query)) {
    $escaped = escapeshellarg($search_query);
    $command = "python aggregator.py $escaped 30";
    $output = shell_exec($command);

    if ($output !== null) {
        $papers = json_decode($output, true);
        if (!empty($papers) && is_array($papers)) {
            // Deduplicate by lowercase title
            $seenTitles = [];
            $papers = array_filter($papers, function($paper) use (&$seenTitles) {
                $titleKey = strtolower(trim($paper['title']));
                if (in_array($titleKey, $seenTitles)) {
                    return false;
                }
                $seenTitles[] = $titleKey;
                return true;
            });

            $papers = array_values($papers);

            $searchWords = explode(" ", strtolower($search_query));
            $extractedKeywordsForDisplay = $searchWords;
            usort($papers, function($a, $b) use ($searchWords) {
                return scorePaper($b, $searchWords) <=> scorePaper($a, $searchWords);
            });

            $relatedTopics = getRelatedTopicsAI($search_query);
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

  <form method="GET" class="input-section" onsubmit="showSpinner()">
    <input type="text" name="search" placeholder="Enter research topic..." value="<?= htmlspecialchars($search_query) ?>" required>
    <button type="submit">Search</button>
  </form>

  <div id="spinnerContainer" class="spinner-container">
      <div class="spinner"></div>
    </div>
  <div id="loadingTime" class="loading-time"></div>

  <?php if (!empty($relatedTopics)): ?>
    <div class="related-box" style="margin-top: 10px;">
      <strong>🧠 Related Topics:</strong>
      <ul style="margin-top: 5px;">
        <?php foreach ($relatedTopics as $topic): ?>
          <li style="display: inline-block; margin: 5px;">
            <form method="get" style="display:inline;" onsubmit="showSpinner()">
              <input type="hidden" name="search" value="<?= htmlspecialchars($topic) ?>">
              <button type="submit" style="border:none; background:#eee; padding:5px 10px; border-radius:5px; cursor:pointer;">
                <?= htmlspecialchars($topic) ?>
              </button>
            </form>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div class="papers-list">
    <?php if (!empty($search_query)): ?>
      <?php if (isset($papers['error'])): ?>
        <p style="color:red;">Error: <?= htmlspecialchars($papers['error']) ?></p>
      <?php elseif (empty($papers)): ?>
        <p>No papers found.</p>
      <?php else: ?>
        <h2><?= count($papers) ?> research paper<?= count($papers) > 1 ? 's' : '' ?> found for "<?= htmlspecialchars($search_query) ?>"</h2>
        <?php foreach ($papers as $paper): ?>
          <div class="paper-item">
            <div class="paper-title"><?= htmlspecialchars($paper['title']) ?></div>
            <div class="paper-details">
              <strong>Authors:</strong> <?= htmlspecialchars(is_array($paper['authors']) ? implode(', ', $paper['authors']) : $paper['authors']) ?><br>
              <strong>Year:</strong> <?= htmlspecialchars($paper['year']) ?><br>
              <strong>Citations:</strong> <?= htmlspecialchars($paper['num_citations'] ?? 0) ?><br><br>
              <?php if (!empty($paper['abstract'])): ?>
                <strong>Abstract:</strong> <?= htmlspecialchars($paper['abstract']) ?><br><br>
              <?php endif; ?>
              <?php if (!empty($paper['url'])): ?>
                <a href="view_logger.php?paper_id=<?= urlencode($paper['url']) ?>&title=<?= urlencode($paper['title']) ?>" target="_blank" class="view-btn">View Paper</a>
            <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
</body>
  <script src="../js/Prototype.js"></script>
</html>
