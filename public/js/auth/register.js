(function () {
    'use strict';

    const passwordInput = document.getElementById('password');
    const eyeIcon       = document.getElementById('eyeIcon');

    // Mostrar / ocultar contraseña
    document.getElementById('togglePassword').addEventListener('click', () => {
        const isPassword = passwordInput.type === 'password';
        passwordInput.type = isPassword ? 'text' : 'password';
        eyeIcon.classList.toggle('bi-eye-fill',      !isPassword);
        eyeIcon.classList.toggle('bi-eye-slash-fill',  isPassword);
    });

    // Indicador de fortaleza de contraseña
    const bars = [
        document.getElementById('bar1'),
        document.getElementById('bar2'),
        document.getElementById('bar3'),
        document.getElementById('bar4'),
    ];
    const strengthLabel  = document.getElementById('strengthLabel');
    const strengthColors = ['bg-danger', 'bg-warning', 'bg-info', 'bg-success'];
    const strengthTexts  = ['Muy débil', 'Débil', 'Aceptable', 'Fuerte'];

    passwordInput.addEventListener('input', () => {
        const val = passwordInput.value;
        let score = 0;
        if (val.length >= 8)            score++;
        if (/[A-Z]/.test(val))          score++;
        if (/[0-9]/.test(val))          score++;
        if (/[^A-Za-z0-9]/.test(val))  score++;

        bars.forEach((bar, i) => {
            bar.className = 'password-strength flex-fill';
            if (i < score) {
                bar.classList.add(strengthColors[score - 1]);
            } else {
                bar.classList.add('bg-secondary', 'opacity-25');
            }
        });

        strengthLabel.textContent = val.length ? (strengthTexts[score - 1] ?? 'Muy débil') : '';
    });

    // Verificar que las contraseñas coincidan
    const confirmInput = document.getElementById('password_confirmation');
    const matchError   = document.getElementById('matchError');

    confirmInput.addEventListener('input', () => {
        const mismatch = confirmInput.value && confirmInput.value !== passwordInput.value;
        matchError.classList.toggle('d-none', !mismatch);
        confirmInput.classList.toggle('is-invalid', mismatch);
    });

    // Bloquear envío si contraseñas no coinciden
    document.getElementById('registerForm').addEventListener('submit', (e) => {
        if (passwordInput.value !== confirmInput.value) {
            e.preventDefault();
            matchError.classList.remove('d-none');
            confirmInput.focus();
        }
    });
})();
