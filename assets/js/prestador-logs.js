const API = (() => {
    const p = window.location.pathname;
    const iv = p.indexOf('/views/');
    if (iv > 0) return p.slice(0, iv);
    const ia = p.indexOf('/assets/');
    if (ia > 0) return p.slice(0, ia);
    return '';
})();

let medicoId     = null;
const modalLog   = new bootstrap.Modal(document.getElementById('modal-log'));

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

/* ── Cargar citas finalizadas sin log ────────────────────── */
async function cargarPendientes() {
    if (!medicoId) return;

    const res  = await fetch(`${API}/citas/medico/${medicoId}`, { credentials: 'include' });
    const data = await res.json();

    const contenedor = document.getElementById('lista-pendientes');

    const finalizadas = (data.citas ?? []).filter(c => c.estado === 'finalizada');

    // Verificar cuáles ya tienen log
    const conLog = await Promise.all(
        finalizadas.map(async c => {
            const r = await fetch(`${API}/citas/${c.id}/log`, { credentials: 'include' });
            return { cita: c, tieneLog: r.ok };
        })
    );

    const sinLog = conLog.filter(x => !x.tieneLog).map(x => x.cita);

    contenedor.innerHTML = '';

    if (sinLog.length === 0) {
        contenedor.innerHTML = '<p class="text-muted small mb-0">No hay citas finalizadas pendientes de log.</p>';
        return;
    }

    sinLog.forEach(c => {
        const fecha = new Date(c.fecha_cita + 'T00:00:00').toLocaleDateString('es-CO', {
            day: '2-digit', month: 'short', year: 'numeric'
        });

        contenedor.insertAdjacentHTML('beforeend', `
            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                <div>
                    <span class="fw-semibold small">${c.nombre_cliente}</span>
                    <span class="text-muted small ms-2">${c.nombre_servicio} · ${fecha} ${c.hora_cita.slice(0,5)}</span>
                </div>
                <button class="btn btn-sm btn-primary btn-mc"
                        onclick="abrirCrear(${c.id}, '${c.nombre_cliente}', '${c.nombre_servicio}', '${fecha}')">
                    Registrar log
                </button>
            </div>
        `);
    });
}

/* ── Cargar logs registrados ─────────────────────────────── */
async function cargarLogs() {
    if (!medicoId) return;

    const res  = await fetch(`${API}/medico/logs`, { credentials: 'include' });
    const data = await res.json();

    const tbody = document.getElementById('tabla-logs');
    const logs  = data.logs ?? [];
    tbody.innerHTML = '';

    if (logs.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No hay logs registrados aún.</td></tr>';
        return;
    }

    logs.forEach(l => {
        const fecha = new Date(l.fecha_cita + 'T00:00:00').toLocaleDateString('es-CO', {
            day: '2-digit', month: 'short', year: 'numeric'
        });

        const proxima = l.proxima_cita_sugerida
            ? new Date(l.proxima_cita_sugerida + 'T00:00:00').toLocaleDateString('es-CO', {
                day: '2-digit', month: 'short', year: 'numeric'
            })
            : '—';

        tbody.insertAdjacentHTML('beforeend', `
            <tr>
                <td class="text-muted">#${l.id}</td>
                <td class="fw-semibold">${l.nombre_cliente}</td>
                <td>${l.nombre_servicio}</td>
                <td>${fecha}</td>
                <td>${proxima}</td>
                <td class="text-end">
                    <button class="btn btn-xs btn-outline-primary"
                            onclick="abrirEditar(${l.id}, ${l.cita_id}, '${l.nombre_cliente}',
                            '${l.nombre_servicio}', '${fecha}',
                            '${(l.observaciones_finales ?? '').replace(/'/g, "\\'")}',
                            '${(l.tratamiento_o_resultado ?? '').replace(/'/g, "\\'")}',
                            '${l.proxima_cita_sugerida ?? ''}')">
                        Editar
                    </button>
                </td>
            </tr>
        `);
    });
}

/* ── Abrir modal crear ───────────────────────────────────── */
function abrirCrear(citaId, cliente, servicio, fecha) {
    clearModalAlert();
    document.getElementById('modal-log-titulo').textContent = 'Registrar log de atención';
    document.getElementById('log-id').value          = '';
    document.getElementById('log-cita-id').value     = citaId;
    document.getElementById('log-cita-info').textContent =
        `${cliente} · ${servicio} · ${fecha}`;
    document.getElementById('log-observaciones').value = '';
    document.getElementById('log-tratamiento').value   = '';
    document.getElementById('log-proxima').value       = '';
    modalLog.show();
}

/* ── Abrir modal editar ──────────────────────────────────── */
function abrirEditar(id, citaId, cliente, servicio, fecha, observaciones, tratamiento, proxima) {
    clearModalAlert();
    document.getElementById('modal-log-titulo').textContent = 'Editar log de atención';
    document.getElementById('log-id').value             = id;
    document.getElementById('log-cita-id').value        = citaId;
    document.getElementById('log-cita-info').textContent =
        `${cliente} · ${servicio} · ${fecha}`;
    document.getElementById('log-observaciones').value  = observaciones;
    document.getElementById('log-tratamiento').value    = tratamiento;
    document.getElementById('log-proxima').value        = proxima;
    modalLog.show();
}

/* ── Guardar log ─────────────────────────────────────────── */
document.getElementById('btn-guardar-log').addEventListener('click', async () => {
    clearModalAlert();

    const id           = document.getElementById('log-id').value;
    const citaId       = document.getElementById('log-cita-id').value;
    const observaciones = document.getElementById('log-observaciones').value.trim();
    const tratamiento  = document.getElementById('log-tratamiento').value.trim();
    const proxima      = document.getElementById('log-proxima').value;

    if (!observaciones && !tratamiento) {
        modalAlert('Ingresa al menos observaciones o tratamiento.');
        return;
    }

    const txt  = document.getElementById('btn-log-txt');
    const spin = document.getElementById('btn-log-spin');
    txt.classList.add('d-none');
    spin.classList.remove('d-none');

    try {
        const esEdicion = !!id;
        const url    = esEdicion ? `${API}/logs/${id}` : `${API}/citas/${citaId}/log`;
        const method = esEdicion ? 'PUT' : 'POST';

        const res  = await fetch(url, {
            method,
            credentials: 'include',
            headers:     { 'Content-Type': 'application/json' },
            body:        JSON.stringify({
                observaciones_finales:   observaciones,
                tratamiento_o_resultado: tratamiento,
                proxima_cita_sugerida:   proxima || null,
            }),
        });
        const data = await res.json();

        if (!res.ok) { modalAlert(data.error ?? 'Error al guardar.'); return; }

        modalLog.hide();
        toast(esEdicion ? 'Log actualizado.' : 'Log registrado correctamente.');
        await cargarPendientes();
        await cargarLogs();

    } catch {
        modalAlert('Error de conexión.');
    } finally {
        txt.classList.remove('d-none');
        spin.classList.add('d-none');
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
    spinner(true);
    await Promise.all([cargarPendientes(), cargarLogs()]);
    spinner(false);
})();