document.addEventListener('DOMContentLoaded', () => {

    /*
    |--------------------------------------------------------------------------
    | DATOS PRINCIPALES
    |--------------------------------------------------------------------------
    */

    const expedienteInput =
        document.querySelector(
            '#formParticipante input[name="expediente_id"]'
        );

    const expedienteId =
        expedienteInput
            ? expedienteInput.value
            : '';


    /*
    |--------------------------------------------------------------------------
    | MODAL AGREGAR
    |--------------------------------------------------------------------------
    */

    const modal =
        document.getElementById(
            'modalParticipante'
        );

    const btnAgregar =
        document.getElementById(
            'btnAgregarParticipante'
        );

    const cerrar =
        document.getElementById(
            'cerrarModalParticipante'
        );

    const cancelar =
        document.getElementById(
            'cancelarParticipante'
        );

    const formulario =
        document.getElementById(
            'formParticipante'
        );


    /*
    |--------------------------------------------------------------------------
    | BUSCAR PERSONA
    |--------------------------------------------------------------------------
    */

    const buscar =
        document.getElementById(
            'buscarParticipante'
        );

    const resultados =
        document.getElementById(
            'resultadosParticipante'
        );

    const personaId =
        document.getElementById(
            'participantePersonaId'
        );

    const personaSeleccionada =
        document.getElementById(
            'participanteSeleccionado'
        );

    const personaNombre =
        document.getElementById(
            'participanteNombre'
        );

    const cambiar =
        document.getElementById(
            'cambiarParticipante'
        );


    /*
    |--------------------------------------------------------------------------
    | ROL
    |--------------------------------------------------------------------------
    */

    const rol =
        document.getElementById(
            'rolParticipante'
        );


    const grupoRepresentado =
        document.getElementById(
            'grupoRepresentado'
        );

    const personaRepresentada =
        document.getElementById(
            'personaRepresentada'
        );


    /*
    |--------------------------------------------------------------------------
    | GUARDAR PARTICIPANTE
    |--------------------------------------------------------------------------
    */

    const errorBox =
        document.getElementById(
            'participanteError'
        );

    const botonGuardar =
        document.getElementById(
            'guardarParticipante'
        );


    /*
    |--------------------------------------------------------------------------
    | CREAR PERSONA
    |--------------------------------------------------------------------------
    */

    const btnCrearPersona =
        document.getElementById(
            'btnCrearPersonaParticipante'
        );

    const formNuevaPersona =
        document.getElementById(
            'formNuevaPersonaParticipante'
        );

    const cancelarNuevaPersona =
        document.getElementById(
            'cancelarNuevaPersona'
        );

    const guardarNuevaPersona =
        document.getElementById(
            'guardarNuevaPersona'
        );

    const nuevaPersonaError =
        document.getElementById(
            'nuevaPersonaError'
        );


    /*
    |--------------------------------------------------------------------------
    | LISTA PARTICIPANTES
    |--------------------------------------------------------------------------
    */

    const lista =
        document.getElementById(
            'listaParticipantes'
        );


    /*
    |--------------------------------------------------------------------------
    | MODAL EDITAR
    |--------------------------------------------------------------------------
    */

    const modalEditar =
        document.getElementById(
            'modalEditarParticipante'
        );

    const cerrarModalEditar =
        document.getElementById(
            'cerrarModalEditar'
        );

    const cancelarEditarParticipante =
        document.getElementById(
            'cancelarEditarParticipante'
        );

    const formularioEditar =
        document.getElementById(
            'formEditarParticipante'
        );

    const editarParticipanteId =
        document.getElementById(
            'editarParticipanteId'
        );

    const editarPersonaNombre =
        document.getElementById(
            'editarPersonaNombre'
        );

    const editarPersonaSeleccionada =
        document.getElementById(
            'editarPersonaSeleccionada'
        );

    const editarRol =
        document.getElementById(
            'editarRolParticipante'
        );

    const editarRepresentado =
        document.getElementById(
            'editarPersonaRepresentada'
        );

    const grupoEditarRepresentado =
        document.getElementById(
            'grupoEditarRepresentado'
        );

    const editarObservaciones =
        document.getElementById(
            'editarObservaciones'
        );

    const editarError =
        document.getElementById(
            'editarParticipanteError'
        );

    const buscarEditar =
        document.getElementById(
            'buscarEditarParticipante'
        );

    const resultadosEditar =
        document.getElementById(
            'resultadosEditarParticipante'
        );

    const cambiarEditarPersona =
        document.getElementById(
            'cambiarEditarPersona'
        );

    const guardarEditar =
        document.getElementById(
            'guardarEditarParticipante'
        );


    /*
    |--------------------------------------------------------------------------
    | ABRIR MODAL AGREGAR
    |--------------------------------------------------------------------------
    */

    btnAgregar?.addEventListener(
        'click',
        () => {

            modal.hidden = false;

            document.body.classList.add(
                'modal-open'
            );

            cargarRoles();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | CERRAR MODAL AGREGAR
    |--------------------------------------------------------------------------
    */

    function cerrarModalParticipante() {

        modal.hidden = true;

        document.body.classList.remove(
            'modal-open'
        );

        formulario?.reset();

        if (personaId) {
            personaId.value = '';
        }

        if (personaSeleccionada) {
            personaSeleccionada.hidden = true;
        }

        if (resultados) {
            resultados.innerHTML = '';
        }

        if (grupoRepresentado) {
            grupoRepresentado.hidden = true;
        }

        if (errorBox) {
            errorBox.hidden = true;
        }

        if (formNuevaPersona) {
            formNuevaPersona.hidden = true;
        }

        if (btnCrearPersona) {
            btnCrearPersona.hidden = false;
        }

        limpiarNuevaPersona();

    }


    cerrar?.addEventListener(
        'click',
        cerrarModalParticipante
    );


    cancelar?.addEventListener(
        'click',
        cerrarModalParticipante
    );


    /*
    |--------------------------------------------------------------------------
    | CARGAR ROLES
    |--------------------------------------------------------------------------
    */

    async function cargarRoles() {

        if (!rol) {
            return;
        }


        try {

            const response =
                await fetch(
                    'obtener_roles_participante.php'
                );


            const data =
                await response.json();


            if (!data.success) {

                throw new Error(
                    data.message
                );

            }


            rol.innerHTML = `
                <option value="">
                    Seleccionar rol...
                </option>
            `;


            data.roles.forEach(
                item => {

                    const option =
                        document.createElement(
                            'option'
                        );

                    option.value =
                        item.id;

                    option.textContent =
                        item.nombre;

                    rol.appendChild(
                        option
                    );

                }
            );


        } catch (error) {

            mostrarError(
                error.message
            );

        }

    }


    /*
    |--------------------------------------------------------------------------
    | CAMBIO DE ROL - AGREGAR
    |--------------------------------------------------------------------------
    */

    rol?.addEventListener(
        'change',
        () => {

            controlarRepresentacion();

        }
    );


    function controlarRepresentacion() {

        const nombreRol =
            rol.options[
                rol.selectedIndex
            ]?.textContent
            ?.toLowerCase()
            || '';


        const requiereRepresentado =
            nombreRol.includes(
                'apoderado'
            )
            ||
            nombreRol.includes(
                'representante'
            );


        grupoRepresentado.hidden =
            !requiereRepresentado;


        if (!requiereRepresentado) {

            personaRepresentada.value =
                '';

            return;

        }


        cargarPersonasRepresentables();

    }


    /*
    |--------------------------------------------------------------------------
    | PERSONAS REPRESENTABLES
    |--------------------------------------------------------------------------
    */

    async function cargarPersonasRepresentables() {

        personaRepresentada.innerHTML = `
            <option value="">
                Cargando...
            </option>
        `;


        try {

            const response =
                await fetch(
                    'obtener_participantes.php?expediente_id='
                    +
                    encodeURIComponent(
                        expedienteId
                    )
                );


            const data =
                await response.json();


            if (!data.success) {

                throw new Error(
                    data.message
                );

            }


            personaRepresentada.innerHTML = `
                <option value="">
                    Seleccionar persona...
                </option>
            `;


            data.participantes.forEach(
                item => {

                    if (
                        Number(
                            item.persona_id
                        )
                        ===
                        Number(
                            personaId.value
                        )
                    ) {

                        return;

                    }


                    const option =
                        document.createElement(
                            'option'
                        );

                    option.value =
                        item.persona_id;

                    option.textContent =
                        item.nombre_completo;

                    personaRepresentada.appendChild(
                        option
                    );

                }
            );


        } catch (error) {

            mostrarError(
                error.message
            );

        }

    }


    /*
    |--------------------------------------------------------------------------
    | BUSCAR PERSONA
    |--------------------------------------------------------------------------
    */

    let timerBuscar = null;


    buscar?.addEventListener(
        'input',
        () => {

            clearTimeout(
                timerBuscar
            );


            const texto =
                buscar.value.trim();


            if (
                texto.length < 2
            ) {

                resultados.innerHTML =
                    '';

                return;

            }


            timerBuscar =
                setTimeout(
                    buscarPersonas,
                    300
                );

        }
    );


    async function buscarPersonas() {

        const texto =
            buscar.value.trim();


        resultados.innerHTML = `
            <div class="search-loading">
                🔎 Buscando...
            </div>
        `;


        try {

            const response =
                await fetch(
                    'buscar_personas_participantes.php?q='
                    +
                    encodeURIComponent(
                        texto
                    )
                );


            const data =
                await response.json();


            if (!data.success) {

                throw new Error(
                    data.message
                );

            }


            resultados.innerHTML =
                '';


            if (
                !data.personas.length
            ) {

                resultados.innerHTML = `
                    <div class="search-empty">
                        No encontramos esa persona.
                    </div>
                `;

                return;

            }


            data.personas.forEach(
                persona => {

                    const item =
                        crearResultadoPersona(
                            persona,
                            seleccionarPersona
                        );

                    resultados.appendChild(
                        item
                    );

                }
            );


        } catch (error) {

            resultados.innerHTML = `
                <div class="search-empty">
                    ${escapeHtml(
                        error.message
                    )}
                </div>
            `;

        }

    }


    /*
    |--------------------------------------------------------------------------
    | RESULTADO DE PERSONA
    |--------------------------------------------------------------------------
    */

    function crearResultadoPersona(
        persona,
        callback
    ) {

        const item =
            document.createElement(
                'button'
            );


        item.type =
            'button';


        item.className =
            'participant-search-item';


        const nombre =
            document.createElement(
                'strong'
            );


        nombre.textContent =
            persona.nombre_completo;


        const datos =
            document.createElement(
                'span'
            );


        datos.textContent =
            [
                persona.curp
                    ? 'CURP: ' + persona.curp
                    : '',

                persona.rfc
                    ? 'RFC: ' + persona.rfc
                    : ''

            ]
            .filter(Boolean)
            .join(' · ');


        item.appendChild(
            nombre
        );


        item.appendChild(
            datos
        );


        item.addEventListener(
            'click',
            () => {

                callback(
                    persona
                );

            }
        );


        return item;

    }


    /*
    |--------------------------------------------------------------------------
    | SELECCIONAR PERSONA
    |--------------------------------------------------------------------------
    */

    function seleccionarPersona(
        persona
    ) {

        personaId.value =
            persona.id;


        personaNombre.textContent =
            persona.nombre_completo;


        personaSeleccionada.hidden =
            false;


        buscar.value =
            '';


        resultados.innerHTML =
            '';

    }


    /*
    |--------------------------------------------------------------------------
    | CAMBIAR PERSONA
    |--------------------------------------------------------------------------
    */

    cambiar?.addEventListener(
        'click',
        () => {

            personaId.value =
                '';

            personaSeleccionada.hidden =
                true;

            buscar.focus();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | CREAR PERSONA NUEVA
    |--------------------------------------------------------------------------
    */

    btnCrearPersona?.addEventListener(
        'click',
        () => {

            formNuevaPersona.hidden =
                false;


            btnCrearPersona.hidden =
                true;


            resultados.innerHTML =
                '';


            document
                .getElementById(
                    'nuevaPersonaNombre'
                )
                ?.focus();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | CANCELAR CREAR PERSONA
    |--------------------------------------------------------------------------
    */

    cancelarNuevaPersona?.addEventListener(
        'click',
        () => {

            formNuevaPersona.hidden =
                true;


            btnCrearPersona.hidden =
                false;


            limpiarNuevaPersona();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | GUARDAR PERSONA NUEVA
    |--------------------------------------------------------------------------
    */

    guardarNuevaPersona?.addEventListener(
        'click',
        async () => {

            nuevaPersonaError.hidden =
                true;


            const nombre =
                obtenerValor(
                    'nuevaPersonaNombre'
                );


            const apellidoPaterno =
                obtenerValor(
                    'nuevaPersonaApellidoPaterno'
                );


            if (!nombre) {

                mostrarErrorNuevaPersona(
                    'El nombre es obligatorio.'
                );

                return;

            }


            if (!apellidoPaterno) {

                mostrarErrorNuevaPersona(
                    'El apellido paterno es obligatorio.'
                );

                return;

            }


            guardarNuevaPersona.disabled =
                true;


            guardarNuevaPersona.textContent =
                'Creando persona...';


            try {

                const formData =
                    new FormData();


                formData.append(
                    'nombre',
                    nombre
                );


                formData.append(
                    'apellido_paterno',
                    apellidoPaterno
                );


                formData.append(
                    'apellido_materno',
                    obtenerValor(
                        'nuevaPersonaApellidoMaterno'
                    )
                );


                formData.append(
                    'fecha_nacimiento',
                    obtenerValor(
                        'nuevaPersonaFechaNacimiento'
                    )
                );


                formData.append(
                    'curp',
                    obtenerValor(
                        'nuevaPersonaCurp'
                    ).toUpperCase()
                );


                formData.append(
                    'rfc',
                    obtenerValor(
                        'nuevaPersonaRfc'
                    ).toUpperCase()
                );


                formData.append(
                    'telefono',
                    obtenerValor(
                        'nuevaPersonaTelefono'
                    )
                );


                formData.append(
                    'correo',
                    obtenerValor(
                        'nuevaPersonaCorreo'
                    )
                );


                formData.append(
                    'domicilio',
                    obtenerValor(
                        'nuevaPersonaDomicilio'
                    )
                );


                formData.append(
                    'observaciones',
                    obtenerValor(
                        'nuevaPersonaObservaciones'
                    )
                );


                const response =
                    await fetch(
                        'guardar_persona_participante.php',
                        {
                            method: 'POST',
                            body: formData
                        }
                    );


                const data =
                    await response.json();


                if (!data.success) {

                    throw new Error(
                        data.message
                    );

                }


                /*
                | Seleccionar automáticamente
                */

                seleccionarPersona(
                    data.persona
                );


                /*
                | Ocultar formulario
                */

                formNuevaPersona.hidden =
                    true;


                btnCrearPersona.hidden =
                    false;


                limpiarNuevaPersona();


                /*
                | Ir al rol
                */

                rol.focus();


            } catch (error) {

                mostrarErrorNuevaPersona(
                    error.message
                );

            } finally {

                guardarNuevaPersona.disabled =
                    false;


                guardarNuevaPersona.textContent =
                    '👤 Crear y seleccionar persona';

            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | LIMPIAR NUEVA PERSONA
    |--------------------------------------------------------------------------
    */

    function limpiarNuevaPersona() {

        const campos = [

            'nuevaPersonaNombre',

            'nuevaPersonaApellidoPaterno',

            'nuevaPersonaApellidoMaterno',

            'nuevaPersonaFechaNacimiento',

            'nuevaPersonaCurp',

            'nuevaPersonaRfc',

            'nuevaPersonaTelefono',

            'nuevaPersonaCorreo',

            'nuevaPersonaDomicilio',

            'nuevaPersonaObservaciones'

        ];


        campos.forEach(
            id => {

                const elemento =
                    document.getElementById(
                        id
                    );


                if (elemento) {

                    elemento.value =
                        '';

                }

            }
        );


        if (nuevaPersonaError) {

            nuevaPersonaError.hidden =
                true;

        }

    }


    /*
    |--------------------------------------------------------------------------
    | GUARDAR PARTICIPANTE
    |--------------------------------------------------------------------------
    */

    formulario?.addEventListener(
        'submit',
        async event => {

            event.preventDefault();


            if (!personaId.value) {

                mostrarError(
                    'Selecciona una persona.'
                );

                return;

            }


            if (!rol.value) {

                mostrarError(
                    'Selecciona el rol del participante.'
                );

                return;

            }


            errorBox.hidden =
                true;


            botonGuardar.disabled =
                true;


            botonGuardar.textContent =
                'Guardando...';


            try {

                const formData =
                    new FormData(
                        formulario
                    );


                const response =
                    await fetch(
                        'guardar_participante.php',
                        {
                            method: 'POST',
                            body: formData
                        }
                    );


                const data =
                    await response.json();


                if (!data.success) {

                    throw new Error(
                        data.message
                    );

                }


                cerrarModalParticipante();


                cargarParticipantes();


            } catch (error) {

                mostrarError(
                    error.message
                );


            } finally {

                botonGuardar.disabled =
                    false;


                botonGuardar.textContent =
                    '👥 Agregar participante';

            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | CARGAR PARTICIPANTES
    |--------------------------------------------------------------------------
    */

    async function cargarParticipantes() {

        if (!lista) {
            return;
        }


        lista.innerHTML = `
            <div class="empty-box">
                <span>🔎</span>
                <p>
                    Cargando participantes...
                </p>
            </div>
        `;


        try {

            const response =
                await fetch(
                    'obtener_participantes.php?expediente_id='
                    +
                    encodeURIComponent(
                        expedienteId
                    )
                );


            const data =
                await response.json();


            if (!data.success) {

                throw new Error(
                    data.message
                );

            }


            lista.innerHTML =
                '';


            if (
                !data.participantes.length
            ) {

                lista.innerHTML = `
                    <div class="empty-box">
                        <span>👥</span>
                        <p>
                            Todavía no hay participantes registrados.
                        </p>
                    </div>
                `;

                return;

            }


            data.participantes.forEach(
                participante => {

                    const card =
                        document.createElement(
                            'div'
                        );


                    card.className =
                        'participant-card';


                    card.dataset.participantId =
                        participante.id;


                    /*
                    | Avatar
                    */

                    const avatar =
                        document.createElement(
                            'div'
                        );


                    avatar.className =
                        'participant-avatar';


                    avatar.textContent =
                        participante
                            .nombre_completo
                            .charAt(0)
                            .toUpperCase();


                    /*
                    | Información
                    */

                    const info =
                        document.createElement(
                            'div'
                        );


                    info.className =
                        'participant-info';


                    const nombre =
                        document.createElement(
                            'strong'
                        );


                    nombre.textContent =
                        participante
                            .nombre_completo;


                    const rolTexto =
                        document.createElement(
                            'span'
                        );


                    rolTexto.textContent =
                        participante
                            .rol_nombre;


                    info.appendChild(
                        nombre
                    );


                    info.appendChild(
                        rolTexto
                    );


                    /*
                    | Representación
                    */

                    if (
                        participante
                            .representada_nombre_completo
                    ) {

                        const representa =
                            document.createElement(
                                'small'
                            );


                        representa.textContent =
                            'Representa a: '
                            +
                            participante
                                .representada_nombre_completo;


                        info.appendChild(
                            representa
                        );

                    }


                    /*
                    | Observaciones
                    */

                    if (
                        participante
                            .observaciones
                    ) {

                        const observacion =
                            document.createElement(
                                'small'
                            );


                        observacion.textContent =
                            '📝 '
                            +
                            participante
                                .observaciones;


                        info.appendChild(
                            observacion
                        );

                    }


                    /*
                    | Acciones
                    */

                    const acciones =
                        document.createElement(
                            'div'
                        );


                    acciones.className =
                        'participant-actions';


                    /*
                    | Editar
                    */

                    const btnEditar =
                        document.createElement(
                            'button'
                        );


                    btnEditar.type =
                        'button';


                    btnEditar.className =
                        'participant-action participant-edit';


                    btnEditar.title =
                        'Editar participante';


                    btnEditar.textContent =
                        '✏️';


                    btnEditar.addEventListener(
                        'click',
                        () => {

                            abrirEditarParticipante(
                                participante
                            );

                        }
                    );


                    /*
                    | Quitar
                    */

                    const btnEliminar =
                        document.createElement(
                            'button'
                        );


                    btnEliminar.type =
                        'button';


                    btnEliminar.className =
                        'participant-action participant-delete';


                    btnEliminar.title =
                        'Quitar participante';


                    btnEliminar.textContent =
                        '🗑️';


                    btnEliminar.addEventListener(
                        'click',
                        () => {

                            quitarParticipante(
                                participante
                            );

                        }
                    );


                    acciones.appendChild(
                        btnEditar
                    );


                    acciones.appendChild(
                        btnEliminar
                    );


                    /*
                    | Armar tarjeta
                    */

                    card.appendChild(
                        avatar
                    );


                    card.appendChild(
                        info
                    );


                    card.appendChild(
                        acciones
                    );


                    lista.appendChild(
                        card
                    );

                }
            );


        } catch (error) {

            lista.innerHTML = `
                <div class="empty-box">
                    <span>⚠️</span>
                    <p>
                        ${escapeHtml(
                            error.message
                        )}
                    </p>
                </div>
            `;

        }

    }


    /*
    |--------------------------------------------------------------------------
    | QUITAR PARTICIPANTE
    |--------------------------------------------------------------------------
    */

    async function quitarParticipante(
        participante
    ) {

        const confirmar =
            confirm(
                '¿Quieres retirar a '
                +
                participante.nombre_completo
                +
                ' de este expediente?'
            );


        if (!confirmar) {

            return;

        }


        try {

            const formData =
                new FormData();


            formData.append(
                'id',
                participante.id
            );


            const response =
                await fetch(
                    'quitar_participante.php',
                    {
                        method: 'POST',
                        body: formData
                    }
                );


            const data =
                await response.json();


            if (!data.success) {

                throw new Error(
                    data.message
                );

            }


            cargarParticipantes();


        } catch (error) {

            alert(
                error.message
            );

        }

    }


    /*
    |--------------------------------------------------------------------------
    | ABRIR EDITAR
    |--------------------------------------------------------------------------
    */

    async function abrirEditarParticipante(
        participante
    ) {

        editarParticipanteId.value =
            participante.id;


        editarPersonaNombre.textContent =
            participante.nombre_completo;


        editarPersonaSeleccionada.hidden =
            false;


        editarPersonaSeleccionada.dataset.personaId =
            participante.persona_id;


        editarObservaciones.value =
            participante.observaciones || '';


        editarError.hidden =
            true;


        try {

            await cargarRolesEditar(
                participante
            );


            await cargarRepresentadoEditar(
                participante
            );


            modalEditar.hidden =
                false;


            document.body.classList.add(
                'modal-open'
            );


        } catch (error) {

            mostrarErrorEditar(
                error.message
            );

        }

    }


    /*
    |--------------------------------------------------------------------------
    | ROLES EDITAR
    |--------------------------------------------------------------------------
    */

    async function cargarRolesEditar(
        participante
    ) {

        const response =
            await fetch(
                'obtener_roles_participante.php'
            );


        const data =
            await response.json();


        if (!data.success) {

            throw new Error(
                data.message
            );

        }


        editarRol.innerHTML = `
            <option value="">
                Seleccionar rol...
            </option>
        `;


        data.roles.forEach(
            item => {

                const option =
                    document.createElement(
                        'option'
                    );


                option.value =
                    item.id;


                option.textContent =
                    item.nombre;


                if (
                    Number(item.id)
                    ===
                    Number(
                        participante
                            .rol_participante_id
                    )
                ) {

                    option.selected =
                        true;

                }


                editarRol.appendChild(
                    option
                );

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | REPRESENTADO EDITAR
    |--------------------------------------------------------------------------
    */

    async function cargarRepresentadoEditar(
        participante
    ) {

        const nombreRol =
            editarRol.options[
                editarRol.selectedIndex
            ]?.textContent
            ?.toLowerCase()
            || '';


        const requiereRepresentado =
            nombreRol.includes(
                'apoderado'
            )
            ||
            nombreRol.includes(
                'representante'
            );


        grupoEditarRepresentado.hidden =
            !requiereRepresentado;


        editarRepresentado.innerHTML = `
            <option value="">
                Seleccionar persona...
            </option>
        `;


        if (!requiereRepresentado) {

            return;

        }


        const response =
            await fetch(
                'obtener_participantes.php?expediente_id='
                +
                encodeURIComponent(
                    expedienteId
                )
            );


        const data =
            await response.json();


        if (!data.success) {

            throw new Error(
                data.message
            );

        }


        data.participantes.forEach(
            item => {

                /*
                | No permitimos que se
                | represente a sí mismo.
                */

                if (
                    Number(
                        item.persona_id
                    )
                    ===
                    Number(
                        participante.persona_id
                    )
                ) {

                    return;

                }


                const option =
                    document.createElement(
                        'option'
                    );


                option.value =
                    item.persona_id;


                option.textContent =
                    item.nombre_completo;


                if (
                    participante
                        .persona_representada_id
                    &&
                    Number(
                        item.persona_id
                    )
                    ===
                    Number(
                        participante
                            .persona_representada_id
                    )
                ) {

                    option.selected =
                        true;

                }


                editarRepresentado.appendChild(
                    option
                );

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | CAMBIO DE ROL EN EDICIÓN
    |--------------------------------------------------------------------------
    */

    editarRol?.addEventListener(
        'change',
        async () => {

            const nombreRol =
                editarRol.options[
                    editarRol.selectedIndex
                ]?.textContent
                ?.toLowerCase()
                || '';


            const requiereRepresentado =
                nombreRol.includes(
                    'apoderado'
                )
                ||
                nombreRol.includes(
                    'representante'
                );


            grupoEditarRepresentado.hidden =
                !requiereRepresentado;


            if (!requiereRepresentado) {

                editarRepresentado.value =
                    '';

                return;

            }


            try {

                const response =
                    await fetch(
                        'obtener_participantes.php?expediente_id='
                        +
                        encodeURIComponent(
                            expedienteId
                        )
                    );


                const data =
                    await response.json();


                if (!data.success) {

                    throw new Error(
                        data.message
                    );

                }


                editarRepresentado.innerHTML = `
                    <option value="">
                        Seleccionar persona...
                    </option>
                `;


                const personaEditada =
                    obtenerPersonaEditadaId();


                data.participantes.forEach(
                    item => {

                        if (
                            Number(
                                item.persona_id
                            )
                            ===
                            Number(
                                personaEditada
                            )
                        ) {

                            return;

                        }


                        const option =
                            document.createElement(
                                'option'
                            );


                        option.value =
                            item.persona_id;


                        option.textContent =
                            item.nombre_completo;


                        editarRepresentado.appendChild(
                            option
                        );

                    }
                );


            } catch (error) {

                mostrarErrorEditar(
                    error.message
                );

            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | CAMBIAR PERSONA EN EDICIÓN
    |--------------------------------------------------------------------------
    */

    cambiarEditarPersona?.addEventListener(
        'click',
        () => {

            editarPersonaSeleccionada.hidden =
                true;


            editarPersonaSeleccionada.dataset
                .personaId =
                '';


            buscarEditar.focus();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | BUSCAR PERSONA PARA EDICIÓN
    |--------------------------------------------------------------------------
    */

    let timerEditar = null;


    buscarEditar?.addEventListener(
        'input',
        () => {

            clearTimeout(
                timerEditar
            );


            const texto =
                buscarEditar.value.trim();


            if (
                texto.length < 2
            ) {

                resultadosEditar.innerHTML =
                    '';

                return;

            }


            timerEditar =
                setTimeout(
                    buscarPersonasEditar,
                    300
                );

        }
    );


    async function buscarPersonasEditar() {

        const texto =
            buscarEditar.value.trim();


        resultadosEditar.innerHTML = `
            <div class="search-loading">
                🔎 Buscando...
            </div>
        `;


        try {

            const response =
                await fetch(
                    'buscar_personas_participantes.php?q='
                    +
                    encodeURIComponent(
                        texto
                    )
                );


            const data =
                await response.json();


            if (!data.success) {

                throw new Error(
                    data.message
                );

            }


            resultadosEditar.innerHTML =
                '';


            if (
                !data.personas.length
            ) {

                resultadosEditar.innerHTML = `
                    <div class="search-empty">
                        No encontramos esa persona.
                    </div>
                `;

                return;

            }


            data.personas.forEach(
                persona => {

                    const item =
                        crearResultadoPersona(
                            persona,
                            seleccionarPersonaEdicion
                        );


                    resultadosEditar.appendChild(
                        item
                    );

                }
            );


        } catch (error) {

            resultadosEditar.innerHTML = `
                <div class="search-empty">
                    ${escapeHtml(
                        error.message
                    )}
                </div>
            `;

        }

    }


    /*
    |--------------------------------------------------------------------------
    | SELECCIONAR PERSONA EN EDICIÓN
    |--------------------------------------------------------------------------
    */

    function seleccionarPersonaEdicion(
        persona
    ) {

        editarPersonaSeleccionada.dataset
            .personaId =
            persona.id;


        editarPersonaNombre.textContent =
            persona.nombre_completo;


        editarPersonaSeleccionada.hidden =
            false;


        buscarEditar.value =
            '';


        resultadosEditar.innerHTML =
            '';

    }


    /*
    |--------------------------------------------------------------------------
    | GUARDAR EDICIÓN
    |--------------------------------------------------------------------------
    */

    formularioEditar?.addEventListener(
        'submit',
        async event => {

            event.preventDefault();


            editarError.hidden =
                true;


            const personaIdEditar =
                obtenerPersonaEditadaId();


            if (!personaIdEditar) {

                mostrarErrorEditar(
                    'Selecciona una persona.'
                );

                return;

            }


            if (!editarRol.value) {

                mostrarErrorEditar(
                    'Selecciona el rol del participante.'
                );

                return;

            }


            guardarEditar.disabled =
                true;


            guardarEditar.textContent =
                'Guardando...';


            try {

                const formData =
                    new FormData();


                formData.append(
                    'id',
                    editarParticipanteId.value
                );


                formData.append(
                    'persona_id',
                    personaIdEditar
                );


                formData.append(
                    'rol_participante_id',
                    editarRol.value
                );


                formData.append(
                    'persona_representada_id',
                    editarRepresentado.value
                );


                formData.append(
                    'observaciones',
                    editarObservaciones.value.trim()
                );


                const response =
                    await fetch(
                        'editar_participante.php',
                        {
                            method: 'POST',
                            body: formData
                        }
                    );


                const data =
                    await response.json();


                if (!data.success) {

                    throw new Error(
                        data.message
                    );

                }


                cerrarEditar();


                cargarParticipantes();


            } catch (error) {

                mostrarErrorEditar(
                    error.message
                );

            } finally {

                guardarEditar.disabled =
                    false;


                guardarEditar.textContent =
                    '💾 Guardar cambios';

            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | CERRAR EDICIÓN
    |--------------------------------------------------------------------------
    */

    function cerrarEditar() {

        if (modalEditar) {

            modalEditar.hidden =
                true;

        }


        if (editarError) {

            editarError.hidden =
                true;

        }


        document.body.classList.remove(
            'modal-open'
        );

    }


    cerrarModalEditar?.addEventListener(
        'click',
        cerrarEditar
    );


    cancelarEditarParticipante?.addEventListener(
        'click',
        cerrarEditar
    );


    /*
    |--------------------------------------------------------------------------
    | FUNCIONES AUXILIARES
    |--------------------------------------------------------------------------
    */

    function mostrarError(
        mensaje
    ) {

        if (!errorBox) {
            return;
        }


        errorBox.hidden =
            false;


        errorBox.textContent =
            mensaje;

    }


    function mostrarErrorNuevaPersona(
        mensaje
    ) {

        if (!nuevaPersonaError) {
            return;
        }


        nuevaPersonaError.hidden =
            false;


        nuevaPersonaError.textContent =
            mensaje;

    }


    function mostrarErrorEditar(
        mensaje
    ) {

        if (!editarError) {
            return;
        }


        editarError.hidden =
            false;


        editarError.textContent =
            mensaje;

    }


    function obtenerValor(
        id
    ) {

        const elemento =
            document.getElementById(
                id
            );


        return elemento
            ? elemento.value.trim()
            : '';

    }


    function obtenerPersonaEditadaId() {

        return (
            editarPersonaSeleccionada
                ?.dataset
                ?.personaId
            || ''
        );

    }


    function escapeHtml(
        texto
    ) {

        const div =
            document.createElement(
                'div'
            );


        div.textContent =
            texto;


        return div.innerHTML;

    }


    /*
    |--------------------------------------------------------------------------
    | INICIO
    |--------------------------------------------------------------------------
    */

    cargarParticipantes();

});