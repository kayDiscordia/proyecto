<?php
require_once 'Database.php';

class modeloEmpleado {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function crearEmpleado($data) {
    // Verificar si el estado está definido, si no, asignar activo (1) por defecto
    $idEstado = $data['idEstado'] ?? 1;
    
    $sql = "INSERT INTO empleados (nombres, apellidos, cedula, idCargo, idDepartamento, usuarioEmpleado, contrasena, idRol, idEstado) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $this->db->getConnection()->prepare($sql);
    
    // Nota: Corregí "nombres" (tenías "nombres" en tu código original)
    $stmt->bind_param("sssiissii", 
        $data['nombres'], 
        $data['apellidos'], 
        $data['cedula'], 
        $data['idCargo'], 
        $data['idDepartamento'], 
        $data['usuarioEmpleado'], 
        $data['contrasena'],
        $data['idRol'],
        $idEstado
    );
    
    if ($stmt->execute()) {
        return true;
    } else {
        throw new Exception("Error al crear empleado: " . $this->db->getConnection()->error);
    }
}

    public function obtenerEmpleados() {
    $query = "SELECT e.idEmpleado, e.nombres, e.apellidos, e.cedula, e.usuarioEmpleado, e.contrasena, 
                     c.nombreCargo AS cargo_nombre, d.nombreDepartamentos AS departamento_nombre,
                     r.nombreRol AS rol_nombre, es.nombreEstado AS estado_nombre
              FROM empleados e
              LEFT JOIN cargos c ON e.idCargo = c.idCargo
              LEFT JOIN departamentos d ON e.idDepartamento = d.idDepartamentos
              LEFT JOIN roles r ON e.idRol = r.idRol
              LEFT JOIN estadosEmpleados es ON e.idEstado = es.idEstado";
    $resultado = $this->db->getConnection()->query($query);
    if ($resultado) {
        return $resultado->fetch_all(MYSQLI_ASSOC);
    } else {
        throw new Exception("Error al obtener empleados: " . $this->db->getConnection()->error);
    }
}

    public function obtenerCargos() {
        $query = "SELECT * FROM cargos";
        $resultado = $this->db->getConnection()->query($query);
        if ($resultado) {
            return $resultado->fetch_all(MYSQLI_ASSOC);
        } else {
            throw new Exception("Error al obtener cargos: " . $this->db->getConnection()->error);
        }
    }

    public function obtenerDepartamentos() {
        $query = "SELECT * FROM departamentos";
        $resultado = $this->db->getConnection()->query($query);
        if ($resultado) {
            return $resultado->fetch_all(MYSQLI_ASSOC);
        } else {
            throw new Exception("Error al obtener departamentos: " . $this->db->getConnection()->error);
        }
    }

    public function obtenerEmpleadoPorId($idEmpleado) {
        $query = "SELECT * FROM empleados WHERE idEmpleado = ?";
        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->bind_param("i", $idEmpleado);
        $stmt->execute();
        $resultado = $stmt->get_result();
        if ($resultado->num_rows === 1) {
            return $resultado->fetch_object();
        } else {
            throw new Exception("No se encontró el empleado.");
        }
    }

 public function actualizarEmpleado($data) {
    $sql = "UPDATE empleados SET 
            nombres = ?, 
            apellidos = ?, 
            cedula = ?, 
            usuarioEmpleado = ?, 
            contrasena = ?,
            idEstado = ?
            WHERE idEmpleado = ?";
    
    $stmt = $this->db->getConnection()->prepare($sql);
    $stmt->bind_param("sssssii", 
        $data['nombres'], 
        $data['apellidos'], 
        $data['cedula'], 
        $data['usuarioEmpleado'], 
        $data['contrasena'],
        $data['idEstado'],
        $data['idEmpleado']
    );
    
    if ($stmt->execute()) {
        return true;
    } else {
        throw new Exception("Error al actualizar empleado: " . $this->db->getConnection()->error);
    }
}
   public function obtenerEstadosEmpleados() {
    $query = "SELECT idEstado, nombreEstado FROM estadosEmpleados ORDER BY idEstado";
    $resultado = $this->db->getConnection()->query($query);
    
    if (!$resultado) {
        error_log("Error en obtenerEstadosEmpleados: ".$this->db->getConnection()->error);
        return [];
    }
    
    return $resultado->fetch_all(MYSQLI_ASSOC);
}

   public function obtenerActividadesPorEmpleado($idEmpleado, $filtros = []) {
    try {
        // Consulta base
        $query = "SELECT 
                    a.idActividad,
                    a.descripcionActividad,
                    DATE_FORMAT(a.fechaInicio, '%d/%m/%Y') as fechaInicio,
                    DATE_FORMAT(a.fechaCulminacion, '%d/%m/%Y') as fechaCulminacion,
                    ea.nombreEstado as estado,
                    ca.nombreCategoria,
                    d.nombreDepartamentos as departamento
                 FROM actividades a
                 JOIN estadoActividad ea ON a.idEstado = ea.idEstado
                 JOIN categoriasactividades ca ON a.idCategoria = ca.idCategoria
                 JOIN departamentos d ON ca.idDepartamento = d.idDepartamentos
                 WHERE a.idEmpleado = ?";
        
        // Preparar parámetros para bind_param
        $paramTypes = "i"; // Siempre tendremos al menos el idEmpleado
        $paramValues = [$idEmpleado];
        
        // Aplicar filtro por estado si existe
        if (!empty($filtros['estado'])) {
            $query .= " AND ea.nombreEstado = ?";
            $paramTypes .= "s";
            $paramValues[] = $filtros['estado'];
        }
        
        // Aplicar filtro por rango de fechas
        if (!empty($filtros['fecha_inicio']) && !empty($filtros['fecha_fin'])) {
            $query .= " AND a.fechaInicio >= STR_TO_DATE(?, '%Y-%m-%d') 
                        AND a.fechaCulminacion <= STR_TO_DATE(?, '%Y-%m-%d')";
            $paramTypes .= "ss";
            $paramValues[] = $filtros['fecha_inicio'];
            $paramValues[] = $filtros['fecha_fin'];
        } elseif (!empty($filtros['fecha_inicio'])) {
            $query .= " AND a.fechaInicio >= STR_TO_DATE(?, '%Y-%m-%d')";
            $paramTypes .= "s";
            $paramValues[] = $filtros['fecha_inicio'];
        } elseif (!empty($filtros['fecha_fin'])) {
            $query .= " AND a.fechaCulminacion <= STR_TO_DATE(?, '%Y-%m-%d')";
            $paramTypes .= "s";
            $paramValues[] = $filtros['fecha_fin'];
        }
        
        $query .= " ORDER BY a.fechaInicio DESC";
        
        $stmt = $this->db->getConnection()->prepare($query);
        
        // Bind dinámico de parámetros
        if (count($paramValues) > 1) {
            $stmt->bind_param($paramTypes, ...$paramValues);
        } else {
            $stmt->bind_param($paramTypes, $paramValues[0]);
        }
        
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        $actividades = [];
        while ($row = $resultado->fetch_assoc()) {
            $actividades[] = $row;
        }
        
        return $actividades;
    } catch (Exception $e) {
        error_log("Error en modeloEmpleado: " . $e->getMessage());
        throw new Exception("No se pudieron obtener las actividades del empleado");
    }
}
    public function verificarCedula($cedula) {
        $query = "SELECT COUNT(*) AS count FROM empleados WHERE cedula = ?";
        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->bind_param("s", $cedula);
        $stmt->execute();
        $resultado = $stmt->get_result();
        if ($resultado) {
            return $resultado->fetch_assoc();
        } else {
            throw new Exception("Error al verificar la cédula: " . $this->db->getConnection()->error);
        }
    }
}