<!DOCTYPE html>
<html>
<head>
  <title>Chatbot AI</title>
  <style>
    body { font-family: sans-serif; background: #f9f9f9; padding: 20px; }
    #chatbox { max-width: 600px; margin: auto; background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .message { margin: 10px 0; padding: 10px; border-radius: 6px; }
    .user { background-color: #e0f7fa; text-align: right; }
    .bot { background-color: #f1f8e9; text-align: left; }
    #input { width: 70%; padding: 10px; }
    button { padding: 10px 20px; }
  </style>
</head>
<body>
  <div id="chatbox">
    <div id="messages"></div>
    <input type="text" id="input" placeholder="Tulis pesan..." />
    <button onclick="sendMessage()">Kirim</button>
  </div>

  <script>
    async function sendMessage() {
      const input = document.getElementById('input');
      const msg = input.value;
      if (!msg) return;

      addMessage('user', msg);
      input.value = '';

      const response = await fetch('chat.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: msg })
      });

      const data = await response.json();
      addMessage('bot', data.reply);
    }

    function addMessage(sender, text) {
      const div = document.createElement('div');
      div.className = 'message ' + sender;
      div.textContent = text;
      document.getElementById('messages').appendChild(div);
    }
  </script>
</body>
</html>
