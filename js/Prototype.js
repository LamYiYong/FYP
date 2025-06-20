let interval;
let startTime;

function showSpinner() {
    const spinner = document.getElementById("spinnerContainer");
    const timeDisplay = document.getElementById("loadingTime");
    spinner.style.display = "flex";
    startTime = Date.now();
    interval = setInterval(() => {
        const seconds = Math.floor((Date.now() - startTime) / 1000);
        timeDisplay.innerText = `Estimated Time: ${seconds}s`;
    }, 1000);
}

function hideSpinner() {
    const spinner = document.getElementById("spinnerContainer");
    spinner.style.display = "none";
    clearInterval(interval);
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
