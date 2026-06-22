<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DocsController {

    public function showApiDocs(Request $request, Response $response): Response {
        $html = <<<'HTML'
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Documentación de API</title>
    <style>
                :root {
            color-scheme: light;
            --bg: #f4f6fb;
            --aside-bg: #eef4ff;
            --content-bg: #f8fafc;
            --surface: #ffffff;
            --surface-strong: #f8fafc;
            --border: #dde2ea;
            --text: #0f172a;
            --muted: #475569;
            --accent: #2563eb;
            --accent-soft: #dbeafe;
            --success: #0f766e;
            --danger: #b91c1c;
            --shadow: 0 24px 80px rgba(15, 23, 42, 0.08);
        }

        [data-theme="dark"] {
            color-scheme: dark;
            --bg: #0f172a;
            --aside-bg: #111827;
            --content-bg: #1f2937;
            --surface: #111827;
            --surface-strong: #1f2937;
            --border: #334155;
            --text: #e2e8f0;
            --muted: #94a3b8;
            --accent: #60a5fa;
            --accent-soft: #1e3a8a;
            --success: #2dd4bf;
            --danger: #fca5a5;
            --shadow: 0 24px 80px rgba(0, 0, 0, 0.35);
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            min-height: 100%;
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: var(--bg);
            color: var(--text);
        }

        body {
            padding: 0 20px 40px;
        }

        .layout {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 24px;
            max-width: 1440px;
            margin: 0 auto;
            padding: 28px 0;
        }

        .panel, .main {
            border: 1px solid var(--border);
            border-radius: 22px;
            box-shadow: var(--shadow);
        }

        .panel {
            padding: 24px;
            position: sticky;
            top: 20px;
            align-self: start;
            height: calc(100vh - 56px);
            max-height: calc(100vh - 56px);
            overflow-y: auto;
            background: var(--aside-bg);
        }

        .main {
            padding: 28px 30px;
            background: var(--content-bg);
            color: var(--text);
        }

        .main h1,
        .endpoint-card strong,
        .menu-list a,
        .section-title {
            color: var(--text);
        }

        h1 {
            margin: 0 0 12px;
            font-size: clamp(2rem, 3vw, 2.6rem);
            letter-spacing: -0.03em;
        }

        .intro {
            margin: 0 0 28px;
            max-width: 760px;
            line-height: 1.75;
            color: var(--muted);
        }

        .section-title {
            margin: 0 0 18px;
            font-size: 1.1rem;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            color: var(--accent);
        }

        .status, .message {
            border-radius: 18px;
            padding: 18px 20px;
            margin-bottom: 24px;
            line-height: 1.6;
            border: 1px solid var(--border);
            background: var(--surface-strong);
        }

        .status.success {
            border-color: #0f766e;
            color: #d1fae5;
            background: rgba(34, 197, 94, 0.12);
        }

        .status.alert {
            border-color: #f87171;
            color: #fee2e2;
            background: rgba(248, 113, 113, 0.16);
        }

        .message.error {
            border-color: #fca5a5;
            color: var(--danger);
            background: rgba(248, 113, 113, 0.1);
        }

        .controls,
        .login-card,
        .theme-card,
        .navigation {
            margin-bottom: 24px;
        }

        .login-card,
        .theme-card,
        .navigation,
        .section {
            background: var(--surface-strong);
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 20px;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-size: 0.95rem;
            color: var(--muted);
        }

        input, select, textarea {
            width: 100%;
            max-width: 100%;
            border-radius: 14px;
            border: 1px solid var(--border);
            padding: 12px 14px;
            background: var(--bg);
            color: var(--text);
            font: inherit;
            margin-bottom: 16px;
            overflow: hidden;
        }

        textarea {
            min-height: 120px;
            resize: vertical;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        }

        button {
            border: none;
            border-radius: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.18s ease, opacity 0.18s ease, background 0.18s ease;
        }

        button.primary {
            background: var(--accent);
            color: white;
            padding: 14px 20px;
        }

        button.secondary {
            background: transparent;
            color: var(--text);
            border: 1px solid var(--border);
            padding: 14px 20px;
        }

        button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .menu-list {
            list-style: none;
            margin: 0;
            padding: 0;
            display: grid;
            gap: 10px;
        }

        .menu-list a {
            display: block;
            padding: 12px 16px;
            border-radius: 14px;
            text-decoration: none;
            color: var(--text);
            background: var(--surface);
            border: 1px solid transparent;
            transition: border-color 0.18s ease, transform 0.18s ease;
        }

        .menu-list a:hover,
        .menu-list a:focus-visible {
            border-color: var(--accent);
            transform: translateX(2px);
        }

        .endpoint-card {
            margin-bottom: 24px;
            padding: 24px;
            border-radius: 22px;
            background: var(--surface-strong);
            border: 1px solid var(--border);
            box-shadow: inset 0 0 0 1px rgba(255,255,255,0.05);
        }

        .endpoint-header {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 16px;
            margin-bottom: 18px;
        }

        .endpoint-header strong {
            font-size: 1.1rem;
            line-height: 1.3;
        }

        .tag {
            display: inline-flex;
            align-items: center;
            padding: 8px 12px;
            border-radius: 999px;
            font-size: 0.8rem;
            letter-spacing: 0.02em;
        }

        .method-GET { background: rgba(56, 189, 248, .15); color: #38bdf8; }
        .method-POST { background: rgba(34, 197, 94, .15); color: #4ade80; }
        .method-PUT { background: rgba(251, 191, 36, .15); color: #fbbf24; }
        .method-DELETE { background: rgba(248, 113, 113, .15); color: #f87171; }

        .endpoint-meta {
            margin: 0 0 18px;
            color: var(--muted);
            line-height: 1.75;
        }

        .details {
            display: grid;
            gap: 8px;
            margin-bottom: 18px;
        }

        .details dt {
            font-weight: 700;
            color: var(--text);
        }

        .details dd {
            margin: 0;
            color: var(--muted);
        }

        .endpoint-body {
            margin-bottom: 16px;
        }

        .endpoint-body textarea {
            min-height: 110px;
        }

        .footer-note {
            margin-top: 28px;
            color: var(--muted);
            font-size: 0.95rem;
            line-height: 1.7;
        }

        @media (max-width: 1024px) {
            .layout { grid-template-columns: 1fr; }
            .panel { position: static; }
        }
    </style>
</head>
<body>
    <div class="layout" data-theme="light" id="app-shell">
        <aside class="panel">
            <div class="section theme-card">
                <h2 class="section-title">Preferencias</h2>
                <label for="theme-select">Apariencia</label>
                <select id="theme-select">
                    <option value="system">Por defecto del sistema</option>
                    <option value="light">Claro</option>
                    <option value="dark">Oscuro</option>
                </select>
            </div>

            <div class="section login-card">
                <h2 class="section-title">Acceso</h2>
                <label for="email">Email</label>
                <input id="email" type="email" placeholder="empresa@dominio.com" />
                <label for="password">Contraseña</label>
                <input id="password" type="password" placeholder="••••••••" />
                <div style="display: grid; gap: 12px; margin-top: 10px;">
                    <button class="primary" id="login-button">Iniciar sesión</button>
                    <button class="secondary" id="logout-button" disabled>Cerrar sesión</button>
                </div>
            </div>

            <div class="section navigation">
                <h2 class="section-title">Índice</h2>
                <ul class="menu-list" id="doc-nav"></ul>
            </div>
        </aside>

        <main class="main">
            <header>
                <h1>Documentación de API</h1>
                <p class="intro">Explore las rutas disponibles y ejecute solicitudes en tiempo real. El acceso está diseñado para que solo las cuentas de tipo empresa puedan realizar pruebas con autorización.</p>
            </header>

            <div id="message-box" class="message" style="display:none;"></div>
            <div id="auth-status" class="status">Estado: no autenticado. Pruebas deshabilitadas.</div>

            <section>
                <h2 class="section-title">Endpoints disponibles</h2>
                <div id="docs-list"></div>
            </section>

            <p class="footer-note">Para habilitar el modo de prueba, autentíquese con credenciales de empresa. Las solicitudes se enviarán con el header <code>Authorization: Bearer &lt;token&gt;</code> cuando corresponda.</p>
        </main>
    </div>

    <script>
        const apiDocs = [
            { id: 'login', title: 'Login', method: 'POST', path: '/api/login', description: 'Autentica un usuario y devuelve access_token + datos de empresa.', authRequired: false, body: { email: 'empresa@demo.com', password: 'demo1234' } },
            { id: 'signup', title: 'Signup', method: 'POST', path: '/api/signup', description: 'Registra un nuevo usuario en el sistema.', authRequired: false, body: { username: 'empresa', name: 'Mi Empresa', email: 'empresa@demo.com', password: 'demo1234', is_company: true } },
            { id: 'ranking', title: 'Ranking', method: 'GET', path: '/api/ranking', description: 'Obtiene el ranking público de usuarios.', authRequired: false },
            { id: 'locations', title: 'Oscar locations', method: 'GET', path: '/api/oscar/location', description: 'Lista las ubicaciones disponibles de Oscar.', authRequired: false },
            { id: 'create-oscar-item', title: 'Crear ítem de Oscar', method: 'POST', path: '/api/oscar/item', description: 'Crea un nuevo ítem de Oscar.', authRequired: false, body: { name: 'Coffee voucher', points: 10, location_id: 1 } },
            { id: 'get-oscar', title: 'Stats Oscar', method: 'GET', path: '/api/oscar', description: 'Obtiene estadísticas de Oscar.', authRequired: true },
            { id: 'get-oscar-item', title: 'Obtener ítem de Oscar', method: 'GET', path: '/api/oscar/item?id=1', description: 'Obtiene un ítem de Oscar por ID.', authRequired: true },
            { id: 'delete-oscar', title: 'Eliminar Oscar', method: 'DELETE', path: '/api/oscar', description: 'Elimina un Oscar existente.', authRequired: true, body: { id: 1 } },
            { id: 'update-oscar', title: 'Actualizar Oscar', method: 'PUT', path: '/api/oscar', description: 'Actualiza un Oscar existente.', authRequired: true, body: { id: 1, name: 'New award name', points: 15 } },
            { id: 'claim-oscar', title: 'Claim Oscar', method: 'POST', path: '/api/oscar/claim', description: 'Reclama un Oscar con autorización.', authRequired: true, body: { item_id: 1 } },
            { id: 'redeem', title: 'Redeem', method: 'POST', path: '/api/redeem', description: 'Redime una recompensa para el usuario autenticado.', authRequired: true, body: { reward_id: 1 } },
            { id: 'history', title: 'Historial', method: 'GET', path: '/api/history', description: 'Recupera el historial de transacciones del usuario.', authRequired: true },
            { id: 'profile', title: 'Profile', method: 'GET', path: '/api/profile', description: 'Recupera los datos de perfil del usuario autenticado.', authRequired: true },
            { id: 'company-create', title: 'Crear empresa', method: 'POST', path: '/api/company', description: 'Crea la compañía asociada al usuario.', authRequired: true, body: { name: 'Mi Empresa', address: 'Calle 123', phone: '+34123456789' } },
            { id: 'company-update', title: 'Actualizar empresa', method: 'PUT', path: '/api/company', description: 'Actualiza los datos de la empresa.', authRequired: true, body: { name: 'Mi Empresa S.A.', address: 'Calle 1234', phone: '+34123456789' } }
        ];

        const state = { token: null, user: null, isCompany: false };
        const themeKey = 'apiDocsTheme';

        const appShell = document.getElementById('app-shell');
        const authStatusNode = document.getElementById('auth-status');
        const messageBox = document.getElementById('message-box');
        const docsList = document.getElementById('docs-list');
        const navList = document.getElementById('doc-nav');
        const loginButton = document.getElementById('login-button');
        const logoutButton = document.getElementById('logout-button');
        const themeSelect = document.getElementById('theme-select');

        function applyTheme(theme) {
            const mode = theme === 'system' ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light') : theme;
            document.body.dataset.theme = mode;
            appShell.dataset.theme = mode;
        }

        function saveThemePreference(theme) {
            localStorage.setItem(themeKey, theme);
        }

        function loadThemePreference() {
            const theme = localStorage.getItem(themeKey) || 'system';
            themeSelect.value = theme;
            applyTheme(theme);
        }

        function showMessage(text, type = 'info') {
            messageBox.style.display = 'block';
            messageBox.textContent = text;
            messageBox.className = type === 'error' ? 'message error' : 'message';
        }

        function hideMessage() {
            messageBox.style.display = 'none';
        }

        function updateAuthStatus() {
            if (state.isCompany) {
                authStatusNode.textContent = `Autenticado como empresa: ${state.user.email}. Try-it-out habilitado.`;
                authStatusNode.className = 'status success';
                loginButton.disabled = true;
                logoutButton.disabled = false;
            } else if (state.user) {
                authStatusNode.textContent = `Usuario autenticado, sin acceso de empresa. Pruebas deshabilitadas.`;
                authStatusNode.className = 'status alert';
                loginButton.disabled = true;
                logoutButton.disabled = false;
            } else {
                authStatusNode.textContent = 'Estado: no autenticado. Pruebas deshabilitadas.';
                authStatusNode.className = 'status';
                loginButton.disabled = false;
                logoutButton.disabled = true;
            }
            renderDocs();
            renderNavigation();
        }

        async function login() {
            hideMessage();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value.trim();

            if (!email || !password) {
                showMessage('Complete email y contraseña.', 'error');
                return;
            }

            try {
                const response = await fetch('/api/login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email, password })
                });
                const data = await response.json();

                if (!data.authenticated) {
                    clearAuth();
                    showMessage('Credenciales incorrectas.', 'error');
                    return;
                }

                if (!data.user || data.user.is_company !== true) {
                    clearAuth();
                    showMessage('No autorizado', 'error');
                    return;
                }

                state.token = data.access_token;
                state.user = data.user;
                state.isCompany = true;
                localStorage.setItem('apiDocsToken', state.token);
                localStorage.setItem('apiDocsUser', JSON.stringify(state.user));

                showMessage('Autenticación exitosa. Pruebas habilitadas.', 'info');
                updateAuthStatus();
            } catch (error) {
                clearAuth();
                showMessage('Error de conexión con el servidor.', 'error');
            }
        }

        function clearAuth() {
            state.token = null;
            state.user = null;
            state.isCompany = false;
            localStorage.removeItem('apiDocsToken');
            localStorage.removeItem('apiDocsUser');
            updateAuthStatus();
        }

        function loadAuthFromStorage() {
            const token = localStorage.getItem('apiDocsToken');
            const user = localStorage.getItem('apiDocsUser');
            if (!token || !user) {
                clearAuth();
                return;
            }
            try {
                state.token = token;
                state.user = JSON.parse(user);
                state.isCompany = state.user.is_company === true;
            } catch {
                clearAuth();
            }
            updateAuthStatus();
        }

        function renderNavigation() {
            navList.innerHTML = apiDocs.map(endpoint => `<li><a href="#endpoint-${endpoint.id}">${endpoint.method} ${endpoint.title}</a></li>`).join('');
        }

        function renderDocs() {
            docsList.innerHTML = apiDocs.map(endpoint => {
                const canTry = state.isCompany;
                const inputId = `body-${endpoint.id}`;
                const buttonDisabled = !canTry ? 'disabled' : '';
                const authBadge = endpoint.authRequired ? '<span class="tag">Auth</span>' : '<span class="tag" style="background: rgba(96, 165, 250, 0.14); color: #2563eb;">Público</span>';
                const sampleBody = endpoint.body ? JSON.stringify(endpoint.body, null, 2) : '';

                return `
                    <article class="endpoint-card" id="endpoint-${endpoint.id}">
                        <div class="endpoint-header">
                            <div>
                                <span class="tag method-${endpoint.method}">${endpoint.method}</span>
                                <strong>${endpoint.title}</strong>
                            </div>
                            ${authBadge}
                        </div>
                        <p class="endpoint-meta">${endpoint.description}</p>
                        <dl class="details">
                            <dt>Ruta</dt>
                            <dd>${endpoint.path}</dd>
                            <dt>Método</dt>
                            <dd>${endpoint.method}</dd>
                        </dl>
                        ${sampleBody ? `<div class="endpoint-body"><label for="${inputId}">Payload</label><textarea id="${inputId}">${sampleBody}</textarea></div>` : ''}
                        <button class="primary" ${buttonDisabled} onclick="tryEndpoint('${endpoint.id}')">Probar endpoint</button>
                    </article>
                `;
            }).join('');
        }

        async function tryEndpoint(endpointId) {
            hideMessage();
            const endpoint = apiDocs.find(item => item.id === endpointId);
            if (!endpoint) {
                showMessage('Endpoint no encontrado.', 'error');
                return;
            }

            if (!state.isCompany) {
                showMessage('Inicie sesión con una cuenta de empresa para ejecutar pruebas.', 'error');
                return;
            }

            const bodyInput = document.getElementById(`body-${endpoint.id}`);
            let body = null;
            if (bodyInput) {
                try {
                    body = JSON.parse(bodyInput.value);
                } catch (err) {
                    showMessage('JSON inválido en el payload.', 'error');
                    return;
                }
            }

            const headers = { 'Content-Type': 'application/json' };
            if (state.token) {
                headers.Authorization = `Bearer ${state.token}`;
            }

            const response = await fetch(endpoint.path, {
                method: endpoint.method,
                headers,
                body: ['POST', 'PUT', 'PATCH'].includes(endpoint.method) ? JSON.stringify(body) : undefined
            });

            const resultText = await response.text();
            messageBox.innerHTML = `<pre style="white-space: pre-wrap; margin:0; color: var(--text);">HTTP ${response.status} ${response.statusText}\n\n${resultText}</pre>`;
            messageBox.style.display = 'block';
            messageBox.className = 'status';
        }

        themeSelect.addEventListener('change', () => {
            const theme = themeSelect.value;
            applyTheme(theme);
            saveThemePreference(theme);
        });

        loginButton.addEventListener('click', login);
        logoutButton.addEventListener('click', () => {
            clearAuth();
            showMessage('Sesión cerrada.', 'info');
        });

        loadThemePreference();
        loadAuthFromStorage();
        renderNavigation();
    </script>
</body>
</html>
HTML;

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=UTF-8');
    }
}
