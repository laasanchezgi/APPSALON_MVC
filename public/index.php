<?php 

    require_once __DIR__ . '/../includes/app.php';

    // Namespaces
    use Controllers\ServicioController;
    use Controllers\AdminController;
    use Controllers\APIController;
    use Controllers\CitaController;
    use Controllers\LoginController;
    use MVC\Router;

    // Router instanciado
    $router = new Router();

    // Iniciar sesion
    $router->get('/', [LoginController::class, 'login']);
    $router->post('/', [LoginController::class, 'login']);

    // Cerrar sesion
    $router->get('/logout', [LoginController::class, 'login']);

    // Recupear password
    $router->get('/olvide', [LoginController::class, 'olvide']);
    $router->post('/olvide', [LoginController::class, 'olvide']);
    $router->get('/recuperar', [LoginController::class, 'recuperar']);
    $router->post('/recuperar', [LoginController::class, 'recuperar']);

    // Crear cuenta
    $router->get('/crear-cuenta', [LoginController::class, 'crear']);
    $router->post('/crear-cuenta', [LoginController::class, 'crear']);

    // Confirmar cuenta
    $router->get('/confirmar-cuenta', [LoginController::class, 'confirmar']);
    $router->get('/mensaje', [LoginController::class, 'mensaje']);

    // Área privada
    $router->get('/cita', [CitaController::class, 'index']);
    $router->get('/admin', [AdminController::class, 'index']);

    // API de citas
    $router->get('/api/servicios', [APIController::class, 'index']);
    $router->post('/api/citas', [APIController::class, 'guardar']);
    $router->post('/api/eliminar', [APIController::class, 'eliminar']);

    // CRUD de servicios
    $router->get('/servicios', [ServicioController::class, 'index']);
    $router->get('/servicios/crear', [ServicioController::class, 'crear']);
    $router->get('/servicios/actualizar', [ServicioController::class, 'actualizar']);
    $router->post('/servicios/crear', [ServicioController::class, 'crear']);
    $router->post('/servicios/actualizar', [ServicioController::class, 'actualizar']);
    $router->post('/servicios/eliminar', [ServicioController::class, 'eliminar']);

    // Comprueba y valida las rutas, que existan y les asigna las funciones del Controlador
    $router->comprobarRutas();