document.addEventListener('DOMContentLoaded', () => {
    // Transición de salida para enlaces de logout o navegación
    const links = document.querySelectorAll('.animated-exit');
    links.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const targetUrl = link.href;
            const container = document.querySelector('.glass-panel');
            if (container) {
                container.classList.add('exit-anim');
                setTimeout(() => {
                    window.location.href = targetUrl;
                }, 500);
            } else {
                window.location.href = targetUrl;
            }
        });
    });
});