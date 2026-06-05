const API = (() => {
    const p = window.location.pathname;
    const iv = p.indexOf('/views/');
    if (iv > 0) return p.slice(0, iv);
    const ia = p.indexOf('/assets/');
    if (ia > 0) return p.slice(0, ia);
    return '';
})();

let citaEditandoId = null;
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
    return `<span class="badge-${estado}">${estado.charAt(0).toUpperCase() + estado.slice(1)}</span>`;
}

/* ── Cargar citas ────────────────────────────────────────── */
async function cargarCitas() {
    spinner(true);
    try {
        const res  = await fetch(`${API}/admin/citas`, { credentials: 'include' });
        const data = await res.json();

        if (!res.ok) {
            if (res.status === 401) { window.location.href = `${API}/views/auth/login.php`; return; }
            toast(data.error ?? 'Error al cargar citas.', 'danger');
            return;
        }

        const citas = data.citas ?? [];

        // Calcular stats
        document.getElementById('stat-total').textContent      = citas.length;
        document.getElementById('stat-pendientes').textContent  = citas.filter(c => c.estado === 'pendiente').length;
        document.getElementById('stat-confirmadas').textContent = citas.filter(c => c.estado === 'confirmada').length;
        document.getElementById('stat-canceladas').textContent  = citas.filter(c => c.estado === 'cancelada').length;

        // Renderizar las 10 más recientes
        const tbody = document.getElementById('tabla-citas');
        tbody.innerHTML = '';

        const recientes = citas.slice(0, 10);

        if (recientes.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">Sin citas registradas.</td></tr>';
            return;
        }

        recientes.forEach(c => {
            const fecha = new Date(c.fecha_cita + 'T00:00:00').toLocaleDateString('es-CO', {
                day: '2-digit', month: 'short', year: 'numeric'
            });

            tbody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td class="text-muted">#${c.id}</td>
                    <td>${c.nombre_cliente}</td>
                    <td>${c.nombre_medico}</td>
                    <td>${c.nombre_servicio}</td>
                    <td>${fecha}</td>
                    <td>${c.hora_cita.slice(0, 5)}</td>
                    <td>${badgeEstado(c.estado)}</td>
                    <td>
                        <button class="btn-cambiar"
                                onclick="abrirModalEstado(${c.id}, '${c.estado}', '${c.nombre_cliente}', '${fecha}')">
                           <i class="bi bi-pencil-square"></i> Cambiar
                        </button>
                    </td>
                </tr>
            `);
        });

    } catch {
        toast('Error de conexión.', 'danger');
    } finally {
        spinner(false);
    }
}

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
    modalEstado.hide();
    spinner(true);

    try {
        const res  = await fetch(`${API}/citas/${citaEditandoId}/estado`, {
            method:      'PATCH',
            credentials: 'include',
            headers:     { 'Content-Type': 'application/json' },
            body:        JSON.stringify({ estado }),
        });
        const data = await res.json();

        res.ok
            ? toast('Estado actualizado correctamente.')
            : toast(data.error ?? 'Error al actualizar.', 'danger');

        await cargarCitas();

    } catch {
        toast('Error de conexión.', 'danger');
    } finally {
        spinner(false);
        citaEditandoId = null;
    }
});

/* ── Sidebar móvil ───────────────────────────────────────── */
document.getElementById('btn-open-sidebar')?.addEventListener('click', () => {
    document.getElementById('sidebar').classList.add('open');
});

document.getElementById('btn-close-sidebar')?.addEventListener('click', () => {
    document.getElementById('sidebar').classList.remove('open');
});

/* ── Logout ──────────────────────────────────────────────── */
document.getElementById('btn-logout').addEventListener('click', async () => {
    await fetch(`${API}/auth/logout`, { method: 'POST', credentials: 'include' });
    window.location.href = `${API}/views/auth/login.php`;
});

/* ── Init ────────────────────────────────────────────────── */
cargarCitas();