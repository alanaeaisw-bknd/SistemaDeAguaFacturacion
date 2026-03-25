// ============================
// CONFIGURACIÓN GLOBAL
// ============================
const API_URL = '/sistemadeagua';
let usuario = null;

// ============================
// UTILIDADES
// ============================
function showAlert(message, type = 'info') {
    const alertHTML = `
        <div class="alert alert-${type}">
            <span>${message}</span>
        </div>
    `;
    
    const container = document.querySelector('.container') || document.body;
    container.insertAdjacentHTML('afterbegin', alertHTML);
    
    setTimeout(() => {
        document.querySelector('.alert').remove();
    }, 5000);
}

function showLoading(show = true) {
    let loader = document.getElementById('loading');
    
    if (show) {
        if (!loader) {
            loader = document.createElement('div');
            loader.id = 'loading';
            loader.className = 'loading';
            loader.innerHTML = '<div class="spinner"></div><p>Cargando...</p>';
            document.body.appendChild(loader);
        }
        loader.style.display = 'block';
    } else {
        if (loader) loader.style.display = 'none';
    }
}

async function fetchAPI(endpoint, options = {}) {
    try {
        showLoading(true);
        
        const response = await fetch(`${API_URL}${endpoint}`, {
            ...options,
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            }
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.error || 'Error en la petición');
        }
        
        return data;
        
    } catch (error) {
        showAlert(error.message, 'danger');
        throw error;
    } finally {
        showLoading(false);
    }
}

function formatMoney(amount) {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(amount);
}

function formatDate(dateString) {
    return new Intl.DateTimeFormat('es-MX', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }).format(new Date(dateString));
}

// ============================
// AUTENTICACIÓN
// ============================
async function checkAuth() {
    try {
        const data = await fetchAPI('/auth/check.php');
        
        if (data.autenticado) {
            usuario = data.usuario;
            return true;
        }
        
        return false;
        
    } catch (error) {
        return false;
    }
}

async function login(correo, password) {
    try {
        const data = await fetchAPI('/auth/login.php', {
            method: 'POST',
            body: JSON.stringify({ correo, password })
        });
        
        if (data.success) {
            usuario = data.usuario;
            showAlert('Sesión iniciada correctamente', 'success');
            setTimeout(() => {
                window.location.href = 'dashboard.php';
            }, 1000);
        }
        
    } catch (error) {
        console.error('Error en login:', error);
    }
}

async function register(formData) {
    try {
        const data = await fetchAPI('/auth/register.php', {
            method: 'POST',
            body: JSON.stringify(formData)
        });
        
        if (data.success) {
            usuario = data.usuario;
            showAlert('Registro exitoso. Redirigiendo...', 'success');
            setTimeout(() => {
                window.location.href = 'dashboard.php';
            }, 1000);
        }
        
    } catch (error) {
        console.error('Error en registro:', error);
    }
}

async function logout() {
    try {
        await fetchAPI('/auth/logout.php');
        showAlert('Sesión cerrada', 'info');
        setTimeout(() => {
            window.location.href = 'login.html';
        }, 1000);
        
    } catch (error) {
        console.error('Error en logout:', error);
    }
}

// ============================
// PRODUCTOS
// ============================
async function loadProductos() {
    try {
        const data = await fetchAPI('/api/productos.php');
        
        const grid = document.getElementById('productos-grid');
        if (!grid) return;
        
        grid.innerHTML = '';
        
        const iconos = {
            1: '🍶',  // Botella
            2: '🥤',  // Galón
            3: '🫙'   // Garrafón
        };
        
        data.productos.forEach(producto => {
            const stockClass = producto.stock > 100 ? 'stock-ok' : 
                              producto.stock > 50 ? 'stock-bajo' : 'stock-critico';
            
            const card = `
                <div class="producto-card">
                    <div class="producto-icon">${iconos[producto.id_producto] || '💧'}</div>
                    <h3 class="producto-nombre">${producto.nombre}</h3>
                    <p class="producto-descripcion">${producto.descripcion || ''}</p>
                    <div class="producto-precio">${producto.precio}</div>
                    <div class="producto-stock ${stockClass}">
                        Stock: ${producto.stock} unidades
                    </div>
                    <div class="cantidad-control">
                        <button class="cantidad-btn" onclick="cambiarCantidad(${producto.id_producto}, -1)">−</button>
                        <span id="cantidad-${producto.id_producto}" class="cantidad-display">1</span>
                        <button class="cantidad-btn" onclick="cambiarCantidad(${producto.id_producto}, 1)">+</button>
                    </div>
                    <button class="btn btn-primary btn-block" 
                            onclick="agregarAlCarrito(${producto.id_producto}, ${producto.stock})"
                            ${producto.stock === 0 ? 'disabled' : ''}>
                        ${producto.stock === 0 ? 'Sin Stock' : 'Agregar al Carrito'}
                    </button>
                </div>
            `;
            
            grid.innerHTML += card;
        });
        
    } catch (error) {
        console.error('Error al cargar productos:', error);
    }
}

function cambiarCantidad(idProducto, delta) {
    const display = document.getElementById(`cantidad-${idProducto}`);
    let cantidad = parseInt(display.textContent) + delta;
    
    if (cantidad < 1) cantidad = 1;
    if (cantidad > 999) cantidad = 999;
    
    display.textContent = cantidad;
}

async function agregarAlCarrito(idProducto, stockDisponible) {
    const cantidadElement = document.getElementById(`cantidad-${idProducto}`);
    const cantidad = parseInt(cantidadElement.textContent);
    
    if (cantidad > stockDisponible) {
        showAlert(`Solo hay ${stockDisponible} unidades disponibles`, 'warning');
        return;
    }
    
    try {
        const data = await fetchAPI('/api/carrito_agregar.php', {
            method: 'POST',
            body: JSON.stringify({
                id_producto: idProducto,
                cantidad: cantidad
            })
        });
        
        if (data.success) {
            showAlert(`${cantidad} producto(s) agregado(s) al carrito`, 'success');
            cantidadElement.textContent = '1';
        }
        
    } catch (error) {
        console.error('Error al agregar al carrito:', error);
    }
}

// ============================
// CARRITO
// ============================
async function loadCarrito() {
    try {
        const data = await fetchAPI('/api/carrito_ver.php');
        
        const container = document.getElementById('carrito-container');
        if (!container) return;
        
        if (!data.carrito || data.items.length === 0) {
            container.innerHTML = `
                <div class="carrito-vacio">
                    <div class="carrito-vacio-icon">🛒</div>
                    <h3>Tu carrito está vacío</h3>
                    <p>Agrega productos para comenzar tu compra</p>
                    <a href="productos.php" class="btn btn-primary mt-20">Ver Productos</a>
                </div>
            `;
            return;
        }
        
        let tablaHTML = `
            <table class="carrito-tabla">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Precio Unitario</th>
                        <th>Cantidad</th>
                        <th>Subtotal</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
        `;
        
        data.items.forEach(item => {
            tablaHTML += `
                <tr>
                    <td>
                        <strong>${item.nombre}</strong><br>
                        <small>${item.descripcion || ''}</small>
                    </td>
                    <td>${formatMoney(item.precio_unitario)}</td>
                    <td>
                        <div class="cantidad-control">
                            <button class="cantidad-btn btn-sm" 
                                    onclick="actualizarCantidadCarrito(${item.id_detalle_carrito}, ${item.cantidad - 1}, ${item.stock})">
                                −
                            </button>
                            <span class="cantidad-display">${item.cantidad}</span>
                            <button class="cantidad-btn btn-sm" 
                                    onclick="actualizarCantidadCarrito(${item.id_detalle_carrito}, ${item.cantidad + 1}, ${item.stock})">
                                +
                            </button>
                        </div>
                        <small class="text-center">Stock: ${item.stock}</small>
                    </td>
                    <td><strong>${formatMoney(item.subtotal)}</strong></td>
                    <td>
                        <button class="btn btn-danger btn-sm" 
                                onclick="eliminarDelCarrito(${item.id_detalle_carrito})">
                            🗑️ Eliminar
                        </button>
                    </td>
                </tr>
            `;
        });
        
        tablaHTML += `
                </tbody>
            </table>
            
            <div class="carrito-total">
                <div class="carrito-total-label">TOTAL A PAGAR:</div>
                <div class="carrito-total-monto">${formatMoney(data.total)}</div>
            </div>
            
            <div class="btn-group">
                <a href="productos.php" class="btn btn-secondary">Seguir Comprando</a>
                <button class="btn btn-success btn-block" onclick="irADatosFiscales()">
                    Proceder al Pago
                </button>
            </div>
        `;
        
        container.innerHTML = tablaHTML;
        
    } catch (error) {
        console.error('Error al cargar carrito:', error);
    }
}

async function actualizarCantidadCarrito(idDetalle, nuevaCantidad, stockDisponible) {
    if (nuevaCantidad < 1) {
        showAlert('La cantidad mínima es 1', 'warning');
        return;
    }
    
    if (nuevaCantidad > stockDisponible) {
        showAlert(`Solo hay ${stockDisponible} unidades disponibles`, 'warning');
        return;
    }
    
    try {
        const data = await fetchAPI('/api/carrito_actualizar.php', {
            method: 'POST',
            body: JSON.stringify({
                id_detalle_carrito: idDetalle,
                cantidad: nuevaCantidad
            })
        });
        
        if (data.success) {
            showAlert('Cantidad actualizada', 'success');
            loadCarrito();
        }
        
    } catch (error) {
        console.error('Error al actualizar cantidad:', error);
    }
}

async function eliminarDelCarrito(idDetalle) {
    if (!confirm('¿Eliminar este producto del carrito?')) return;
    
    try {
        const data = await fetchAPI('/api/carrito_eliminar.php', {
            method: 'POST',
            body: JSON.stringify({
                id_detalle_carrito: idDetalle
            })
        });
        
        if (data.success) {
            showAlert('Producto eliminado', 'info');
            loadCarrito();
        }
        
    } catch (error) {
        console.error('Error al eliminar:', error);
    }
}

function irADatosFiscales() {
    window.location.href = 'datos_fiscales.php';
}

// ============================
// DATOS FISCALES Y VENTA
// ============================
async function guardarDatosFiscales(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const datos = Object.fromEntries(formData);
    
    try {
        const response = await fetchAPI('/api/datos_fiscales.php', {
            method: 'POST',
            body: JSON.stringify(datos)
        });
        
        if (response.success) {
            showAlert('Datos fiscales guardados', 'success');
            
            // Proceder con la venta
            await realizarVenta(response.id_datos_fiscales);
        }
        
    } catch (error) {
        console.error('Error al guardar datos fiscales:', error);
    }
}

async function realizarVenta(idDatosFiscales) {
    try {
        const data = await fetchAPI('/api/venta.php', {
            method: 'POST',
            body: JSON.stringify({
                id_datos_fiscales: idDatosFiscales
            })
        });
        
        if (data.success) {
            showAlert('¡Venta realizada con éxito!', 'success');
            
            setTimeout(() => {
                window.location.href = `ticket.php?id_venta=${data.id_venta}`;
            }, 1500);
        }
        
    } catch (error) {
        console.error('Error al realizar venta:', error);
    }
}

// ============================
// VENTAS
// ============================
async function loadMisVentas() {
    try {
        const data = await fetchAPI('/api/mis_ventas.php');
        
        const container = document.getElementById('ventas-container');
        if (!container) return;
        
        if (data.ventas.length === 0) {
            container.innerHTML = `
                <div class="text-center mt-20">
                    <p>No tienes ventas registradas aún</p>
                    <a href="productos.php" class="btn btn-primary mt-20">Realizar Primera Compra</a>
                </div>
            `;
            return;
        }
        
        let tablaHTML = `
            <table class="ventas-tabla">
                <thead>
                    <tr>
                        <th>ID Venta</th>
                        <th>Fecha</th>
                        <th>Razón Social</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
        `;
        
        data.ventas.forEach(venta => {
            const badgeClass = venta.estado === 'pagada' ? 'badge-success' : 
                              venta.estado === 'pendiente' ? 'badge-warning' : 'badge-danger';
            
            tablaHTML += `
                <tr>
                    <td>#${venta.id_venta}</td>
                    <td>${formatDate(venta.fecha_venta)}</td>
                    <td>${venta.razon_social}</td>
                    <td>${venta.total_items}</td>
                    <td><strong>${formatMoney(venta.total)}</strong></td>
                    <td><span class="badge ${badgeClass}">${venta.estado}</span></td>
                    <td>
                        <a href="ticket.php?id_venta=${venta.id_venta}" 
                           class="btn btn-primary btn-sm">
                            Ver Ticket
                        </a>
                    </td>
                </tr>
            `;
        });
        
        tablaHTML += `
                </tbody>
            </table>
        `;
        
        container.innerHTML = tablaHTML;
        
    } catch (error) {
        console.error('Error al cargar ventas:', error);
    }
}

// ============================
// TICKET
// ============================
async function loadTicket() {
    const urlParams = new URLSearchParams(window.location.search);
    const idVenta = urlParams.get('id_venta');
    
    if (!idVenta) {
        showAlert('ID de venta no válido', 'danger');
        return;
    }
    
    try {
        const data = await fetchAPI(`/api/ticket.php?id_venta=${idVenta}`);
        
        const container = document.getElementById('ticket-container');
        if (!container) return;
        
        const venta = data.venta;
        
        let ticketHTML = `
            <div class="card">
                <div class="text-center mb-20">
                    <h1 style="color: var(--primary); font-size: 36px;">💧 PURIFICADORA DE AGUA</h1>
                    <p>Ticket de Venta</p>
                    <hr>
                </div>
                
                <div class="mb-20">
                    <p><strong>Folio:</strong> #${venta.id_venta}</p>
                    <p><strong>Fecha:</strong> ${formatDate(venta.fecha_venta)}</p>
                    <p><strong>Estado:</strong> ${venta.estado.toUpperCase()}</p>
                </div>
                
                <hr>
                
                <h3 class="mb-20">Datos Fiscales</h3>
                <p><strong>RFC:</strong> ${venta.rfc}</p>
                <p><strong>Razón Social:</strong> ${venta.razon_social}</p>
                <p><strong>Correo:</strong> ${venta.correo_fiscal}</p>
                <p><strong>Dirección:</strong> ${venta.calle} ${venta.numero_ext}${venta.numero_int ? ' Int. ' + venta.numero_int : ''}</p>
                <p>${venta.colonia}, ${venta.municipio}, ${venta.estado} - CP ${venta.cp}</p>
                <p><strong>Régimen Fiscal:</strong> ${venta.regimen}</p>
                <p><strong>Uso CFDI:</strong> ${venta.uso_cfdi}</p>
                
                <hr>
                
                <h3 class="mb-20">Detalle de Productos</h3>
                <table class="carrito-tabla">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio Unit.</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        data.items.forEach(item => {
            ticketHTML += `
                <tr>
                    <td>${item.nombre}</td>
                    <td>${item.cantidad}</td>
                    <td>${formatMoney(item.precio_unitario)}</td>
                    <td>${formatMoney(item.subtotal)}</td>
                </tr>
            `;
        });
        
        ticketHTML += `
                    </tbody>
                </table>
                
                <div class="carrito-total">
                    <div class="carrito-total-label">TOTAL PAGADO:</div>
                    <div class="carrito-total-monto">${formatMoney(venta.total)}</div>
                </div>
                
                <div class="btn-group mt-20">
                    <button class="btn btn-primary" onclick="window.print()">🖨️ Imprimir</button>
                    <a href="ventas.php" class="btn btn-secondary">Ver Mis Ventas</a>
                    <a href="productos.php" class="btn btn-success">Nueva Compra</a>
                </div>
            </div>
        `;
        
        container.innerHTML = ticketHTML;
        
    } catch (error) {
        console.error('Error al cargar ticket:', error);
    }
}

// ============================
// INICIALIZACIÓN
// ============================
document.addEventListener('DOMContentLoaded', async () => {
    // Verificar autenticación en páginas protegidas
    const paginasProtegidas = ['dashboard.php', 'productos.php', 'carrito.php', 'datos_fiscales.php', 'ventas.php', 'ticket.php'];
    const paginaActual = window.location.pathname.split('/').pop();
    
    if (paginasProtegidas.includes(paginaActual)) {
        const autenticado = await checkAuth();
        
        if (!autenticado) {
            window.location.href = 'login.html';
            return;
        }
    }
});