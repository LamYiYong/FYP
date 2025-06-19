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
