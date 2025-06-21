<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Chatbot AI</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(to right, #f0f4f8, #dfe9f3);
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px;
      transition: background-color 0.3s ease;
    }

    body.dark {
      background: #121212;
    }

    #chatbox {
      width: 100%;
      max-width: 700px;
      height: 90vh;
      background: white;
      border-radius: 15px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.1);
      display: flex;
      flex-direction: column;
      overflow: hidden;
      transition: background 0.3s ease;
    }

    body.dark #chatbox {
      background: #1e1e1e;
    }

    #chat-header {
      background-color: #007bff;
      color: white;
      padding: 16px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-weight: 600;
    }

    #controls {
      display: flex;
      gap: 10px;
    }

    #controls button {
      background: rgba(255,255,255,0.2);
      border: none;
      color: white;
      padding: 6px 12px;
      border-radius: 6px;
      cursor: pointer;
    }

    #messages {
      flex: 1;
      padding: 20px;
      overflow-y: auto;
      background-color: #f8fafc;
    }

    body.dark #messages {
      background-color: #2a2a2a;
    }

    .message {
      max-width: 80%;
      margin: 10px 0;
      padding: 12px 18px;
      border-radius: 22px;
      line-height: 1.5;
      white-space: pre-wrap;
      word-wrap: break-word;
      position: relative;
    }

    .user {
      align-self: flex-end;
      background-color: #007bff;
      color: white;
      border-bottom-right-radius: 4px;
    }

    .bot {
      align-self: flex-start;
      background-color: #e2f0ff;
      color: #333;
      border-bottom-left-radius: 4px;
    }

    body.dark .bot {
      background-color: #333;
      color: #eee;
    }

    .timestamp {
      font-size: 0.7rem;
      position: absolute;
      bottom: -16px;
      right: 10px;
      opacity: 0.6;
    }

    #input-area {
      display: flex;
      padding: 15px;
      border-top: 1px solid #ddd;
      background-color: #fff;
    }

    body.dark #input-area {
      background-color: #1e1e1e;
    }

    #input {
      flex: 1;
      padding: 12px 18px;
      font-size: 16px;
      border: 1px solid #ccc;
      border-radius: 25px;
      outline: none;
      resize: none;
      font-family: inherit;
      line-height: 1.4;
      overflow-y: hidden;
      min-height: 45px;
      max-height: 200px;
    }

    button {
      margin-left: 10px;
      padding: 12px 24px;
      font-size: 16px;
      background-color: #28a745;
      color: white;
      border: none;
      border-radius: 25px;
      cursor: pointer;
      transition: background-color 0.3s;
    }

    button:hover {
      background-color: #218838;
    }

    @media (max-width: 600px) {
      #chatbox {
        height: 95vh;
        border-radius: 0;
      }
    }

    .typing {
      font-style: italic;
      opacity: 0.7;
    }
  </style>
</head>
<body>
  <div id="chatbox">
    <div id="chat-header">
      🤖 Chatbot AI
      <div id="controls">
        <button onclick="toggleDarkMode()">🌙</button>
        <button onclick="clearChat()">🗑</button>
      </div>
    </div>
    <div id="messages"></div>
    <div id="input-area">
      <textarea id="input" placeholder="Ketik pesan..." rows="1"></textarea>
      <button onclick="sendMessage()">Kirim</button>
    </div>
  </div>

  <script>
    const inputField = document.getElementById('input');
    const messagesContainer = document.getElementById('messages');

    // === Load dark mode ===
    if (localStorage.getItem('darkMode') === 'true') {
      document.body.classList.add('dark');
    }

    // === Load history ===
    window.onload = () => {
      const history = JSON.parse(localStorage.getItem('chatHistory')) || [];
      history.forEach(({ sender, text, time }) => addMessage(sender, text, time));
      scrollToBottom();
    };

    inputField.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
      }
    });

    inputField.addEventListener('input', function () {
      this.style.height = 'auto';
      this.style.height = this.scrollHeight + 'px';
    });

    async function sendMessage() {
      const msg = inputField.value.trim();
      if (!msg) return;

      const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

      addMessage('user', msg, time);
      saveToHistory('user', msg, time);

      inputField.value = '';
      inputField.style.height = 'auto';
      scrollToBottom();

      // Tampilkan "Bot sedang mengetik..."
      const typingMsg = document.createElement('div');
      typingMsg.className = 'message bot typing';
      typingMsg.textContent = 'Bot sedang mengetik...';
      messagesContainer.appendChild(typingMsg);
      scrollToBottom();

      try {
        const response = await fetch('chat.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ message: msg })
        });

        const data = await response.json();
        const botReply = data.reply || '(Tidak ada balasan)';
        messagesContainer.removeChild(typingMsg);
        const replyTime = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        addMessage('bot', botReply, replyTime);
        saveToHistory('bot', botReply, replyTime);
        scrollToBottom();
      } catch (error) {
        messagesContainer.removeChild(typingMsg);
        addMessage('bot', 'Terjadi kesalahan saat mengirim pesan.', time);
        saveToHistory('bot', 'Terjadi kesalahan saat mengirim pesan.', time);
        console.error(error);
      }
    }

    function addMessage(sender, text, time = '') {
      const div = document.createElement('div');
      div.className = 'message ' + sender;
      div.textContent = text;

      if (time) {
        const span = document.createElement('span');
        span.className = 'timestamp';
        span.textContent = time;
        div.appendChild(span);
      }

      messagesContainer.appendChild(div);
    }

    function saveToHistory(sender, text, time) {
      const history = JSON.parse(localStorage.getItem('chatHistory')) || [];
      history.push({ sender, text, time });
      localStorage.setItem('chatHistory', JSON.stringify(history));
    }

    function clearChat() {
      localStorage.removeItem('chatHistory');
      messagesContainer.innerHTML = '';
    }

    function scrollToBottom() {
      messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function toggleDarkMode() {
      const isDark = document.body.classList.toggle('dark');
      localStorage.setItem('darkMode', isDark);
    }
  </script>
</body>
</html>
