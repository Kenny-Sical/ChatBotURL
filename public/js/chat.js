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
    const btnVoice      = document.getElementById('btnVoice');
    const chatTitle     = document.getElementById('chatTitle');
    const historyList   = document.getElementById('chatHistoryList');
    const historyEmpty  = document.getElementById('historyEmpty');

    const voiceModal      = document.getElementById('voiceModal');
    const btnCloseVoice   = document.getElementById('btnCloseVoice');
    const btnBigMic       = document.getElementById('btnBigMic');
    const voiceMicWrapper = document.getElementById('voiceMicWrapper');
    const voiceStatusText = document.getElementById('voiceStatusText');
    const voiceHelpText   = document.getElementById('voiceHelpText');
    const bigMicIcon      = document.getElementById('bigMicIcon');

    const btnAttach       = document.getElementById('btnAttach');
    const fileAttach      = document.getElementById('fileAttach');
    const attachmentPreview = document.getElementById('attachmentPreview');
    const attachmentName  = document.getElementById('attachmentName');
    const btnRemoveAttachment = document.getElementById('btnRemoveAttachment');

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
        const hasFile = fileAttach && fileAttach.files.length > 0;
        btnSend.disabled  = (this.value.trim() === '' && !hasFile) || waitingForBot;
    });

    // ── Adjuntar Archivo ───────────────────────────────────────────────────────
    if (btnAttach) {
        btnAttach.addEventListener('click', () => fileAttach.click());
        
        fileAttach.addEventListener('change', function() {
            if (this.files.length > 0) {
                const file = this.files[0];
                attachmentName.textContent = file.name;
                attachmentPreview.classList.add('show');
                btnSend.disabled = false;
            } else {
                attachmentPreview.classList.remove('show');
                btnSend.disabled = chatInput.value.trim() === '';
            }
        });

        btnRemoveAttachment.addEventListener('click', () => {
            fileAttach.value = '';
            attachmentPreview.classList.remove('show');
            btnSend.disabled = chatInput.value.trim() === '';
        });
    }

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
        const hasFile = fileAttach && fileAttach.files.length > 0;

        if ((!text && !hasFile) || waitingForBot) return;

        // Si no hay chat activo, crear uno antes de enviar
        if (!activeChatId) {
            await startNewChat();
        }

        // Mostrar burbuja del usuario de inmediato
        chatEmpty.style.display = 'none';
        
        let displayHtml = text;
        if (hasFile) {
            const fileName = fileAttach.files[0].name;
            const fileBadge = `<div style="opacity:0.8; font-size:0.8rem; margin-top: ${text ? '0.5rem' : '0'}"><i class="bi bi-paperclip"></i> ${escapeHtml(fileName)}</div>`;
            displayHtml = escapeHtml(text) + fileBadge;
        } else {
            displayHtml = escapeHtml(text);
        }
        
        appendMessage('user', displayHtml, true); // usar true para inyectar como HTML dado el fileBadge

        // Preparar archivo
        let file = hasFile ? fileAttach.files[0] : null;

        // Limpiar input y adjuntos
        chatInput.value        = '';
        chatInput.style.height = 'auto';
        chatInput.disabled     = true;
        btnSend.disabled       = true;
        btnAttach.disabled     = true;
        if (fileAttach) fileAttach.value = '';
        if (attachmentPreview) attachmentPreview.classList.remove('show');
        waitingForBot          = true;

        showTypingIndicator();
        scrollToBottom();

        try {
            const selectedModel = document.getElementById('modelSelector') ? document.getElementById('modelSelector').value : 'vertex';
            
            let res;
            if (hasFile) {
                // Si hay archivo usamos FormData en lugar de fetchJSON básico
                const formData = new FormData();
                formData.append('message', text);
                formData.append('model', selectedModel);
                formData.append('file', file);
                
                const fetchRes = await fetch('/chat/' + activeChatId + '/message', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json',
                    },
                    body: formData
                });
                if (!fetchRes.ok) throw new Error('HTTP ' + fetchRes.status);
                res = await fetchRes.json();
            } else {
                // Si solo es texto, fetchJSON (json plano)
                res = await fetchJSON('/chat/' + activeChatId + '/message', 'POST', { 
                    message: text,
                    model: selectedModel 
                });
            }

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
            if (btnAttach) btnAttach.disabled = false;
            chatInput.focus();
        } catch {
            removeTypingIndicator();
            waitingForBot     = false;
            chatInput.disabled = false;
            btnAttach.disabled = false;
            showErrorBubble('Error al enviar el mensaje. Verifica tu conexión.');
        }
    }

    // ── Funciones de Voz (Grabación y Sintetizador Modal) ──────────────────────
    let mediaRecorder = null;
    let audioChunks = [];
    let isRecording = false;
    let isModalOpen = false;

    function openVoiceModal() {
        if (!voiceModal) return;
        voiceModal.classList.add('active');
        isModalOpen = true;
        resetModalUI();
    }

    function closeVoiceModal() {
        if (!voiceModal) return;
        voiceModal.classList.remove('active');
        isModalOpen = false;
        
        if (isRecording) {
            mediaRecorder.stop();
            isRecording = false;
        }
        if (synth.speaking) {
            synth.cancel();
        }
    }

    function resetModalUI() {
        voiceModal.classList.remove('recording', 'processing', 'speaking');
        voiceMicWrapper.classList.remove('recording', 'processing', 'speaking');
        voiceStatusText.textContent = 'Pulsa para hablar';
        voiceHelpText.textContent = 'Toca el micrófono para iniciar grabación';
        btnBigMic.disabled = false;
        bigMicIcon.className = 'bi bi-mic-fill';
    }

    if (btnCloseVoice) {
        btnCloseVoice.addEventListener('click', closeVoiceModal);
    }

    // ── Workaround para desbloquear SpeechSynthesis en iOS/Safari ──
    function unlockIOSAudio() {
        if (synth && synth.speak) {
            let unlockUtterance = new SpeechSynthesisUtterance('');
            unlockUtterance.volume = 0;
            unlockUtterance.rate = 1;
            unlockUtterance.pitch = 1;
            synth.speak(unlockUtterance);
            // Reanudar por precaución en algunos navegadores
            if (synth.resume) synth.resume();
        }
    }

    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        // Botón pequeño abre el modal
        btnVoice.addEventListener('click', function() {
            unlockIOSAudio();
            openVoiceModal();
        });

        // Botón gigante dentro del modal
        btnBigMic.addEventListener('click', async function() {
            unlockIOSAudio();
            if (isRecording) {
                // Detener grabación y procesar
                mediaRecorder.stop();
                isRecording = false;

                // UI Processing
                voiceModal.classList.remove('recording');
                voiceMicWrapper.classList.remove('recording');
                voiceModal.classList.add('processing');
                voiceMicWrapper.classList.add('processing');
                voiceStatusText.textContent = 'Pensando...';
                voiceHelpText.textContent = 'Espera un momento';
                btnBigMic.disabled = true;
                bigMicIcon.className = 'bi bi-hourglass-split';
            } else {
                try {
                    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                    mediaRecorder = new MediaRecorder(stream);
                    audioChunks = [];
                    
                    mediaRecorder.ondataavailable = e => {
                        if (e.data.size > 0) audioChunks.push(e.data);
                    };
                    
                    mediaRecorder.onstop = () => {
                        if (isModalOpen && audioChunks.length > 0) {
                            const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                            sendVoiceMessage(audioBlob);
                        }
                        stream.getTracks().forEach(track => track.stop());
                    };
                    
                    mediaRecorder.start();
                    isRecording = true;

                    // UI Recording
                    voiceModal.classList.remove('processing', 'speaking');
                    voiceMicWrapper.classList.remove('processing', 'speaking');
                    voiceModal.classList.add('recording');
                    voiceMicWrapper.classList.add('recording');
                    voiceStatusText.textContent = 'Te escucho...';
                    voiceHelpText.textContent = 'Toca de nuevo para enviar mensaje';
                    bigMicIcon.className = 'bi bi-stop-fill';
                } catch (err) {
                    alert('No se pudo acceder al micrófono. Verifica los permisos.');
                }
            }
        });
    }

    async function sendVoiceMessage(audioBlob) {
        if (waitingForBot) return;

        if (!activeChatId) {
            await startNewChat();
        }

        chatEmpty.style.display = 'none';
        chatInput.disabled     = true;
        btnSend.disabled       = true;
        btnVoice.disabled      = true;
        if (btnAttach) btnAttach.disabled = true;
        waitingForBot          = true;

        showTypingIndicator();
        scrollToBottom();

        const formData = new FormData();
        formData.append('audio', audioBlob, 'voice.webm');
        
        const selectedModel = document.getElementById('modelSelector') ? document.getElementById('modelSelector').value : 'vertex';
        formData.append('model', selectedModel);

        try {
            const res = await fetch('/chat/' + activeChatId + '/voice-message', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json',
                },
                body: formData
            });

            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();
            
            if (data.is_first) {
                chatTitle.textContent = data.chat_title;
                updateHistoryItemTitle(activeChatId, data.chat_title);
            }

            removeTypingIndicator();
            waitingForBot = false;
            
            appendMessage('user', data.user_message);
            appendMessage('bot', data.bot_message);
            scrollToBottom();
            
            chatInput.disabled = false;
            btnVoice.disabled = false;
            if (btnAttach) btnAttach.disabled = false;
            chatInput.focus();

            playTTS(data.bot_message);

        } catch (err) {
            removeTypingIndicator();
            waitingForBot     = false;
            chatInput.disabled = false;
            btnVoice.disabled = false;
            if (btnAttach) btnAttach.disabled = false;
            
            if (isModalOpen) {
                resetModalUI();
                voiceStatusText.textContent = 'Ocurrió un error';
            }
            showErrorBubble('Error al enviar el mensaje de voz. Asegúrate de grabar con volumen.');
        }
    }

    let synth = window.speechSynthesis;
    let text_to_speech = new SpeechSynthesisUtterance();
    text_to_speech.lang = 'es-ES';
    
    // Cargar voces en español preferentemente
    if (synth.onvoiceschanged !== undefined) {
        synth.onvoiceschanged = () => {
            const voices = synth.getVoices();
            const spanishVoice = voices.find(v => v.lang.startsWith('es'));
            if (spanishVoice) {
                text_to_speech.voice = spanishVoice;
            }
        };
    }

    // Eventos TTS para Modal
    text_to_speech.onstart = () => {
        if (isModalOpen) {
            voiceModal.classList.remove('processing', 'recording');
            voiceMicWrapper.classList.remove('processing', 'recording');
            voiceModal.classList.add('speaking');
            voiceMicWrapper.classList.add('speaking');
            voiceStatusText.textContent = 'Respondiendo...';
            voiceHelpText.textContent = 'Escuchando respuesta de IA';
            btnBigMic.disabled = true;
            bigMicIcon.className = 'bi bi-soundwave';
        }
    };

    text_to_speech.onend = () => {
        if (isModalOpen) {
            resetModalUI();
        }
    };

    text_to_speech.onerror = () => {
        if (isModalOpen) {
            resetModalUI();
        }
    };

    function playTTS(text) {
        if (synth.speaking) {
            synth.cancel();
        }
        
        let cleanText = text.replace(/[*_#`~]/g, '');
        text_to_speech.text = cleanText;
        synth.speak(text_to_speech);
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

    function appendMessage(role, content, isHtml = false) {
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
            if (isHtml) bubble.innerHTML = content;
            else bubble.textContent = content;
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
    btnVoice.disabled  = false;
    btnSend.disabled   = true;

    // Colapsar sidebar en móviles por defecto
    if (isMobile()) {
        sidebar.classList.add('collapsed');
    }

})();
