document.addEventListener("DOMContentLoaded", function () {

    const form = document.getElementById("garantiaForm");

    if (!form) {
        return;
    }


    /*
    ============================================================
    FECHA Y HORA
    ============================================================
    */

    const fechaEntrada = document.getElementById("fecha_entrada");
    const horaEntrada = document.getElementById("hora_entrada");

    if (fechaEntrada && !fechaEntrada.value) {

        const ahora = new Date();

        fechaEntrada.value =
            ahora.toISOString().split("T")[0];

    }

    if (horaEntrada && !horaEntrada.value) {

        const ahora = new Date();

        const horas =
            String(ahora.getHours()).padStart(2, "0");

        const minutos =
            String(ahora.getMinutes()).padStart(2, "0");

        horaEntrada.value =
            `${horas}:${minutos}`;

    }


    /*
    ============================================================
    TELÉFONO
    ============================================================
    */

    const telefono =
        document.getElementById("telefono_cliente");

    if (telefono) {

        telefono.addEventListener("input", function () {

            this.value =
                this.value.replace(/[^0-9+\-\s]/g, "");

        });

    }


    /*
    ============================================================
    IMEI
    ============================================================
    */

    const imei =
        document.getElementById("imei");

    if (imei) {

        imei.addEventListener("input", function () {

            this.value =
                this.value.replace(/[^0-9]/g, "");

        });

    }


    /*
    ============================================================
    CONFIRMACIÓN ANTES DE GUARDAR
    ============================================================
    */

    form.addEventListener("submit", function (event) {

        const nombre =
            document.getElementById("nombre_cliente").value.trim();

        const telefono =
            document.getElementById("telefono_cliente").value.trim();

        const marca =
            document.getElementById("marca").value.trim();

        const modelo =
            document.getElementById("modelo").value.trim();

        const falla =
            document.getElementById("falla").value.trim();

        const costo =
            document.getElementById("costo").value;

        if (
            !nombre ||
            !telefono ||
            !marca ||
            !modelo ||
            !falla ||
            costo === ""
        ) {

            event.preventDefault();

            alert(
                "Por favor completa todos los campos obligatorios."
            );

            return;
        }


        const confirmar =
            confirm(
                "¿Deseas guardar este registro de garantía?"
            );

        if (!confirmar) {

            event.preventDefault();

        }

    });


    /*
    ============================================================
    LIMPIAR FORMULARIO
    ============================================================
    */

    form.addEventListener("reset", function () {

        setTimeout(function () {

            if (fechaEntrada) {

                const ahora = new Date();

                fechaEntrada.value =
                    ahora.toISOString().split("T")[0];

            }

            if (horaEntrada) {

                const ahora = new Date();

                const horas =
                    String(ahora.getHours()).padStart(2, "0");

                const minutos =
                    String(ahora.getMinutes()).padStart(2, "0");

                horaEntrada.value =
                    `${horas}:${minutos}`;

            }

        }, 0);

    });

});