<?php

    namespace Model;

    use Model\ActiveRecord;

    class Proyecto extends ActiveRecord {
        // Base de datos
        protected static $tabla = 'proyectos';
        protected static $columnasDB = ['id', 'proyecto', 'url', 'usuarioid'];

        // constructor
        public function __construct($args = [])
        {
            $this->id = $args['id'] ?? null;
            $this->proyecto = $args['proyecto'] ?? '';
            $this->url = $args['url'] ?? '';
            $this->usuarioid = $args['usuarioid'] ?? '';
        }

        // Función para validar proyecto
        public function validarProyecto() {
            if(!$this->proyecto) {
                self::$alertas['error'][] = 'El nombre del proyecto es obligatorio';
            }
            return self::$alertas;
        }
    }