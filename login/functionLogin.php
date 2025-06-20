<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'Conexion.php';

class Login extends Conexion
{
    private $id;

    public function IniciarSesion($Usuario, $Contrasena)
    {
        // Verifica si la conexión está establecida
        if (!$this->conexion) {
            die("Error: No se pudo conectar a la base de datos.");
        }

        // Consulta preparada para evitar inyección SQL
        $stmt = $this->prepare("SELECT e.idEmpleado, e.contrasena, e.idRol, e.nombres, e.apellidos, e.idDepartamento, es.nombreEstado 
                                FROM empleados e
                                INNER JOIN estadosEmpleados es ON e.idEstado = es.idEstado
                                WHERE e.usuarioEmpleado = ?");
        if (!$stmt) {
            die("Error en la preparación de la consulta: " . $this->error());
        }

        $stmt->bind_param("s", $Usuario);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $columna = $resultado->fetch_assoc();

            // Verifica si el estado es Permisado o Suspendido
            if (in_array(strtolower($columna['nombreEstado']), ['permisado', 'suspendido'])) {
                return 20; // Código para usuario no autorizado por estado
            }

            // Verificar la contraseña hasheada
            if (password_verify($Contrasena, $columna['contrasena'])) {
                $this->id = $columna['idEmpleado'];

                // Establecer datos de sesión directamente aquí
                $_SESSION['iniciosesion'] = true;
                $_SESSION['id'] = $columna['idEmpleado'];
                $_SESSION['idRol'] = $columna['idRol'];
                $_SESSION['nombres'] = $columna['nombres'];
                $_SESSION['apellidos'] = $columna['apellidos'];
                $_SESSION['idDepartamento'] = $columna['idDepartamento'];

                return 1; // Inicio de sesión exitoso
            } else {
                return 10; // Contraseña incorrecta
            }
        } else {
            return 100; // Usuario no registrado
        }
    }

    public function IdUsuario()
    {
        return $this->id;
    }

    public function establecerDatosSesion($idEmpleado)
    {
        // Verifica si la conexión está establecida
        if (!$this->conexion) {
            die("Error: No se pudo conectar a la base de datos.");
        }

        // Consulta preparada para obtener los datos del empleado
        $stmt = $this->prepare("SELECT nombres, apellidos FROM empleados WHERE idEmpleado = ?");
        if (!$stmt) {
            die("Error en la preparación de la consulta: " . $this->error());
        }

        $stmt->bind_param("i", $idEmpleado);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $columna = $resultado->fetch_assoc();
            // Asignar los datos a las variables de sesión
            $_SESSION['nombre'] = $columna['nombres'];
            $_SESSION['apellido'] = $columna['apellidos'];
        } else {
            // Si no se encuentra el empleado, limpiar las variables de sesión
            $_SESSION['nombre'] = null;
            $_SESSION['apellido'] = null;
        }
    }

    public function SelectuserByuser($id)
    {
        // Verifica si la conexión está establecida
        if (!$this->conexion) {
            die("Error: No se pudo conectar a la base de datos.");
        }

        // Consulta preparada para evitar inyección SQL
        $stmt = $this->prepare("SELECT * FROM empleados WHERE idEmpleado = ?");
        if (!$stmt) {
            die("Error en la preparación de la consulta: " . $this->error());
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();

        return $resultado->fetch_assoc();
    }

    public function obtenerDatosEmpleadoPorId($idEmpleado)
    {
        // Verifica si la conexión está establecida
        if (!$this->conexion) {
            die("Error: No se pudo conectar a la base de datos.");
        }

        // Consulta preparada para obtener todos los datos del empleado
        $stmt = $this->prepare("SELECT e.*, c.nombreCargo FROM empleados e 
                                INNER JOIN cargos c ON e.idCargo = c.idCargo 
                                WHERE e.idEmpleado = ?");
        if (!$stmt) {
            die("Error en la preparación de la consulta: " . $this->error());
        }

        $stmt->bind_param("i", $idEmpleado);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            return $resultado->fetch_assoc(); // Devuelve todos los datos del empleado
        } else {
            return null; // No se encontró el empleado
        }
    }
}
