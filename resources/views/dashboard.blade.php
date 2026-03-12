<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChatBotURL</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/favicon.png">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        /* ── Layout base ── */
        html, body {
            height: 100%;
            margin: 0;
            overflow: hidden;
            background-color: #f4f7fb;
        }

        .chat-wrapper {
            display: flex;
            height: 100vh;
        }

        /* ── Sidebar ── */
        #sidebar {
            width: 280px;
            min-width: 280px;
            background-color: #2873b8;
            display: flex;
            flex-direction: column;
            transition: width 0.25s ease, min-width 0.25s ease;
            overflow: hidden;
        }

        #sidebar.collapsed {
            width: 0;
            min-width: 0;
        }

        .sidebar-header {
            padding: 1rem 1.25rem 0.75rem;
            border-bottom: 1px solid rgba(207, 217, 231, 0.25);
            flex-shrink: 0;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            color: #fff;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.05rem;
            white-space: nowrap;
        }

        .sidebar-brand img {
            width: 30px;
            height: 30px;
            object-fit: contain;
            filter: brightness(0) invert(1);
            flex-shrink: 0;
        }

        .btn-new-chat {
            width: 100%;
            background-color: rgba(255, 255, 255, 0.12);
            border: 1.5px solid rgba(207, 217, 231, 0.45);
            color: #fff;
            border-radius: 0.6rem;
            padding: 0.55rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: background-color 0.2s ease;
            white-space: nowrap;
        }

        .btn-new-chat:hover {
            background-color: rgba(255, 255, 255, 0.22);
            color: #fff;
        }

        .sidebar-history {
            flex: 1;
            overflow-y: auto;
            padding: 0.5rem 0.75rem;
        }

        .sidebar-history::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar-history::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar-history::-webkit-scrollbar-thumb {
            background: rgba(207, 217, 231, 0.35);
            border-radius: 4px;
        }

        .history-section-label {
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.5);
            padding: 0.75rem 0.5rem 0.35rem;
            white-space: nowrap;
        }

        .chat-history-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.65rem;
            border-radius: 0.5rem;
            color: rgba(255, 255, 255, 0.82);
            font-size: 0.875rem;
            cursor: pointer;
            transition: background-color 0.15s ease;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .chat-history-item:hover {
            background-color: rgba(255, 255, 255, 0.12);
            color: #fff;
        }

        .chat-history-item.active {
            background-color: rgba(255, 255, 255, 0.18);
            color: #fff;
        }

        .chat-history-item i {
            font-size: 0.8rem;
            flex-shrink: 0;
            opacity: 0.7;
        }

        .history-item-text {
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* ── Sidebar footer (usuario) ── */
        .sidebar-footer {
            padding: 0.85rem 1.25rem;
            border-top: 1px solid rgba(207, 217, 231, 0.25);
            flex-shrink: 0;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            white-space: nowrap;
            overflow: hidden;
        }

        .user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .user-name {
            color: #fff;
            font-size: 0.875rem;
            font-weight: 500;
            overflow: hidden;
            text-overflow: ellipsis;
            flex: 1;
        }

        .btn-logout-sidebar {
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.65);
            font-size: 1.1rem;
            padding: 0.15rem 0.3rem;
            border-radius: 0.4rem;
            transition: color 0.15s ease, background-color 0.15s ease;
            flex-shrink: 0;
        }

        .btn-logout-sidebar:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.15);
        }

        /* ── Área principal del chat ── */
        .chat-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background-color: #fff;
        }

        /* Barra superior del chat */
        .chat-topbar {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1.25rem;
            border-bottom: 1.5px solid #cfd9e7;
            background-color: #fff;
            flex-shrink: 0;
        }

        .btn-toggle-sidebar {
            background: none;
            border: none;
            color: #2873b8;
            font-size: 1.25rem;
            padding: 0.25rem 0.4rem;
            border-radius: 0.4rem;
            transition: background-color 0.15s ease;
        }

        .btn-toggle-sidebar:hover {
            background-color: #eef3fb;
        }

        .chat-topbar-title {
            font-size: 0.95rem;
            font-weight: 600;
            color: #2873b8;
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Zona de mensajes */
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 2rem 1.25rem 1rem;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .chat-messages::-webkit-scrollbar {
            width: 5px;
        }

        .chat-messages::-webkit-scrollbar-thumb {
            background: #cfd9e7;
            border-radius: 4px;
        }

        /* Estado vacío */
        .chat-empty {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #7fa5d7;
            text-align: center;
            padding: 2rem;
        }

        .chat-empty i {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            opacity: 0.6;
        }

        .chat-empty p {
            font-size: 1rem;
            color: #a0b4cc;
            max-width: 320px;
            line-height: 1.6;
        }

        /* Burbujas de mensajes */
        .message-row {
            display: flex;
            gap: 0.65rem;
        }

        .message-row.user-row {
            flex-direction: row-reverse;
        }

        .message-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            flex-shrink: 0;
            align-self: flex-end;
        }

        .message-avatar.bot-avatar {
            background-color: #eef3fb;
            color: #2873b8;
            border: 1.5px solid #cfd9e7;
        }

        .message-avatar.user-avatar-msg {
            background-color: #2873b8;
            color: #fff;
        }

        .message-bubble {
            max-width: 68%;
            padding: 0.65rem 1rem;
            border-radius: 1rem;
            font-size: 0.9rem;
            line-height: 1.55;
            word-break: break-word;
        }

        .bot-bubble {
            background-color: #f4f7fb;
            border: 1.5px solid #cfd9e7;
            border-bottom-left-radius: 0.25rem;
            color: #2d3748;
        }

        .user-bubble {
            background-color: #2873b8;
            color: #fff;
            border-bottom-right-radius: 0.25rem;
        }

        /* ── Área de escritura ── */
        .chat-input-area {
            padding: 0.85rem 1.25rem 1rem;
            border-top: 1.5px solid #cfd9e7;
            background-color: #fff;
            flex-shrink: 0;
        }

        .chat-input-box {
            display: flex;
            align-items: flex-end;
            gap: 0.6rem;
            background-color: #f4f7fb;
            border: 1.5px solid #cfd9e7;
            border-radius: 0.85rem;
            padding: 0.5rem 0.75rem;
            transition: border-color 0.2s ease;
        }

        .chat-input-box:focus-within {
            border-color: #7fa5d7;
        }

        #chatInput {
            flex: 1;
            border: none;
            background: transparent;
            resize: none;
            outline: none;
            font-size: 0.9rem;
            line-height: 1.5;
            max-height: 140px;
            min-height: 24px;
            color: #2d3748;
            padding: 0;
        }

        #chatInput::placeholder {
            color: #a0b4cc;
        }

        .btn-send {
            background-color: #2873b8;
            border: none;
            color: #fff;
            width: 36px;
            height: 36px;
            border-radius: 0.6rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
            transition: background-color 0.2s ease, opacity 0.2s ease;
        }

        .btn-send:hover:not(:disabled) {
            background-color: #2369c6;
        }

        .btn-send:disabled {
            opacity: 0.45;
            cursor: not-allowed;
        }

        .input-hint {
            font-size: 0.72rem;
            color: #a0b4cc;
            text-align: center;
            margin-top: 0.4rem;
        }

        /* ── Responsivo ── */
        @media (max-width: 767.98px) {
            #sidebar {
                position: fixed;
                top: 0;
                left: 0;
                height: 100%;
                z-index: 1045;
                width: 260px;
                min-width: 260px;
            }

            #sidebar.collapsed {
                width: 0;
                min-width: 0;
            }

            #sidebar-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,0.35);
                z-index: 1044;
            }

            #sidebar-overlay.show {
                display: block;
            }
        }
    </style>
</head>
<body>

<div class="chat-wrapper">

    <!--  SIDEBAR  -->
    <nav id="sidebar">

        <!-- Logo + botón nuevo chat -->
        <div class="sidebar-header">
            <a href="{{ route('dashboard') }}" class="sidebar-brand mb-3 d-block">
                <img src="/favicon.png" alt="Logo">
                ChatBotURL
            </a>
            <button class="btn-new-chat" id="btnNewChat" type="button">
                <i class="bi bi-plus-lg"></i>
                Nuevo chat
            </button>
        </div>

        <!-- Historial de conversaciones -->
        <div class="sidebar-history">
            <div class="history-section-label">Recientes</div>

            {{-- Aquí se renderizará el historial dinámicamente --}}
            <div id="chatHistoryList">
                {{-- Placeholder visual mientras no hay tabla conectada --}}
                <div class="chat-history-item active">
                    <i class="bi bi-chat-text"></i>
                    <span class="history-item-text">Nueva conversación</span>
                </div>
            </div>

            <div id="historyEmpty" class="text-center py-4" style="color: rgba(255,255,255,0.35); font-size: 0.8rem; display: none;">
                <i class="bi bi-clock-history d-block mb-1" style="font-size: 1.2rem;"></i>
                Sin historial aún
            </div>
        </div>

        <!-- Usuario + cerrar sesión -->
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar">
                    <i class="bi bi-person-fill"></i>
                </div>
                <span class="user-name" title="{{ Auth::user()->name }}">
                    {{ Auth::user()->name }}
                </span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-logout-sidebar" title="Cerrar sesión">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>

    </nav>

    <!-- Overlay para móvil -->
    <div id="sidebar-overlay"></div>

    <!-- CHAT PRINCIPAL -->
    <main class="chat-main">

        <!-- Barra superior -->
        <div class="chat-topbar">
            <button class="btn-toggle-sidebar" id="btnToggleSidebar" title="Mostrar / ocultar menú">
                <i class="bi bi-layout-sidebar"></i>
            </button>
            <span class="chat-topbar-title" id="chatTitle">Nueva conversación</span>
        </div>

        <!-- Zona de mensajes -->
        <div class="chat-messages" id="chatMessages">

            <!-- Estado vacío inicial -->
            <div class="chat-empty" id="chatEmpty">
                <i class="bi bi-robot"></i>
                <p>¡Hola, {{ Auth::user()->name }}!<br>Escribe tu primera pregunta para comenzar.</p>
            </div>

        </div>

        <!-- Input de texto -->
        <div class="chat-input-area">
            <div class="chat-input-box">
                <textarea
                    id="chatInput"
                    rows="1"
                    placeholder="Escribe tu mensaje…"
                    disabled
                ></textarea>
                <button class="btn-send" id="btnSend" type="button" disabled title="Enviar">
                    <i class="bi bi-arrow-up"></i>
                </button>
            </div>
            <p class="input-hint">Presiona <kbd>Enter</kbd> para enviar · <kbd>Shift+Enter</kbd> para nueva línea</p>
        </div>

    </main>

</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Chat -->
<script src="/js/chat.js?v={{ $version }}"></script>

</body>
</html>
