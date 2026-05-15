<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/paths.php';
$w = mc_web_base();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Citas – MediConnect Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($w) ?>/assets/css/main.css">
</head>

<body>

    <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="admin-topbar">
        <h2><i class="bi bi-calendar-check me-2 text-success"></i>Gestión de Citas</h2>
    </div>

    <div class="admin-content">

        <!-- Filtros -->
        <div class="panel-card mb-4 fade-up">
            <div class="panel-card-header">
                <h5><i class="bi bi-funnel me-2"></i>Filtrar citas</h5>
            </div>
            <div style="padding:1.2rem 1.5rem;">
                <div class="row g-3 align-items-end">
                    <div class="col-sm-4 col-md-3">
                        <label class="form-label">Estado</label>
                        <select id="filtroEstado" class="form-control" onchange="cargarCitas()">
                            <option value="">Todos</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="confirmada">Confirmada</option>
                            <option value="cancelada">Cancelada</option>
                            <option value="finalizada">Finalizada</option>
                        </select>
                    </div>
                    <div class="col-sm-4 col-md-3">
                        <label class="form-label">Buscar paciente / médico</label>
                        <input type="text" id="filtroBusqueda" class="form-control" placeholder="Nombre..." oninput="filtrarLocal()">
                    </div>
                    <div class="col-sm-4 col-md-3">
                        <label class="form-label">Fecha</label>
                        <input type="date" id="filtroFecha" class="form-control" onchange="filtrarLocal()">
                    </div>
                    <div class="col-md-3">
                        <button class="btn-mc-outline w-100" onclick="limpiarFiltros()">
                            <i class="bi bi-x-circle me-1"></i> Limpiar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla -->
        <div class="panel-card fade-up fade-up-1">
            <div class="panel-card-header">
                <h5><i class="bi bi-list-ul me-2 text-success"></i>Listado de citas</h5>
                <span id="totalLabel" style="font-size:.83rem;color:var(--gray-400);"></span>
            </div>
            <div style="overflow-x:auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Paciente</th>
                            <th>Médico</th>
                            <th>Servicio</th>
                            <th>Sede</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyCitas">
                        <tr>
                            <td colspan="9" class="text-center py-4" style="color:var(--gray-400);">Cargando...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <?php require_once __DIR__ . '/../partials/sidebar_close.php'; ?>

    <!-- Modal cambiar estado -->
    <div class="modal fade" id="modalEstado" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" style="max-width:380px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" style="font-family:var(--font-body);font-weight:700;">
                        Cambiar estado de cita
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p style="font-size:.9rem;color:var(--gray-600);margin-bottom:1rem;">
                        Cita <strong id="modalCitaInfo"></strong>
                    </p>
                    <label class="form-label">Nuevo estado</label>
                    <select id="nuevoEstado" class="form-control">
                        <option value="pendiente">Pendiente</option>
                        <option value="confirmada">Confirmada</option>
                        <option value="cancelada">Cancelada</option>
                        <option value="finalizada">Finalizada</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button class="btn-mc-outline" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn-mc-primary" style="width:auto;padding:.6rem 1.5rem;" onclick="guardarEstado()">
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API = (() => {
            const p = window.location.pathname;
            const iv = p.indexOf('/views/');
            if (iv > 0) return p.slice(0, iv);
            const ia = p.indexOf('/assets/');
            if (ia > 0) return p.slice(0, ia);
            return '';
        })();
        const BADGE = {
            pendiente: '<span class="cita-badge badge-pendiente">Pendiente</span>',
            confirmada: '<span class="cita-badge badge-confirmada">Confirmada</span>',
            cancelada: '<span class="cita-badge badge-cancelada">Cancelada</span>',
            finalizada: '<span class="cita-badge badge-finalizada">Finalizada</span>',
        };

        let todasCitas = [];
        let citaActualId = null;
        const modalEstado = new bootstrap.Modal(document.getElementById('modalEstado'));

        function fmtFecha(f) {
            return new Date(f + 'T00:00:00').toLocaleDateString('es-CO', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
        }

        function renderTabla(lista) {
            const tbody = document.getElementById('tbodyCitas');
            document.getElementById('totalLabel').textContent = `${lista.length} registro(s)`;

            if (!lista.length) {
                tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4" style="color:var(--gray-400);">Sin resultados</td></tr>';
                return;
            }

            tbody.innerHTML = lista.map((c, i) => `
      <tr>
        <td style="color:var(--gray-400);font-size:.82rem;">${i + 1}</td>
        <td style="font-weight:600;font-size:.88rem;">${c.nombre_cliente ?? '–'}</td>
        <td style="font-size:.88rem;">${c.nombre_medico ?? '–'}</td>
        <td style="font-size:.83rem;color:var(--gray-600);">${c.nombre_servicio ?? '–'}</td>
        <td style="font-size:.83rem;color:var(--gray-600);">${c.nombre_sede ?? '–'}</td>
        <td style="font-size:.88rem;">${fmtFecha(c.fecha_cita)}</td>
        <td style="font-family:monospace;font-size:.88rem;">${c.hora_cita?.substring(0,5)}</td>
        <td>${BADGE[c.estado] ?? c.estado}</td>
        <td>
          <button class="btn-action edit" onclick="abrirModal(${c.id}, '${c.nombre_cliente}', '${c.estado}')">
            <i class="bi bi-pencil"></i> Estado
          </button>
        </td>
      </tr>`).join('');
        }

        async function cargarCitas() {
            const estado = document.getElementById('filtroEstado').value;
            const url = estado ? `${API}/admin/citas?estado=${estado}` : `${API}/admin/citas`;

            try {
                const res = await fetch(url, {
                    credentials: 'same-origin'
                });
                const data = await res.json();
                todasCitas = data.citas ?? [];
                filtrarLocal();
            } catch {
                document.getElementById('tbodyCitas').innerHTML =
                    '<tr><td colspan="9" class="text-center py-3" style="color:var(--danger);">Error al cargar</td></tr>';
            }
        }

        function filtrarLocal() {
            const q = document.getElementById('filtroBusqueda').value.toLowerCase();
            const fecha = document.getElementById('filtroFecha').value;

            const filtradas = todasCitas.filter(c => {
                const matchQ = !q || (c.nombre_cliente ?? '').toLowerCase().includes(q) ||
                    (c.nombre_medico ?? '').toLowerCase().includes(q);
                const matchF = !fecha || c.fecha_cita === fecha;
                return matchQ && matchF;
            });

            renderTabla(filtradas);
        }

        function limpiarFiltros() {
            document.getElementById('filtroEstado').value = '';
            document.getElementById('filtroBusqueda').value = '';
            document.getElementById('filtroFecha').value = '';
            cargarCitas();
        }

        function abrirModal(id, cliente, estadoActual) {
            citaActualId = id;
            document.getElementById('modalCitaInfo').textContent = `de ${cliente}`;
            document.getElementById('nuevoEstado').value = estadoActual;
            modalEstado.show();
        }

        async function guardarEstado() {
            const estado = document.getElementById('nuevoEstado').value;

            const res = await fetch(`${API}/citas/${citaActualId}/estado`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    estado
                }),
            });

            if (res.ok) {
                modalEstado.hide();
                cargarCitas();
            } else {
                const d = await res.json();
                alert(d.error ?? 'Error al actualizar');
            }
        }

        cargarCitas();
    </script>
</body>

</html>