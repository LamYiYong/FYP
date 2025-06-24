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
$suggestedPapers = [];

if (empty($search_query) && isset($_SESSION['UserID'])) {
    // Get most searched topic by this user
    $userId = $_SESSION['UserID'];
    $res = $conn->query("SELECT Keyword FROM search_logs WHERE UserID = $userId GROUP BY Keyword ORDER BY COUNT(*) DESC LIMIT 1");
    $row = $res->fetch_assoc();
    
    if ($row) {
        $topTopic = $row['Keyword'];
        $api_url = "http://127.0.0.1:5000/aggregate?q=" . urlencode($topTopic);
        $response = file_get_contents($api_url);
        
        if ($response !== false) {
            $suggestedPapers = json_decode($response, true);
            $suggestedPapers = array_slice($suggestedPapers, 0, 5); // limit to top 5
        }
    }
}

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
    if (isset($_SESSION['UserID'])) {
        $keyword = $conn->real_escape_string($search_query);
        $userId = $_SESSION['UserID'];
        $conn->query("INSERT INTO search_logs (UserID, Keyword) VALUES ('$userId', '$keyword')");
    }
    $api_url = "http://127.0.0.1:5000/aggregate?q=" . urlencode($search_query);
    $response = file_get_contents($api_url);

    if ($response !== false) {
        $papers = json_decode($response, true);
        if (!empty($papers) && is_array($papers)) {
            // Deduplicate by lowercase title
            $seenTitles = [];
            $papers = array_filter($papers, function($paper) use (&$seenTitles) {
    if (!isset($paper['title']) || !is_string($paper['title'])) {
        return false;
    }
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
    } else {
        $papers = ['error' => 'Failed to fetch papers from API. Please try again.'];
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
  <?php if (!empty($suggestedPapers)): ?>
  <div class="suggested-box">
    <h2>🔍 Recommended for You</h2>
    <p>Based on your most searched topic: <strong><?= htmlspecialchars($topTopic ?? 'N/A') ?></strong></p>
    <?php foreach ($suggestedPapers as $paper): ?>
      <div class="paper-item">
        <div class="paper-title"><?= htmlspecialchars($paper['title']) ?></div>
        <div class="paper-details">
          <?php if (!empty($paper['year'])): ?>
            <strong>Year:</strong> <?= htmlspecialchars($paper['year']) ?><br>
          <?php endif; ?>
          <?php if (!empty($paper['num_citations'])): ?>
            <strong>Citations:</strong> <?= htmlspecialchars($paper['num_citations']) ?><br>
          <?php endif; ?>
          <br>
          <a href="<?= htmlspecialchars($paper['url']) ?>" target="_blank" class="view-btn">View</a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?><br>

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
              <button type="submit" class="reltopic-btn">
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
        <?php if (!empty($papers)): ?>
  <div class="filter-panel">
    <h3>Filter Papers</h3>
    <div class="filter-group">
  <label>Year Range:</label>
  <input type="range" id="yearStart" min="1931" max="2025" value="1931" oninput="updateYearDisplay()">
  <input type="range" id="yearEnd" min="1931" max="2025" value="2025" oninput="updateYearDisplay()">
  <p style="margin-top: 5px;">From <span id="yearDisplayStart">1931</span> to <span id="yearDisplayEnd">2025</span></p>

  <div class="quick-buttons">
    <button type="button" onclick="filterThisYear()">This year</button>
    <button type="button" onclick="filterLastYears(5)">Last 5 years</button>
    <button type="button" onclick="filterLastYears(10)">Last 10 years</button>
  </div>
</div>
    </div>
<?php endif; ?>
        <?php foreach ($papers as $paper): ?>
          <div class="paper-item"
                data-year="<?= htmlspecialchars($paper['year'] ?? '') ?>">
          <div class="paper-title"><?= htmlspecialchars($paper['title']) ?></div>
            <div class="paper-details">

              <?php if (!empty($paper['authors'])): ?>
                <strong>Authors:</strong> <?= htmlspecialchars(is_array($paper['authors']) ? implode(', ', $paper['authors']) : $paper['authors']) ?><br>
              <?php endif; ?>

              <?php if (!empty($paper['year'])): ?>
                <strong>Year:</strong> <?= htmlspecialchars($paper['year']) ?><br>
              <?php endif; ?>

              <?php if (!empty($paper['num_citations'])): ?>
                <strong>Citations:</strong> <?= htmlspecialchars($paper['num_citations']) ?><br>
              <?php endif; ?>

              <?php if (!empty($paper['abstract'])): ?>
                <br><strong>Abstract:</strong> <?= htmlspecialchars($paper['abstract']) ?><br>
              <?php endif; ?>

              <?php if (!empty($paper['url'])): ?>
                <br>
                <a href="view_logger.php?paper_id=<?= urlencode($paper['url']) ?>&title=<?= urlencode($paper['title']) ?>" target="_blank" class="view-btn">View Paper</a>
                <button class="cite-btn" onclick='showCitation(<?php echo json_encode([
                  "title" => $paper["title"],
                  "authors" => $paper["authors"] ?? "Unknown",
                  "year" => $paper["year"] ?? "n.d.",
                  "publisher" => $paper["publisher"] ?? "Unknown Publisher",
                  "url" => $paper["url"] ?? "#"
                  ]); ?>)'>📚 Cite</button>
                <form method="POST" action="bookmark.php" style="display:inline;">
                  <input type="hidden" name="paper_id" value="<?= htmlspecialchars($paper['url']) ?>">
                  <input type="hidden" name="title" value="<?= htmlspecialchars($paper['title']) ?>">
                  <button class="svpaper-button" type="submit" title="Save to Library">Save</button>
                </form>
              <?php endif; ?>
                  <!-- Citation Modal -->
<div id="citationModal" class="modal hidden">
  <div class="modal-content">
    <span class="close" onclick="closeCitationModal()">&times;</span>
    <h3>Generated Citation</h3>
    <label for="citationFormat">Select Format:</label>
    <select id="citationFormat" onchange="updateCitation()">
      <option value="APA">APA</option>
      <option value="MLA">MLA</option>
      <option value="IEEE">IEEE</option>
    </select>
    <pre id="citationText" style="margin-top: 10px; white-space: pre-wrap;"></pre>
    <button onclick="copyCitation()">📋 Copy</button>
  </div>
</div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
<script src="../js/Prototype.js"></script>
</body>
</html>
