const API = (() => {
    const p = window.location.pathname;
    const iv = p.indexOf('/views/');
    if (iv > 0) return p.slice(0, iv);
    const ia = p.indexOf('/assets/');
    if (ia > 0) return p.slice(0, ia);
    return '';
})();

let medicoId        = null;
let bloqueEliminarId = null;
const modalBloque   = new bootstrap.Modal(document.getElementById('modal-bloque'));
const modalEliminar = new bootstrap.Modal(document.getElementById('modal-eliminar'));

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

/* ── Alerta modal ────────────────────────────────────────── */
function modalAlert(msg) {
    const el = document.getElementById('modal-alert');
    el.textContent = msg;
    el.classList.remove('d-none');
}
function clearModalAlert() {
    document.getElementById('modal-alert').classList.add('d-none');
}

/* ── Obtener medicoId ────────────────────────────────────── */
async function obtenerMedicoId() {
    const res  = await fetch(`${API}/auth/me`, { credentials: 'include' });
    const data = await res.json();

    if (!res.ok) { window.location.href = `${API}/views/auth/login.php`; return null; }

    const res2  = await fetch(`${API}/medicos`, { credentials: 'include' });
    const data2 = await res2.json();

    const medico = (data2.medicos ?? []).find(m => m.usuario_id === data.usuario.id);
    return medico?.id ?? null;
}

/* ── Cargar disponibilidad ───────────────────────────────── */
async function cargarDisponibilidad() {
    if (!medicoId) return;
    spinner(true);
    try {
        const res  = await fetch(`${API}/medicos/${medicoId}/disponibilidad`, { credentials: 'include' });
        const data = await res.json();

        if (!res.ok) {
            if (res.status === 401) { window.location.href = `${API}/views/auth/login.php`; return; }
            toast(data.error ?? 'Error al cargar disponibilidad.', 'danger');
            return;
        }

        const tbody  = document.getElementById('tabla-disponibilidad');
        const bloques = data.disponibilidad ?? [];
        tbody.innerHTML = '';

        if (bloques.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No tienes bloques de disponibilidad registrados.</td></tr>';
            return;
        }

        bloques.forEach(b => {
            tbody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td class="text-muted">${b.id}</td>
                    <td class="fw-semibold">${b.dia_semana}</td>
                    <td>${b.hora_inicio.slice(0, 5)}</td>
                    <td>${b.hora_fin.slice(0, 5)}</td>
                    <td class="text-end">
                        <button class="btn btn-xs btn-outline-primary me-1"
                                onclick="abrirEditar(${b.id}, '${b.dia_semana}',
                                '${b.hora_inicio.slice(0,5)}', '${b.hora_fin.slice(0,5)}')">
                            Editar
                        </button>
                        <button class="btn btn-xs btn-outline-danger"
                                onclick="abrirEliminar(${b.id}, '${b.dia_semana}',
                                '${b.hora_inicio.slice(0,5)}', '${b.hora_fin.slice(0,5)}')">
                            Eliminar
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

/* ── Abrir modal nuevo ───────────────────────────────────── */
document.getElementById('btn-nuevo-bloque').addEventListener('click', () => {
    clearModalAlert();
    document.getElementById('modal-bloque-titulo').textContent = 'Nuevo bloque';
    document.getElementById('bloque-id').value     = '';
    document.getElementById('bloque-dia').value    = '';
    document.getElementById('bloque-inicio').value = '';
    document.getElementById('bloque-fin').value    = '';
    modalBloque.show();
});

/* ── Abrir modal editar ──────────────────────────────────── */
function abrirEditar(id, dia, inicio, fin) {
    clearModalAlert();
    document.getElementById('modal-bloque-titulo').textContent = 'Editar bloque';
    document.getElementById('bloque-id').value     = id;
    document.getElementById('bloque-dia').value    = dia;
    document.getElementById('bloque-inicio').value = inicio;
    document.getElementById('bloque-fin').value    = fin;
    modalBloque.show();
}

/* ── Guardar ─────────────────────────────────────────────── */
document.getElementById('btn-guardar-bloque').addEventListener('click', async () => {
    clearModalAlert();

    const id     = document.getElementById('bloque-id').value;
    const dia    = document.getElementById('bloque-dia').value;
    const inicio = document.getElementById('bloque-inicio').value;
    const fin    = document.getElementById('bloque-fin').value;

    if (!dia)    { modalAlert('Selecciona un día.');           return; }
    if (!inicio) { modalAlert('Ingresa la hora de inicio.');   return; }
    if (!fin)    { modalAlert('Ingresa la hora de fin.');      return; }
    if (inicio >= fin) { modalAlert('La hora de inicio debe ser anterior a la hora de fin.'); return; }

    const txt  = document.getElementById('btn-bloque-txt');
    const spin = document.getElementById('btn-bloque-spin');
    txt.classList.add('d-none');
    spin.classList.remove('d-none');

    try {
        const esEdicion = !!id;
        const url    = esEdicion
            ? `${API}/admin/disponibilidad/${id}`
            : `${API}/admin/medicos/${medicoId}/disponibilidad`;
        const method = esEdicion ? 'PUT' : 'POST';

        const res  = await fetch(url, {
            method,
            credentials: 'include',
            headers:     { 'Content-Type': 'application/json' },
            body:        JSON.stringify({ dia_semana: dia, hora_inicio: inicio, hora_fin: fin }),
        });
        const data = await res.json();

        if (!res.ok) { modalAlert(data.error ?? 'Error al guardar.'); return; }

        modalBloque.hide();
        toast(esEdicion ? 'Bloque actualizado.' : 'Bloque registrado.');
        await cargarDisponibilidad();

    } catch {
        modalAlert('Error de conexión.');
    } finally {
        txt.classList.remove('d-none');
        spin.classList.add('d-none');
    }
});

/* ── Eliminar ────────────────────────────────────────────── */
function abrirEliminar(id, dia, inicio, fin) {
    bloqueEliminarId = id;
    document.getElementById('modal-eliminar-info').textContent =
        `¿Eliminar el bloque del ${dia} de ${inicio} a ${fin}?`;
    modalEliminar.show();
}

document.getElementById('btn-confirmar-eliminar').addEventListener('click', async () => {
    if (!bloqueEliminarId) return;
    modalEliminar.hide();
    spinner(true);

    try {
        const res  = await fetch(`${API}/admin/disponibilidad/${bloqueEliminarId}`, {
            method: 'DELETE', credentials: 'include',
        });
        const data = await res.json();

        res.ok
            ? toast('Bloque eliminado.')
            : toast(data.error ?? 'No se pudo eliminar.', 'danger');

        await cargarDisponibilidad();

    } catch {
        toast('Error de conexión.', 'danger');
    } finally {
        spinner(false);
        bloqueEliminarId = null;
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
    await cargarDisponibilidad();
})();