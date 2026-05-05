<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard – MediConnect Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/mediconnect/assets/css/main.css">
</head>

<body>

    <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

    <!-- Topbar -->
    <div class="admin-topbar">
        <h2><i class="bi bi-grid-1x2 me-2 text-success"></i>Dashboard</h2>
        <span style="font-size:.85rem;color:var(--gray-400);">
            <?= date('l, d \d\e F Y') ?>
        </span>
    </div>

    <div class="admin-content">

        <!-- ── Stat cards ── -->
        <div class="row g-3 mb-4">

            <div class="col-sm-6 col-xl-3 fade-up">
                <div class="stat-card">
                    <div class="stat-icon green"><i class="bi bi-calendar-check" style="color:var(--primary);"></i></div>
                    <div>
                        <div class="stat-value" id="statTotal">–</div>
                        <div class="stat-label">Total de citas</div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3 fade-up fade-up-1">
                <div class="stat-card">
                    <div class="stat-icon amber"><i class="bi bi-hourglass-split" style="color:#D97706;"></i></div>
                    <div>
                        <div class="stat-value" id="statPendientes">–</div>
                        <div class="stat-label">Pendientes</div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3 fade-up fade-up-2">
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="bi bi-person-badge" style="color:#2563EB;"></i></div>
                    <div>
                        <div class="stat-value" id="statMedicos">–</div>
                        <div class="stat-label">Médicos activos</div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3 fade-up fade-up-3">
                <div class="stat-card">
                    <div class="stat-icon red"><i class="bi bi-x-circle" style="color:#DC2626;"></i></div>
                    <div>
                        <div class="stat-value" id="statCanceladas">–</div>
                        <div class="stat-label">Canceladas</div>
                    </div>
                </div>
            </div>

        </div>

        <!-- ── Citas recientes + distribución por estado ── -->
        <div class="row g-3 mb-4">

            <!-- Tabla citas recientes -->
            <div class="col-lg-8 fade-up">
                <div class="panel-card">
                    <div class="panel-card-header">
                        <h5><i class="bi bi-clock-history me-2 text-success"></i>Citas recientes</h5>
                        <a href="citas.php" style="font-size:.85rem;color:var(--primary);font-weight:600;">Ver todas →</a>
                    </div>
                    <div style="overflow-x:auto;">
                        <table class="admin-table" id="tablaCitas">
                            <thead>
                                <tr>
                                    <th>Paciente</th>
                                    <th>Médico</th>
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                    <th>Estado</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyCitas">
                                <tr>
                                    <td colspan="6" class="text-center py-4" style="color:var(--gray-400);">Cargando...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Panel lateral: distribución + accesos rápidos -->
            <div class="col-lg-4 fade-up fade-up-1">

                <!-- Distribución por estado -->
                <div class="panel-card mb-3">
                    <div class="panel-card-header">
                        <h5><i class="bi bi-pie-chart me-2 text-success"></i>Por estado</h5>
                    </div>
                    <div style="padding:1.2rem 1.5rem;" id="estadoChart">
                        <div style="color:var(--gray-400);font-size:.88rem;">Cargando...</div>
                    </div>
                </div>

                <!-- Accesos rápidos -->
                <div class="panel-card">
                    <div class="panel-card-header">
                        <h5><i class="bi bi-lightning me-2 text-success"></i>Accesos rápidos</h5>
                    </div>
                    <div style="padding:1rem 1.5rem;display:flex;flex-direction:column;gap:.6rem;">
                        <a href="medicos.php" class="btn-mc-outline text-decoration-none" style="justify-content:flex-start;">
                            <i class="bi bi-person-plus"></i> Registrar médico
                        </a>
                        <a href="servicios.php" class="btn-mc-outline text-decoration-none" style="justify-content:flex-start;">
                            <i class="bi bi-plus-circle"></i> Nuevo servicio
                        </a>
                        <a href="citas.php" class="btn-mc-outline text-decoration-none" style="justify-content:flex-start;">
                            <i class="bi bi-calendar-plus"></i> Gestionar citas
                        </a>
                        <?php if (($_SESSION['user_rol'] ?? '') === 'superadmin'): ?>
                            <a href="sedes.php" class="btn-mc-outline text-decoration-none" style="justify-content:flex-start;">
                                <i class="bi bi-building-add"></i> Nueva sede
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>

    </div><!-- .admin-content -->

    <?php require_once __DIR__ . '/../partials/sidebar_close.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const BADGE = {
            pendiente: '<span class="cita-badge badge-pendiente">Pendiente</span>',
            confirmada: '<span class="cita-badge badge-confirmada">Confirmada</span>',
            cancelada: '<span class="cita-badge badge-cancelada">Cancelada</span>',
            finalizada: '<span class="cita-badge badge-finalizada">Finalizada</span>',
        };

        function fmtFecha(f) {
            return new Date(f + 'T00:00:00').toLocaleDateString('es-CO', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
        }

        async function cargarDashboard() {
            try {
                const [resCitas, resMedicos] = await Promise.all([
                    fetch('/mediconnect/admin/citas', {
                        credentials: 'same-origin'
                    }),
                    fetch('/mediconnect/medicos', {
                        credentials: 'same-origin'
                    }),
                ]);

                const dataCitas = await resCitas.json();
                const dataMedicos = await resMedicos.json();

                const citas = dataCitas.citas ?? [];
                const medicos = dataMedicos.medicos ?? [];

                // ── Estadísticas ──
                const total = citas.length;
                const pendientes = citas.filter(c => c.estado === 'pendiente').length;
                const canceladas = citas.filter(c => c.estado === 'cancelada').length;

                document.getElementById('statTotal').textContent = total;
                document.getElementById('statPendientes').textContent = pendientes;
                document.getElementById('statMedicos').textContent = medicos.length;
                document.getElementById('statCanceladas').textContent = canceladas;

                // ── Tabla: últimas 8 citas ──
                const recientes = [...citas].slice(0, 8);
                const tbody = document.getElementById('tbodyCitas');

                if (!recientes.length) {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4" style="color:var(--gray-400);">Sin citas registradas</td></tr>';
                } else {
                    tbody.innerHTML = recientes.map(c => `
          <tr>
            <td>
              <div style="font-weight:600;font-size:.88rem;">${c.nombre_cliente ?? '–'}</div>
            </td>
            <td style="font-size:.88rem;">${c.nombre_medico ?? '–'}</td>
            <td style="font-size:.88rem;">${fmtFecha(c.fecha_cita)}</td>
            <td style="font-size:.88rem;font-family:monospace;">${c.hora_cita?.substring(0,5)}</td>
            <td>${BADGE[c.estado] ?? c.estado}</td>
            <td>
              ${c.estado === 'pendiente' ? `
                <button class="btn-action success" onclick="cambiarEstado(${c.id},'confirmada')">
                  <i class="bi bi-check"></i> Confirmar
                </button>` : ''}
              ${c.estado === 'pendiente' || c.estado === 'confirmada' ? `
                <button class="btn-action danger ms-1" onclick="cambiarEstado(${c.id},'cancelada')">
                  <i class="bi bi-x"></i>
                </button>` : ''}
            </td>
          </tr>`).join('');
                }

                // ── Gráfico de barras por estado (CSS puro) ──
                const estados = ['pendiente', 'confirmada', 'cancelada', 'finalizada'];
                const colores = {
                    pendiente: '#F4A943',
                    confirmada: '#0B6E4F',
                    cancelada: '#D94F4F',
                    finalizada: '#9CA3AF'
                };
                const max = Math.max(...estados.map(e => citas.filter(c => c.estado === e).length), 1);

                document.getElementById('estadoChart').innerHTML = estados.map(e => {
                    const cnt = citas.filter(c => c.estado === e).length;
                    const pct = Math.round((cnt / max) * 100);
                    return `
          <div style="margin-bottom:.9rem;">
            <div style="display:flex;justify-content:space-between;font-size:.82rem;margin-bottom:.3rem;">
              <span style="text-transform:capitalize;font-weight:500;">${e}</span>
              <span style="color:var(--gray-400);">${cnt}</span>
            </div>
            <div style="height:8px;background:var(--gray-100);border-radius:99px;overflow:hidden;">
              <div style="height:100%;width:${pct}%;background:${colores[e]};border-radius:99px;transition:width .6s ease;"></div>
            </div>
          </div>`;
                }).join('');

            } catch (err) {
                console.error(err);
            }
        }

        async function cambiarEstado(id, estado) {
            if (!confirm(`¿Cambiar estado de la cita a "${estado}"?`)) return;

            const res = await fetch(`/mediconnect/citas/${id}/estado`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    estado
                }),
            });

            if (res.ok) cargarDashboard();
            else alert('Error al cambiar el estado');
        }

        cargarDashboard();
    </script>
</body>

</html>