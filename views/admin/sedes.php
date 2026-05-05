<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sedes – MediConnect Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/mediconnect/assets/css/main.css">
</head>

<body>

    <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="admin-topbar">
        <h2><i class="bi bi-building me-2 text-success"></i>Sedes</h2>
        <button class="btn-mc-primary" style="width:auto;padding:.55rem 1.2rem;" onclick="abrirModalNuevo()">
            <i class="bi bi-plus-lg me-1"></i> Nueva sede
        </button>
    </div>

    <div class="admin-content">

        <!-- Cards de sedes -->
        <div class="row g-3" id="sedesGrid">
            <div class="col-12 text-center py-5" style="color:var(--gray-400);">Cargando...</div>
        </div>

    </div>

    <?php require_once __DIR__ . '/../partials/sidebar_close.php'; ?>

    <!-- Modal sede -->
    <div class="modal fade" id="modalSede" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" style="max-width:500px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle" style="font-family:var(--font-body);font-weight:700;">Nueva sede</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mc-alert mc-alert-error" id="modalAlert">
                        <i class="bi bi-exclamation-circle-fill"></i><span id="modalAlertMsg"></span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre de la sede *</label>
                        <input type="text" id="sedeNombre" class="form-control" placeholder="Ej: Sede Norte">
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label">Ciudad *</label>
                            <input type="text" id="sedeCiudad" class="form-control" placeholder="Ej: Montería">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Teléfono</label>
                            <input type="text" id="sedeTelefono" class="form-control" placeholder="(604) 000-0000">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Dirección *</label>
                        <input type="text" id="sedeDireccion" class="form-control" placeholder="Calle / Carrera...">
                    </div>
                    <div class="mt-3" id="estadoField" style="display:none;">
                        <label class="form-label">Estado</label>
                        <select id="sedeEstado" class="form-control">
                            <option value="activa">Activa</option>
                            <option value="inactiva">Inactiva</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn-mc-outline" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn-mc-primary" style="width:auto;padding:.6rem 1.5rem;" id="btnGuardar" onclick="guardar()">
                        <span class="spinner" id="spinnerModal"></span>
                        <span id="btnText">Guardar</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let sedes = [];
        let modoEditar = false;
        let editarId = null;
        const modal = new bootstrap.Modal(document.getElementById('modalSede'));

        async function cargar() {
            const res = await fetch('/mediconnect/sedes?estado=', {
                credentials: 'same-origin'
            });
            const data = await res.json();
            sedes = data.sedes ?? [];
            renderGrid();
        }

        function renderGrid() {
            const grid = document.getElementById('sedesGrid');
            if (!sedes.length) {
                grid.innerHTML = '<div class="col-12 text-center py-5" style="color:var(--gray-400);">No hay sedes registradas</div>';
                return;
            }

            grid.innerHTML = sedes.map(s => `
      <div class="col-sm-6 col-lg-4 fade-up">
        <div class="panel-card h-100">
          <div class="panel-card-header" style="background:${s.estado === 'activa' ? 'var(--primary-light)' : 'var(--gray-100)'}">
            <h5 style="color:${s.estado === 'activa' ? 'var(--primary-dark)' : 'var(--gray-400)'}">
              <i class="bi bi-building me-2"></i>${s.nombre_sede}
            </h5>
            <span style="font-size:.75rem;font-weight:600;padding:.2rem .6rem;border-radius:999px;
              background:${s.estado === 'activa' ? 'var(--primary)' : 'var(--gray-400)'};color:white;">
              ${s.estado}
            </span>
          </div>
          <div style="padding:1.2rem 1.5rem;">
            <p style="font-size:.88rem;margin-bottom:.5rem;"><i class="bi bi-geo-alt me-2 text-muted"></i>${s.ciudad}</p>
            <p style="font-size:.85rem;color:var(--gray-600);margin-bottom:.5rem;">
              <i class="bi bi-map me-2 text-muted"></i>${s.direccion}
            </p>
            ${s.telefono_contacto ? `<p style="font-size:.85rem;color:var(--gray-600);margin-bottom:0;">
              <i class="bi bi-telephone me-2 text-muted"></i>${s.telefono_contacto}
            </p>` : ''}
          </div>
          <div style="padding:.8rem 1.5rem;border-top:1px solid var(--gray-100);display:flex;gap:.5rem;">
            <button class="btn-action edit" onclick="abrirModalEditar(${s.id})">
              <i class="bi bi-pencil"></i> Editar
            </button>
            <button class="btn-action ${s.estado === 'activa' ? 'warning' : 'success'}"
                    onclick="toggleEstado(${s.id}, '${s.estado === 'activa' ? 'inactiva' : 'activa'}')">
              <i class="bi bi-${s.estado === 'activa' ? 'pause' : 'play'}-circle"></i>
              ${s.estado === 'activa' ? 'Desactivar' : 'Activar'}
            </button>
            <button class="btn-action danger ms-auto" onclick="eliminar(${s.id}, '${s.nombre_sede}')">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </div>
      </div>`).join('');
        }

        function abrirModalNuevo() {
            modoEditar = false;
            editarId = null;
            document.getElementById('modalTitle').textContent = 'Nueva sede';
            document.getElementById('estadoField').style.display = 'none';
            limpiar();
            modal.show();
        }

        function abrirModalEditar(id) {
            const s = sedes.find(x => x.id === id);
            if (!s) return;
            modoEditar = true;
            editarId = id;
            document.getElementById('modalTitle').textContent = 'Editar sede';
            document.getElementById('estadoField').style.display = 'block';
            limpiar();
            document.getElementById('sedeNombre').value = s.nombre_sede;
            document.getElementById('sedeCiudad').value = s.ciudad;
            document.getElementById('sedeDireccion').value = s.direccion;
            document.getElementById('sedeTelefono').value = s.telefono_contacto ?? '';
            document.getElementById('sedeEstado').value = s.estado;
            modal.show();
        }

        function limpiar() {
            ['sedeNombre', 'sedeCiudad', 'sedeDireccion', 'sedeTelefono'].forEach(id => {
                document.getElementById(id).value = '';
            });
            document.getElementById('modalAlert').classList.remove('show');
        }

        async function guardar() {
            const nombre = document.getElementById('sedeNombre').value.trim();
            const ciudad = document.getElementById('sedeCiudad').value.trim();
            const direccion = document.getElementById('sedeDireccion').value.trim();
            const telefono = document.getElementById('sedeTelefono').value.trim();
            const estado = document.getElementById('sedeEstado').value;

            if (!nombre || !ciudad || !direccion) {
                mostrarAlerta('Nombre, ciudad y dirección son obligatorios');
                return;
            }

            setLoading(true);
            const body = {
                nombre_sede: nombre,
                ciudad,
                direccion,
                telefono_contacto: telefono,
                estado
            };

            try {
                const res = modoEditar ?
                    await fetch(`/mediconnect/admin/sedes/${editarId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify(body)
                    }) :
                    await fetch('/mediconnect/admin/sedes', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify(body)
                    });

                const data = await res.json();
                if (res.ok) {
                    modal.hide();
                    await cargar();
                } else mostrarAlerta(data.error ?? 'Error al guardar');
            } catch {
                mostrarAlerta('Error de conexión');
            } finally {
                setLoading(false);
            }
        }

        async function toggleEstado(id, nuevoEstado) {
            const res = await fetch(`/mediconnect/admin/sedes/${id}/estado`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    estado: nuevoEstado
                }),
            });
            if (res.ok) cargar();
        }

        async function eliminar(id, nombre) {
            if (!confirm(`¿Eliminar la sede "${nombre}"?`)) return;
            const res = await fetch(`/mediconnect/admin/sedes/${id}`, {
                method: 'DELETE',
                credentials: 'same-origin'
            });
            if (res.ok) cargar();
            else {
                const d = await res.json();
                alert(d.error ?? 'No se pudo eliminar');
            }
        }

        function mostrarAlerta(msg) {
            const el = document.getElementById('modalAlert');
            document.getElementById('modalAlertMsg').textContent = msg;
            el.classList.add('show');
        }

        function setLoading(v) {
            document.getElementById('spinnerModal').classList.toggle('show', v);
            document.getElementById('btnText').textContent = v ? 'Guardando...' : 'Guardar';
            document.getElementById('btnGuardar').disabled = v;
        }

        cargar();
    </script>
</body>

</html>