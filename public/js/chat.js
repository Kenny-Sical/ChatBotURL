(function () {
    'use strict';

    const sidebar        = document.getElementById('sidebar');
    const overlay        = document.getElementById('sidebar-overlay');
    const btnToggle      = document.getElementById('btnToggleSidebar');
    const btnNewChat     = document.getElementById('btnNewChat');
    const chatMessages   = document.getElementById('chatMessages');
    const chatEmpty      = document.getElementById('chatEmpty');
    const chatInput      = document.getElementById('chatInput');
    const btnSend        = document.getElementById('btnSend');
    const chatTitle      = document.getElementById('chatTitle');
    const historyList    = document.getElementById('chatHistoryList');

    // ── Activar input al iniciar (se desactivará cuando no haya sesión activa) ──
    chatInput.disabled = false;
    btnSend.disabled   = true;

    // ── Toggle Sidebar ──
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

    // ── Nuevo chat ──
    btnNewChat.addEventListener('click', function () {
        // Limpiar mensajes y volver al estado vacío
        Array.from(chatMessages.children).forEach(el => {
            if (el !== chatEmpty) el.remove();
        });
        chatEmpty.style.display = '';
        chatTitle.textContent   = 'Nueva conversación';
        chatInput.value         = '';
        chatInput.style.height  = 'auto';
        btnSend.disabled        = true;

        // Marcar activo en historial (se expandirá cuando esté conectado a BD)
        document.querySelectorAll('.chat-history-item').forEach(i => i.classList.remove('active'));

        if (isMobile()) {
            sidebar.classList.add('collapsed');
            overlay.classList.remove('show');
        }
    });

    // ── Auto-resize textarea ──
    chatInput.addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 140) + 'px';
        btnSend.disabled  = this.value.trim() === '';
    });

    // ── Enviar con Enter (Shift+Enter = nueva línea) ──
    chatInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            if (!btnSend.disabled) sendMessage();
        }
    });

    btnSend.addEventListener('click', sendMessage);

    // ── Función de envío ──
    function sendMessage() {
        const text = chatInput.value.trim();
        if (!text) return;

        // Ocultar estado vacío al primer mensaje
        chatEmpty.style.display = 'none';

        // Actualizar título de la conversación con las primeras palabras
        if (chatTitle.textContent === 'Nueva conversación') {
            chatTitle.textContent = text.substring(0, 45) + (text.length > 45 ? '…' : '');
        }

        // Agregar burbuja del usuario
        appendMessage('user', text);

        // Limpiar input
        chatInput.value        = '';
        chatInput.style.height = 'auto';
        btnSend.disabled       = true;

        // Scroll al último mensaje
        scrollToBottom();

        // TODO: aquí se conectará la lógica del chatbot
    }

    // ── Crear burbuja de mensaje ──
    function appendMessage(role, content) {
        const row = document.createElement('div');
        row.className = 'message-row ' + (role === 'user' ? 'user-row' : '');

        const avatar = document.createElement('div');
        avatar.className = 'message-avatar ' + (role === 'user' ? 'user-avatar-msg' : 'bot-avatar');
        avatar.innerHTML = role === 'user'
            ? '<i class="bi bi-person-fill"></i>'
            : '<i class="bi bi-robot"></i>';

        const bubble = document.createElement('div');
        bubble.className = 'message-bubble ' + (role === 'user' ? 'user-bubble' : 'bot-bubble');
        bubble.textContent = content;

        row.appendChild(avatar);
        row.appendChild(bubble);
        chatMessages.appendChild(row);
    }

    function scrollToBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
})();
