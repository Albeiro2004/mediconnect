const API = (() => {
    const p = window.location.pathname;
    const iv = p.indexOf('/views/');
    if (iv > 0) return p.slice(0, iv);
    const ia = p.indexOf('/assets/');
    if (ia > 0) return p.slice(0, ia);
    return '';
})();
let todasLasCitas = [];
let citaAcancelarId = null;
const modalCancelar = new bootstrap.Modal(document.getElementById('modal-cancelar'));

/* ── Toast ───────────────────────────────────────────────── */
function toast(msg, type = 'success') {
    const box = document.getElementById('toast-box');
    const id  = `toast-${Date.now()}`;
    box.insertAdjacentHTML('beforeend', `
        <div id="${id}" class="toast align-items-center text-bg-${type} border-0 show" role="alert">
            <div class="d-flex">
                <div class="toast-body small">${msg}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto"
                        onclick="document.getElementById('${id}').remove()"></button>
            </div>
        </div>
    `);
    setTimeout(() => document.getElementById(id)?.remove(), 4000);
}

/* ── Spinner ─────────────────────────────────────────────── */
function spinner(show) {
    document.getElementById('spinner-overlay').classList.toggle('show', show);
}

/* ── Badge de estado ─────────────────────────────────────── */
function badgeEstado(estado) {
    return `<span class="cita-badge badge-${estado}">${estado.charAt(0).toUpperCase() + estado.slice(1)}</span>`;
}

/* ── Renderizar lista de citas ───────────────────────────── */
function renderCitas(citas) {
    const lista  = document.getElementById('lista-citas');
    const empty  = document.getElementById('empty-state');

    lista.innerHTML = '';

    if (citas.length === 0) {
        empty.classList.remove('d-none');
        return;
    }

    empty.classList.add('d-none');

    citas.forEach(c => {
        const fecha = new Date(c.fecha_cita + 'T00:00:00').toLocaleDateString('es-CO', {
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
        });
        const hora = c.hora_cita.slice(0, 5);

        const puedeCancel = c.estado === 'pendiente';

        lista.insertAdjacentHTML('beforeend', `
            <div class="col-12 col-md-4">
                <div class="card mc-card p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="fw-bold mb-0">${c.nombre_servicio}</h6>
                        ${badgeEstado(c.estado)}
                    </div>
                    <p class="small text-muted mb-1">
                        👨‍⚕️ ${c.nombre_medico}
                    </p>
                    <p class="small text-muted mb-1">
                        🏥 ${c.nombre_sede}
                    </p>
                    <p class="small text-muted mb-0">
                        📅 ${fecha} &nbsp;·&nbsp; 🕐 ${hora}
                    </p>
                    ${puedeCancel ? `
                    <div class="mt-3 text-end">
                        <button class="btn-cancelar"
                                onclick="solicitarCancelar(${c.id}, '${c.nombre_servicio}', '${fecha}', '${hora}')">
                            Cancelar cita
                        </button>
                    </div>` : ''}
                </div>
            </div>
        `);
    });
}

/* ── Cargar citas desde la API ───────────────────────────── */
async function cargarCitas() {
    spinner(true);
    try {
        const res  = await fetch(`${API}/citas`, { credentials: 'include' });
        const data = await res.json();

        if (!res.ok) {
            if (res.status === 401) {
                window.location.href = `${API}/views/auth/login.php`;
                return;
            }
            toast(data.error ?? 'Error al cargar citas.', 'danger');
            return;
        }

        todasLasCitas = data.citas ?? [];

        const pendientes = todasLasCitas.filter(c => c.estado === 'pendiente');

        renderCitas(pendientes);

    } catch {
        toast('Error de conexión.', 'danger');
    } finally {
        spinner(false);
    }
}

/* ── Filtros ─────────────────────────────────────────────── */
document.querySelectorAll('.filter-pill').forEach(btn => {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.filter-pill').forEach(b => b.classList.remove('active'));
        this.classList.add('active');

        const filtro = this.dataset.filter;
        const filtradas = filtro === 'todas'
            ? todasLasCitas
            : todasLasCitas.filter(c => c.estado === filtro);

        renderCitas(filtradas);
    });
});

/* ── Cancelar cita ───────────────────────────────────────── */
function solicitarCancelar(id, servicio, fecha, hora) {
    citaAcancelarId = id;
    document.getElementById('modal-cancelar-info').innerHTML =
        `<strong>${servicio}</strong><br>${fecha} a las ${hora}`;
    modalCancelar.show();
}

document.getElementById('btn-confirmar-cancelar').addEventListener('click', async () => {
    if (!citaAcancelarId) return;

    modalCancelar.hide();
    spinner(true);

    try {
        const res  = await fetch(`${API}/citas/${citaAcancelarId}`, {
            method:      'DELETE',
            credentials: 'include',
        });
        const data = await res.json();

        if (!res.ok) {
            toast(data.error ?? 'No se pudo cancelar.', 'danger');
            return;
        }

        toast('Cita cancelada correctamente.');
        await cargarCitas();

    } catch {
        toast('Error de conexión.', 'danger');
    } finally {
        spinner(false);
        citaAcancelarId = null;
    }
});

/* ── Logout ──────────────────────────────────────────────── */
document.getElementById('btn-logout').addEventListener('click', async () => {
    await fetch(`${API}/auth/logout`, { method: 'POST', credentials: 'include' });
    window.location.href = `${API}/views/auth/login.php`;
});

/* ── Init ────────────────────────────────────────────────── */
cargarCitas();