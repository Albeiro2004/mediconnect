const API = (() => {
    const p = window.location.pathname;
    const iv = p.indexOf('/views/');
    if (iv > 0) return p.slice(0, iv);
    const ia = p.indexOf('/assets/');
    if (ia > 0) return p.slice(0, ia);
    return '';
})();

/* ── Estado del wizard ───────────────────────────────────── */
const seleccion = {
    sede_id:     null, nombre_sede:    '',
    servicio_id: null, nombre_servicio:'',
    medico_id:   null, nombre_medico:  '',
    fecha:       null,
    hora:        null,
};

let pasoActual = 1;

/* ── Toast ───────────────────────────────────────────────── */
function toast(msg, type = 'danger') {
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

/* ── Navegación entre pasos ──────────────────────────────── */
function irAPaso(n) {
    document.getElementById(`step-${pasoActual}`)?.classList.add('d-none');
    document.getElementById(`step-${n}`)?.classList.remove('d-none');

    document.querySelectorAll('.step-item').forEach(el => {
        const s = parseInt(el.dataset.step);
        el.classList.remove('active', 'done');
        if (s < n)       el.classList.add('done');
        else if (s === n) el.classList.add('active');
    });

    pasoActual = n;
}

/* ── Botones "Atrás" delegados ───────────────────────────── */
document.addEventListener('click', e => {
    const btn = e.target.closest('[data-prev]');
    if (btn) irAPaso(parseInt(btn.dataset.prev));
});

/* ── Fetch helpers ───────────────────────────────────────── */
async function apiFetch(url) {
    const res = await fetch(`${API}${url}`, { credentials: 'include' });
    if (res.status === 401) { window.location.href = `${API}/views/auth/login.php`; return null; }
    return res.json();
}

/* ── PASO 1 · Sedes ──────────────────────────────────────── */
async function cargarSedes() {
    spinner(true);
    try {
        const data = await apiFetch('/sedes');
        if (!data) return;

        const lista = document.getElementById('lista-sedes');
        lista.innerHTML = '';

        (data.sedes ?? []).forEach(s => {
            lista.insertAdjacentHTML('beforeend', `
                <div class="col-12 col-sm-6">
                    <div class="card mc-card p-3 seleccionable" style="cursor:pointer"
                         data-id="${s.id}" data-nombre="${s.nombre_sede}">
                        <div class="fw-semibold">${s.nombre_sede}</div>
                        <div class="small text-muted">${s.ciudad} · ${s.direccion}</div>
                    </div>
                </div>
            `);
        });

        lista.querySelectorAll('.seleccionable').forEach(card => {
            card.addEventListener('click', () => {
                seleccion.sede_id    = parseInt(card.dataset.id);
                seleccion.nombre_sede = card.dataset.nombre;
                cargarServicios();
                irAPaso(2);
            });
        });

    } catch { toast('Error al cargar sedes.'); }
    finally  { spinner(false); }
}

/* ── PASO 2 · Servicios ──────────────────────────────────── */
async function cargarServicios() {
    spinner(true);
    try {
        const data = await apiFetch('/servicios');
        if (!data) return;

        const lista = document.getElementById('lista-servicios');
        lista.innerHTML = '';

        (data.servicios ?? []).forEach(s => {
            lista.insertAdjacentHTML('beforeend', `
                <div class="col-12 col-sm-6">
                    <div class="card mc-card p-3 seleccionable" style="cursor:pointer"
                         data-id="${s.id}" data-nombre="${s.nombre_servicio}">
                        <div class="fw-semibold">${s.nombre_servicio}</div>
                        <div class="small text-muted">
                            ⏱ ${s.duracion_minutos} min &nbsp;·&nbsp;
                            💲 $${parseFloat(s.precio).toLocaleString('es-CO')}
                        </div>
                        ${s.descripcion ? `<div class="small text-muted mt-1">${s.descripcion}</div>` : ''}
                    </div>
                </div>
            `);
        });

        lista.querySelectorAll('.seleccionable').forEach(card => {
            card.addEventListener('click', () => {
                seleccion.servicio_id     = parseInt(card.dataset.id);
                seleccion.nombre_servicio = card.dataset.nombre;
                cargarMedicos();
                irAPaso(3);
            });
        });

    } catch { toast('Error al cargar servicios.'); }
    finally  { spinner(false); }
}

/* ── PASO 3 · Médicos ────────────────────────────────────── */
async function cargarMedicos() {
    spinner(true);
    try {
        const data = await apiFetch(`/medicos/sede/${seleccion.sede_id}`);
        if (!data) return;

        const lista = document.getElementById('lista-medicos');
        lista.innerHTML = '';

        const medicos = data.medicos ?? [];

        if (medicos.length === 0) {
            lista.innerHTML = '<p class="text-muted small">No hay médicos disponibles en esta sede.</p>';
            return;
        }

        medicos.forEach(m => {
            lista.insertAdjacentHTML('beforeend', `
                <div class="col-12 col-sm-6">
                    <div class="card mc-card p-3 seleccionable" style="cursor:pointer"
                         data-id="${m.id}" data-nombre="${m.nombre_completo}">
                        <div class="fw-semibold">Dr(a). ${m.nombre_completo}</div>
                        <div class="small text-muted">${m.cargo_especialidad}</div>
                        ${m.perfil_profesional
                ? `<div class="small text-muted mt-1">${m.perfil_profesional}</div>`
                : ''}
                    </div>
                </div>
            `);
        });

        lista.querySelectorAll('.seleccionable').forEach(card => {
            card.addEventListener('click', () => {
                seleccion.medico_id    = parseInt(card.dataset.id);
                seleccion.nombre_medico = card.dataset.nombre;
                irAPaso(4);
            });
        });

    } catch { toast('Error al cargar médicos.'); }
    finally  { spinner(false); }
}

/* ── PASO 4 · Fecha y slots ──────────────────────────────── */
const inputFecha = document.getElementById('fecha-cita');

// Bloquear fechas pasadas
const hoy = new Date().toISOString().split('T')[0];
inputFecha.min = hoy;

inputFecha.addEventListener('change', async () => {
    const fecha = inputFecha.value;
    if (!fecha) return;

    seleccion.fecha = fecha;
    seleccion.hora  = null;

    document.getElementById('btn-ir-confirmar').classList.add('d-none');
    document.getElementById('slots-container').classList.add('d-none');
    document.getElementById('slots-empty').classList.add('d-none');

    spinner(true);
    try {
        const data = await apiFetch(`/medicos/${seleccion.medico_id}/slots?fecha=${fecha}`);
        if (!data) return;

        const slots = data.slots ?? [];
        const lista = document.getElementById('lista-slots');
        lista.innerHTML = '';

        if (slots.length === 0) {
            document.getElementById('slots-empty').classList.remove('d-none');
            return;
        }

        slots.forEach(h => {
            const btn = document.createElement('button');
            btn.className   = 'slot-btn';
            btn.textContent = h.slice(0, 5);
            btn.addEventListener('click', () => {
                lista.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('selected'));
                btn.classList.add('selected');
                seleccion.hora = h;
                document.getElementById('btn-ir-confirmar').classList.remove('d-none');
            });
            lista.appendChild(btn);
        });

        document.getElementById('slots-container').classList.remove('d-none');

    } catch { toast('Error al cargar horarios.'); }
    finally  { spinner(false); }
});

document.getElementById('btn-ir-confirmar').addEventListener('click', () => {
    if (!seleccion.hora) { toast('Selecciona un horario.', 'warning'); return; }
    construirResumen();
    irAPaso(5);
});

/* ── PASO 5 · Resumen ────────────────────────────────────── */
function construirResumen() {
    const fecha = new Date(seleccion.fecha + 'T00:00:00').toLocaleDateString('es-CO', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
    });

    document.getElementById('resumen-cita').innerHTML = `
        <li class="list-group-item d-flex justify-content-between px-0">
            <span class="text-muted small">Sede</span>
            <span class="small fw-semibold">${seleccion.nombre_sede}</span>
        </li>
        <li class="list-group-item d-flex justify-content-between px-0">
            <span class="text-muted small">Servicio</span>
            <span class="small fw-semibold">${seleccion.nombre_servicio}</span>
        </li>
        <li class="list-group-item d-flex justify-content-between px-0">
            <span class="text-muted small">Médico</span>
            <span class="small fw-semibold">Dr(a). ${seleccion.nombre_medico}</span>
        </li>
        <li class="list-group-item d-flex justify-content-between px-0">
            <span class="text-muted small">Fecha</span>
            <span class="small fw-semibold">${fecha}</span>
        </li>
        <li class="list-group-item d-flex justify-content-between px-0">
            <span class="text-muted small">Hora</span>
            <span class="small fw-semibold">${seleccion.hora.slice(0, 5)}</span>
        </li>
    `;
}

/* ── Confirmar cita ──────────────────────────────────────── */
document.getElementById('btn-agendar').addEventListener('click', async () => {
    const btn     = document.getElementById('btn-agendar');
    const txt     = document.getElementById('btn-agendar-txt');
    const spin    = document.getElementById('spin-agendar');

    btn.disabled = true;
    txt.classList.add('d-none');
    spin.classList.remove('d-none');

    try {
        const res  = await fetch(`${API}/citas`, {
            method:      'POST',
            credentials: 'include',
            headers:     { 'Content-Type': 'application/json' },
            body:        JSON.stringify({
                medico_id:   seleccion.medico_id,
                servicio_id: seleccion.servicio_id,
                sede_id:     seleccion.sede_id,
                fecha_cita:  seleccion.fecha,
                hora_cita:   seleccion.hora,
            }),
        });
        const data = await res.json();

        if (!res.ok) {
            toast(data.error ?? 'No se pudo agendar la cita.');
            return;
        }

        // Mostrar pantalla de éxito
        document.getElementById('step-5').classList.add('d-none');
        document.getElementById('step-ok').classList.remove('d-none');

        document.querySelectorAll('.step-item').forEach(el => el.classList.add('done'));

    } catch {
        toast('Error de conexión. Intenta de nuevo.');
    } finally {
        btn.disabled = false;
        txt.classList.remove('d-none');
        spin.classList.add('d-none');
    }
});

/* ── Init ────────────────────────────────────────────────── */
cargarSedes();