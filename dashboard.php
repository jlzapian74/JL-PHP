<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$u = $_SESSION['user'];
$rol = $u['rol'];

// PROCESAR MATRÍCULA DE ESTUDIANTE (Solo Admin)
if (isset($_POST['matricular']) && $rol === 'administrador') {
    if (!file_exists('uploads')) {
        mkdir('uploads', 0777, true);
    }
    
    $fotoName = time() . '_' . basename($_FILES['foto']['name']);
    move_uploaded_file($_FILES['foto']['tmp_name'], 'uploads/' . $fotoName);

    $stmt = $conn->prepare("INSERT INTO estudiantes (cedula, nombres, apellidos, correo, foto, grado, fecha_nacimiento, pais_nacimiento, telefono_contacto, domicilio, nombre_padre, cedula_padre, telefono_padre, nombre_madre, cedula_madre, telefono_madre, autorizados_retirar) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param("sssssssssssssssss", $_POST['cedula'], $_POST['nombres'], $_POST['apellidos'], $_POST['correo'], $fotoName, $_POST['grado'], $_POST['fecha_nacimiento'], $_POST['pais_nacimiento'], $_POST['telefono_contacto'], $_POST['domicilio'], $_POST['nombre_padre'], $_POST['cedula_padre'], $_POST['telefono_padre'], $_POST['nombre_madre'], $_POST['cedula_madre'], $_POST['telefono_madre'], $_POST['autorizados_retirar']);
    $stmt->execute();
}

// PROCESAR REGISTRO DE PERSONAL (Solo Admin)
if (isset($_POST['registrar_personal']) && $rol === 'administrador') {
    $passHash = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO usuarios (cedula, nombre_completo, correo, password, rol, grado_asignado) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param("ssssss", $_POST['cedula'], $_POST['nombre_completo'], $_POST['correo'], $passHash, $_POST['rol_nuevo'], $_POST['grado_asignado']);
    $stmt->execute();
}

// PROCESAR SUBIDA DE CALIFICACIONES (Docente y Coordinación)
if (isset($_POST['subir_nota']) && in_array($rol, ['docente', 'coordinacion'])) {
    if (!file_exists('uploads/notas')) {
        mkdir('uploads/notas', 0777, true);
    }

    $fileExt = strtolower(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION));
    $allowedExts = ['pdf', 'doc', 'docx'];

    if (in_array($fileExt, $allowedExts)) {
        $fileName = time() . '_' . basename($_FILES['archivo']['name']);
        $targetPath = 'uploads/notas/' . $fileName;

        if (move_uploaded_file($_FILES['archivo']['tmp_name'], $targetPath)) {
            $stmt = $conn->prepare("INSERT INTO calificaciones (estudiante_id, docente_id, archivo_pdf) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $_POST['estudiante_id'], $u['id'], $fileName);
            $stmt->execute();
        }
    } else {
        echo "<script>alert('Formato no permitido. Solo se aceptan archivos PDF y Word (.doc, .docx)');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard CyberSchool</title>
    <link rel="stylesheet" href="styles.css">
    <script src="main.js" defer></script>
</head>
<body>
<div class="glass-panel">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <!-- Bloque de Logo y Nombres -->
        <div style="display: flex; align-items: center; gap: 15px;">
            <img src="img/LOGO UECM .png" alt="Logo Colegio" style="width: auto; height: 80px; object-fit: contain; filter: drop-shadow(0 0 8px var(--neon-blue));">
            <div>
                <center>
                <span style="display: block; color: var(--neon-pink); font-size: 0.9em; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; margin-bottom: 2px;">
                    UNIDAD EDUCATIVA
                </span>
                <h1 style="margin: 0; line-height: 1.1; font-size: 1.8em;">"CAPITÁN MORONI"</h1>
                <span style="display: block; color: var(--neon-blue); font-size: 1.5em; font-weight: 600; margin-top: 5px; text-transform: uppercase; letter-spacing: 1px;">
                    PANEL DE <?php echo strtoupper($rol); ?>
                </span>
                </center>
            </div>
        </div>

        <!-- Botón de Cerrar Sesión -->
        <a href="logout.php" class="animated-exit" style="color: var(--neon-pink); font-weight: bold; text-decoration: none; padding: 10px 15px; border: 1px solid var(--neon-pink); border-radius: 8px; transition: 0.3s;">
            CERRAR SESIÓN
        </a>
    </div>
    <hr style="border: 0; height: 1px; background: rgba(255, 255, 255, 0.1); margin: 15px 0;">
    <p style="margin: 0;">Usuario: <?php echo htmlspecialchars($u['nombre_completo']); ?> | Correo: <?php echo htmlspecialchars($u['correo']); ?></p>
</div>

    <!-- MÓDULO ADMINISTRADOR -->
    <?php if ($rol === 'administrador'): ?>
        <div class="glass-panel">
            <h2>Matrícula de Estudiantes</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="file" name="foto" accept="image/*" required>
                <input type="text" name="cedula" placeholder="Número de Cédula" required>
                <input type="text" name="nombres" placeholder="Nombres" required>
                <input type="text" name="apellidos" placeholder="Apellidos" required>
                <input type="email" name="correo" placeholder="Correo Electrónico" required>
                <select name="grado" required>
                    <option value="Inicial 2">Inicial 2</option>
                    <option value="Primero">Primero</option>
                    <option value="Segundo">Segundo</option>
                    <option value="Tercero">Tercero</option>
                    <option value="Cuarto">Cuarto</option>
                    <option value="Quinto">Quinto</option>
                    <option value="Sexto">Sexto</option>
                    <option value="Séptimo">Séptimo</option>
                    
                </select>
                <input type="date" name="fecha_nacimiento" required>
                <input type="text" name="pais_nacimiento" placeholder="País de Nacimiento" required>
                <input type="text" name="telefono_contacto" placeholder="Teléfono Contacto" required>
                <input type="text" name="domicilio" placeholder="Domicilio" required>
                
                <h3>Datos de los Padres</h3>
                <input type="text" name="nombre_padre" placeholder="Nombre del Padre">
                <input type="text" name="cedula_padre" placeholder="Cédula del Padre">
                <input type="text" name="telefono_padre" placeholder="Teléfono del Padre">
                <input type="text" name="nombre_madre" placeholder="Nombre de la Madre">
                <input type="text" name="cedula_madre" placeholder="Cédula de la Madre">
                <input type="text" name="telefono_madre" placeholder="Teléfono de la Madre">
                <input type="text" name="autorizados_retirar" placeholder="Autorizados a Retirar">
                <button type="submit" name="matricular">Matricular Estudiante</button>
            </form>
        </div>

        <div class="glass-panel">
            <h2>Registrar Dirección, Coordinación o Docente</h2>
            <form method="POST">
                <input type="text" name="cedula" placeholder="Cédula" required>
                <input type="text" name="nombre_completo" placeholder="Nombre Completo" required>
                <input type="email" name="correo" placeholder="Correo Electrónico" required>
                <input type="password" name="password" placeholder="Contraseña" required>
                <select name="rol_nuevo" required>
                    <option value="direccion">Dirección</option>
                    <option value="coordinacion">Coordinación</option>
                    <option value="docente">Docente</option>
                </select>
                <select name="grado_asignado">
                    <option value="">-- Asignar Grado (Si es docente) --</option>
                    <option value="Inicial 2">Inicial 2</option>
                    <option value="Primero">Primero</option>
                    <option value="Segundo">Segundo</option>
                    <option value="Tercero">Tercero</option>
                    <option value="Cuarto">Cuarto</option>
                    <option value="Quinto">Quinto</option>
                    <option value="Sexto">Sexto</option>
                    <option value="Séptimo">Séptimo</option>
                    <option value="Computación">Computación</option>
                    <option value="Inglés">Inglés</option>
                </select>
                <button type="submit" name="registrar_personal">Registrar Usuario</button>
            </form>
        </div>
    <?php endif; ?>

    <!-- CONSULTA DE ESTUDIANTES (Admin, Dirección, Coordinación, Docentes) -->
    <?php if (in_array($rol, ['administrador', 'direccion', 'coordinacion', 'docente'])): ?>
        <div class="glass-panel">
            <h2>Base de Datos de Estudiantes y Calificaciones</h2>
            <table>
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Nombre del Alumno</th>
                        <th>Cédula</th>
                        <th>Grado</th>
                        <th>Calificaciones Subidas</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM estudiantes";
                    if ($rol === 'docente' && !empty($u['grado_asignado'])) {
                        $gradoDoc = $conn->real_escape_string($u['grado_asignado']);
                        $sql .= " WHERE grado = '$gradoDoc'";
                    }
                    $res = $conn->query($sql);

                    while ($est = $res->fetch_assoc()):
                    ?>
                    <tr>
                        <td><img src="uploads/<?php echo htmlspecialchars($est['foto']); ?>" width="40" height="40" style="border-radius:50%; object-fit:cover;"></td>
                        <td><strong><?php echo htmlspecialchars($est['nombres'] . " " . $est['apellidos']); ?></strong></td>
                        <td><?php echo htmlspecialchars($est['cedula']); ?></td>
                        <td><?php echo htmlspecialchars($est['grado']); ?></td>
                        <td>
    <!-- Lista de archivos subidos por estudiante -->
    <?php
    $e_id = (int)$est['id'];
    $calif_query = "SELECT c.archivo_pdf, c.fecha_subida, u.nombre_completo, u.rol 
                    FROM calificaciones c 
                    JOIN usuarios u ON c.docente_id = u.id 
                    WHERE c.estudiante_id = $e_id 
                    ORDER BY c.fecha_subida DESC";
    $calif_res = $conn->query($calif_query);

    if ($calif_res && $calif_res->num_rows > 0):
        while ($nota = $calif_res->fetch_assoc()):
            $nombre_limpio = htmlspecialchars($nota['archivo_pdf']);
    ?>
        <div style="margin-bottom: 8px; font-size: 0.9em;">
            📄 <a href="uploads/notas/<?php echo $nombre_limpio; ?>" download="<?php echo $nombre_limpio; ?>" style="color: var(--neon-blue); text-decoration: underline; font-weight: bold;">
                <?php echo $nombre_limpio; ?>
            </a>
            <br><small style="color: #aaa;">Subido por: <?php echo htmlspecialchars($nota['nombre_completo']); ?> (<?php echo strtoupper($nota['rol']); ?>)</small>
        </div>
    <?php 
        endwhile;
    else:
        echo "<small style='color:#888;'>Sin calificaciones</small>";
    endif;
    ?>
</td>
                        <td>
                            <?php if (in_array($rol, ['docente', 'coordinacion'])): ?>
                                <form method="POST" enctype="multipart/form-data" style="display:flex; flex-direction:column; gap:5px;">
                                    <input type="hidden" name="estudiante_id" value="<?php echo $est['id']; ?>">
                                    <input type="file" name="archivo" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required style="padding:4px; font-size:0.8em;">
                                    <button type="submit" name="subir_nota" style="padding:6px; font-size:0.8em;">Subir Archivo</button>
                                </form>
                            <?php else: ?>
                                <span style="color:var(--neon-pink); font-size:0.85em;">Modo Lectura / Descarga</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <!-- MÓDULO VER PERSONAL (Admin, Dirección, Coordinación) -->
    <?php if (in_array($rol, ['administrador', 'direccion', 'coordinacion'])): ?>
        <div class="glass-panel">
            <h2>Base de Datos de Personal</h2>
            <table>
                <thead>
                    <tr>
                        <th>Cédula</th>
                        <th>Nombre Completo</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Grado Asignado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $res = $conn->query("SELECT * FROM usuarios WHERE rol != 'administrador'");
                    while ($usr = $res->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($usr['cedula']); ?></td>
                        <td><?php echo htmlspecialchars($usr['nombre_completo']); ?></td>
                        <td><?php echo htmlspecialchars($usr['correo']); ?></td>
                        <td><?php echo strtoupper($usr['rol']); ?></td>
                        <td><?php echo htmlspecialchars($usr['grado_asignado'] ?: 'N/A'); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

</body>
</html>