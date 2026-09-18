document.addEventListener('DOMContentLoaded', () => {

    const sidebar =
        document.getElementById('sidebar');

    const menuButton =
        document.getElementById('menuButton');

    const overlay =
        document.getElementById('sidebarOverlay');

    if (!sidebar || !menuButton || !overlay) {
        return;
    }


    function abrirMenu() {

        sidebar.classList.add('open');

        overlay.classList.add('show');

        document.body.classList.add('menu-open');
    }


    function cerrarMenu() {

        sidebar.classList.remove('open');

        overlay.classList.remove('show');

        document.body.classList.remove('menu-open');
    }


    menuButton.addEventListener(
        'click',
        () => {

            if (
                sidebar.classList.contains('open')
            ) {

                cerrarMenu();

            } else {

                abrirMenu();

            }

        }
    );


    overlay.addEventListener(
        'click',
        cerrarMenu
    );


    document
        .querySelectorAll('.menu-item')
        .forEach(item => {

            item.addEventListener(
                'click',
                () => {

                    if (
                        window.innerWidth <= 760
                    ) {
                        cerrarMenu();
                    }

                }
            );

        });


    window.addEventListener(
        'resize',
        () => {

            if (window.innerWidth > 760) {
                cerrarMenu();
            }

        }
    );

});