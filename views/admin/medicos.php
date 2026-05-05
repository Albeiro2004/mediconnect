<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Médicos – MediConnect Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/mediconnect/assets/css/main.css">
</head>

<body>

    <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="admin-topbar">
        <h2><i class="bi bi-person-badge me-2 text-success"></i>Médicos</h2>
        <button class="btn-mc-primary" style="width:auto;padding:.55rem 1.2rem;" onclick="abrirModalNuevo()">
            <i class="bi bi-plus-lg me-1"></i> Nuevo médico
        </button>
    </div>

    <div class="admin-content">

        <!-- Buscador -->
        <div class="row mb-3 fade-up">
            <div class="col-md-4">
                <div style="position:relative;">
                    <i class="bi bi-search" style="position:absolute;left:.9rem;top:50%;transform:translateY(-50%);color:var(--gray-400);"></i>
                    <input type="text" id="busqueda" class="form-control ps-5" placeholder="Buscar por nombre o especialidad..." oninput="filtrar()">
                </div>
            </div>
            <div class="col-md-3">
                <select id="filtroSede" class="form-control" onchange="filtrar()">
                    <option value="">Todas las sedes</option>
                </select>
            </div>
        </div>

        <!-- Tabla -->
        <div class="panel-card fade-up fade-up-1">
            <div class="panel-card-header">
                <h5><i class="bi bi-list-ul me-2 text-success"></i>Listado de médicos</h5>
                <span id="totalLabel" style="font-size:.83rem;color:var(--gray-400);"></span>
            </div>
            <div style="overflow-x:auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Médico</th>
                            <th>Especialidad</th>
                            <th>Sede</th>
                            <th>Ciudad</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyMedicos">
                        <tr>
                            <td colspan="5" class="text-center py-4" style="color:var(--gray-400);">Cargando...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <?php require_once __DIR__ . '/../partials/sidebar_close.php'; ?>

    <!-- Modal nuevo/editar médico -->
    <div class="modal fade" id="modalMedico" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" style="max-width:520px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle" style="font-family:var(--font-body);font-weight:700;">Nuevo médico</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">

                    <div class="mc-alert mc-alert-error" id="modalAlert">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        <span id="modalAlertMsg"></span>
                    </div>

                    <div id="camposNuevo">
                        <div class="row g-3 mb-3">
                            <div class="col-sm-6">
                                <label class="form-label">Nombre completo *</label>
                                <input type="text" id="mNombre" class="form-control" placeholder="Dr. Juan García">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">Email *</label>
                                <input type="email" id="mEmail" class="form-control" placeholder="medico@correo.com">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña inicial *</label>
                            <input type="password" id="mPassword" class="form-control" placeholder="Mín. 8 caracteres">
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label">Sede *</label>
                            <select id="mSede" class="form-control">
                                <option value="">Selecciona...</option>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Especialidad *</label>
                            <input type="text" id="mEspecialidad" class="form-control" placeholder="Ej: Cardiología">
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label">Perfil profesional</label>
                        <textarea id="mPerfil" class="form-control" rows="3"
                            placeholder="Descripción del médico, años de experiencia..."></textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <button class="btn-mc-outline" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn-mc-primary" style="width:auto;padding:.6rem 1.5rem;" id="btnGuardar" onclick="guardarMedico()">
                        <span class="spinner" id="spinnerModal"></span>
                        <span id="btnGuardarText">Guardar</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal confirmar eliminar -->
    <div class="modal fade" id="modalEliminar" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" style="max-width:380px;">
            <div class="modal-content">
                <div class="modal-body p-4 text-center">
                    <div style="font-size:2.5rem;margin-bottom:1rem;">⚠️</div>
                    <h5 style="font-family:var(--font-body);font-weight:700;">¿Eliminar médico?</h5>
                    <p style="color:var(--gray-400);font-size:.9rem;margin:.5rem 0 1.5rem;">
                        Se eliminará el médico <strong id="eliminarNombre"></strong> y su usuario asociado.
                        Esta acción no se puede deshacer.
                    </p>
                    <div class="d-flex gap-2 justify-content-center">
                        <button class="btn-mc-outline" data-bs-dismiss="modal">Cancelar</button>
                        <button class="btn-action danger px-4 py-2" onclick="confirmarEliminar()" style="font-size:.9rem;">
                            Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let todosMedicos = [];
        let sedes = [];
        let modoEditar = false;
        let editarId = null;
        let eliminarId = null;

        const modalMedico = new bootstrap.Modal(document.getElementById('modalMedico'));
        const modalEliminar = new bootstrap.Modal(document.getElementById('modalEliminar'));

        async function init() {
            const [resMed, resSedes] = await Promise.all([
                fetch('/mediconnect/medicos', {
                    credentials: 'same-origin'
                }),
                fetch('/mediconnect/sedes', {
                    credentials: 'same-origin'
                }),
            ]);

            const dataMed = await resMed.json();
            const dataSedes = await resSedes.json();

            todosMedicos = dataMed.medicos ?? [];
            sedes = dataSedes.sedes ?? [];

            // Poblar selector de sedes
            const selSede = document.getElementById('filtroSede');
            sedes.forEach(s => {
                selSede.innerHTML += `<option value="${s.id}">${s.nombre_sede}</option>`;
            });

            filtrar();
        }

        function filtrar() {
            const q = document.getElementById('busqueda').value.toLowerCase();
            const sede = document.getElementById('filtroSede').value;

            const filtrados = todosMedicos.filter(m => {
                const matchQ = !q || m.nombre_completo.toLowerCase().includes(q) ||
                    m.grupo_especialidad.toLowerCase().includes(q);
                const matchS = !sede || String(m.sede_id) === sede;
                return matchQ && matchS;
            });

            renderTabla(filtrados);
        }

        function renderTabla(lista) {
            document.getElementById('totalLabel').textContent = `${lista.length} médico(s)`;
            const tbody = document.getElementById('tbodyMedicos');

            if (!lista.length) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4" style="color:var(--gray-400);">Sin resultados</td></tr>';
                return;
            }

            tbody.innerHTML = lista.map(m => `
      <tr>
        <td>
          <div style="display:flex;align-items:center;gap:.75rem;">
            <div style="width:36px;height:36px;background:var(--primary-light);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;color:var(--primary);font-size:.85rem;flex-shrink:0;">
              ${m.nombre_completo.charAt(0).toUpperCase()}
            </div>
            <div>
              <div style="font-weight:600;font-size:.9rem;">${m.nombre_completo}</div>
              <div style="font-size:.78rem;color:var(--gray-400);">${m.email}</div>
            </div>
          </div>
        </td>
        <td style="font-size:.88rem;">${m.grupo_especialidad}</td>
        <td style="font-size:.88rem;">${m.nombre_sede}</td>
        <td style="font-size:.83rem;color:var(--gray-400);">${m.ciudad}</td>
        <td>
          <button class="btn-action edit me-1" onclick="abrirModalEditar(${m.id})">
            <i class="bi bi-pencil"></i> Editar
          </button>
          <button class="btn-action danger" onclick="abrirModalEliminar(${m.id}, '${m.nombre_completo}')">
            <i class="bi bi-trash"></i>
          </button>
        </td>
      </tr>`).join('');
        }

        // ── Modal nuevo ──
        function abrirModalNuevo() {
            modoEditar = false;
            editarId = null;
            document.getElementById('modalTitle').textContent = 'Nuevo médico';
            document.getElementById('camposNuevo').style.display = 'block';
            limpiarModal();
            poblarSedesModal();
            modalMedico.show();
        }

        // ── Modal editar ──
        function abrirModalEditar(id) {
            const m = todosMedicos.find(x => x.id === id);
            if (!m) return;
            modoEditar = true;
            editarId = id;
            document.getElementById('modalTitle').textContent = 'Editar médico';
            document.getElementById('camposNuevo').style.display = 'none';
            limpiarModal();
            poblarSedesModal();
            document.getElementById('mSede').value = m.sede_id;
            document.getElementById('mEspecialidad').value = m.grupo_especialidad;
            document.getElementById('mPerfil').value = m.perfil_profesional ?? '';
            modalMedico.show();
        }

        function poblarSedesModal() {
            const sel = document.getElementById('mSede');
            sel.innerHTML = '<option value="">Selecciona una sede...</option>';
            sedes.forEach(s => {
                sel.innerHTML += `<option value="${s.id}">${s.nombre_sede}</option>`;
            });
        }

        function limpiarModal() {
            ['mNombre', 'mEmail', 'mPassword', 'mEspecialidad', 'mPerfil'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.value = '';
            });
            document.getElementById('modalAlert').classList.remove('show');
        }

        async function guardarMedico() {
            const sede = document.getElementById('mSede').value;
            const especialidad = document.getElementById('mEspecialidad').value.trim();
            const perfil = document.getElementById('mPerfil').value.trim();

            if (!sede || !especialidad) {
                mostrarAlertModal('Sede y especialidad son obligatorios');
                return;
            }

            setLoadingModal(true);

            try {
                let res, body;

                if (modoEditar) {
                    body = {
                        sede_id: parseInt(sede),
                        grupo_especialidad: especialidad,
                        perfil_profesional: perfil
                    };
                    res = await fetch(`/mediconnect/admin/medicos/${editarId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify(body),
                    });
                } else {
                    const nombre = document.getElementById('mNombre').value.trim();
                    const email = document.getElementById('mEmail').value.trim();
                    const password = document.getElementById('mPassword').value;
                    if (!nombre || !email || !password) {
                        mostrarAlertModal('Todos los campos son obligatorios');
                        setLoadingModal(false);
                        return;
                    }
                    body = {
                        nombre_completo: nombre,
                        email,
                        password,
                        sede_id: parseInt(sede),
                        grupo_especialidad: especialidad,
                        perfil_profesional: perfil
                    };
                    res = await fetch('/mediconnect/admin/medicos', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify(body),
                    });
                }

                const data = await res.json();
                if (res.ok) {
                    modalMedico.hide();
                    await init();
                } else mostrarAlertModal(data.error ?? 'Error al guardar');

            } catch {
                mostrarAlertModal('Error de conexión');
            } finally {
                setLoadingModal(false);
            }
        }

        function mostrarAlertModal(msg) {
            const el = document.getElementById('modalAlert');
            document.getElementById('modalAlertMsg').textContent = msg;
            el.classList.add('show');
        }

        function setLoadingModal(v) {
            document.getElementById('spinnerModal').classList.toggle('show', v);
            document.getElementById('btnGuardarText').textContent = v ? 'Guardando...' : 'Guardar';
            document.getElementById('btnGuardar').disabled = v;
        }

        // ── Eliminar ──
        function abrirModalEliminar(id, nombre) {
            eliminarId = id;
            document.getElementById('eliminarNombre').textContent = nombre;
            modalEliminar.show();
        }

        async function confirmarEliminar() {
            const res = await fetch(`/mediconnect/admin/medicos/${eliminarId}`, {
                method: 'DELETE',
                credentials: 'same-origin',
            });
            if (res.ok) {
                modalEliminar.hide();
                await init();
            } else {
                const d = await res.json();
                alert(d.error ?? 'No se pudo eliminar');
            }
        }

        init();
    </script>
</body>

</html>