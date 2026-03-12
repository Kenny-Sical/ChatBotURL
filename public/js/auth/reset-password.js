(function () {
    'use strict';

    const passwordInput = document.getElementById('password');

    // Toggle contraseña
    document.getElementById('togglePass').addEventListener('click', () => {
        const icon = document.getElementById('eyeIcon1');
        const show = passwordInput.type === 'password';
        passwordInput.type = show ? 'text' : 'password';
        icon.classList.toggle('bi-eye-fill',       !show);
        icon.classList.toggle('bi-eye-slash-fill',  show);
    });

    // Fortaleza de contraseña
    const bars           = [document.getElementById('bar1'), document.getElementById('bar2'), document.getElementById('bar3'), document.getElementById('bar4')];
    const strengthLabel  = document.getElementById('strengthLabel');
    const strengthColors = ['bg-danger', 'bg-warning', 'bg-info', 'bg-success'];
    const strengthTexts  = ['Muy débil', 'Débil', 'Aceptable', 'Fuerte'];

    passwordInput.addEventListener('input', function () {
        const val = this.value;
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

    // Verificar coincidencia
    const confirmInput = document.getElementById('password_confirmation');
    const matchError   = document.getElementById('matchError');

    confirmInput.addEventListener('input', () => {
        const mismatch = confirmInput.value && confirmInput.value !== passwordInput.value;
        matchError.classList.toggle('d-none', !mismatch);
        confirmInput.classList.toggle('is-invalid', mismatch);
    });

    document.getElementById('resetForm').addEventListener('submit', (e) => {
        if (passwordInput.value !== confirmInput.value) {
            e.preventDefault();
            matchError.classList.remove('d-none');
            confirmInput.focus();
        }
    });
})();
