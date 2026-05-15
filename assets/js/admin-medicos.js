const API = (() => {
    const p = window.location.pathname;
    const iv = p.indexOf('/views/');
    if (iv > 0) return p.slice(0, iv);
    const ia = p.indexOf('/assets/');
    if (ia > 0) return p.slice(0, ia);
    return '';
})();
let medicoEliminarId = null;
const modalMedico   = new bootstrap.Modal(document.getElementById('modal-medico'));
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

/* ── Cargar sedes en selects ─────────────────────────────── */
async function cargarSedes() {
    const res  = await fetch(`${API}/sedes`, { credentials: 'include' });
    const data = await res.json();
    const sedes = data.sedes ?? [];

    // Filtro
    const filtro = document.getElementById('filtro-sede');
    sedes.forEach(s => {
        filtro.insertAdjacentHTML('beforeend',
            `<option value="${s.id}">${s.nombre_sede}</option>`);
    });

    // Select del modal
    const selectSede = document.getElementById('medico-sede');
    selectSede.innerHTML = '<option value="">Selecciona una sede</option>';
    sedes.forEach(s => {
        selectSede.insertAdjacentHTML('beforeend',
            `<option value="${s.id}">${s.nombre_sede}</option>`);
    });
}

/* ── Cargar médicos ──────────────────────────────────────── */
async function cargarMedicos(sedeId = '') {
    spinner(true);
    try {
        const url  = sedeId ? `${API}/medicos/sede/${sedeId}` : `${API}/medicos`;
        const res  = await fetch(url, { credentials: 'include' });
        const data = await res.json();

        if (!res.ok) {
            if (res.status === 401) { window.location.href = `${API}/views/auth/login.php`; return; }
            toast(data.error ?? 'Error al cargar médicos.', 'danger');
            return;
        }

        const tbody   = document.getElementById('tabla-medicos');
        const medicos = data.medicos ?? [];
        tbody.innerHTML = '';

        if (medicos.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No hay médicos registrados.</td></tr>';
            return;
        }

        medicos.forEach(m => {
            tbody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td class="text-muted">${m.id}</td>
                    <td class="fw-semibold">Dr(a). ${m.nombre_completo}</td>
                    <td>${m.email}</td>
                    <td>${m.cargo_especialidad}</td>
                    <td>${m.nombre_sede}</td>
                    <td class="text-end">
                        <button class="btn btn-xs btn-outline-primary me-1"
                                onclick="abrirEditar(${m.id}, ${m.sede_id}, '${m.cargo_especialidad}',
                                '${(m.perfil_profesional ?? '').replace(/'/g, "\\'")}')">
                            Editar
                        </button>
                        <button class="btn btn-xs btn-outline-danger"
                                onclick="abrirEliminar(${m.id}, '${m.nombre_completo}')">
                            Eliminar
                        </button>
                    </td>
                </tr>
            `);
        });

    } catch(err) {
        toast('Error de conexiónz.', 'danger');
        console.error('💥 Error de fetch:', err);
    } finally {
        spinner(false);
    }
}

/* ── Filtro por sede ─────────────────────────────────────── */
document.getElementById('filtro-sede').addEventListener('change', function () {
    cargarMedicos(this.value);
});

/* ── Abrir modal nuevo ───────────────────────────────────── */
document.getElementById('btn-nuevo-medico').addEventListener('click', () => {
    clearModalAlert();
    document.getElementById('modal-medico-titulo').textContent = 'Nuevo médico';
    document.getElementById('medico-id').value           = '';
    document.getElementById('medico-nombre').value       = '';
    document.getElementById('medico-email').value        = '';
    document.getElementById('medico-password').value     = '';
    document.getElementById('medico-sede').value         = '';
    document.getElementById('medico-especialidad').value = '';
    document.getElementById('medico-perfil').value       = '';
    document.getElementById('campos-creacion').classList.remove('d-none');
    modalMedico.show();
});

/* ── Abrir modal editar ──────────────────────────────────── */
function abrirEditar(id, sedeId, especialidad, perfil) {
    clearModalAlert();
    document.getElementById('modal-medico-titulo').textContent = 'Editar médico';
    document.getElementById('medico-id').value           = id;
    document.getElementById('medico-sede').value         = sedeId;
    document.getElementById('medico-especialidad').value = especialidad;
    document.getElementById('medico-perfil').value       = perfil;
    document.getElementById('campos-creacion').classList.add('d-none');
    modalMedico.show();
}

/* ── Guardar ─────────────────────────────────────────────── */
document.getElementById('btn-guardar-medico').addEventListener('click', async () => {
    clearModalAlert();

    const id           = document.getElementById('medico-id').value;
    const sedeId       = document.getElementById('medico-sede').value;
    const especialidad = document.getElementById('medico-especialidad').value.trim();
    const perfil       = document.getElementById('medico-perfil').value.trim();
    const esEdicion    = !!id;

    if (!sedeId || !especialidad) {
        modalAlert('Sede y especialidad son obligatorias.');
        return;
    }

    let body = { sede_id: parseInt(sedeId), cargo_especialidad: especialidad, perfil_profesional: perfil };

    if (!esEdicion) {
        const nombre   = document.getElementById('medico-nombre').value.trim();
        const email    = document.getElementById('medico-email').value.trim();
        const password = document.getElementById('medico-password').value;

        if (!nombre || !email || !password) {
            modalAlert('Nombre, email y contraseña son obligatorios.');
            return;
        }
        if (password.length < 8) {
            modalAlert('La contraseña debe tener al menos 8 caracteres.');
            return;
        }

        body = { ...body, nombre_completo: nombre, email, password };
    }

    const txt  = document.getElementById('btn-medico-txt');
    const spin = document.getElementById('btn-medico-spin');
    txt.classList.add('d-none');
    spin.classList.remove('d-none');

    try {
        const url    = esEdicion ? `${API}/admin/medicos/${id}` : `${API}/admin/medicos`;
        const method = esEdicion ? 'PUT' : 'POST';

        const res  = await fetch(url, {
            method,
            credentials: 'include',
            headers:     { 'Content-Type': 'application/json' },
            body:        JSON.stringify(body),
        });
        const data = await res.json();

        if (!res.ok) { modalAlert(data.error ?? 'Error al guardar.'); return; }

        modalMedico.hide();
        toast(esEdicion ? 'Médico actualizado.' : 'Médico registrado.');
        await cargarMedicos(document.getElementById('filtro-sede').value);

    } catch {
        modalAlert('Error de conexión.');
    } finally {
        txt.classList.remove('d-none');
        spin.classList.add('d-none');
    }
});

/* ── Eliminar ────────────────────────────────────────────── */
function abrirEliminar(id, nombre) {
    medicoEliminarId = id;
    document.getElementById('modal-eliminar-info').textContent =
        `¿Seguro que deseas eliminar al médico "${nombre}"?`;
    modalEliminar.show();
}

document.getElementById('btn-confirmar-eliminar').addEventListener('click', async () => {
    if (!medicoEliminarId) return;
    modalEliminar.hide();
    spinner(true);

    try {
        const res  = await fetch(`${API}/admin/medicos/${medicoEliminarId}`, {
            method: 'DELETE', credentials: 'include',
        });
        const data = await res.json();

        res.ok
            ? toast('Médico eliminado.')
            : toast(data.error ?? 'No se pudo eliminar.', 'danger');

        await cargarMedicos(document.getElementById('filtro-sede').value);

    } catch {
        toast('Error de conexión.', 'danger');
    } finally {
        spinner(false);
        medicoEliminarId = null;
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
    await cargarSedes();
    await cargarMedicos();
})();