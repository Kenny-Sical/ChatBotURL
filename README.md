<div align="center">
  <img src="public/favicon.png" alt="ChatBot IA Logo" width="100" />
  <h1>ChatBot Educativo Inmersivo con IA</h1>
  <p>
    Una plataforma de asistencia educativa construida con <strong>Laravel</strong> que combina capacidades conversacionales avanzadas de LLMs generativos y un sistema integral de notas de voz en tiempo real.
  </p>
</div>

---

## ✨ Características Principales

- 🤖 **Motor de Inteligencia Artificial Avanzado**: Integrado nativamente con los modelos fine-tuned de **Google Vertex AI** (`gemini-2.5-flash-lite`).
- 🎙️ **Interacción por Voz Real**: Conversa con tu asistente sin tocar el teclado.
    - Capta audio en vivo y utiliza **Groq API (Whisper)** para una transcripción increíblemente veloz.
    - El asistente te responderá de vuelta y de viva voz usando **Web Speech API**.
- 🖥️ **Interfaz Inmersiva "Premium"**: Implementa principios de _Glassmorphism_ y modales responsivos con latidos fluidos para representar el estado del procesamiento cognitivo (Escuchando > Pensando > Hablando).
- 🧩 **Conciencia de Contexto Asertiva (Few-Shot Bypass)**: Entiende la diferencia en tu estilo de interacción. Si escribes por chat, arrojará ejemplos ricos y Markdown de pseudocódigo. Si le hablas, te contestará de forma directa, corta y amigable al oído.
- 📱 **100% Responsivo**: Diseñado para móviles y pantallas grandes con un panel lateral colapsable nativo.

## 🛠️ Tecnologías Empleadas

- **Framework Backend:** Laravel 10/11 (PHP 8.2+)
- **Base de Datos:** MySQL / MariaDB
- **Inteligencia Artificial LLM:** Google Vertex AI API (Google Cloud Platform)
- **Motor STT (Voice to Text):** Groq Whisper-large-v3
- **Motor TTS (Text to Voice):** Navegador Nativo (SpeechSynthesis)
- **Frontend UI:** Blade, Bootstrap 5, Markdown.js, Vanilla CSS & JS.

---

## ⚙️ Requisitos Previos

Antes de instalar este proyecto en tu entorno local, asegúrate de tener instalado:

- PHP >= 8.1
- Composer
- Base de datos MySQL o MariaDB
- Una cuenta en [Groq Cloud](https://console.groq.com/keys)
- Una cuenta de Servicio en [Google Cloud Platform](https://console.cloud.google.com/) con permisos para Vertex AI.

---

## 🚀 Instalación Rápida Local

Sigue estos pasos para arrancar el ChatBot IA en tu propio ordenador:

1. **Clona y entra en el proyecto:**

    ```bash
    git clone https://ruta-del-repositorio/chatbot.git
    cd chatbot
    ```

2. **Instala las Dependencias de PHP:**

    ```bash
    composer install
    ```

3. **Configura tus Variables de Entorno (`.env`):**
    - Renombra el archivo `.env.example` a `.env`.
    - Modifica tus configuraciones de base de datos (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).
    - Añade tus claves API en la parte inferior:

    ```ini
    VERTEX_AI_PROJECT_ID=tu-id-de-proyecto
    VERTEX_AI_LOCATION=us-east1
    VERTEX_AI_ENDPOINT_ID=tu-endpoint-fine-tunned
    VERTEX_AI_CREDENTIALS=storage/app/private/google-credenciales.json

    GROQ_API_KEY=tu-api-key-de-groq
    ```

4. **Guarda el Archivo de Cuenta de Google Vertex:**
   Descarga el archivo estricto JSON de servicio desde Google Cloud, nómbralo `google-credenciales.json` y colócalo exactamente en esta ruta local para máxima seguridad:
   `storage/app/private/google-credenciales.json`

5. **Genera la Clave de Aplicación y Base de datos:**

    ```bash
    php artisan key:generate
    php artisan migrate
    ```

6. **Despliega el Servidor Integrado:**
    ```bash
    php artisan serve
    ```
    Entra a `http://localhost:8000` y regístrate para empezar a debatir en tu primera conversación inteligente.

---

## Despliegue a Producción

La plataforma de notas de voz en este proyecto requiere estrictamente una conexión segura SSL (`https://`) para poder operar, debido a las políticas de seguridad en los navegadores para capturar el micrófono. Te recomendamos leer la [Guía Completa de Despliegue Incluida (`DEPLOYMENT_CLOUD_PANEL.md`)](./DEPLOYMENT_CLOUD_PANEL.md) donde detallamos cómo subir esto a DigitalOcean mediante CloudPanel.

---

## Licencia

Este es un proyecto privativo y educativo desarrollado en colaboración autónoma mediante las directrices de diseño de Arquitectura en Tiempo Real. Las licencias de las APIs de Google Vertex y Groq dependen de los TOS dictaminados por sus propietarios.
