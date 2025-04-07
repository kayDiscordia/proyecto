<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'Conexion.php';

class Login extends Conexion {
    private $id;

    public function IniciarSesion($Usuario, $Contrasena) {
        // Verifica si la conexión está establecida
        if (!$this->conexion) {
            die("Error: No se pudo conectar a la base de datos.");
        }

        // Consulta preparada para evitar inyección SQL
        $stmt = $this->prepare("SELECT idEmpleado, contrasena FROM empleados WHERE usuarioEmpleado = ?");
        if (!$stmt) {
            die("Error en la preparación de la consulta: " . $this->error());
        }

        $stmt->bind_param("s", $Usuario);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $columna = $resultado->fetch_assoc();

            // Compara la contraseña en texto plano
            if ($Contrasena === $columna['contrasena']) {
                $this->id = $columna['idEmpleado'];
                return 1; // Inicio de sesión exitoso
            } else {
                return 10; // Contraseña incorrecta
            }
        } else {
            return 100; // Usuario no registrado
        }
    }

    public function IdUsuario() {
        return $this->id;
    }

public function establecerDatosSesion($idEmpleado) {
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

    public function SelectuserByuser($id) {
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

    public function obtenerCargoPorId($idEmpleado) {
        // Verifica si la conexión está establecida
        if (!$this->conexion) {
            die("Error: No se pudo conectar a la base de datos.");
        }
    
        // Consulta preparada para evitar inyección SQL
        $stmt = $this->prepare("SELECT c.nombreCargo FROM empleados e 
                                INNER JOIN cargos c ON e.idCargo = c.idCargo 
                                WHERE e.idEmpleado = ?");
        if (!$stmt) {
            die("Error en la preparación de la consulta: " . $this->error());
        }
    
        $stmt->bind_param("i", $idEmpleado);
        $stmt->execute();
        $resultado = $stmt->get_result();
    
        if ($resultado->num_rows > 0) {
            $columna = $resultado->fetch_assoc();
            return $columna['nombreCargo']; // Devuelve el nombre del cargo
        } else {
            return null; // No se encontró el cargo
        }
    }
}
