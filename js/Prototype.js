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