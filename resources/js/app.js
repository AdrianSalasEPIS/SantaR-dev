import '@fontsource/playfair-display/600.css';
import '@fontsource/playfair-display/700.css';
import '@fontsource/nunito-sans/400.css';
import '@fontsource/nunito-sans/600.css';
import '@fontsource/nunito-sans/700.css';
import 'bootstrap-icons/font/bootstrap-icons.css';
import * as bootstrap from 'bootstrap';

window.bootstrap = bootstrap;

document.addEventListener('DOMContentLoaded', () => {
    // ------------------------------------------------------------------
    // RF-02 / RN-05: cierre automático por inactividad. En el terminal POS compartido,
    // tras 15 minutos sin actividad se cierra la sesión y se vuelve al inicio de sesión.
    const formSalir = document.getElementById('form-salir');
    if (formSalir) {
        const minutos = Number(document.body.dataset.inactividad || 15);
        let temporizador;
        const reiniciar = () => {
            clearTimeout(temporizador);
            temporizador = setTimeout(() => {
                formSalir.querySelector('[name="inactividad"]').value = '1';
                formSalir.submit();
            }, Math.max(minutos * 60 * 1000 - 5000, 5000));
        };
        ['click', 'keydown', 'touchstart', 'scroll'].forEach((e) => document.addEventListener(e, reiniciar, { passive: true }));
        reiniciar();
    }

    // ------------------------------------------------------------------
    // Mostrar u ocultar la contraseña en los campos que lo permiten.
    document.querySelectorAll('[data-ver-password]').forEach((boton) => {
        const campo = document.getElementById(boton.dataset.verPassword);
        if (!campo) return;
        boton.addEventListener('click', () => {
            const visible = campo.type === 'text';
            campo.type = visible ? 'password' : 'text';
            boton.innerHTML = visible ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
            boton.setAttribute('aria-pressed', String(!visible));
        });
    });

    // ------------------------------------------------------------------
    // RF-08: vista previa de la imagen del producto antes de guardarla.
    const archivo = document.getElementById('imagen');
    const vista = document.getElementById('vista-previa');
    if (archivo && vista) {
        archivo.addEventListener('change', () => {
            const f = archivo.files[0];
            if (f) {
                vista.src = URL.createObjectURL(f);
                vista.classList.remove('d-none');
                document.getElementById('sin-imagen')?.classList.add('d-none');
            }
        });
    }

    // RN-10: el tipo IMPLEMENTO no se vende ni se muestra en la carta.
    const tipo = document.getElementById('tipo');
    const visible = document.getElementById('visible_en_carta');
    if (tipo && visible) {
        const sincronizar = () => {
            const esImplemento = tipo.value === 'IMPLEMENTO';
            visible.disabled = esImplemento;
            if (esImplemento) visible.checked = false;
            document.querySelectorAll('[data-solo-producto]').forEach((el) => el.classList.toggle('d-none', esImplemento));
        };
        tipo.addEventListener('change', sincronizar);
        sincronizar();
    }

    // ------------------------------------------------------------------
    // Confirmación antes de desactivar o reactivar registros.
    document.querySelectorAll('form[data-confirmar]').forEach((f) => {
        f.addEventListener('submit', (e) => {
            if (!confirm(f.dataset.confirmar)) e.preventDefault();
        });
    });

    // Evita el doble envío de formularios (doble toque en la pantalla táctil).
    document.querySelectorAll('form[data-un-envio]').forEach((f) => {
        f.addEventListener('submit', () => {
            f.querySelectorAll('button:not([type="button"])').forEach((b) => {
                b.disabled = true;
            });
        });
    });
});
