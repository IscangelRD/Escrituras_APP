document.addEventListener(
    'DOMContentLoaded',
    () => {

        /*
        |--------------------------------------------------------------------------
        | Elementos principales
        |--------------------------------------------------------------------------
        */

        const tipoTramite =
            document.getElementById(
                'tipo_tramite_id'
            );

        const etapa =
            document.getElementById(
                'etapa_actual_id'
            );

        const etapaAyuda =
            document.getElementById(
                'etapaAyuda'
            );

        const buscarCliente =
            document.getElementById(
                'buscarCliente'
            );

        const btnBuscarCliente =
            document.getElementById(
                'btnBuscarCliente'
            );

        const resultados =
            document.getElementById(
                'resultadosClientes'
            );

        const clienteId =
            document.getElementById(
                'cliente_id'
            );

        const clienteSeleccionado =
            document.getElementById(
                'clienteSeleccionado'
            );

        const clienteNombre =
            document.getElementById(
                'clienteNombre'
            );

        const quitarCliente =
            document.getElementById(
                'quitarCliente'
            );


        /*
        |--------------------------------------------------------------------------
        | Modal nueva persona
        |--------------------------------------------------------------------------
        */

        const modal =
            document.getElementById(
                'modalNuevaPersona'
            );

        const btnNuevaPersona =
            document.getElementById(
                'btnNuevaPersona'
            );

        const cerrarModal =
            document.getElementById(
                'cerrarModalPersona'
            );

        const cancelarPersona =
            document.getElementById(
                'cancelarNuevaPersona'
            );

        const formularioPersona =
            document.getElementById(
                'formNuevaPersona'
            );

        const personaError =
            document.getElementById(
                'personaError'
            );

        const btnGuardarPersona =
            document.getElementById(
                'guardarNuevaPersona'
            );


        /*
        |--------------------------------------------------------------------------
        | Abrir modal
        |--------------------------------------------------------------------------
        */

        function abrirModalPersona() {

            if (!modal) {
                return;
            }

            modal.hidden = false;

            document.body.classList.add(
                'modal-open'
            );

            const nombre =
                document.getElementById(
                    'persona_nombre'
                );

            if (nombre) {
                setTimeout(
                    () => nombre.focus(),
                    100
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Cerrar modal
        |--------------------------------------------------------------------------
        */

        function cerrarModalPersona() {

            if (!modal) {
                return;
            }

            modal.hidden = true;

            document.body.classList.remove(
                'modal-open'
            );

            if (personaError) {

                personaError.hidden = true;

                personaError.textContent = '';

            }
        }


        if (btnNuevaPersona) {

            btnNuevaPersona.addEventListener(
                'click',
                abrirModalPersona
            );

        }


        if (cerrarModal) {

            cerrarModal.addEventListener(
                'click',
                cerrarModalPersona
            );

        }


        if (cancelarPersona) {

            cancelarPersona.addEventListener(
                'click',
                cerrarModalPersona
            );

        }


        if (modal) {

            modal.addEventListener(
                'click',
                event => {

                    if (
                        event.target === modal
                    ) {

                        cerrarModalPersona();

                    }

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Etapas
        |--------------------------------------------------------------------------
        */

        async function cargarEtapas() {

            const tipoId =
                tipoTramite.value;


            etapa.innerHTML = '';

            const opcionInicial =
                document.createElement(
                    'option'
                );

            opcionInicial.value = '';

            opcionInicial.textContent =
                tipoId
                    ? 'Cargando etapas...'
                    : 'Seleccionar etapa...';

            etapa.appendChild(
                opcionInicial
            );


            if (!tipoId) {

                etapaAyuda.textContent =
                    'Selecciona primero el tipo de trámite.';

                return;
            }


            try {

                const respuesta =
                    await fetch(
                        'obtener_etapas.php?tipo_tramite_id='
                        + encodeURIComponent(
                            tipoId
                        )
                    );


                const data =
                    await respuesta.json();


                if (!data.success) {

                    throw new Error(
                        data.message ||
                        'No se pudieron cargar las etapas.'
                    );

                }


                etapa.innerHTML = '';


                const opcion =
                    document.createElement(
                        'option'
                    );

                opcion.value = '';

                opcion.textContent =
                    'Seleccionar etapa...';

                etapa.appendChild(
                    opcion
                );


                if (!data.etapas.length) {

                    etapaAyuda.textContent =
                        'Este trámite todavía no tiene etapas configuradas.';

                    return;
                }


                data.etapas.forEach(
                    item => {

                        const option =
                            document.createElement(
                                'option'
                            );

                        option.value =
                            item.id;

                        option.textContent =
                            item.nombre;

                        etapa.appendChild(
                            option
                        );

                    }
                );


                etapaAyuda.textContent =
                    'Etapas configuradas para este tipo de trámite.';


            } catch (error) {

                console.error(error);

                etapa.innerHTML = '';

                const option =
                    document.createElement(
                        'option'
                    );

                option.value = '';

                option.textContent =
                    'Error al cargar etapas';

                etapa.appendChild(
                    option
                );

                etapaAyuda.textContent =
                    'Ocurrió un error al consultar las etapas.';

            }

        }


        if (tipoTramite) {

            tipoTramite.addEventListener(
                'change',
                cargarEtapas
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Buscar personas
        |--------------------------------------------------------------------------
        */

        async function buscarPersonas() {

            const texto =
                buscarCliente.value.trim();


            if (texto.length < 2) {

                resultados.innerHTML = `
                    <div class="client-placeholder">
                        Escribe al menos 2 caracteres.
                    </div>
                `;

                return;
            }


            resultados.innerHTML = `
                <div class="client-placeholder">
                    🔎 Buscando...
                </div>
            `;


            try {

                const respuesta =
                    await fetch(
                        'buscar_personas.php?q='
                        + encodeURIComponent(
                            texto
                        )
                    );


                const data =
                    await respuesta.json();


                if (!data.success) {

                    throw new Error(
                        data.message ||
                        'Error al buscar personas.'
                    );

                }


                if (!data.personas.length) {

                    resultados.innerHTML = `
                        <div class="client-placeholder">
                            No encontramos a esa persona.
                            <br><br>
                            Puedes utilizar
                            <strong>Crear persona</strong>
                            para registrarla.
                        </div>
                    `;

                    return;
                }


                resultados.innerHTML = '';


                data.personas.forEach(
                    persona => {

                        const item =
                            document.createElement(
                                'div'
                            );

                        item.className =
                            'client-result';


                        const informacion =
                            document.createElement(
                                'div'
                            );


                        const nombre =
                            document.createElement(
                                'div'
                            );

                        nombre.className =
                            'client-result-name';

                        nombre.textContent =
                            persona.nombre_completo;


                        const datos =
                            document.createElement(
                                'div'
                            );

                        datos.className =
                            'client-result-data';


                        const partes = [];


                        if (persona.curp) {

                            partes.push(
                                'CURP: ' +
                                persona.curp
                            );

                        }


                        if (persona.rfc) {

                            partes.push(
                                'RFC: ' +
                                persona.rfc
                            );

                        }


                        datos.textContent =
                            partes.join(' · ');


                        informacion.appendChild(
                            nombre
                        );

                        informacion.appendChild(
                            datos
                        );


                        const boton =
                            document.createElement(
                                'button'
                            );

                        boton.type =
                            'button';

                        boton.className =
                            'client-select-button';

                        boton.textContent =
                            'Seleccionar';


                        boton.addEventListener(
                            'click',
                            () => {

                                seleccionarCliente(
                                    persona
                                );

                            }
                        );


                        item.appendChild(
                            informacion
                        );

                        item.appendChild(
                            boton
                        );


                        resultados.appendChild(
                            item
                        );

                    }
                );


            } catch (error) {

                console.error(error);

                resultados.innerHTML = `
                    <div class="client-placeholder">
                        Ocurrió un error al buscar personas.
                    </div>
                `;

            }

        }


        if (btnBuscarCliente) {

            btnBuscarCliente.addEventListener(
                'click',
                buscarPersonas
            );

        }


        if (buscarCliente) {

            buscarCliente.addEventListener(
                'keydown',
                event => {

                    if (
                        event.key === 'Enter'
                    ) {

                        event.preventDefault();

                        buscarPersonas();

                    }

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Seleccionar cliente
        |--------------------------------------------------------------------------
        */

        function seleccionarCliente(persona) {

            clienteId.value =
                persona.cliente_id;

            clienteNombre.textContent =
                persona.nombre_completo;

            clienteSeleccionado.hidden =
                false;

            resultados.innerHTML = '';

            buscarCliente.value = '';

            buscarCliente.blur();

        }


        /*
        |--------------------------------------------------------------------------
        | Cambiar cliente
        |--------------------------------------------------------------------------
        */

        if (quitarCliente) {

            quitarCliente.addEventListener(
                'click',
                () => {

                    clienteId.value =
                        '';

                    clienteNombre.textContent =
                        '';

                    clienteSeleccionado.hidden =
                        true;

                    buscarCliente.focus();

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Guardar nueva persona
        |--------------------------------------------------------------------------
        */

        if (formularioPersona) {

            formularioPersona.addEventListener(
                'submit',
                async event => {

                    event.preventDefault();


                    if (
                        !formularioPersona.reportValidity()
                    ) {

                        return;

                    }


                    personaError.hidden =
                        true;

                    personaError.textContent =
                        '';


                    btnGuardarPersona.disabled =
                        true;

                    btnGuardarPersona.textContent =
                        'Guardando...';


                    try {

                        const datos =
                            new FormData(
                                formularioPersona
                            );


                        const respuesta =
                            await fetch(
                                'guardar_persona.php',
                                {
                                    method: 'POST',
                                    body: datos
                                }
                            );


                        const data =
                            await respuesta.json();


                        if (!data.success) {

                            throw new Error(
                                data.message ||
                                'No se pudo guardar la persona.'
                            );

                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Seleccionar automáticamente
                        |--------------------------------------------------------------------------
                        */

                        seleccionarCliente(
                            data.persona
                        );


                        /*
                        |--------------------------------------------------------------------------
                        | Limpiar formulario
                        |--------------------------------------------------------------------------
                        */

                        formularioPersona.reset();


                        /*
                        |--------------------------------------------------------------------------
                        | Cerrar modal
                        |--------------------------------------------------------------------------
                        */

                        cerrarModalPersona();


                        /*
                        |--------------------------------------------------------------------------
                        | Mensaje visual
                        |--------------------------------------------------------------------------
                        */

                        resultados.innerHTML = `
                            <div class="client-success">
                                ✓ Persona creada y seleccionada como cliente.
                            </div>
                        `;


                    } catch (error) {

                        console.error(error);

                        personaError.hidden =
                            false;

                        personaError.textContent =
                            error.message ||
                            'Ocurrió un error al guardar la persona.';


                    } finally {

                        btnGuardarPersona.disabled =
                            false;

                        btnGuardarPersona.textContent =
                            '💾 Guardar persona';

                    }

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Validación expediente
        |--------------------------------------------------------------------------
        */

        const formulario =
            document.getElementById(
                'formCrearExpediente'
            );


        if (formulario) {

            formulario.addEventListener(
                'submit',
                event => {

                    if (!clienteId.value) {

                        event.preventDefault();

                        alert(
                            'Selecciona o crea un cliente para continuar.'
                        );

                        buscarCliente.focus();

                        return;
                    }


                    if (!tipoTramite.value) {

                        event.preventDefault();

                        alert(
                            'Selecciona el tipo de trámite.'
                        );

                        tipoTramite.focus();

                        return;
                    }


                    if (!etapa.value) {

                        event.preventDefault();

                        alert(
                            'Selecciona la etapa inicial.'
                        );

                        etapa.focus();

                    }

                }
            );

        }

    }
);