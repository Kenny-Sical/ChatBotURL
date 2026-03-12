(function () {
    'use strict';

    // ── Elementos del DOM ──────────────────────────────────────────────────────
    const sidebar       = document.getElementById('sidebar');
    const overlay       = document.getElementById('sidebar-overlay');
    const btnToggle     = document.getElementById('btnToggleSidebar');
    const btnNewChat    = document.getElementById('btnNewChat');
    const chatMessages  = document.getElementById('chatMessages');
    const chatEmpty     = document.getElementById('chatEmpty');
    const chatInput     = document.getElementById('chatInput');
    const btnSend       = document.getElementById('btnSend');
    const chatTitle     = document.getElementById('chatTitle');
    const historyList   = document.getElementById('chatHistoryList');
    const historyEmpty  = document.getElementById('historyEmpty');

    const CSRF  = document.querySelector('meta[name="csrf-token"]').content;

    // ── Estado de la sesión ────────────────────────────────────────────────────
    let activeChatId  = null;
    let waitingForBot = false;

    // ── Toggle Sidebar ─────────────────────────────────────────────────────────
    function isMobile() { return window.innerWidth < 768; }

    btnToggle.addEventListener('click', function () {
        sidebar.classList.toggle('collapsed');
        if (isMobile()) {
            overlay.classList.toggle('show', !sidebar.classList.contains('collapsed'));
        }
    });

    overlay.addEventListener('click', function () {
        sidebar.classList.add('collapsed');
        overlay.classList.remove('show');
    });

    // ── Nuevo chat ─────────────────────────────────────────────────────────────
    btnNewChat.addEventListener('click', async function () {
        await startNewChat();
        if (isMobile()) {
            sidebar.classList.add('collapsed');
            overlay.classList.remove('show');
        }
    });

    async function startNewChat() {
        try {
            const res  = await fetchJSON('/chat', 'POST');
            activeChatId = res.chat_id;

            clearMessages();
            chatTitle.textContent  = 'Nueva conversación';
            chatInput.disabled     = false;
            chatInput.focus();

            addHistoryItem(activeChatId, 'Nueva conversación', true);
        } catch {
            alert('No se pudo crear el chat. Intenta de nuevo.');
        }
    }

    // ── Cargar mensajes de un chat del historial ───────────────────────────────
    async function loadChat(chatId) {
        try {
            const res = await fetchJSON('/chat/' + chatId + '/messages', 'GET');

            activeChatId = chatId;
            clearMessages();
            chatTitle.textContent = res.title;

            res.messages.forEach(function (msg) {
                appendMessage(msg.type, msg.message);
            });

            scrollToBottom();
            chatInput.disabled = false;
            chatInput.focus();

            setActiveHistoryItem(chatId);

            if (isMobile()) {
                sidebar.classList.add('collapsed');
                overlay.classList.remove('show');
            }
        } catch {
            alert('No se pudo cargar el chat.');
        }
    }

    // ── Clicks en el historial ─────────────────────────────────────────────────
    historyList.addEventListener('click', function (e) {
        const item = e.target.closest('.chat-history-item');
        if (!item) return;
        const chatId = parseInt(item.dataset.chatId, 10);
        if (chatId && chatId !== activeChatId) loadChat(chatId);
    });

    // ── Auto-resize textarea ───────────────────────────────────────────────────
    chatInput.addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 140) + 'px';
        btnSend.disabled  = this.value.trim() === '' || waitingForBot;
    });

    // ── Enviar con Enter (Shift+Enter = nueva línea) ───────────────────────────
    chatInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            if (!btnSend.disabled) sendMessage();
        }
    });

    btnSend.addEventListener('click', sendMessage);

    // ── Enviar mensaje ─────────────────────────────────────────────────────────
    async function sendMessage() {
        const text = chatInput.value.trim();
        if (!text || waitingForBot) return;

        // Si no hay chat activo, crear uno antes de enviar
        if (!activeChatId) {
            await startNewChat();
        }

        // Mostrar burbuja del usuario de inmediato
        chatEmpty.style.display = 'none';
        appendMessage('user', text);

        // Limpiar input
        chatInput.value        = '';
        chatInput.style.height = 'auto';
        chatInput.disabled     = true;
        btnSend.disabled       = true;
        waitingForBot          = true;

        showTypingIndicator();
        scrollToBottom();

        try {
            const res = await fetchJSON('/chat/' + activeChatId + '/message', 'POST', { message: text });

            // Si el título cambió (primer mensaje), actualizar en UI
            if (res.is_first) {
                chatTitle.textContent = res.chat_title;
                updateHistoryItemTitle(activeChatId, res.chat_title);
            }

            // Mostrar respuesta del bot directamente desde la respuesta HTTP
            removeTypingIndicator();
            waitingForBot = false;
            appendMessage('bot', res.bot_message);
            scrollToBottom();
            chatInput.disabled = false;
            chatInput.focus();
        } catch {
            removeTypingIndicator();
            waitingForBot     = false;
            chatInput.disabled = false;
            showErrorBubble('Error al enviar el mensaje. Verifica tu conexión.');
        }
    }

    // ── Helpers de UI ──────────────────────────────────────────────────────────
    function clearMessages() {
        Array.from(chatMessages.children).forEach(function (el) {
            if (el !== chatEmpty) el.remove();
        });
        chatEmpty.style.display = '';
        waitingForBot = false;
        btnSend.disabled = true;
    }

    function appendMessage(role, content) {
        chatEmpty.style.display = 'none';

        const row = document.createElement('div');
        row.className = 'message-row' + (role === 'user' ? ' user-row' : '');

        const avatar = document.createElement('div');
        avatar.className = 'message-avatar ' + (role === 'user' ? 'user-avatar-msg' : 'bot-avatar');
        avatar.innerHTML = role === 'user'
            ? '<i class="bi bi-person-fill"></i>'
            : '<i class="bi bi-robot"></i>';

        const bubble = document.createElement('div');
        bubble.className = 'message-bubble ' + (role === 'user' ? 'user-bubble' : 'bot-bubble');
        if (role === 'bot') {
            bubble.innerHTML = marked.parse(content);
        } else {
            bubble.textContent = content;
        }

        row.appendChild(avatar);
        row.appendChild(bubble);
        chatMessages.appendChild(row);
    }

    function showTypingIndicator() {
        const row = document.createElement('div');
        row.className = 'message-row';
        row.id        = 'typingIndicator';

        row.innerHTML =
            '<div class="message-avatar bot-avatar"><i class="bi bi-robot"></i></div>' +
            '<div class="message-bubble bot-bubble" style="padding:0.75rem 1rem;">' +
            '  <span class="typing-dots">' +
            '    <span style="animation:blink 1.2s infinite 0s">●</span>' +
            '    <span style="animation:blink 1.2s infinite 0.2s">●</span>' +
            '    <span style="animation:blink 1.2s infinite 0.4s">●</span>' +
            '  </span>' +
            '</div>';

        chatMessages.appendChild(row);

        // Inyectar animación si no existe
        if (!document.getElementById('blinkStyle')) {
            const style = document.createElement('style');
            style.id = 'blinkStyle';
            style.textContent =
                '@keyframes blink{0%,80%,100%{opacity:.2}40%{opacity:1}}' +
                '.typing-dots span{font-size:.5rem;margin:0 1px;color:#7fa5d7;}';
            document.head.appendChild(style);
        }
    }

    function removeTypingIndicator() {
        const el = document.getElementById('typingIndicator');
        if (el) el.remove();
    }

    function showErrorBubble(message) {
        const row = document.createElement('div');
        row.className = 'message-row';
        row.innerHTML =
            '<div class="message-avatar bot-avatar"><i class="bi bi-exclamation-triangle-fill text-danger"></i></div>' +
            '<div class="message-bubble bot-bubble" style="border-color:#f5c2c7;color:#842029;">' +
            message + '</div>';
        chatMessages.appendChild(row);
    }

    function scrollToBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // ── Helpers del historial en sidebar ───────────────────────────────────────
    function addHistoryItem(chatId, title, setActive) {
        historyEmpty.style.display = 'none';

        const item = document.createElement('div');
        item.className        = 'chat-history-item' + (setActive ? ' active' : '');
        item.dataset.chatId   = chatId;
        item.innerHTML        =
            '<i class="bi bi-chat-text"></i>' +
            '<span class="history-item-text">' + escapeHtml(title) + '</span>';

        historyList.insertBefore(item, historyList.firstChild);

        if (setActive) setActiveHistoryItem(chatId);
    }

    function setActiveHistoryItem(chatId) {
        document.querySelectorAll('.chat-history-item').forEach(function (el) {
            el.classList.toggle('active', parseInt(el.dataset.chatId, 10) === chatId);
        });
    }

    function updateHistoryItemTitle(chatId, title) {
        const item = historyList.querySelector('[data-chat-id="' + chatId + '"]');
        if (item) {
            const span = item.querySelector('.history-item-text');
            if (span) span.textContent = title;
        }
    }

    // ── Utilidades ─────────────────────────────────────────────────────────────
    async function fetchJSON(url, method, body) {
        const opts = {
            method:  method,
            headers: {
                'Content-Type':  'application/json',
                'X-CSRF-TOKEN':  CSRF,
                'Accept':        'application/json',
            },
        };
        if (body) opts.body = JSON.stringify(body);

        const res = await fetch(url, opts);
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return res.json();
    }

    function escapeHtml(str) {
        return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    // ── Al cargar: habilitar input de inmediato (el chat se crea al primer envío) ──
    chatInput.disabled = false;
    btnSend.disabled   = true;

})();
