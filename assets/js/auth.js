const API = '/mediconnect';

/* ── Utilidades ──────────────────────────────────────────── */
function showAlert(msg, type = 'danger') {
    const box = document.getElementById('alert-box') ?? document.getElementById('alert-error');
    if (!box) return;
    box.className = `alert alert-${type} py-2 small`;
    box.textContent = msg;
    box.classList.remove('d-none');
}

function hideAlert() {
    const box = document.getElementById('alert-box') ?? document.getElementById('alert-error');
    if (box) box.classList.add('d-none');
}

function setLoading(loading) {
    const btn     = document.getElementById('btn-login') ?? document.getElementById('btn-registro');
    const txt     = document.getElementById('btn-text');
    const spinner = document.getElementById('btn-spinner');
    if (!btn) return;
    btn.disabled       = loading;
    txt.classList.toggle('d-none', loading);
    spinner.classList.toggle('d-none', !loading);
}

/* ── Mostrar / ocultar contraseña ────────────────────────── */
document.getElementById('toggle-pass')?.addEventListener('click', () => {
    const input = document.getElementById('password');
    input.type  = input.type === 'password' ? 'text' : 'password';
});

/* ── Fortaleza de contraseña (solo en registro) ──────────── */
document.getElementById('password')?.addEventListener('input', function () {
    const bar   = document.getElementById('strength-bar');
    const label = document.getElementById('strength-label');
    if (!bar) return;

    const v = this.value;
    let score = 0;
    if (v.length >= 8)              score++;
    if (/[A-Z]/.test(v))            score++;
    if (/[0-9]/.test(v))            score++;
    if (/[^A-Za-z0-9]/.test(v))    score++;

    const levels = [
        { pct: '25%',  cls: 'bg-danger',  txt: 'Débil' },
        { pct: '50%',  cls: 'bg-warning', txt: 'Regular' },
        { pct: '75%',  cls: 'bg-info',    txt: 'Buena' },
        { pct: '100%', cls: 'bg-success', txt: 'Fuerte' },
    ];

    if (v.length === 0) {
        bar.style.width = '0%';
        label.textContent = '';
        return;
    }

    const lvl = levels[score - 1] ?? levels[0];
    bar.style.width  = lvl.pct;
    bar.className    = `progress-bar ${lvl.cls}`;
    label.textContent = lvl.txt;
    label.className   = `form-text text-${lvl.cls.replace('bg-', '')}`;
});

/* ── LOGIN ───────────────────────────────────────────────── */
document.getElementById('form-login')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    hideAlert();

    const email    = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;

    if (!email || !password) {
        showAlert('Completa todos los campos.');
        return;
    }

    setLoading(true);

    try {
        const res  = await fetch(`${API}/auth/login`, {
            method:      'POST',
            credentials: 'include',
            headers:     { 'Content-Type': 'application/json' },
            body:        JSON.stringify({ email, password }),
        });
        const data = await res.json();

        if (!res.ok) {
            showAlert(data.error ?? 'Error al iniciar sesión.');
            return;
        }

        // Redirigir según rol
        const rol = data.usuario.rol;
        const destinos = {
            superadmin: `${API}/views/admin/dashboard.php`,
            admin_sede: `${API}/views/admin/dashboard.php`,
            prestador:  `${API}/views/prestador/dashboard.php`,
            cliente:    `${API}/views/cliente/dashboard.php`,
        };

        window.location.href = destinos[rol] ?? `${API}/views/cliente/dashboard.php`;

    } catch {
        showAlert('Error de conexión. Intenta de nuevo.');
    } finally {
        setLoading(false);
    }
});

/* ── REGISTRO ────────────────────────────────────────────── */
document.getElementById('form-registro')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    hideAlert();

    const nombre   = document.getElementById('nombre').value.trim();
    const email    = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const confirm  = document.getElementById('confirm').value;

    // Validaciones cliente
    if (!nombre || !email || !password || !confirm) {
        showAlert('Completa todos los campos.');
        return;
    }

    if (nombre.length < 3) {
        showAlert('El nombre debe tener al menos 3 caracteres.');
        return;
    }

    if (password.length < 8) {
        showAlert('La contraseña debe tener al menos 8 caracteres.');
        return;
    }

    if (password !== confirm) {
        showAlert('Las contraseñas no coinciden.');
        document.getElementById('confirm').classList.add('is-invalid');
        return;
    }

    document.getElementById('confirm').classList.remove('is-invalid');
    setLoading(true);

    try {
        const res  = await fetch(`${API}/auth/register`, {
            method:      'POST',
            credentials: 'include',
            headers:     { 'Content-Type': 'application/json' },
            body:        JSON.stringify({ nombre_completo: nombre, email, password }),
        });
        const data = await res.json();

        if (!res.ok) {
            showAlert(data.error ?? 'Error al crear la cuenta.');
            return;
        }

        showAlert('¡Cuenta creada! Redirigiendo...', 'success');

        setTimeout(() => {
            window.location.href = `${API}/views/auth/login.php`;
        }, 1500);

    } catch {
        showAlert('Error de conexión. Intenta de nuevo.');
    } finally {
        setLoading(false);
    }
});