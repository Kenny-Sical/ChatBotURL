(function () {
    'use strict';

    // Mostrar / ocultar contraseña
    const toggleBtn    = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const eyeIcon      = document.getElementById('eyeIcon');

    toggleBtn.addEventListener('click', () => {
        const isPassword = passwordInput.type === 'password';
        passwordInput.type = isPassword ? 'text' : 'password';
        eyeIcon.classList.toggle('bi-eye-fill',      !isPassword);
        eyeIcon.classList.toggle('bi-eye-slash-fill',  isPassword);
    });
})();
