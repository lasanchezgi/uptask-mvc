<?php

    namespace Model;

    class Usuario extends ActiveRecord {

        // Base de datos
        protected static $tabla = 'usuarios';
        protected static $columnasDB = ['id','nombre', 'email', 'password', 'token', 'confirmado'];

        // Constructor
        public function __construct($args = [])
        {
            $this->id = $args['id'] ?? null;
            $this->nombre = $args['nombre'] ?? '';
            $this->email = $args['email'] ?? '';
            $this->password = $args['password'] ?? '';
            $this->password2 = $args['password2'] ?? '';
            $this->password_actual = $args['password_actual'] ?? '';
            $this->password_nuevo = $args['password_nuevo'] ?? '';
            $this->token = $args['token'] ?? '';
            $this->confirmado = $args['confirmado'] ?? 0;

        }

        // Validar el login de usuarios
        public function validarLogin() : array {
            
            if (!$this->email) {
                self::$alertas['error'][] = 'El email del usuario es obligatorio';
            }

            if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
                self::$alertas['error'][] = 'Email no válido';
            }

            if (!$this->password) {
                self::$alertas['error'][] = 'El password no puede ir vacio';
            }
            return self::$alertas;
        }

        // Validación para cuentas nuevas
        public function validarNuevaCuenta() : array {

            if (!$this->nombre) {
                self::$alertas['error'][] = 'El nombre del usuario es obligatorio';
            }

            if (!$this->email) {
                self::$alertas['error'][] = 'El email del usuario es obligatorio';
            }

            if (!$this->password) {
                self::$alertas['error'][] = 'El password no puede ir vacio';
            }

            if (strlen($this->password) < 6) {
                self::$alertas['error'][] = 'El password debe contener al menos 6 caracteres';
            }

            if ($this->password !== $this->password2) {
                self::$alertas['error'][] = 'Los passwords son diferentes';
            }

            return self::$alertas;
        }

        // Validar email
        public function validarEmail() : array {

            if (!$this->email) {
                self::$alertas['error'][] = 'El email es obligatorio';
            }

            // Asegurarnos que es email valido
            if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
                self::$alertas['error'][] = 'Email no válido';
            }

            return self::$alertas;
        }

        // Validar el password
        public function validarPassword() : array {

            if (!$this->password) {
                self::$alertas['error'][] = 'El password no puede ir vacio';
            }

            if (strlen($this->password) < 6) {
                self::$alertas['error'][] = 'El password debe contener al menos 6 caracteres';
            }

            return self::$alertas;
        }

        // Validar los campos del perfil
        public function validar_perfil() : array {
            if(!$this->nombre) {
                self::$alertas['error'][] = 'El nombre es obligatorio';
            }
            if(!$this->email) {
                self::$alertas['error'][] = 'El email es obligatorio';
            }
            return self::$alertas;
        }

        // Validar el password actual y el password nuevo
        public function nuevo_password() : array {
            if(!$this->password_actual) {
                self::$alertas['error'][] = 'El password actual no puede ir vacio';
            }
            if(!$this->password_nuevo) {
                self::$alertas['error'][] = 'El password nuevo no puede ir vacio';
            }
            if(strlen($this->password_nuevo) < 6) {
                self::$alertas['error'][] = 'El password debe contener al menos 6 caracteres';
            }
            return self::$alertas;
        }

        // Comprobar password
        public function comprobar_password() : bool {
            return password_verify($this->password_actual,$this->password);
        }

        // Hashea el password
        public function hashPassword() : void {

            $this->password = password_hash($this->password, PASSWORD_BCRYPT);
        }

        // Genera un Token
        public function crearToken() : void {

            $this->token = uniqid();
        }

    }