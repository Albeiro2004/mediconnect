const API = (() => {
    const p = window.location.pathname;
    const iv = p.indexOf('/views/');
    if (iv > 0) return p.slice(0, iv);
    const ia = p.indexOf('/assets/');
    if (ia > 0) return p.slice(0, ia);
    return '';
})();

let citaEditandoId = null;
let medicoId       = null;
const modalEstado  = new bootstrap.Modal(document.getElementById('modal-estado'));

/* ── Toast ───────────────────────────────────────────────── */
function toast(msg, type = 'success') {
    const box = document.getElementById('toast-box');
    const id  = `t-${Date.now()}`;
    box.insertAdjacentHTML('beforeend', `
        <div id="${id}" class="toast align-items-center text-bg-${type} border-0 show">
            <div class="d-flex">
                <div class="toast-body small">${msg}</div>
                <button class="btn-close btn-close-white me-2 m-auto"
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

/* ── Badge estado ────────────────────────────────────────── */
function badgeEstado(estado) {
    return `<span class="badge badge-${estado}">${estado.charAt(0).toUpperCase() + estado.slice(1)}</span>`;
}

/* ── Obtener medicoId del usuario en sesión ──────────────── */
async function obtenerMedicoId() {
    const res  = await fetch(`${API}/auth/me`, { credentials: 'include' });
    const data = await res.json();

    if (!res.ok) {
        window.location.href = `${API}/views/auth/login.php`;
        return null;
    }

    // Buscar el médico asociado al usuario
    const res2  = await fetch(`${API}/medicos`, { credentials: 'include' });
    const data2 = await res2.json();

    const medico = (data2.medicos ?? []).find(m => m.usuario_id === data.usuario.id);
    return medico?.id ?? null;
}

/* ── Renderizar tabla ────────────────────────────────────── */
function renderCitas(citas) {
    const tbody = document.getElementById('tabla-citas');
    tbody.innerHTML = '';

    document.getElementById('stat-total').textContent      = citas.length;
    document.getElementById('stat-pendientes').textContent  = citas.filter(c => c.estado === 'pendiente').length;
    document.getElementById('stat-confirmadas').textContent = citas.filter(c => c.estado === 'confirmada').length;
    document.getElementById('stat-finalizadas').textContent = citas.filter(c => c.estado === 'finalizada').length;

    if (citas.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">No hay citas para mostrar.</td></tr>';
        return;
    }

    citas.forEach(c => {
        const fecha = new Date(c.fecha_cita + 'T00:00:00').toLocaleDateString('es-CO', {
            day: '2-digit', month: 'short', year: 'numeric'
        });

        const puedeCambiar = ['pendiente', 'confirmada'].includes(c.estado);

        tbody.insertAdjacentHTML('beforeend', `
            <tr>
                <td class="text-muted">#${c.id}</td>
                <td>${c.nombre_cliente}</td>
                <td>${c.nombre_servicio}</td>
                <td>${fecha}</td>
                <td>${c.hora_cita.slice(0, 5)}</td>
                <td>${badgeEstado(c.estado)}</td>
                <td class="text-end">
                    ${puedeCambiar ? `
                    <button class="btn btn-xs btn-outline-secondary"
                            onclick="abrirModalEstado(${c.id}, '${c.estado}', '${c.nombre_cliente}', '${fecha}')">
                        Cambiar
                    </button>` : ''}
                </td>
            </tr>
        `);
    });
}

/* ── Cargar citas ────────────────────────────────────────── */
async function cargarCitas(fecha = '') {
    if (!medicoId) return;
    spinner(true);
    try {
        const url  = fecha
            ? `${API}/citas/medico/${medicoId}?fecha=${fecha}`
            : `${API}/citas/medico/${medicoId}`;

        const res  = await fetch(url, { credentials: 'include' });
        const data = await res.json();

        if (!res.ok) {
            if (res.status === 401) { window.location.href = `${API}/views/auth/login.php`; return; }
            toast(data.error ?? 'Error al cargar citas.', 'danger');
            return;
        }

        renderCitas(data.citas ?? []);

    } catch {
        toast('Error de conexión.', 'danger');
    } finally {
        spinner(false);
    }
}

/* ── Filtro por fecha ────────────────────────────────────── */
document.getElementById('filtro-fecha').addEventListener('change', function () {
    cargarCitas(this.value);
});

document.getElementById('btn-limpiar-fecha').addEventListener('click', () => {
    document.getElementById('filtro-fecha').value = '';
    cargarCitas();
});

/* ── Modal cambiar estado ────────────────────────────────── */
function abrirModalEstado(id, estadoActual, cliente, fecha) {
    citaEditandoId = id;
    document.getElementById('modal-estado-info').textContent =
        `Cita #${id} · ${cliente} · ${fecha}`;
    document.getElementById('select-estado').value = estadoActual;
    modalEstado.show();
}

document.getElementById('btn-guardar-estado').addEventListener('click', async () => {
    if (!citaEditandoId) return;

    const estado = document.getElementById('select-estado').value;
    const txt    = document.getElementById('btn-estado-txt');
    const spin   = document.getElementById('btn-estado-spin');

    txt.classList.add('d-none');
    spin.classList.remove('d-none');

    try {
        const res  = await fetch(`${API}/citas/${citaEditandoId}/estado`, {
            method:      'PATCH',
            credentials: 'include',
            headers:     { 'Content-Type': 'application/json' },
            body:        JSON.stringify({ estado }),
        });
        const data = await res.json();

        modalEstado.hide();

        res.ok
            ? toast('Estado actualizado.')
            : toast(data.error ?? 'Error al actualizar.', 'danger');

        await cargarCitas(document.getElementById('filtro-fecha').value);

    } catch {
        toast('Error de conexión.', 'danger');
    } finally {
        txt.classList.remove('d-none');
        spin.classList.add('d-none');
        citaEditandoId = null;
    }
});

/* ── Sidebar móvil ───────────────────────────────────────── */
document.getElementById('btn-open-sidebar')?.addEventListener('click', () =>
    document.getElementById('sidebar').classList.add('open'));
document.getElementById('btn-close-sidebar')?.addEventListener('click', () =>
    document.getElementById('sidebar').classList.remove('open'));

/* ── Logout ──────────────────────────────────────────────── */
document.getElementById('btn-logout').addEventListener('click', async () => {
    await fetch(`${API}/auth/logout`, { method: 'POST', credentials: 'include' });
    window.location.href = `${API}/views/auth/login.php`;
});

/* ── Init ────────────────────────────────────────────────── */
(async () => {
    medicoId = await obtenerMedicoId();
    if (!medicoId) {
        toast('No se encontró perfil de médico.', 'danger');
        return;
    }
    await cargarCitas();
})();