document.addEventListener(
    'DOMContentLoaded',
    () => {

        const buscar =
            document.getElementById('buscar');

        const mobileSearch =
            document.getElementById(
                'mobileSearch'
            );

        if (!buscar || !mobileSearch) {
            return;
        }


        mobileSearch.addEventListener(
            'click',
            (event) => {

                event.preventDefault();

                buscar.focus();

                buscar.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });

            }
        );

    }
);