<?php
    namespace Controllers;

    use Classes\Email;
    use Model\Usuario;
    use MVC\Router;

    class LoginController {
        public static function login(Router $router) {

            // Alertas
            $alertas = [];

            // Metodo POST
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Instanciar modelo de usuario y le pasamos lo que el usuario escriba (solo  email y password)
                $auth = new Usuario($_POST);

                // Se requieren ambos campos de email y password
                $alertas = $auth->validarLogin();

                if (empty($alertas)) {
                    // Comprobar que el usuario exista
                    $usuario = Usuario::where('email', $auth->email);

                    if ($usuario) {
                        // Verificar el password
                        if ($usuario->comprobarPasswordVerificado($auth->password)) {
                        // Autenticar el usuario
                        // Inciar sesión
                        session_start();
                        $_SESSION['id'] = $usuario->id;
                        $_SESSION['nombre'] = $usuario->nombre . " " . $usuario->apellido;
                        $_SESSION['email'] = $usuario->email;
                        $_SESSION['login'] = true;

                        // Redireccionamiento (cliente o admin)
                        if ($usuario->admin) {
                            $_SESSION['admin'] = $usuario->admin ?? null;
                            header ('Location: /admin');
                        } else {
                            header('Location: /cita');
                        }

                        }
                    } else {
                        Usuario::setAlerta('error', 'Usuario no encontrado');
                    }
                }
            }

            $alertas = Usuario::getAlertas();

            $router->render('auth/login', [
                'alertas' => $alertas
            ]);
        }
        public static function logout() {
            session_start();
            $_SESSION = [];
            header('Location: /');
        }
        public static function olvide(Router $router) {
            $alertas = [];

            if ($_SERVER ['REQUEST_METHOD'] === 'POST') {
                $auth = new Usuario($_POST);
                $alertas = $auth->validarEmail();

                // Existencia de usuario
                if (empty($alertas)) {
                    $usuario = Usuario::where('email', $auth->email);
                    // Si existe y esta confirmado
                    if ($usuario && $usuario->confirmado === "1") {
                        //Generar un token temporal
                        $usuario->crearToken();
                        // Actualizar en la BD
                        $usuario->guardar();
                        // Enviar el e-mail
                        $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                        $email->enviarInstrucciones();
                        // Revisar email (alerta de exito)
                        Usuario::setAlerta('exito', 'Revisa tu e-mail');
                    } else {
                        Usuario::setAlerta('error','El usuario no existe o no esta confirmado');
                    }
                }
            }

            $alertas = Usuario::getAlertas();

            $router->render('auth/olvide-password', [
                'alertas' => $alertas
            ]);
        }
        public static function recuperar(Router $router) {
            $alertas = [];
            $error = false;

            $token = s($_GET['token']);
            // Buscar usuario por su token
            $usuario = Usuario::where('token', $token);

            if (empty($usuario)) {
                Usuario::setAlerta('error','Token no válido');
                $error = true;
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST'){
                // Leer el nuevo password y guardarlo
                $password = new Usuario($_POST);
                $alertas = $password->validarPassword();

                if (empty ($alertas)){
                    $usuario->password = null;
                    $usuario->password = $password->password;
                    $usuario->hashPassword();
                    $usuario->token = null;

                    $resultado = $usuario->guardar();
                    if ($resultado) {
                        header ('Location: /');
                    }
                }
            }

            $alertas = Usuario::getAlertas();

            $router->render('auth/recuperar-password', [
                'alertas' => $alertas,
                'error' => $error
            ]);
        }
        public static function crear(Router $router) {
            // Instanciar el usuario
            $usuario = new Usuario;

            // Alertas vacias
            $alertas = [];

            // Al enviar el formulario
            if ($_SERVER['REQUEST_METHOD'] === 'POST'){
                $usuario->sincronizar($_POST);
                $alertas = $usuario->validarNuevaCuenta();

                // Revisar que el arreglo de alertas este vacio
                if (empty($alertas)) {
                    // Verificar que el usuario NO este registrado
                    $resultado = $usuario->existeUsuario();
                    if ($resultado->num_row) {
                        $alertas = Usuario::getAlertas();
                    } else {
                        // Hashear el password
                        $usuario->hashPassword();

                        // Generar un token único
                        $usuario->crearToken();

                        // Enviar el email
                        $email = new Email($usuario->nombre, $usuario->email, $usuario->token);
                        $email->enviarConfirmacion();

                        // Crear el usuario
                        $resultado = $usuario->guardar();
                        if ($resultado) {
                            // Una vez creada la cuenta se redirecciona al usuario
                            header('Location: /mensaje');
                        }
                    }
                }
            }
            // Router -> Pasar a la vista
            $router->render('auth/crear-cuenta', [
                'usuario' => $usuario,
                'alertas' => $alertas
            ]);
        }
        public static function mensaje(Router $router) {
            $router->render('auth/mensaje');
        }

        public static function confirmar (Router $router) {
            $alertas = [];

            $token = s($_GET['token']);
            $usuario = Usuario::where('token', $token);
            
            if (empty($usuario)) {
                // Mostrar mensaje de error
                Usuario::setAlerta('error', 'Token no válido');
            } else {
                // Modificar a usuario confirmado
                $usuario->confirmado = "1";
                $usuario->token = null;
                $usuario->guardar();
                Usuario::setAlerta('exito', 'Cuenta comprobada correctamente');
            }
            
            // Obtener alertas
            $alertas = Usuario::getAlertas();

            // Renderizar la vista
            $router->render('auth/confirmar-cuenta', [
                'alertas' => $alertas
            ]);
        }
    }