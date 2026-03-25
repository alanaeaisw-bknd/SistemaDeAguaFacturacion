<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Datos Fiscales - Sistema de Agua</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>
    <div class="container">
        <!-- NAVBAR -->
        <nav class="navbar">
            <div class="navbar-brand">
                Sistema de Agua
            </div>
            <ul class="navbar-menu">
                <li><a href="dashboard.php">Inicio</a></li>
                <li><a href="productos.php">Productos</a></li>
                <li><a href="carrito.php">Carrito</a></li>
                <li><a href="ventas.php">Mis Ventas</a></li>
            </ul>
            <div class="navbar-user">
                <span class="user-name">👤 <?php echo htmlspecialchars($_SESSION['usuario']['nombre']); ?></span>
                <button class="btn btn-danger btn-sm" onclick="logout()">Salir</button>
            </div>
        </nav>

        <!-- CONTENIDO -->
        <div class="card">
            <h1 class="card-title">📄 Datos Fiscales para Facturación</h1>
            
            <div class="alert alert-warning">
                <strong>⚠️ Importante:</strong> Todos los campos son obligatorios para generar tu factura.
            </div>

            <form id="datosFiscalesForm">
                <!-- DATOS FISCALES -->
                <h3 style="margin-top: 30px; margin-bottom: 20px; color: var(--primary);">Información Fiscal</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="rfc" class="form-label required">RFC</label>
                        <input 
                            type="text" 
                            id="rfc" 
                            name="rfc" 
                            class="form-control" 
                            placeholder="XAXX010101000"
                            maxlength="13"
                            required
                            style="text-transform: uppercase;">
                    </div>

                    <div class="form-group">
                        <label for="razon_social" class="form-label required">Razón Social</label>
                        <input 
                            type="text" 
                            id="razon_social" 
                            name="razon_social" 
                            class="form-control" 
                            placeholder="Mi Empresa S.A. de C.V."
                            required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="correo" class="form-label required">Correo Electrónico</label>
                        <input 
                            type="email" 
                            id="correo" 
                            name="correo" 
                            class="form-control" 
                            placeholder="facturacion@empresa.com"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input 
                            type="tel" 
                            id="telefono" 
                            name="telefono" 
                            class="form-control" 
                            placeholder="6181234567"
                            pattern="[0-9]{10}">
                    </div>
                </div>

                <!-- DIRECCIÓN -->
                <h3 style="margin-top: 30px; margin-bottom: 20px; color: var(--primary);">Dirección Fiscal</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="calle" class="form-label required">Calle</label>
                        <input 
                            type="text" 
                            id="calle" 
                            name="calle" 
                            class="form-control" 
                            placeholder="Av. Principal"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="numero_ext" class="form-label required">Número Exterior</label>
                        <input 
                            type="text" 
                            id="numero_ext" 
                            name="numero_ext" 
                            class="form-control" 
                            placeholder="123"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="numero_int" class="form-label">Número Interior</label>
                        <input 
                            type="text" 
                            id="numero_int" 
                            name="numero_int" 
                            class="form-control" 
                            placeholder="A">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="colonia" class="form-label required">Colonia</label>
                        <input 
                            type="text" 
                            id="colonia" 
                            name="colonia" 
                            class="form-control" 
                            placeholder="Centro"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="cp" class="form-label required">Código Postal</label>
                        <input 
                            type="text" 
                            id="cp" 
                            name="cp" 
                            class="form-control" 
                            placeholder="34000"
                            maxlength="5"
                            pattern="[0-9]{5}"
                            required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="municipio" class="form-label required">Municipio/Alcaldía</label>
                        <input 
                            type="text" 
                            id="municipio" 
                            name="municipio" 
                            class="form-control" 
                            placeholder="Durango"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="estado" class="form-label required">Estado</label>
                        <input 
                            type="text" 
                            id="estado" 
                            name="estado" 
                            class="form-control" 
                            placeholder="Durango"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="pais" class="form-label required">País</label>
                        <input 
                            type="text" 
                            id="pais" 
                            name="pais" 
                            class="form-control" 
                            value="México"
                            required>
                    </div>
                </div>

                <!-- DATOS ADICIONALES -->
                <h3 style="margin-top: 30px; margin-bottom: 20px; color: var(--primary);">Información Adicional</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="regimen" class="form-label required">Régimen Fiscal</label>
                        <select id="regimen" name="regimen" class="form-control" required>
                            <option value="">Seleccione...</option>
                            <option value="601 - General de Ley Personas Morales">601 - General de Ley Personas Morales</option>
                            <option value="603 - Personas Morales con Fines no Lucrativos">603 - Personas Morales con Fines no Lucrativos</option>
                            <option value="605 - Sueldos y Salarios e Ingresos Asimilados a Salarios">605 - Sueldos y Salarios</option>
                            <option value="606 - Arrendamiento">606 - Arrendamiento</option>
                            <option value="612 - Personas Físicas con Actividades Empresariales">612 - Personas Físicas con Actividades Empresariales</option>
                            <option value="621 - Incorporación Fiscal">621 - Incorporación Fiscal</option>
                            <option value="625 - Régimen de las Actividades Empresariales con ingresos a través de Plataformas Tecnológicas">625 - Plataformas Tecnológicas</option>
                            <option value="626 - Régimen Simplificado de Confianza">626 - Régimen Simplificado de Confianza</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="uso_cfdi" class="form-label required">Uso de CFDI</label>
                        <select id="uso_cfdi" name="uso_cfdi" class="form-control" required>
                            <option value="">Seleccione...</option>
                            <option value="G01 - Adquisición de mercancías">G01 - Adquisición de mercancías</option>
                            <option value="G02 - Devoluciones, descuentos o bonificaciones">G02 - Devoluciones</option>
                            <option value="G03 - Gastos en general">G03 - Gastos en general</option>
                            <option value="I01 - Construcciones">I01 - Construcciones</option>
                            <option value="I02 - Mobilario y equipo de oficina">I02 - Mobiliario</option>
                            <option value="I03 - Equipo de transporte">I03 - Equipo de transporte</option>
                            <option value="I04 - Equipo de computo">I04 - Equipo de cómputo</option>
                            <option value="I05 - Dados, troqueles, moldes">I05 - Dados, troqueles</option>
                            <option value="I06 - Comunicaciones telefónicas">I06 - Comunicaciones</option>
                            <option value="I07 - Comunicaciones satelitales">I07 - Comunicaciones satelitales</option>
                            <option value="I08 - Otra maquinaria y equipo">I08 - Otra maquinaria</option>
                            <option value="D01 - Honorarios médicos">D01 - Honorarios médicos</option>
                            <option value="D02 - Gastos médicos por incapacidad">D02 - Gastos médicos</option>
                            <option value="D03 - Gastos funerales">D03 - Gastos funerales</option>
                            <option value="D04 - Donativos">D04 - Donativos</option>
                            <option value="D05 - Intereses reales">D05 - Intereses</option>
                            <option value="D06 - Aportaciones voluntarias al SAR">D06 - Aportaciones SAR</option>
                            <option value="D07 - Primas por seguros de gastos médicos">D07 - Seguros médicos</option>
                            <option value="D08 - Gastos de transportación escolar">D08 - Transporte escolar</option>
                            <option value="D09 - Depósitos en cuentas para el ahorro">D09 - Cuentas de ahorro</option>
                            <option value="D10 - Pagos por servicios educativos">D10 - Servicios educativos</option>
                            <option value="S01 - Sin efectos fiscales">S01 - Sin efectos fiscales</option>
                            <option value="CP01 - Pagos">CP01 - Pagos</option>
                            <option value="CN01 - Nómina">CN01 - Nómina</option>
                        </select>
                    </div>
                </div>

                <!-- BOTONES -->
                <div class="btn-group">
                    <a href="carrito.php" class="btn btn-secondary">← Volver al Carrito</a>
                    <button type="submit" class="btn btn-success btn-block">
                        Confirmar y Realizar Venta 💳
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/app.js"></script>
    <script>
        // Convertir RFC a mayúsculas automáticamente
        document.getElementById('rfc').addEventListener('input', function(e) {
            this.value = this.value.toUpperCase();
        });

        // Validar solo números en CP
        document.getElementById('cp').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        // Validar solo números en teléfono
        document.getElementById('telefono').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        // Enviar formulario
        document.getElementById('datosFiscalesForm').addEventListener('submit', guardarDatosFiscales);
    </script>
</body>
</html>