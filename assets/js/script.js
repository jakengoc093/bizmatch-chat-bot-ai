document.addEventListener('DOMContentLoaded', () => {
    const launcher = document.getElementById('bizmatch-chat-launcher');
    const box = document.getElementById('bizmatch-chat-box');
    const closeBtn = document.getElementById('bizmatch-chat-close');
    const sendBtn = document.getElementById('bizmatch-chat-send');
    const input = document.getElementById('bizmatch-chat-input');
    const messagesContainer = document.getElementById('bizmatch-chat-messages');

    const STORAGE_KEY = 'bizmatch_chat_history';
    const EXPIRE_DAYS = 7;

    function saveHistory(history) {
        if (history.length > 20) {
            history = history.slice(-20);
        }
        const data = {
            timestamp: new Date().getTime(),
            messages: history
        };
        localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
    }

    function loadHistory() {
        const saved = localStorage.getItem(STORAGE_KEY);
        if (!saved) return [];

        try {
            const data = JSON.parse(saved);
            const now = new Date().getTime();
            const daysPassed = (now - data.timestamp) / (1000 * 60 * 60 * 24);

            if (daysPassed > EXPIRE_DAYS) {
                localStorage.removeItem(STORAGE_KEY);
                return [];
            }
            return data.messages || [];
        } catch (e) {
            return [];
        }
    }

    if (typeof BizmatchBotData !== 'undefined' && BizmatchBotData.position === 'left') {
        launcher.classList.add('pos-left');
        box.classList.add('pos-left');
    }

    let chatHistory = loadHistory();

    // Tải lịch sử chat và tự động cuộn xuống cuối
    if (chatHistory.length > 0 && messagesContainer) {
        messagesContainer.innerHTML = '';
        chatHistory.forEach(msg => {
            appendMessageUI(msg.text, msg.type);
        });
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    // Hàm mở/đóng khung chat & TỰ ĐỘNG CUỘN XUỐNG CUỐI KHI MỞ
    const toggleChat = () => {
        const isHidden = box.style.display === 'none' || box.style.display === '';
        box.style.display = isHidden ? 'flex' : 'none';

        // Nếu vừa mở khung chat -> cuộn ngay xuống tin nhắn dưới cùng
        if (isHidden && messagesContainer) {
            setTimeout(() => {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }, 50);
        }
    };

    launcher.addEventListener('click', toggleChat);
    closeBtn.addEventListener('click', toggleChat);

    function formatMessageContent(text) {
        if (!text) return '';

        let safeText = text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;");

        safeText = safeText.replace(/\[([^\]]+)\]\((https?:\/\/[^\s]+)\)/g, '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>');

        const urlPattern = /(?<!href=")(https?:\/\/[^\s<]+)/g;
        safeText = safeText.replace(urlPattern, '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>');

        return safeText.replace(/\n/g, '<br>');
    }

    function appendMessageUI(text, type) {
        const msgDiv = document.createElement('div');
        msgDiv.className = `b-msg ${type}`;

        if (type === 'user') {
            msgDiv.innerText = text;
        } else {
            msgDiv.innerHTML = formatMessageContent(text);
        }

        if (messagesContainer) {
            messagesContainer.appendChild(msgDiv);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
        return msgDiv;
    }

    const sendMessage = async () => {
        const text = input.value.trim();
        if (!text) return;

        // Khóa input và nút gửi để chống người dùng spam click nhiều lần
        input.disabled = true;
        sendBtn.disabled = true;

        appendMessageUI(text, 'user');
        chatHistory.push({ text: text, type: 'user' });
        saveHistory(chatHistory);

        input.value = '';

        const botMsg = appendMessageUI('Đang suy nghĩ...', 'bot');

        try {
            const response = await fetch(BizmatchBotData.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': BizmatchBotData.nonce
                },
                body: JSON.stringify({ message: text })
            });

            const data = await response.json();

            if (data.reply) {
                botMsg.innerHTML = formatMessageContent(data.reply);
                chatHistory.push({ text: data.reply, type: 'bot' });
                saveHistory(chatHistory);
            } else {
                botMsg.innerText = 'Có lỗi xảy ra.';
            }
        } catch (err) {
            botMsg.innerText = 'Không thể kết nối máy chủ.';
        } finally {
            // Mở lại input và nút gửi sau khi nhận kết quả (hoặc bị lỗi)
            input.disabled = false;
            sendBtn.disabled = false;
            input.focus();
        }

        if (messagesContainer) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    };

    sendBtn.addEventListener('click', sendMessage);
    input.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') sendMessage();
    });
});