<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Servicios – MediConnect Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/mediconnect/assets/css/main.css">
</head>

<body>

    <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="admin-topbar">
        <h2><i class="bi bi-heart-pulse me-2 text-success"></i>Servicios</h2>
        <button class="btn-mc-primary" style="width:auto;padding:.55rem 1.2rem;" onclick="abrirModalNuevo()">
            <i class="bi bi-plus-lg me-1"></i> Nuevo servicio
        </button>
    </div>

    <div class="admin-content">

        <div class="row mb-3 fade-up">
            <div class="col-md-4">
                <div style="position:relative;">
                    <i class="bi bi-search" style="position:absolute;left:.9rem;top:50%;transform:translateY(-50%);color:var(--gray-400);"></i>
                    <input type="text" id="busqueda" class="form-control ps-5" placeholder="Buscar servicio..." oninput="filtrar()">
                </div>
            </div>
        </div>

        <div class="panel-card fade-up fade-up-1">
            <div class="panel-card-header">
                <h5><i class="bi bi-list-ul me-2 text-success"></i>Listado de servicios</h5>
                <span id="totalLabel" style="font-size:.83rem;color:var(--gray-400);"></span>
            </div>
            <div style="overflow-x:auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Servicio</th>
                            <th>Descripción</th>
                            <th>Precio</th>
                            <th>Duración</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyServicios">
                        <tr>
                            <td colspan="5" class="text-center py-4" style="color:var(--gray-400);">Cargando...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <?php require_once __DIR__ . '/../partials/sidebar_close.php'; ?>

    <!-- Modal servicio -->
    <div class="modal fade" id="modalServicio" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle" style="font-family:var(--font-body);font-weight:700;">Nuevo servicio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mc-alert mc-alert-error" id="modalAlert">
                        <i class="bi bi-exclamation-circle-fill"></i><span id="modalAlertMsg"></span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre del servicio *</label>
                        <input type="text" id="sNombre" class="form-control" placeholder="Ej: Consulta general">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea id="sDesc" class="form-control" rows="2" placeholder="Descripción breve..."></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label">Precio (COP) *</label>
                            <input type="number" id="sPrecio" class="form-control" placeholder="0" min="0" step="1000">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Duración (minutos) *</label>
                            <input type="number" id="sDuracion" class="form-control" placeholder="30" min="1">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn-mc-outline" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn-mc-primary" style="width:auto;padding:.6rem 1.5rem;" id="btnGuardar" onclick="guardarServicio()">
                        <span class="spinner" id="spinnerModal"></span>
                        <span id="btnGuardarText">Guardar</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let todosServicios = [];
        let modoEditar = false;
        let editarId = null;
        const fmt = new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            maximumFractionDigits: 0
        });
        const modal = new bootstrap.Modal(document.getElementById('modalServicio'));

        async function cargar() {
            const res = await fetch('/mediconnect/servicios', {
                credentials: 'same-origin'
            });
            const data = await res.json();
            todosServicios = data.servicios ?? [];
            filtrar();
        }

        function filtrar() {
            const q = document.getElementById('busqueda').value.toLowerCase();
            const f = todosServicios.filter(s => s.nombre_servicio.toLowerCase().includes(q) || (s.descripcion ?? '').toLowerCase().includes(q));
            renderTabla(f);
        }

        function renderTabla(lista) {
            document.getElementById('totalLabel').textContent = `${lista.length} servicio(s)`;
            const tbody = document.getElementById('tbodyServicios');
            if (!lista.length) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4" style="color:var(--gray-400);">Sin resultados</td></tr>';
                return;
            }
            tbody.innerHTML = lista.map(s => `
      <tr>
        <td style="font-weight:600;font-size:.9rem;">${s.nombre_servicio}</td>
        <td style="font-size:.85rem;color:var(--gray-600);max-width:220px;">
          <span style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
            ${s.descripcion ?? '–'}
          </span>
        </td>
        <td style="font-weight:600;color:var(--primary);">${fmt.format(s.precio)}</td>
        <td style="font-size:.88rem;"><i class="bi bi-clock me-1 text-muted"></i>${s.duracion_minutos} min</td>
        <td>
          <button class="btn-action edit me-1" onclick="abrirModalEditar(${s.id})">
            <i class="bi bi-pencil"></i> Editar
          </button>
          <button class="btn-action danger" onclick="eliminar(${s.id}, '${s.nombre_servicio}')">
            <i class="bi bi-trash"></i>
          </button>
        </td>
      </tr>`).join('');
        }

        function abrirModalNuevo() {
            modoEditar = false;
            editarId = null;
            document.getElementById('modalTitle').textContent = 'Nuevo servicio';
            limpiar();
            modal.show();
        }

        function abrirModalEditar(id) {
            const s = todosServicios.find(x => x.id === id);
            if (!s) return;
            modoEditar = true;
            editarId = id;
            document.getElementById('modalTitle').textContent = 'Editar servicio';
            limpiar();
            document.getElementById('sNombre').value = s.nombre_servicio;
            document.getElementById('sDesc').value = s.descripcion ?? '';
            document.getElementById('sPrecio').value = s.precio;
            document.getElementById('sDuracion').value = s.duracion_minutos;
            modal.show();
        }

        function limpiar() {
            ['sNombre', 'sDesc', 'sPrecio', 'sDuracion'].forEach(id => {
                document.getElementById(id).value = '';
            });
            document.getElementById('modalAlert').classList.remove('show');
        }

        async function guardarServicio() {
            const nombre = document.getElementById('sNombre').value.trim();
            const desc = document.getElementById('sDesc').value.trim();
            const precio = parseFloat(document.getElementById('sPrecio').value);
            const duracion = parseInt(document.getElementById('sDuracion').value);

            if (!nombre || isNaN(precio) || isNaN(duracion)) {
                mostrarAlerta('Nombre, precio y duración son obligatorios');
                return;
            }

            setLoading(true);
            const body = {
                nombre_servicio: nombre,
                descripcion: desc,
                precio,
                duracion_minutos: duracion
            };

            try {
                const res = modoEditar ?
                    await fetch(`/mediconnect/admin/servicios/${editarId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify(body)
                    }) :
                    await fetch('/mediconnect/admin/servicios', {
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

        async function eliminar(id, nombre) {
            if (!confirm(`¿Eliminar el servicio "${nombre}"?`)) return;
            const res = await fetch(`/mediconnect/admin/servicios/${id}`, {
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
            document.getElementById('btnGuardarText').textContent = v ? 'Guardando...' : 'Guardar';
            document.getElementById('btnGuardar').disabled = v;
        }

        cargar();
    </script>
</body>

</html>