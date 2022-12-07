<?php

    namespace Controllers;
    
    use Classes\Email;
    use Model\Usuario;
    use MVC\Router;

    class LoginController {
        public static function login(Router $router) {

            $alertas = [];

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                $usuario = new Usuario($_POST);
                
                $alertas = $usuario->validarLogin();

                if(empty($alertas)) {
                    // Verificar que el usuario exista
                    $usuario = Usuario::where('email', $usuario->email);
                    if (!$usuario || !$usuario->confirmado) {
                        Usuario::setAlerta('error', 'El usuario no existe o no esta confirmado');
                    } else {
                        // El usuario existe
                        if (password_verify($_POST['password'], $usuario->password)) {
                            // Iniciar la sesión
                            session_start();
                            $_SESSION['id'] = $usuario->id;
                            $_SESSION['nombre'] = $usuario->nombre;
                            $_SESSION['email'] = $usuario->email;
                            $_SESSION['login'] = true;
                            // Redireccionar
                            header('Location: /dashboard');
                        } else {
                            Usuario::setAlerta('error', 'Password incorrecto');
                        }
                    }
                }
            }

            $alertas = Usuario::getAlertas();

            // Render a la vista
            $router->render('auth/login', [
                'titulo' => 'Iniciar sesión',
                'alertas' => $alertas
            ]);
        }

        public static function logout() {
            session_start();
            $_SESSION = [];
            header('Location: /');
        }

        public static function crear(Router $router) {

            // Arreglo vacio de alertas para empezar
            $alertas = [];

            // Instanciar usuario (objeto vacio)
            $usuario = new Usuario;

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                $usuario->sincronizar($_POST);

                $alertas = $usuario->validarNuevaCuenta();

                if (empty($alertas)) {
                    $existeUsuario = Usuario::where('email', $usuario->email);
                    if ($existeUsuario) {
                        Usuario::setAlerta('error', 'El usuario ya esta registrado');
                        $alertas = Usuario::getAlertas();
                    } else {
                        // Haserar password
                        $usuario->hashPassword();
                        // Eliminar password2
                        unset($usuario->password2);
                        // Generar el Token
                        $usuario->crearToken();
                        // Crear un nuevo usuario
                        $resultado = $usuario->guardar();
                        // Enviar email
                        $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                        $email->enviarConfirmacion();

                        if ($resultado) {
                            header('Location: /mensaje');
                        }
                    }
                }
            }

            // Render a la vista
            $router->render('auth/crear', [
                'titulo' => 'Crea tu cuenta en UpTask',
                'usuario' => $usuario,
                'alertas' => $alertas
            ]);
        }

        public static function olvide(Router $router) {

            $alertas = [];

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $usuario = new Usuario($_POST);
                $alertas = $usuario->validarEmail();
                if (empty($alertas)) {
                    // Buscar el usuario
                    $usuario = Usuario::where('email', $usuario->email);
                    if ($usuario && $usuario->confirmado) {
                        // Se encontró el usuario
                        // Generar un nuevo token
                        $usuario->crearToken();
                        unset($usuario->password2);
                        // Actualizar el usuario
                        $usuario->guardar();
                        // Enviar el email
                        $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                        $email->enviarInstrucciones();
                        // Imprimir la alerta
                        Usuario::setAlerta('exito', 'Hemos enviado las instrucciones a tu email');
                    } else {
                        Usuario::setAlerta('error', 'El usuario no existe o no esta confirmado');
                    }
                }
            }

            $alertas = Usuario::getAlertas();

            // Render a la vista
            $router->render('auth/olvide', [
                'titulo' => 'Olvide mi password',
                'alertas' => $alertas
            ]);
        }

        public static function restablecer(Router $router) {

            $token = s($_GET['token']);

            $mostrar = true;

            if (!$token) header('Location: /');

            // Identificar el usuario con el respectivo token
            $usuario = Usuario::where('token', $token);
            if (empty($usuario)) {
                Usuario::setAlerta('error', 'Token no válido');
                $mostrar = false;
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Añadir el nuevo password
                $usuario->sincronizar($_POST);
                // Validar el password
                $alertas = $usuario->validarPassword();
                if (empty($alertas)) {
                    // Hasear el nuevo password
                    $usuario->hashPassword();
                    unset($usuario->password2);
                    // Eliminar el token
                    $usuario->token = null;
                    // Guardar el usuario en la BD
                    $resultado = $usuario->guardar();
                    // Redireccionar
                    if ($resultado) {
                        header('Location: /');
                    }
                }
            }

            $alertas = Usuario::getAlertas();

            // Render a la vista
            $router->render('auth/restablecer', [
                'titulo' => 'Restablecer mi password',
                'alertas' => $alertas,
                'mostrar' => $mostrar
            ]);
        }

        public static function mensaje(Router $router) {
            // Render a la vista
            $router->render('auth/mensaje', [
                'titulo' => 'Cuenta creada exitosamente'
            ]);
        }

        public static function confirmar(Router $router) {

            $token = s($_GET['token']);

            if (!$token) header('Location: /');
            
            // Encontrar al usuario con este token
            $usuario = Usuario::where('token', $token);

            if (empty($usuario)) {
                // No se encontró un usuario con ese Token
                Usuario::setAlerta('error', 'Token no válido');
            } else {
                $usuario->confirmado = 1;
                $usuario->token = null;
                unset($usuario->password2);
                // Guardar usuario en la BD
                $usuario->guardar();
                Usuario::setAlerta('exito', 'Cuenta comprobada correctamente');
            }

            $alertas = Usuario::getAlertas();

            // Render a la vista
            $router->render('auth/confirmar', [
                'titulo' => 'Cuenta confirmada',
                'alertas' => $alertas
            ]);
        }
    }
