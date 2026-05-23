<?php
$savedEmail = $_COOKIE['apidocs_email'] ?? '';
$savedPassword = $_COOKIE['apidocs_password'] ?? '';
$remember = isset($_COOKIE['apidocs_remember']) && $_COOKIE['apidocs_remember'] === '1';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <?php include __DIR__ . '/components/head.php'; ?>
</head>
<body>

<!-- ────────────────────────────────────────────
     SIDEBAR
──────────────────────────────────────────── -->
<?php include __DIR__ . '/components/sidebar.php'; ?>

<!-- ────────────────────────────────────────────
     MAIN
──────────────────────────────────────────── -->
<main class="main">

  <div class="page-header">
    <h1>API Docs</h1>
    <p>Base URL: <span id="baseUrlText" style="color:var(--accent)">http://localhost:8080</span>
       &nbsp;·&nbsp; Todas las respuestas son <code style="color:var(--text)">application/json</code></p>
  </div>

  <!-- ════════════════════════════════════════
       SECCIÓN: AUTH
  ════════════════════════════════════════ -->
  <div class="section-title">Autenticación</div>

  <!-- ── POST /api/login ── -->
  <div class="endpoint-card" id="login">
    <div class="endpoint-header" onclick="toggleEndpoint(this)">
      <span class="endpoint-method method-POST">POST</span>
      <span class="endpoint-path">/api/login</span>
      <span class="endpoint-desc">Iniciar sesión y obtener tokens</span>
      <span class="endpoint-chevron">▶</span>
    </div>

    <div class="endpoint-body">
      <div class="endpoint-inner">
        <div class="tabs">
          <button class="tab-btn active" onclick="switchTab(this, 'login-params')">Parámetros</button>
          <button class="tab-btn" onclick="switchTab(this, 'login-response')">Respuesta</button>
          <button class="tab-btn" onclick="switchTab(this, 'login-try')">Probar →</button>
        </div>

        <!-- Params tab -->
        <div class="tab-pane active" id="login-params">
          <table class="params-table">
            <thead><tr><th>Campo</th><th>Tipo</th><th>Estado</th><th>Descripción</th></tr></thead>
            <tbody>
              <tr>
                <td class="param-name">email</td>
                <td class="param-type">string</td>
                <td><span class="param-required">requerido</span></td>
                <td class="param-desc">Correo del usuario registrado</td>
              </tr>
              <tr>
                <td class="param-name">password</td>
                <td class="param-type">string</td>
                <td><span class="param-required">requerido</span></td>
                <td class="param-desc">Contraseña del usuario</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Response tab -->
        <div class="tab-pane" id="login-response">
          <p style="font-size:11px;color:var(--muted);margin-bottom:8px">200 OK</p>
          <div class="code-block"><span class="key">"authenticated"</span>: <span class="bool">true</span>,
<span class="key">"access_token"</span>:  <span class="str">"eyJ0eXAiOiJKV1Q..."</span>,
<span class="key">"refresh_token"</span>: <span class="str">"eyJ0eXAiOiJKV1Q..."</span>,
<span class="key">"user"</span>: {
  <span class="key">"id"</span>:         <span class="num">1</span>,
  <span class="key">"name"</span>:       <span class="str">"Juan Pérez"</span>,
  <span class="key">"email"</span>:      <span class="str">"juan@ejemplo.com"</span>,
  <span class="key">"is_company"</span>: <span class="bool">false</span>
}</div>
          <p style="font-size:11px;color:var(--muted);margin:12px 0 8px">401 Unauthorized</p>
          <div class="code-block"><span class="key">"authenticated"</span>: <span class="bool">false</span></div>
        </div>

        <!-- Try tab -->
        <div class="tab-pane" id="login-try">
          <label class="try-label">Body (JSON)</label>
          <textarea class="try-textarea" id="login-body">{
  "email": "usuario@ejemplo.com",
  "password": "secreto123"
}</textarea>
          <button class="btn-send" onclick="sendRequest('POST','/api/login','login', false)">Enviar solicitud</button>
          <div class="response-box" id="login-resp">
            <div class="response-meta">
              <span class="status-code" id="login-status"></span>
              <span class="response-time" id="login-time"></span>
            </div>
            <div class="response-body" id="login-body-resp"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ── POST /api/signup ── -->
  <div class="endpoint-card" id="signup">
    <div class="endpoint-header" onclick="toggleEndpoint(this)">
      <span class="endpoint-method method-POST">POST</span>
      <span class="endpoint-path">/api/signup</span>
      <span class="endpoint-desc">Registrar nuevo usuario</span>
      <span class="endpoint-chevron">▶</span>
    </div>

    <div class="endpoint-body">
      <div class="endpoint-inner">
        <div class="tabs">
          <button class="tab-btn active" onclick="switchTab(this, 'signup-params')">Parámetros</button>
          <button class="tab-btn" onclick="switchTab(this, 'signup-response')">Respuesta</button>
          <button class="tab-btn" onclick="switchTab(this, 'signup-try')">Probar →</button>
        </div>

        <div class="tab-pane active" id="signup-params">
          <table class="params-table">
            <thead><tr><th>Campo</th><th>Tipo</th><th>Estado</th><th>Descripción</th></tr></thead>
            <tbody>
              <tr>
                <td class="param-name">name</td>
                <td class="param-type">string</td>
                <td><span class="param-required">requerido</span></td>
                <td class="param-desc">Nombre completo del usuario</td>
              </tr>
              <tr>
                <td class="param-name">email</td>
                <td class="param-type">string</td>
                <td><span class="param-required">requerido</span></td>
                <td class="param-desc">Correo único del usuario</td>
              </tr>
              <tr>
                <td class="param-name">password</td>
                <td class="param-type">string</td>
                <td><span class="param-required">requerido</span></td>
                <td class="param-desc">Contraseña (min. 8 caracteres)</td>
              </tr>
              <tr>
                <td class="param-name">is_company</td>
                <td class="param-type">boolean</td>
                <td><span class="param-optional">opcional</span></td>
                <td class="param-desc">¿Es una cuenta empresarial? Default: false</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="tab-pane" id="signup-response">
          <p style="font-size:11px;color:var(--muted);margin-bottom:8px">201 Created</p>
          <div class="code-block"><span class="key">"message"</span>: <span class="str">"Usuario registrado exitosamente"</span>,
<span class="key">"user"</span>: {
  <span class="key">"id"</span>:         <span class="num">42</span>,
  <span class="key">"name"</span>:       <span class="str">"Ana García"</span>,
  <span class="key">"email"</span>:      <span class="str">"ana@empresa.com"</span>,
  <span class="key">"is_company"</span>: <span class="bool">true</span>
}</div>
          <p style="font-size:11px;color:var(--muted);margin:12px 0 8px">422 Unprocessable</p>
          <div class="code-block"><span class="key">"error"</span>: <span class="str">"El correo ya está en uso"</span></div>
        </div>

        <div class="tab-pane" id="signup-try">
          <label class="try-label">Body (JSON)</label>
          <textarea class="try-textarea" id="signup-body">{
  "name": "Ana García",
  "email": "ana@empresa.com",
  "password": "secreto123",
  "is_company": false
}</textarea>
          <button class="btn-send" onclick="sendRequest('POST','/api/signup','signup', false)">Enviar solicitud</button>
          <div class="response-box" id="signup-resp">
            <div class="response-meta">
              <span class="status-code" id="signup-status"></span>
              <span class="response-time" id="signup-time"></span>
            </div>
            <div class="response-body" id="signup-body-resp"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ════════════════════════════════════════
       SECCIÓN: USUARIO
  ════════════════════════════════════════ -->
  <div class="section-title">Usuario</div>

  <!-- ── GET /api/profile ── -->
  <div class="endpoint-card" id="profile">
    <div class="endpoint-header" onclick="toggleEndpoint(this)">
      <span class="endpoint-method method-GET">GET</span>
      <span class="endpoint-path">/api/profile</span>
      <span class="endpoint-desc">Perfil del usuario autenticado</span>
      <span class="auth-required-badge">🔒 Bearer</span>
      <span class="endpoint-chevron">▶</span>
    </div>

    <div class="endpoint-body">
      <div class="endpoint-inner">
        <div class="tabs">
          <button class="tab-btn active" onclick="switchTab(this, 'profile-params')">Parámetros</button>
          <button class="tab-btn" onclick="switchTab(this, 'profile-response')">Respuesta</button>
          <button class="tab-btn" onclick="switchTab(this, 'profile-try')">Probar →</button>
        </div>

        <div class="tab-pane active" id="profile-params">
          <div class="info-row">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--accent)"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            Este endpoint requiere autenticación. Agrega el header <code style="color:var(--accent)">Authorization: Bearer &lt;token&gt;</code>
          </div>
          <table class="params-table">
            <thead><tr><th>Header</th><th>Tipo</th><th>Estado</th><th>Descripción</th></tr></thead>
            <tbody>
              <tr>
                <td class="param-name">Authorization</td>
                <td class="param-type">string</td>
                <td><span class="param-required">requerido</span></td>
                <td class="param-desc">Bearer {access_token}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="tab-pane" id="profile-response">
          <p style="font-size:11px;color:var(--muted);margin-bottom:8px">200 OK</p>
          <div class="code-block">{
  <span class="key">"id"</span>:         <span class="num">1</span>,
  <span class="key">"name"</span>:       <span class="str">"Juan Pérez"</span>,
  <span class="key">"email"</span>:      <span class="str">"juan@ejemplo.com"</span>,
  <span class="key">"is_company"</span>: <span class="bool">false</span>
}</div>
          <p style="font-size:11px;color:var(--muted);margin:12px 0 8px">401 Unauthorized</p>
          <div class="code-block"><span class="key">"error"</span>: <span class="str">"Token inválido o expirado"</span></div>
        </div>

        <div class="tab-pane" id="profile-try">
          <div class="info-row" id="profile-auth-warn" style="display:none">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--amber)"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            Debes iniciar sesión para usar este endpoint. El token se adjunta automáticamente.
          </div>
          <button class="btn-send" onclick="sendRequest('GET','/api/profile','profile', true)">Enviar solicitud</button>
          <div class="response-box" id="profile-resp">
            <div class="response-meta">
              <span class="status-code" id="profile-status"></span>
              <span class="response-time" id="profile-time"></span>
            </div>
            <div class="response-body" id="profile-body-resp"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Para agregar un nuevo endpoint, copia el bloque .endpoint-card anterior
       y cambia: id, método, path, tabs (params/response/try), el textarea del body
       y el onclick de sendRequest. -->

</main>

<script src="/docs/app.js"></script>

</body>
</html>