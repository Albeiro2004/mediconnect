const API = (() => {
    const p = window.location.pathname;
    const iv = p.indexOf('/views/');
    if (iv > 0) return p.slice(0, iv);
    const ia = p.indexOf('/assets/');
    if (ia > 0) return p.slice(0, ia);
    return '';
})();
let sedeEliminarId = null;
const modalSede     = new bootstrap.Modal(document.getElementById('modal-sede'));
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

/* ── Alerta dentro del modal ─────────────────────────────── */
function modalAlert(msg) {
    const el = document.getElementById('modal-alert');
    el.textContent = msg;
    el.classList.remove('d-none');
}
function clearModalAlert() {
    document.getElementById('modal-alert').classList.add('d-none');
}

/* ── Cargar sedes ────────────────────────────────────────── */
async function cargarSedes() {
    spinner(true);
    try {
        const res  = await fetch(`${API}/sedes?estado=`, { credentials: 'include' });
        const data = await res.json();

        if (!res.ok) {
            if (res.status === 401) { window.location.href = `${API}/views/auth/login.php`; return; }
            toast(data.error ?? 'Error al cargar sedes.', 'danger');
            return;
        }

        const tbody = document.getElementById('tabla-sedes');
        tbody.innerHTML = '';

        const sedes = data.sedes ?? [];

        if (sedes.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">No hay sedes registradas.</td></tr>';
            return;
        }

        sedes.forEach(s => {
            const badge = s.estado === 'activa'
                ? '<span class="badge bg-success">Activa</span>'
                : '<span class="badge bg-secondary">Inactiva</span>';

            tbody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td class="text-muted">${s.id}</td>
                    <td class="fw-semibold">${s.nombre_sede}</td>
                    <td>${s.ciudad}</td>
                    <td>${s.direccion}</td>
                    <td>${s.telefono_contacto ?? '—'}</td>
                    <td>${badge}</td>
                    <td class="text-end">
                        <button class="btn btn-xs btn-outline-primary me-1"
                                onclick="abrirEditar(${s.id}, '${s.nombre_sede}', '${s.ciudad}',
                                '${s.direccion}', '${s.telefono_contacto ?? ''}', '${s.estado}')">
                           <i class="bi bi-pencil-square"></i> Editar
                        </button>
                        <button class="btn btn-xs btn-outline-danger"
                                onclick="abrirEliminar(${s.id}, '${s.nombre_sede}')">
                           <i class="bi bi-trash3-fill"></i>
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

/* ── Abrir modal nueva sede ──────────────────────────────── */
document.getElementById('btn-nueva-sede').addEventListener('click', () => {
    clearModalAlert();
    document.getElementById('modal-sede-titulo').textContent = 'Nueva sede';
    document.getElementById('sede-id').value        = '';
    document.getElementById('sede-nombre').value    = '';
    document.getElementById('sede-ciudad').value    = '';
    document.getElementById('sede-direccion').value = '';
    document.getElementById('sede-telefono').value  = '';
    document.getElementById('campo-estado').classList.add('d-none');
    modalSede.show();
});

/* ── Abrir modal editar sede ─────────────────────────────── */
function abrirEditar(id, nombre, ciudad, direccion, telefono, estado) {
    clearModalAlert();
    document.getElementById('modal-sede-titulo').textContent = 'Editar sede';
    document.getElementById('sede-id').value        = id;
    document.getElementById('sede-nombre').value    = nombre;
    document.getElementById('sede-ciudad').value    = ciudad;
    document.getElementById('sede-direccion').value = direccion;
    document.getElementById('sede-telefono').value  = telefono;
    document.getElementById('sede-estado').value    = estado;
    document.getElementById('campo-estado').classList.remove('d-none');
    modalSede.show();
}

/* ── Guardar (crear o editar) ────────────────────────────── */
document.getElementById('btn-guardar-sede').addEventListener('click', async () => {
    clearModalAlert();

    const id        = document.getElementById('sede-id').value;
    const nombre    = document.getElementById('sede-nombre').value.trim();
    const ciudad    = document.getElementById('sede-ciudad').value.trim();
    const direccion = document.getElementById('sede-direccion').value.trim();
    const telefono  = document.getElementById('sede-telefono').value.trim();
    const estado    = document.getElementById('sede-estado').value;

    if (!nombre || !ciudad || !direccion) {
        modalAlert('Nombre, ciudad y dirección son obligatorios.');
        return;
    }

    const txt  = document.getElementById('btn-sede-txt');
    const spin = document.getElementById('btn-sede-spin');
    txt.classList.add('d-none');
    spin.classList.remove('d-none');

    try {
        const esEdicion = !!id;
        const url    = esEdicion ? `${API}/admin/sedes/${id}` : `${API}/admin/sedes`;
        const method = esEdicion ? 'PUT' : 'POST';

        const res  = await fetch(url, {
            method,
            credentials: 'include',
            headers:     { 'Content-Type': 'application/json' },
            body:        JSON.stringify({ nombre_sede: nombre, ciudad, direccion, telefono_contacto: telefono, estado }),
        });
        const data = await res.json();

        if (!res.ok) {
            modalAlert(data.error ?? 'Error al guardar.');
            return;
        }

        modalSede.hide();
        toast(esEdicion ? 'Sede actualizada.' : 'Sede creada.');
        await cargarSedes();

    } catch {
        modalAlert('Error de conexión.');
    } finally {
        txt.classList.remove('d-none');
        spin.classList.add('d-none');
    }
});

/* ── Abrir modal eliminar ────────────────────────────────── */
function abrirEliminar(id, nombre) {
    sedeEliminarId = id;
    document.getElementById('modal-eliminar-info').textContent =
        `¿Seguro que deseas eliminar la sede "${nombre}"? Esta acción no se puede deshacer.`;
    modalEliminar.show();
}

/* ── Confirmar eliminar ──────────────────────────────────── */
document.getElementById('btn-confirmar-eliminar').addEventListener('click', async () => {
    if (!sedeEliminarId) return;
    modalEliminar.hide();
    spinner(true);

    try {
        const res  = await fetch(`${API}/admin/sedes/${sedeEliminarId}`, {
            method: 'DELETE', credentials: 'include',
        });
        const data = await res.json();

        res.ok
            ? toast('Sede eliminada.')
            : toast(data.error ?? 'No se pudo eliminar.', 'danger');

        await cargarSedes();

    } catch {
        toast('Error de conexión.', 'danger');
    } finally {
        spinner(false);
        sedeEliminarId = null;
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
cargarSedes();