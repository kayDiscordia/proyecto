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
            $idEstado = 2; // ID del estado "En progreso", asegúrate de que este valor sea correcto

            // Preparar la consulta SQL
            $stmt = $this->db->getConnection()->prepare("
                INSERT INTO actividades (descripcionActividad, fechaInicio, fechaCulminacion, idEmpleado, idCategoria, idEstado)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            // Verificar si la preparación de la consulta fue exitosa
            if (!$stmt) {
                throw new Exception("Error al preparar la consulta: " . $this->db->getConnection()->error);
            }

            // Vincular los parámetros
            $stmt->bind_param(
                "sssiii", // Tipos de datos: s = string, i = integer
                $descripcionActividad,
                $fechaInicio,
                $fechaCulminacion,
                $idEmpleado,
                $idCategoria,
                $idEstado
            );

            // Ejecutar la consulta
            if ($stmt->execute()) {
                return true; // Inserción exitosa
            } else {
                throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
            }
        } catch (Exception $e) {
            throw new Exception("Error al insertar la actividad: " . $e->getMessage());
        } finally {
            // Cerrar la declaración
            if (isset($stmt)) {
                $stmt->close();
            }
        }
    }

    // Método para obtener todas las actividades
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

    // Método para cancelar una actividad
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

    // Método para culminar una actividad
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

    // Método para obtener todas las categorías
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

    // Método para obtener categorías por departamento
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
}
?>