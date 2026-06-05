
const API = (() => {
    const p = window.location.pathname;
    const iv = p.indexOf('/views/');
    if (iv > 0) return p.slice(0, iv);
    const ia = p.indexOf('/assets/');
    if (ia > 0) return p.slice(0, ia);
    return '';
})();
let servicioEliminarId = null;
const modalServicio = new bootstrap.Modal(document.getElementById('modal-servicio'));
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

/* ── Cargar servicios ────────────────────────────────────── */
async function cargarServicios() {
    spinner(true);
    try {
        const res  = await fetch(`${API}/servicios`, { credentials: 'include' });
        const data = await res.json();

        if (!res.ok) {
            if (res.status === 401) { window.location.href = `${API}/views/auth/login.php`; return; }
            toast(data.error ?? 'Error al cargar servicios.', 'danger');
            return;
        }

        const tbody    = document.getElementById('tabla-servicios');
        const servicios = data.servicios ?? [];
        tbody.innerHTML = '';

        if (servicios.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No hay servicios registrados.</td></tr>';
            return;
        }

        servicios.forEach(s => {
            const precio = parseFloat(s.precio).toLocaleString('es-CO', {
                style: 'currency', currency: 'COP', minimumFractionDigits: 0
            });

            tbody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td class="text-muted">${s.id}</td>
                    <td class="fw-semibold">${s.nombre_servicio}</td>
                    <td class="text-muted">${s.descripcion ?? '—'}</td>
                    <td>${precio}</td>
                    <td>${s.duracion_minutos} min</td>
                    <td class="text-end">
                        <button class="btn btn-xs btn-outline-primary me-1"
                                onclick="abrirEditar(${s.id}, '${s.nombre_servicio.replace(/'/g,"\\'")}',
                                '${(s.descripcion ?? '').replace(/'/g,"\\'")}',
                                ${s.precio}, ${s.duracion_minutos})">
                           <i class="bi bi-pencil-square"></i> Editar
                        </button>
                        <button class="btn btn-xs btn-outline-danger"
                                onclick="abrirEliminar(${s.id}, '${s.nombre_servicio.replace(/'/g,"\\'")}')">
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

/* ── Abrir modal nuevo ───────────────────────────────────── */
document.getElementById('btn-nuevo-servicio').addEventListener('click', () => {
    clearModalAlert();
    document.getElementById('modal-servicio-titulo').textContent = 'Nuevo servicio';
    document.getElementById('servicio-id').value          = '';
    document.getElementById('servicio-nombre').value      = '';
    document.getElementById('servicio-descripcion').value = '';
    document.getElementById('servicio-precio').value      = '';
    document.getElementById('servicio-duracion').value    = '';
    modalServicio.show();
});

/* ── Abrir modal editar ──────────────────────────────────── */
function abrirEditar(id, nombre, descripcion, precio, duracion) {
    clearModalAlert();
    document.getElementById('modal-servicio-titulo').textContent = 'Editar servicio';
    document.getElementById('servicio-id').value          = id;
    document.getElementById('servicio-nombre').value      = nombre;
    document.getElementById('servicio-descripcion').value = descripcion;
    document.getElementById('servicio-precio').value      = precio;
    document.getElementById('servicio-duracion').value    = duracion;
    modalServicio.show();
}

/* ── Guardar ─────────────────────────────────────────────── */
document.getElementById('btn-guardar-servicio').addEventListener('click', async () => {
    clearModalAlert();

    const id        = document.getElementById('servicio-id').value;
    const nombre    = document.getElementById('servicio-nombre').value.trim();
    const descripcion = document.getElementById('servicio-descripcion').value.trim();
    const precio    = parseFloat(document.getElementById('servicio-precio').value);
    const duracion  = parseInt(document.getElementById('servicio-duracion').value);

    if (!nombre)           { modalAlert('El nombre es obligatorio.');          return; }
    if (isNaN(precio) || precio < 0) { modalAlert('Ingresa un precio válido.'); return; }
    if (isNaN(duracion) || duracion <= 0) { modalAlert('Ingresa una duración válida.'); return; }

    const txt  = document.getElementById('btn-servicio-txt');
    const spin = document.getElementById('btn-servicio-spin');
    txt.classList.add('d-none');
    spin.classList.remove('d-none');

    try {
        const esEdicion = !!id;
        const url    = esEdicion ? `${API}/admin/servicios/${id}` : `${API}/admin/servicios`;
        const method = esEdicion ? 'PUT' : 'POST';

        const res  = await fetch(url, {
            method,
            credentials: 'include',
            headers:     { 'Content-Type': 'application/json' },
            body:        JSON.stringify({ nombre_servicio: nombre, descripcion, precio, duracion_minutos: duracion }),
        });
        const data = await res.json();

        if (!res.ok) { modalAlert(data.error ?? 'Error al guardar.'); return; }

        modalServicio.hide();
        toast(esEdicion ? 'Servicio actualizado.' : 'Servicio creado.');
        await cargarServicios();

    } catch {
        modalAlert('Error de conexión.');
    } finally {
        txt.classList.remove('d-none');
        spin.classList.add('d-none');
    }
});

/* ── Eliminar ────────────────────────────────────────────── */
function abrirEliminar(id, nombre) {
    servicioEliminarId = id;
    document.getElementById('modal-eliminar-info').textContent =
        `¿Seguro que deseas eliminar el servicio "${nombre}"?`;
    modalEliminar.show();
}

document.getElementById('btn-confirmar-eliminar').addEventListener('click', async () => {
    if (!servicioEliminarId) return;
    modalEliminar.hide();
    spinner(true);

    try {
        const res  = await fetch(`${API}/admin/servicios/${servicioEliminarId}`, {
            method: 'DELETE', credentials: 'include',
        });
        const data = await res.json();

        res.ok
            ? toast('Servicio eliminado.')
            : toast(data.error ?? 'No se pudo eliminar.', 'danger');

        await cargarServicios();

    } catch {
        toast('Error de conexión.', 'danger');
    } finally {
        spinner(false);
        servicioEliminarId = null;
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
cargarServicios();