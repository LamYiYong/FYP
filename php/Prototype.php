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

// Page & limit for pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

// 🔍 History-based keyword function
function getViewedKeywords($conn, $userId) {
    $sql = "SELECT DISTINCT p.Title FROM viewhistory vh
            JOIN paper p ON vh.PaperID = p.PaperID
            WHERE vh.UserID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $keywords = [];

    while ($row = $result->fetch_assoc()) {
        $title = strtolower($row['Title']);
        foreach (explode(' ', $title) as $word) {
            $cleaned = preg_replace("/[^a-zA-Z]/", '', $word);
            if (strlen($cleaned) > 4) {
                $keywords[] = $conn->real_escape_string($cleaned);
            }
        }
    }

    return array_unique($keywords);
}

// 🧠 Fetch suggested papers based on history
$historySuggestions = [];
if (!empty($_SESSION['UserID'])) {
    $keywords = getViewedKeywords($conn, $_SESSION['UserID']);
    if (!empty($keywords)) {
        $likeConditions = implode(" OR ", array_map(fn($k) => "Title LIKE '%$k%'", $keywords));
        $sql = "SELECT PaperID, Title, Source FROM paper WHERE $likeConditions LIMIT 5";
        $historySuggestions = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
    }
}

// 📥 Handle paper view logging
if (isset($_POST['log_view']) && isset($_POST['view_url']) && isset($_SESSION['UserID'])) {
    $userId = $_SESSION['UserID'];
    $title = $_POST['view_title'];
    $date = $_POST['view_date'];
    $paperUrl = $_POST['view_url'];

    if (!empty($paperUrl)) {
        $paperId = md5($paperUrl);
        $checkPaper = $conn->prepare("SELECT 1 FROM paper WHERE PaperID = ?");
        $checkPaper->bind_param("s", $paperId);
        $checkPaper->execute();
        $result = $checkPaper->get_result();

        if ($result->num_rows === 0) {
            $insertPaper = $conn->prepare("INSERT INTO paper (PaperID, Title, Source) VALUES (?, ?, ?)");
            $insertPaper->bind_param("sss", $paperId, $title, $paperUrl);
            $insertPaper->execute();
        }

        $insertView = $conn->prepare("INSERT INTO viewhistory (UserID, PaperID, ViewDate) VALUES (?, ?, ?)");
        $insertView->bind_param("iss", $userId, $paperId, $date);
        $insertView->execute();

        header("Location: " . $paperUrl);
        exit;
    } else {
        echo "<script>alert('Paper URL is missing. Cannot log view.'); window.location.href='Prototype.php';</script>";
        exit;
    }
}

// 🔎 Search logic
$output = null;
$papers = [];
$sort_by = $_POST['sort_by'] ?? '';
$sort_order = $_POST['sort_order'] ?? 'desc';
$search_query = $_POST['search'] ?? $_GET['search'] ?? '';

if (!empty($search_query)) {
    $escaped = escapeshellarg($search_query);
    $command = "python search.py $escaped $offset $limit";
    $output = shell_exec($command);

    if ($output !== null) {
        $papers = json_decode($output, true);

        if (!empty($papers) && is_array($papers)) {
            switch ($sort_by) {
                case 'year':
                    usort($papers, fn($a, $b) => $sort_order === 'asc' ? ($a['year'] ?? 0) <=> ($b['year'] ?? 0) : ($b['year'] ?? 0) <=> ($a['year'] ?? 0));
                    break;
                case 'popularity':
                    usort($papers, fn($a, $b) => $sort_order === 'asc' ? ($a['num_citations'] ?? 0) <=> ($b['num_citations'] ?? 0) : ($b['num_citations'] ?? 0) <=> ($a['num_citations'] ?? 0));
                    break;
                case 'author':
                    usort($papers, function ($a, $b) use ($sort_order) {
                        $authorA = is_array($a['authors']) ? ($a['authors'][0] ?? '') : $a['authors'];
                        $authorB = is_array($b['authors']) ? ($b['authors'][0] ?? '') : $b['authors'];
                        return $sort_order === 'asc' ? strcmp($authorA, $authorB) : strcmp($authorB, $authorA);
                    });
                    break;
            }
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

  <!-- 🔁 History-Based Recommendations -->
  <?php if (!empty($historySuggestions)): ?>
  <div class="papers-list">
    <h2>📚 Based on Your History: Suggested Papers</h2>
    <?php foreach ($historySuggestions as $paper): ?>
      <div class="paper-item">
        <div class="paper-title"><?= htmlspecialchars($paper['Title']) ?></div>
        <div class="paper-details">
          <strong>Source:</strong> <a href="<?= htmlspecialchars($paper['Source']) ?>" target="_blank"><?= htmlspecialchars($paper['Source']) ?></a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- 🔍 Search Form -->
  <form method="post" class="input-section">
    <input type="text" name="search" placeholder="Enter research topic..." value="<?= htmlspecialchars($search_query) ?>" required>
    <select name="sort_by" style="margin-left:10px; padding:0.5rem;">
      <option value="">Sort By</option>
      <option value="year" <?= ($sort_by === 'year' ? 'selected' : '') ?>>Publish Year</option>
      <option value="popularity" <?= ($sort_by === 'popularity' ? 'selected' : '') ?>>Popularity (Citations)</option>
      <option value="author" <?= ($sort_by === 'author' ? 'selected' : '') ?>>Prominent Author</option>
    </select>
    <select name="sort_order" style="margin-left:10px; padding:0.5rem;">
      <option value="desc" <?= ($sort_order === 'desc' ? 'selected' : '') ?>>↓ Descending</option>
      <option value="asc" <?= ($sort_order === 'asc' ? 'selected' : '') ?>>↑ Ascending</option>
    </select>
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
          <?php if ($sort_by): ?>
            (Sorted by <?= htmlspecialchars(ucwords(str_replace('_', ' ', $sort_by))) ?> <?= $sort_order === 'asc' ? '↑' : '↓' ?>)
          <?php endif; ?>
        </h2>
        <?php foreach ($papers as $paper): ?>
          <div class="paper-item">
            <div class="paper-title"><?= htmlspecialchars($paper['title']) ?></div>
            <div class="paper-details">
              <strong>Authors:</strong> <?= htmlspecialchars(is_array($paper['authors']) ? implode(', ', $paper['authors']) : $paper['authors']) ?><br>
              <strong>Year:</strong> <?= htmlspecialchars($paper['year']) ?><br>
              <strong>Citations:</strong> <?= htmlspecialchars($paper['num_citations'] ?? 0) ?><br><br>
              <?php if (!empty($paper['abstract'])): ?>
                <strong>Abstract:</strong> <?= htmlspecialchars($paper['abstract']) ?><br><br>
                <button class="summarize-btn" onclick="summarizeAbstract(this)">Summarize</button>
                <p class="abstract-text" style="display:none;"><?= htmlspecialchars($paper['abstract']) ?></p>
                <p class="summary-text" style="display:none; margin-top: 5px;"></p>
              <?php endif; ?>
              <?php if (!empty($paper['url'])): ?>
                <form method="post" action="Prototype.php" target="_blank" style="display:inline;">
                  <input type="hidden" name="view_url" value="<?= htmlspecialchars($paper['url']) ?>">
                  <input type="hidden" name="view_title" value="<?= htmlspecialchars($paper['title']) ?>">
                  <input type="hidden" name="view_date" value="<?= date('Y-m-d') ?>">
                  <button type="submit" name="log_view" class="summarize-btn">View Paper</button>
                </form>
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

<script>
function summarizeAbstract(button) {
    const container = button.parentElement;
    const abstract = container.querySelector('.abstract-text').textContent;
    const summaryText = container.querySelector('.summary-text');
    summaryText.innerText = 'Summarizing...';
    summaryText.style.display = 'block';

    fetch('../php/summarize.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ abstract })
    })
    .then(res => res.json())
    .then(data => {
        summaryText.innerText = data.summary;
    })
    .catch(() => {
        summaryText.innerText = 'Error summarizing.';
    });
}
</script>
</body>
</html>
