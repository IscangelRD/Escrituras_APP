<?php

declare(strict_types=1);


/*
|--------------------------------------------------------------------------
| CONFIGURACIÓN
|--------------------------------------------------------------------------
*/

require_once dirname(__DIR__, 1) . '/config/database.php';
require_once dirname(__DIR__, 1) . '/app/Helpers/permisos.php';


/*
|--------------------------------------------------------------------------
| VERIFICAR PERMISO
|--------------------------------------------------------------------------
*/

exigirPermiso(
    $pdo,
    'expedientes',
    'ver'
);


/*
|--------------------------------------------------------------------------
| OBTENER ID DEL EXPEDIENTE
|--------------------------------------------------------------------------
*/

$expedienteId =
    filter_input(
        INPUT_GET,
        'id',
        FILTER_VALIDATE_INT
    );


if (!$expedienteId) {

    die(
        'ID de expediente inválido.'
    );

}


/*
|--------------------------------------------------------------------------
| BUSCAR EXPEDIENTE
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("

    SELECT

        id,
        numero_expediente,
        cliente_id,
        estado_id,
        etapa_actual_id

    FROM expedientes

    WHERE id = ?

    LIMIT 1

");


$stmt->execute([
    $expedienteId
]);


$expediente =
    $stmt->fetch(
        PDO::FETCH_ASSOC
    );


if (!$expediente) {

    die(
        'El expediente no existe.'
    );

}

?>
<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Historial | Sistema de Escrituras
    </title>


    <link
        rel="stylesheet"
        href="../public/css/dashboard.css"
    >

    <link
        rel="stylesheet"
        href="../public/css/historial.css"
    >

</head>


<body>


<div class="historial-page">


    <!-- =====================================================
         ENCABEZADO
    ====================================================== -->

    <header class="historial-header">

        <div class="historial-header-left">


            <a
                href="../public/expedientes/ver.php"
                class="historial-back"
            >
                ←
            </a>


            <div>

                <span class="historial-kicker">
                    EXPEDIENTE
                </span>


                <h1>
                    📜 Historial
                </h1>


                <p>

                    <?= htmlspecialchars(
                        $expediente['numero_expediente']
                        ?? 'Sin número',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>

                </p>

            </div>


        </div>

    </header>



    <!-- =====================================================
         CONTENIDO
    ====================================================== -->

    <main class="historial-main">


        <!-- =================================================
             RESUMEN
        ================================================== -->

        <section class="historial-summary">


            <div class="historial-summary-icon">
                📋
            </div>


            <div>

                <span>
                    BITÁCORA DEL EXPEDIENTE
                </span>


                <strong>
                    Movimientos registrados
                </strong>


                <p>
                    Aquí se muestran las acciones
                    realizadas sobre este expediente.
                </p>

            </div>


        </section>



        <!-- =================================================
             FILTROS
        ================================================== -->

        <section class="historial-filters">


            <div class="historial-filter-group">

                <label
                    for="filtroHistorial"
                >
                    Mostrar
                </label>


                <select
                    id="filtroHistorial"
                >

                    <option value="todos">
                        Todos
                    </option>

                    <option value="expedientes">
                        Expediente
                    </option>

                    <option value="personas">
                        Personas
                    </option>

                    <option value="participantes">
                        Participantes
                    </option>

                    <option value="documentos">
                        Documentos
                    </option>

                    <option value="etapas">
                        Etapas
                    </option>

                    <option value="pagos">
                        Pagos
                    </option>

                </select>

            </div>



            <div class="historial-search">

                <label
                    for="buscarHistorial"
                >
                    Buscar
                </label>


                <input
                    type="search"
                    id="buscarHistorial"
                    placeholder="Buscar movimiento..."
                    autocomplete="off"
                >

            </div>


        </section>



        <!-- =================================================
             LISTA
        ================================================== -->

        <section
            id="listaHistorial"
            class="historial-list"
            data-expediente-id="<?= (int)$expedienteId ?>"
        >

            <div class="historial-loading">

                <div>
                    ⏳
                </div>

                <p>
                    Cargando historial...
                </p>

            </div>

        </section>


    </main>


</div>



<script>

    window.EXPEDIENTE_ID =
        <?= (int)$expedienteId ?>;

</script>


<script
    src="../public/js/historial.js"
></script>


</body>

</html>