<?php 

    namespace Controllers;

    use Model\Tarea;
    use Model\Proyecto;

    class TareaController {
        public static function index() {
            $proyectoid = $_GET['id'];

            if(!$proyectoid) header('Location: /dashboard');

            $proyecto = Proyecto::where('url', $proyectoid);

            session_start();

            if(!$proyecto || $proyecto->usuarioid !== $_SESSION['id']) header('Location: /404');

            $tareas = Tarea::belongsTo('proyectoid', $proyecto->id);

            echo json_encode(['tareas' => $tareas]);
        }

        public static function crear() {
            if($_SERVER['REQUEST_METHOD'] === 'POST') {
                
                session_start();

                $proyectoid = $_POST['proyectoid'];

                $proyecto = Proyecto::where('url', $proyectoid);

                if(!$proyecto || $proyecto->usuarioid !== $_SESSION['id']) {
                    $respuesta = [
                        'tipo' => 'error',
                        'mensaje' => 'Hubo un error al agregar la tarea'
                    ];
                    echo json_encode($respuesta);
                    return;
                } 
                
                // Todo bien, instanciar y crear la tarea
                $tarea = new Tarea($_POST);
                $tarea->proyectoid = $proyecto->id;
                $resultado = $tarea->guardar();
                $respuesta = [
                    'tipo' => 'exito',
                    'id' => $resultado['id'],
                    'mensaje' => 'Tarea Creada Correctamente',
                    'proyectoid' => $proyecto->id
                ];
                echo json_encode($respuesta);
            }
        }
        

        public static function actualizar() {
            if($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Validar que el proyecto exista
                $proyecto = Proyecto::where('url', $_POST['proyectoid']);

                session_start();

                // Si el proyecto no existe, o si la persona que inicio sesión es diferente a la propietaria del proyecto
                if(!$proyecto || $proyecto->usuarioid !== $_SESSION['id']) {
                    $respuesta = [
                        'tipo' => 'error',
                        'mensaje' => 'Hubo un error al actualizar la tarea'
                    ];
                    echo json_encode($respuesta);
                    return;
                }
                $tarea = new Tarea($_POST);
                // Para asignarle un nuevo id
                $tarea->proyectoid = $proyecto->id;

                $resultado = $tarea->guardar();
                if($resultado) {
                    $respuesta = [
                        'tipo' => 'exito',
                        'id' => $tarea->id,
                        'proyectoid' => $proyecto->id,
                        'mensaje' => 'Actualizado correctamente'
                    ];
                    echo json_encode(['respuesta' => $respuesta]);
                }
            }
        }

        public static function eliminar() {
            if($_SERVER['REQUEST_METHOD'] === 'POST') {

                // Validar que el proyecto exista
                $proyecto = Proyecto::where('url', $_POST['proyectoid']);

                session_start();

                // Si el proyecto no existe, o si la persona que inicio sesión es diferente a la propietaria del proyecto
                if(!$proyecto || $proyecto->usuarioid !== $_SESSION['id']) {
                    $respuesta = [
                        'tipo' => 'error',
                        'mensaje' => 'Hubo un error al actualizar la tarea'
                    ];
                    echo json_encode($respuesta);
                    return;
                }

                // Instanciar la tarea
                $tarea = new Tarea($_POST);
                $resultado = $tarea->eliminar();
                $resultado = [
                    'resultado' => $resultado,
                    'mensaje' => 'Correctamente',
                    'tipo' => 'exito'
                ];

                echo json_encode($resultado);
            }
        }
    }