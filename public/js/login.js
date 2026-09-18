document.addEventListener('DOMContentLoaded', () => {

    const password =
        document.getElementById('password');

    const toggle =
        document.getElementById('togglePassword');

    if (!password || !toggle) {
        return;
    }

    toggle.addEventListener('click', () => {

        const visible =
            password.type === 'text';

        password.type =
            visible ? 'password' : 'text';

        toggle.textContent =
            visible ? '👁' : '🙈';

        toggle.setAttribute(
            'aria-label',
            visible
                ? 'Mostrar contraseña'
                : 'Ocultar contraseña'
        );
    });

});