<?php

    namespace Controllers;

    use Model\Proyecto;
    use Model\Usuario;
    use MVC\Router;

    class DashboardController {

        public static function index(Router $router) {

            session_start();

            // Proteger el dashboard, solo se puede entrar cuando se empieza la sesión
            isAuth();

            // Proteger los proyectos de los usuarios
            $id = $_SESSION['id'];
            $proyectos = Proyecto::belongsTo('usuarioid', $id);

            // Render a la vista
            $router->render('dashboard/index', [
                'titulo' => 'Proyectos',
                'proyectos' => $proyectos
            ]);
        }

        public static function crear_proyecto(Router $router) {

            session_start();

            isAuth();

            $alertas = [];

            if($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Instanciar con el POST
                $proyecto = new Proyecto($_POST);

                // Validación
                $alertas = $proyecto->validarProyecto();
                if(empty($alertas)) {
                    // Generar una url única
                    $hash = md5(uniqid());
                    $proyecto->url = $hash;
                    // Almacenar el propietario del proyecto
                    $proyecto->usuarioid = $_SESSION['id'];
                    // Guardar proyecto
                    $proyecto->guardar();
                    // Redireccionar al usuario
                    header('Location: /proyecto?url=' . $proyecto->url);
                }
            }

            $router->render('dashboard/crear-proyecto', [
                'alertas' => $alertas,
                'titulo' => 'Crear proyecto'
            ]);
        }

        public static function proyecto(Router $router) {

            session_start();

            isAuth();

            $token = $_GET['id'];
            if(!$token) header('Location: /dashboard');
            // La persona que crea el proyecto, es la única que lo puede ver, editar, borrar..
            $proyecto = Proyecto::where('url', $token);
            if($proyecto->usuarioid !== $_SESSION['id']) {
                header('Location: /dashboard');
            }

            $router->render('dashboard/proyecto', [
                'titulo' => $proyecto->proyecto
            ]);
        }

        public static function perfil(Router $router) {

            session_start();

            isAuth();

            $alertas = [];

            $usuario = Usuario::find($_SESSION['id']);

            if($_SERVER['REQUEST_METHOD'] === 'POST') {
                $usuario->sincronizar($_POST);
                
                $alertas = $usuario->validar_perfil();

                if(empty($alertas)) {
                    // Verificar el email que no pertenezca a otro email ya registardo
                    $existeUsuaio = Usuario::where('email', $usuario->email);
                    if($existeUsuaio && $existeUsuaio->id !== $usuario->id) {
                        // Mensaje de error
                        Usuario::setAlerta('error', 'Email no válido, ya esta asociado a otra cuenta registrada');
                        $alertas = $usuario->getAlertas();
                    } else {
                        // Guarar el registro
                        // Guardar el usuario
                        $usuario->guardar();

                        // Alerta de se guardo bien
                        Usuario::setAlerta('exito', 'Guardado correctamente');
                        $alertas = $usuario->getAlertas();

                        // Asignar el nombre nuevo a la barra
                        $_SESSION['nombre'] = $usuario->nombre;
                    }
                }
            }

            $router->render('dashboard/perfil', [
                'titulo' => 'Perfil',
                'usuario' => $usuario,
                'alertas' => $alertas
            ]);
        }

        public static function cambiar_password(Router $router) {

            session_start();

            isAuth();

            $alertas = [];

            if($_SERVER['REQUEST_METHOD'] === 'POST') {
                $usuario = Usuario::find($_SESSION['id']);

                // Sincronizar con los datos del usuario
                $usuario->sincronizar($_POST);


                $alertas = $usuario->nuevo_password();

                if(empty($alertas)) {
                    $resultado = $usuario->comprobar_password();
                    if($resultado){
                        $usuario->password = $usuario->password_nuevo;

                        // Elimando las propiedades no necesarias (auxiliares, estan NO se pasaran a la BD)
                        unset($usuario->password_actual);
                        unset($usuario->password_nuevo);
                        
                        // Hasear el nuevo password
                        $usuario->hashPassword();

                        // Actualizar
                        $resultado = $usuario->guardar();
                        if($resultado) {
                            Usuario::setAlerta('exito', 'Password guardado correctamente');
                            $alertas = $usuario->getAlertas();
                        }

                    } else {
                        Usuario::setAlerta('error', 'Password incorrecto');
                        $alertas = $usuario->getAlertas();
                    }
                }
            }

            $router->render('dashboard/cambiar-password', [
                'titulo' => 'Cambiar password',
                'alertas' => $alertas
            ]);
        }
    }