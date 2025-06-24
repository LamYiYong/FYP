let countdownInterval;
let remainingSeconds = 30;

function showSpinner() {
    const spinner = document.getElementById("spinnerContainer");
    const timeDisplay = document.getElementById("loadingTime");

    spinner.style.display = "flex";
    remainingSeconds = 30;

    timeDisplay.innerText = `Estimated Time: ${remainingSeconds}s`;

    countdownInterval = setInterval(() => {
        remainingSeconds--;
        if (remainingSeconds <= 0) {
            clearInterval(countdownInterval);
            timeDisplay.innerText = "Still loading...";
        } else {
            timeDisplay.innerText = `Estimated Time: ${remainingSeconds}s`;
        }
    }, 1000);
}
function hideSpinner() {
    const spinner = document.getElementById("spinnerContainer");
    spinner.style.display = "none";
    clearInterval(countdownInterval);
}

document.querySelectorAll(".summarize-btn").forEach(button => {
  button.addEventListener("click", async () => {
    const title = button.dataset.title;
    const output = button.nextElementSibling;
    output.innerText = "Summarizing...";

    try {
      const response = await fetch("../php/chat.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ title })
      });

      const data = await response.json();
      output.innerText = data.summary || "No summary returned.";
    } catch (err) {
      output.innerText = "Error while summarizing.";
    }
  });
});
let currentPaper = {};

function showCitation(paper) {
  currentPaper = paper;
  document.getElementById("citationModal").classList.remove("hidden");
  updateCitation();
}

function updateCitation() {
  const format = document.getElementById("citationFormat").value;
  const { title, authors, year, publisher, url } = currentPaper;

  let citation = "";
  switch (format) {
    case "APA":
      citation = `${authors} (${year}). ${title}. ${publisher}. ${url}`;
      break;
    case "MLA":
      citation = `${authors}. "${title}." ${publisher}, ${year}. ${url}`;
      break;
    case "IEEE":
      citation = `${authors}, "${title}," ${publisher}, ${year}. [Online]. Available: ${url}`;
      break;
  }
  document.getElementById("citationText").innerText = citation;
}

function closeCitationModal() {
  document.getElementById("citationModal").classList.add("hidden");
}

function copyCitation() {
  const text = document.getElementById("citationText").innerText;
  navigator.clipboard.writeText(text).then(() => alert("Citation copied!"));
}

function applyFilters() {
  const yearStart = parseInt(document.getElementById("yearStart").value);
  const yearEnd = parseInt(document.getElementById("yearEnd").value);

  const papers = document.querySelectorAll(".paper-item");

  papers.forEach(paper => {
    const year = parseInt(paper.dataset.year || "0");
    const matchesYear = year >= yearStart && year <= yearEnd;
    paper.style.display = matchesYear ? "block" : "none";
  });
}

function updateYearDisplay() {
  const start = document.getElementById("yearStart").value;
  const end = document.getElementById("yearEnd").value;
  document.getElementById("yearDisplayStart").textContent = start;
  document.getElementById("yearDisplayEnd").textContent = end;
  applyFilters();
}

function filterThisYear() {
  const y = new Date().getFullYear();
  document.getElementById("yearStart").value = y;
  document.getElementById("yearEnd").value = y;
  updateYearDisplay();
}

function filterLastYears(n) {
  const end = new Date().getFullYear();
  const start = end - n + 1;
  document.getElementById("yearStart").value = start;
  document.getElementById("yearEnd").value = end;
  updateYearDisplay();
}