<?php
require_once 'Database.php';

class modeloActividad {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    
    // Método para insertar una actividad
    public function insertarActividad($descripcionActividad, $fechaInicio, $fechaCulminacion, $idEmpleado, $idCategoria) {
        try {
            $idEstado = 2; // ID del estado "En progreso"

            $stmt = $this->db->getConnection()->prepare("
                INSERT INTO actividades (descripcionActividad, fechaInicio, fechaCulminacion, idEmpleado, idCategoria, idEstado)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            if (!$stmt) {
                throw new Exception("Error al preparar la consulta: " . $this->db->getConnection()->error);
            }

            $stmt->bind_param(
                "sssiii",
                $descripcionActividad,
                $fechaInicio,
                $fechaCulminacion,
                $idEmpleado,
                $idCategoria,
                $idEstado
            );

            if ($stmt->execute()) {
                return true;
            } else {
                throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
            }
        } catch (Exception $e) {
            throw new Exception("Error al insertar la actividad: " . $e->getMessage());
        } finally {
            if (isset($stmt)) {
                $stmt->close();
            }
        }
    }

    public function editarActividad($idActividad, $descripcionActividad, $fechaInicio, $fechaCulminacion, $idEmpleado, $idCategoria) {
        try {
            $stmt = $this->db->getConnection()->prepare("
                UPDATE actividades 
                SET descripcionActividad = ?, fechaInicio = ?, fechaCulminacion = ?, idEmpleado = ?, idCategoria = ?
                WHERE idActividad = ?
            ");
    
            if (!$stmt) {
                throw new Exception("Error al preparar la consulta: " . $this->db->getConnection()->error);
            }
    
            $stmt->bind_param("sssiii", $descripcionActividad, $fechaInicio, $fechaCulminacion, $idEmpleado, $idCategoria, $idActividad);
    
            if ($stmt->execute()) {
                return true;
            } else {
                throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
            }
        } catch (Exception $e) {
            throw new Exception("Error al editar la actividad: " . $e->getMessage());
        } finally {
            if (isset($stmt)) {
                $stmt->close();
            }
        }
    }

    public function obtenerActividades() {
        try {
            // Consulta SQL para obtener las actividades con información relacionada, ordenadas por ID
            $query = "
                SELECT 
                    a.idActividad,
                    a.descripcionActividad,
                    a.fechaInicio,
                    a.fechaCulminacion,
                    e.nombres AS nombreEmpleado,
                    es.nombreEstado AS estadoActividad,
                    c.nombreCategoria AS categoriaActividad,
                    a.descripcionCancelacion,
                    a.descripcionCulminacion
                FROM 
                    actividades a
                JOIN 
                    empleados e ON a.idEmpleado = e.idEmpleado
                JOIN 
                    estadoActividad es ON a.idEstado = es.idEstado
                JOIN 
                    categoriasactividades c ON a.idCategoria = c.idCategoria
                ORDER BY 
                    a.idActividad ASC
            ";
    
            // Ejecutar la consulta
            $result = $this->db->getConnection()->query($query);
    
            // Verificar si la consulta fue exitosa
            if (!$result) {
                throw new Exception("Error al ejecutar la consulta: " . $this->db->getConnection()->error);
            }
    
            // Obtener los resultados como un array asociativo
            $actividades = [];
            while ($row = $result->fetch_assoc()) {
                $actividades[] = $row;
            }
    
            return $actividades;
        } catch (Exception $e) {
            throw new Exception("Error al obtener actividades: " . $e->getMessage());
        }
    }

    public function cancelarActividad($idActividad, $descripcionCancelacion) {
        try {
            // Preparar la consulta SQL
            $stmt = $this->db->getConnection()->prepare("
                UPDATE actividades 
                SET idEstado = (SELECT idEstado FROM estadoActividad WHERE nombreEstado = 'Cancelada'),
                    descripcionCancelacion = ?
                WHERE idActividad = ?
            ");

            // Verificar si la preparación de la consulta fue exitosa
            if (!$stmt) {
                throw new Exception("Error al preparar la consulta: " . $this->db->getConnection()->error);
            }

            // Vincular los parámetros
            $stmt->bind_param("si", $descripcionCancelacion, $idActividad);

            // Ejecutar la consulta
            if ($stmt->execute()) {
                return true; // Actualización exitosa
            } else {
                throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
            }
        } catch (Exception $e) {
            throw new Exception("Error al cancelar la actividad: " . $e->getMessage());
        } finally {
            // Cerrar la declaración
            if (isset($stmt)) {
                $stmt->close();
            }
        }
    }

    public function culminarActividad($idActividad, $descripcionCulminacion) {
        try {
            // Preparar la consulta SQL
            $stmt = $this->db->getConnection()->prepare("
                UPDATE actividades 
                SET idEstado = (SELECT idEstado FROM estadoActividad WHERE nombreEstado = 'Completada'),
                    descripcionCulminacion = ?
                WHERE idActividad = ?
            ");

            // Verificar si la preparación de la consulta fue exitosa
            if (!$stmt) {
                throw new Exception("Error al preparar la consulta: " . $this->db->getConnection()->error);
            }

            // Vincular los parámetros
            $stmt->bind_param("si", $descripcionCulminacion, $idActividad);

            // Ejecutar la consulta
            if ($stmt->execute()) {
                return true; // Actualización exitosa
            } else {
                throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
            }
        } catch (Exception $e) {
            throw new Exception("Error al culminar la actividad: " . $e->getMessage());
        } finally {
            // Cerrar la declaración
            if (isset($stmt)) {
                $stmt->close();
            }
        }
    }

    public function obtenerDetallesActividad($idActividad) {
        try {
            $stmt = $this->db->getConnection()->prepare("
                SELECT 
                    a.idActividad,
                    a.descripcionActividad,
                    a.fechaInicio,
                    a.fechaCulminacion,
                    e.nombres AS nombreEmpleado,
                    e.apellidos AS apellidoEmpleado,
                    c.nombreCategoria AS categoriaActividad,
                    es.nombreEstado AS estadoActividad
                FROM 
                    actividades a
                JOIN 
                    empleados e ON a.idEmpleado = e.idEmpleado
                JOIN 
                    categoriasactividades c ON a.idCategoria = c.idCategoria
                JOIN 
                    estadoActividad es ON a.idEstado = es.idEstado
                WHERE 
                    a.idActividad = ?
            ");
    
            if (!$stmt) {
                throw new Exception("Error al preparar la consulta: " . $this->db->getConnection()->error);
            }
    
            $stmt->bind_param("i", $idActividad);
            $stmt->execute();
            $result = $stmt->get_result();
    
            if ($result->num_rows === 0) {
                throw new Exception("No se encontró la actividad con el ID proporcionado.");
            }
    
            return $result->fetch_assoc();
        } catch (Exception $e) {
            throw new Exception("Error al obtener los detalles de la actividad: " . $e->getMessage());
        } finally {
            if (isset($stmt)) {
                $stmt->close();
            }
        }
    }

    public function obtenerCategoriasPorDepartamento($idDepartamento) {
        try {
            $stmt = $this->db->getConnection()->prepare(
                "SELECT idCategoria, nombreCategoria 
                 FROM categoriasactividades 
                 WHERE idDepartamento = ?"
            );
            $stmt->bind_param("i", $idDepartamento);
            $stmt->execute();
            $result = $stmt->get_result();

            $categorias = [];
            while ($row = $result->fetch_assoc()) {
                $categorias[] = $row;
            }

            return $categorias;
        } catch (Exception $e) {
            throw new Exception("Error al obtener categorías: " . $e->getMessage());
        }
    }

    // Método para obtener actividades con filtros
    public function obtenerActividadesFiltradas($estado = 'todos', $fechaInicio = '', $fechaFin = '', $categoria = 'todas') {
        try {
            $query = "
                SELECT 
                    a.idActividad,
                    a.descripcionActividad,
                    a.fechaInicio,
                    a.fechaCulminacion,
                    e.nombres AS nombreEmpleado,
                    es.nombreEstado AS estadoActividad,
                    c.nombreCategoria AS categoriaActividad
                FROM 
                    actividades a
                JOIN 
                    empleados e ON a.idEmpleado = e.idEmpleado
                JOIN 
                    estadoActividad es ON a.idEstado = es.idEstado
                JOIN 
                    categoriasactividades c ON a.idCategoria = c.idCategoria
                WHERE 1=1
            ";

            $conditions = [];
            $params = [];
            $types = '';

            if ($estado !== 'todos') {
                $conditions[] = "es.nombreEstado = ?";
                $params[] = $estado;
                $types .= 's';
            }

            if ($categoria !== 'todas' && $categoria !== '') {
                $conditions[] = "c.idCategoria = ?";
                $params[] = $categoria;
                $types .= 'i'; // ID de categoría es numérico
            }

            if (!empty($fechaInicio)) {
                $conditions[] = "a.fechaInicio >= ?";
                $params[] = $fechaInicio;
                $types .= 's';
            }

            if (!empty($fechaFin)) {
                $conditions[] = "a.fechaCulminacion <= ?";
                $params[] = $fechaFin;
                $types .= 's';
            }

            if (!empty($conditions)) {
                $query .= " AND " . implode(" AND ", $conditions);
            }

            $query .= " ORDER BY a.fechaInicio DESC";

            $stmt = $this->db->getConnection()->prepare($query);
            
            if ($stmt === false) {
                throw new Exception("Error al preparar la consulta: " . $this->db->getConnection()->error);
            }

            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }

            $stmt->execute();
            $result = $stmt->get_result();

            $actividades = [];
            while ($row = $result->fetch_assoc()) {
                $actividades[] = $row;
            }

            $stmt->close();
            return $actividades;

        } catch (Exception $e) {
            throw new Exception("Error al obtener actividades filtradas: " . $e->getMessage());
        }
    }

    // Método para obtener todas las categorías con ID
    public function obtenerTodasCategorias() {
        try {
            $query = "SELECT idCategoria, nombreCategoria FROM categoriasactividades";
            $result = $this->db->getConnection()->query($query);

            if (!$result) {
                throw new Exception("Error al obtener categorías: " . $this->db->getConnection()->error);
            }

            $categorias = [];
            while ($row = $result->fetch_assoc()) {
                $categorias[] = $row;
            }

            return $categorias;
        } catch (Exception $e) {
            throw new Exception("Error en modelo: " . $e->getMessage());
        }
    }
}