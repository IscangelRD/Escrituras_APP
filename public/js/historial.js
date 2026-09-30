document.addEventListener(
    'DOMContentLoaded',
    function () {

        cargarHistorial();

        document
            .getElementById(
                'filtroHistorial'
            )
            ?.addEventListener(
                'change',
                aplicarFiltros
            );


        document
            .getElementById(
                'buscarHistorial'
            )
            ?.addEventListener(
                'input',
                aplicarFiltros
            );

    }
);


/*
|--------------------------------------------------------------------------
| VARIABLES
|--------------------------------------------------------------------------
*/

let historialCompleto = [];


/*
|--------------------------------------------------------------------------
| CARGAR HISTORIAL
|--------------------------------------------------------------------------
*/

async function cargarHistorial() {

    const contenedor =
        document.getElementById(
            'listaHistorial'
        );


    if (!contenedor) {
        return;
    }


    const expedienteId =
        window.EXPEDIENTE_ID;


    if (!expedienteId) {

        mostrarMensajeHistorial(
            '⚠️',
            'No se encontró el expediente.'
        );

        return;

    }


    try {

        const respuesta =
            await fetch(
                'historial_expediente.php?expediente_id='
                +
                encodeURIComponent(
                    expedienteId
                ),
                {
                    cache: 'no-store'
                }
            );


        const texto =
            await respuesta.text();


        console.log(
            'Respuesta historial:',
            texto
        );


        let data;


        try {

            data =
                JSON.parse(
                    texto
                );

        } catch (error) {

            throw new Error(
                'El servidor no devolvió JSON válido.'
            );

        }


        if (!data.success) {

            throw new Error(
                data.message
                ||
                'No fue posible cargar el historial.'
            );

        }


        historialCompleto =
            data.historial
            ||
            [];


        aplicarFiltros();


    } catch (error) {

        console.error(
            'Error historial:',
            error
        );


        mostrarMensajeHistorial(
            '⚠️',
            error.message
        );

    }

}


/*
|--------------------------------------------------------------------------
| FILTROS
|--------------------------------------------------------------------------
*/

function aplicarFiltros() {

    const filtro =
        document.getElementById(
            'filtroHistorial'
        )?.value
        ||
        'todos';


    const texto =
        (
            document.getElementById(
                'buscarHistorial'
            )?.value
            ||
            ''
        )
        .trim()
        .toLowerCase();


    let resultados =
        historialCompleto;


    /*
    |--------------------------------------------------------------------------
    | FILTRAR ENTIDAD
    |--------------------------------------------------------------------------
    */

    if (
        filtro !== 'todos'
    ) {

        resultados =
            resultados.filter(
                movimiento => {

                    const entidad =
                        String(
                            movimiento.entidad
                            ||
                            ''
                        )
                        .toLowerCase();


                    return entidad
                        .includes(
                            filtro
                        );

                }
            );

    }


    /*
    |--------------------------------------------------------------------------
    | BUSCAR
    |--------------------------------------------------------------------------
    */

    if (texto !== '') {

        resultados =
            resultados.filter(
                movimiento => {

                    const contenido = [

                        movimiento.accion,

                        movimiento.descripcion,

                        movimiento.campo,

                        movimiento.valor_anterior,

                        movimiento.valor_nuevo,

                        movimiento.usuario_nombre

                    ]
                    .filter(Boolean)
                    .join(' ')
                    .toLowerCase();


                    return contenido.includes(
                        texto
                    );

                }
            );

    }


    mostrarHistorial(
        resultados
    );

}


/*
|--------------------------------------------------------------------------
| MOSTRAR
|--------------------------------------------------------------------------
*/

function mostrarHistorial(
    historial
) {

    const contenedor =
        document.getElementById(
            'listaHistorial'
        );


    if (!contenedor) {
        return;
    }


    if (
        !historial
        ||
        historial.length === 0
    ) {

        mostrarMensajeHistorial(
            '📭',
            'No hay movimientos que coincidan con la búsqueda.'
        );

        return;

    }


    contenedor.innerHTML =
        historial
            .map(
                movimiento =>
                    crearMovimiento(
                        movimiento
                    )
            )
            .join('');

}


/*
|--------------------------------------------------------------------------
| MOVIMIENTO
|--------------------------------------------------------------------------
*/

function crearMovimiento(
    movimiento
) {

    const usuario =
        movimiento.usuario_nombre
        ?.trim()
        ||
        'Usuario no disponible';


    const fecha =
        formatearFecha(
            movimiento.fecha_hora
        );


    const accion =
        movimiento.accion
        ||
        'MOVIMIENTO';


    const descripcion =
        movimiento.descripcion
        ||
        'Sin descripción';


    let cambio = '';


    if (
        movimiento.campo
    ) {

        cambio = `

            <div class="historial-cambio">

                <div>

                    <span>
                        Campo
                    </span>

                    <strong>
                        ${escaparHTML(
                            movimiento.campo
                        )}
                    </strong>

                </div>


                <div>

                    <span>
                        Antes
                    </span>

                    <strong>
                        ${escaparHTML(
                            movimiento.valor_anterior
                            ??
                            '—'
                        )}
                    </strong>

                </div>


                <div>

                    <span>
                        Después
                    </span>

                    <strong>
                        ${escaparHTML(
                            movimiento.valor_nuevo
                            ??
                            '—'
                        )}
                    </strong>

                </div>

            </div>

        `;

    }


    return `

        <article
            class="historial-item"
        >

            <div class="historial-marker">
                ✓
            </div>


            <div class="historial-content">


                <div class="historial-top">

                    <strong>
                        ${escaparHTML(
                            accion
                        )}
                    </strong>


                    <time>
                        ${escaparHTML(
                            fecha
                        )}
                    </time>

                </div>


                <p class="historial-description">

                    ${escaparHTML(
                        descripcion
                    )}

                </p>


                ${cambio}


                <div class="historial-user">

                    👤
                    ${escaparHTML(
                        usuario
                    )}

                </div>


            </div>

        </article>

    `;

}


/*
|--------------------------------------------------------------------------
| MENSAJE
|--------------------------------------------------------------------------
*/

function mostrarMensajeHistorial(
    icono,
    mensaje
) {

    const contenedor =
        document.getElementById(
            'listaHistorial'
        );


    if (!contenedor) {
        return;
    }


    contenedor.innerHTML = `

        <div class="historial-empty">

            <div>
                ${icono}
            </div>

            <p>
                ${escaparHTML(
                    mensaje
                )}
            </p>

        </div>

    `;

}


/*
|--------------------------------------------------------------------------
| FECHA
|--------------------------------------------------------------------------
*/

function formatearFecha(
    fecha
) {

    if (!fecha) {

        return 'Fecha no disponible';

    }


    const fechaObj =
        new Date(
            String(
                fecha
            ).replace(
                ' ',
                'T'
            )
        );


    if (
        Number.isNaN(
            fechaObj.getTime()
        )
    ) {

        return fecha;

    }


    return fechaObj.toLocaleString(
        'es-MX',
        {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        }
    );

}


/*
|--------------------------------------------------------------------------
| SEGURIDAD
|--------------------------------------------------------------------------
*/

function escaparHTML(
    valor
) {

    return String(
        valor ?? ''
    )
        .replace(
            /&/g,
            '&amp;'
        )
        .replace(
            /</g,
            '&lt;'
        )
        .replace(
            />/g,
            '&gt;'
        )
        .replace(
            /"/g,
            '&quot;'
        )
        .replace(
            /'/g,
            '&#039;'
        );

}