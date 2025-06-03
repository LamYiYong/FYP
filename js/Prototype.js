

function sendChat() {
    const input = document.getElementById('chat-input');
    const message = input.value.trim();
    if (!message) return;

    const chatBox = document.getElementById('chat-messages');
    chatBox.innerHTML += `<div><strong>You:</strong> ${message}</div>`;

    fetch('chatbot.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message })
    })
    .then(res => res.json())
    .then(data => {
        chatBox.innerHTML += `<div><strong>Bot:</strong> ${data.reply.replace(/\\n/g, '<br>')}</div>`;
        input.value = '';
        chatBox.scrollTop = chatBox.scrollHeight;
    });
}

