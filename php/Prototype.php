<?php
session_start();

// Logout logic
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: Login.php");
    exit();
}

// Ensure the user is logged in
if (!isset($_SESSION['UserID'])) {
    header("Location: Login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/Prototype.css">
</head>
<body>
    <div class="container">
        <h1>AI-Driven Research Paper Suggestion System</h1>
        <div class="about">
            <p>Welcome! This system leverages AI to suggest research papers based on your interests.</p>
        </div>
        <div class="input-section">
            <input type="text" id="searchQuery" placeholder="Enter a topic or field of interest...">
            <button onclick="suggestPapers()">Suggest Papers</button>
        </div>
        <div class="papers-list">
            <h2>Here are some papers you might be interested in:</h2>
            <div id="papers"></div>
        </div>
        <div class="logout">
            
        </div>
    </div>

    <script>
        async function suggestPapers() {
            const query = document.getElementById('searchQuery').value.trim();
            const papersContainer = document.getElementById('papers');
            papersContainer.innerHTML = '';

            if (!query) {
                alert('Please enter a topic or field of interest.');
                return;
            }

            const apiUrl = `https://api.crossref.org/works?query=${encodeURIComponent(query)}&rows=5`;

            try {
                const response = await fetch(apiUrl);
                const data = await response.json();

                if (data.message && data.message.items && data.message.items.length > 0) {
                    data.message.items.forEach(paper => {
                        if (paper.title && paper.title.length > 0) {
                            const paperElement = document.createElement('div');
                            paperElement.className = 'paper-item';

                            const titleElement = document.createElement('div');
                            titleElement.className = 'paper-title';
                            const titleLink = document.createElement('a');
                            titleLink.href = paper.URL;
                            titleLink.target = '_blank';
                            titleLink.textContent = paper.title[0];
                            titleElement.appendChild(titleLink);

                            paperElement.appendChild(titleElement);

                            const detailsElement = document.createElement('div');
                            detailsElement.className = 'paper-details';
                            const publishedDate = paper['published-print'] ? paper['published-print']['date-parts'][0].join('-') : 'N/A';
                            detailsElement.textContent = `Published: ${publishedDate} | DOI: ${paper.DOI}`;
                            paperElement.appendChild(detailsElement);

                            papersContainer.appendChild(paperElement);
                        }
                    });
                } else {
                    papersContainer.innerHTML = '<p>No papers found matching your query.</p>';
                }
            } catch (error) {
                console.error('Error fetching papers:', error);
                papersContainer.innerHTML = '<p>There was an error fetching the papers. Please try again later.</p>';
            }
        }
    </script>
</body>

</html>