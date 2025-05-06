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
                // Obtener el ID de la actividad recién creada
                $idActividad = $this->db->getConnection()->insert_id;
    
                // Registrar el evento en el historial
                $evento = "Creación de actividad";
                $detalles = "Se creó la actividad con la descripción: '$descripcionActividad', fecha de inicio: '$fechaInicio', fecha de culminación: '$fechaCulminacion', empleado asignado: $idEmpleado, categoría: $idCategoria.";
                $this->registrarCambioEnHistorial($idActividad, $evento, $detalles);
    
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
            // Obtener los valores actuales de la actividad
            $stmt = $this->db->getConnection()->prepare("
                SELECT 
                    a.descripcionActividad, 
                    a.fechaInicio, 
                    a.fechaCulminacion, 
                    a.idEmpleado, 
                    e.nombres AS nombreEmpleadoActual, 
                    c.nombreCategoria AS nombreCategoriaActual, 
                    a.idCategoria
                FROM actividades a
                JOIN empleados e ON a.idEmpleado = e.idEmpleado
                JOIN categoriasactividades c ON a.idCategoria = c.idCategoria
                WHERE a.idActividad = ?
            ");
            $stmt->bind_param("i", $idActividad);
            $stmt->execute();
            $result = $stmt->get_result();
            $actividadActual = $result->fetch_assoc();
            $stmt->close();
    
            // Obtener los nombres del nuevo empleado y categoría
            $stmtEmpleado = $this->db->getConnection()->prepare("SELECT nombres FROM empleados WHERE idEmpleado = ?");
            $stmtEmpleado->bind_param("i", $idEmpleado);
            $stmtEmpleado->execute();
            $nuevoEmpleado = $stmtEmpleado->get_result()->fetch_assoc()['nombres'] ?? 'Desconocido';
            $stmtEmpleado->close();
    
            $stmtCategoria = $this->db->getConnection()->prepare("SELECT nombreCategoria FROM categoriasactividades WHERE idCategoria = ?");
            $stmtCategoria->bind_param("i", $idCategoria);
            $stmtCategoria->execute();
            $nuevaCategoria = $stmtCategoria->get_result()->fetch_assoc()['nombreCategoria'] ?? 'Desconocida';
            $stmtCategoria->close();
    
            // Comparar los valores actuales con los nuevos
            $cambios = [];
            if ($actividadActual['descripcionActividad'] !== $descripcionActividad) {
                $cambios[] = "Descripción cambiada de '{$actividadActual['descripcionActividad']}' a '$descripcionActividad'";
            }
            if ($actividadActual['fechaInicio'] !== $fechaInicio) {
                $cambios[] = "Fecha de inicio cambiada de '{$actividadActual['fechaInicio']}' a '$fechaInicio'";
            }
            if ($actividadActual['fechaCulminacion'] !== $fechaCulminacion) {
                $cambios[] = "Fecha de culminación cambiada de '{$actividadActual['fechaCulminacion']}' a '$fechaCulminacion'";
            }
            if ($actividadActual['idEmpleado'] != $idEmpleado) {
                $cambios[] = "Empleado reasignado de '{$actividadActual['nombreEmpleadoActual']}' a '$nuevoEmpleado'";
            }
            if ($actividadActual['idCategoria'] != $idCategoria) {
                $cambios[] = "Categoría cambiada de '{$actividadActual['nombreCategoriaActual']}' a '$nuevaCategoria'";
            }
    
            // Actualizar los valores en la base de datos
            $stmt = $this->db->getConnection()->prepare("
                UPDATE actividades 
                SET descripcionActividad = ?, fechaInicio = ?, fechaCulminacion = ?, idEmpleado = ?, idCategoria = ?
                WHERE idActividad = ?
            ");
            $stmt->bind_param("sssiii", $descripcionActividad, $fechaInicio, $fechaCulminacion, $idEmpleado, $idCategoria, $idActividad);
    
            if (!$stmt->execute()) {
                throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
            }
    
            // Registrar los cambios en el historial
            if (!empty($cambios)) {
                $detalles = implode("; ", $cambios);
                $this->registrarCambioEnHistorial($idActividad, "Edición de actividad", $detalles);
            }
    
            return true;
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
    
            $result = $this->db->getConnection()->query($query);
    
            if (!$result) {
                throw new Exception("Error al ejecutar la consulta: " . $this->db->getConnection()->error);
            }
    
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
            $stmt = $this->db->getConnection()->prepare("
                UPDATE actividades 
                SET idEstado = (SELECT idEstado FROM estadoActividad WHERE nombreEstado = 'Cancelada'),
                    descripcionCancelacion = ?
                WHERE idActividad = ?
            ");
    
            if (!$stmt) {
                throw new Exception("Error al preparar la consulta: " . $this->db->getConnection()->error);
            }
    
            $stmt->bind_param("si", $descripcionCancelacion, $idActividad);
    
            if ($stmt->execute()) {
                // Registrar el evento en el historial
                $evento = "Actividad cancelada";
                $detalles = "Motivo de cancelación: $descripcionCancelacion";
                $this->registrarCambioEnHistorial($idActividad, $evento, $detalles);
    
                return true;
            } else {
                throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
            }
        } catch (Exception $e) {
            throw new Exception("Error al cancelar la actividad: " . $e->getMessage());
        } finally {
            if (isset($stmt)) {
                $stmt->close();
            }
        }
    }

    public function culminarActividad($idActividad, $descripcionCulminacion) {
        try {
            $stmt = $this->db->getConnection()->prepare("
                UPDATE actividades 
                SET idEstado = (SELECT idEstado FROM estadoActividad WHERE nombreEstado = 'Completada'),
                    descripcionCulminacion = ?
                WHERE idActividad = ?
            ");
    
            if (!$stmt) {
                throw new Exception("Error al preparar la consulta: " . $this->db->getConnection()->error);
            }
    
            $stmt->bind_param("si", $descripcionCulminacion, $idActividad);
    
            if ($stmt->execute()) {
                // Registrar el evento en el historial
                $evento = "Actividad culminada";
                $detalles = "Descripción de culminación: $descripcionCulminacion";
                $this->registrarCambioEnHistorial($idActividad, $evento, $detalles);
    
                return true;
            } else {
                throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
            }
        } catch (Exception $e) {
            throw new Exception("Error al culminar la actividad: " . $e->getMessage());
        } finally {
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
            $stmt = $this->db->getConnection()->prepare("
                SELECT idCategoria, nombreCategoria 
                FROM categoriasactividades 
                WHERE idDepartamento = ?
            ");
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
    public function actualizarEstadosActividades() {
        try {
            $fechaActual = date('Y-m-d');
    
            // Cambiar estado a "Retraso" si la fecha de culminación ya pasó
            $stmtRetraso = $this->db->getConnection()->prepare("
                UPDATE actividades 
                SET idEstado = (SELECT idEstado FROM estadoActividad WHERE nombreEstado = 'Retraso')
                WHERE idEstado = (SELECT idEstado FROM estadoActividad WHERE nombreEstado = 'En progreso')
                AND fechaCulminacion < ?
            ");
            $stmtRetraso->bind_param("s", $fechaActual);
            if (!$stmtRetraso->execute()) {
                throw new Exception("Error al actualizar actividades a 'Retraso': " . $stmtRetraso->error);
            }
    
            // Cambiar estado a "Por iniciar" si la fecha de inicio aún no ha llegado
            $stmtPorIniciar = $this->db->getConnection()->prepare("
                UPDATE actividades 
                SET idEstado = (SELECT idEstado FROM estadoActividad WHERE nombreEstado = 'Por iniciar')
                WHERE idEstado = (SELECT idEstado FROM estadoActividad WHERE nombreEstado = 'En progreso')
                AND fechaInicio > ?
            ");
            $stmtPorIniciar->bind_param("s", $fechaActual);
            if (!$stmtPorIniciar->execute()) {
                throw new Exception("Error al actualizar actividades a 'Por iniciar': " . $stmtPorIniciar->error);
            }
    
            // Cambiar estado a "En progreso" si la fecha de inicio ya ha llegado
            $stmtEnProgreso = $this->db->getConnection()->prepare("
                UPDATE actividades 
                SET idEstado = (SELECT idEstado FROM estadoActividad WHERE nombreEstado = 'En progreso')
                WHERE idEstado = (SELECT idEstado FROM estadoActividad WHERE nombreEstado = 'Por iniciar')
                AND fechaInicio <= ?
            ");
            $stmtEnProgreso->bind_param("s", $fechaActual);
            if (!$stmtEnProgreso->execute()) {
                throw new Exception("Error al actualizar actividades a 'En progreso': " . $stmtEnProgreso->error);
            }
    
            return true;
        } catch (Exception $e) {
            throw new Exception("Error al actualizar estados de actividades: " . $e->getMessage());
        } finally {
            if (isset($stmtRetraso)) {
                $stmtRetraso->close();
            }
            if (isset($stmtPorIniciar)) {
                $stmtPorIniciar->close();
            }
            if (isset($stmtEnProgreso)) {
                $stmtEnProgreso->close();
            }
        }
    }

    public function obtenerHistorialActividad($idActividad) {
        try {
            $query = "
                SELECT 
                    h.evento,
                    h.fecha,
                    h.detalles
                FROM 
                    historialactividades h
                WHERE 
                    h.idActividad = ?
                ORDER BY 
                    h.fecha ASC
            ";
    
            $stmt = $this->db->getConnection()->prepare($query);
            if (!$stmt) {
                throw new Exception("Error al preparar la consulta: " . $this->db->getConnection()->error);
            }
    
            $stmt->bind_param("i", $idActividad);
            $stmt->execute();
            $result = $stmt->get_result();
    
            $historial = [];
            while ($row = $result->fetch_assoc()) {
                $historial[] = $row;
            }
    
            return $historial;
        } catch (Exception $e) {
            throw new Exception("Error al obtener el historial de la actividad: " . $e->getMessage());
        }
    }

    public function registrarCambioEnHistorial($idActividad, $evento, $detalles) {
        try {
            $query = "
                INSERT INTO historialactividades (idActividad, evento, fecha, detalles)
                VALUES (?, ?, NOW(), ?)
            ";
    
            $stmt = $this->db->getConnection()->prepare($query);
            if (!$stmt) {
                throw new Exception("Error al preparar la consulta: " . $this->db->getConnection()->error);
            }
    
            $stmt->bind_param("iss", $idActividad, $evento, $detalles);
            $stmt->execute();
            $stmt->close();
        } catch (Exception $e) {
            throw new Exception("Error al registrar el cambio en el historial: " . $e->getMessage());
        }
    }

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

            if ($categoria !== 'todas') {
                $conditions[] = "c.idCategoria = ?";
                $params[] = $categoria;
                $types .= 'i';
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

            if (!$stmt) {
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

            return $actividades;

        } catch (Exception $e) {
            throw new Exception("Error al obtener actividades filtradas: " . $e->getMessage());
        }
    }
    

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
    // Agregar este método al modelo
public function obtenerActividadesParaCalendario() {
    try {
        $query = "
            SELECT 
                a.idActividad,
                a.descripcionActividad AS title,
                a.fechaInicio AS start,
                a.fechaCulminacion AS end,
                es.nombreEstado AS estado,
                a.descripcionActividad AS description,
                CONCAT(e.nombres, ' ', e.apellidos) AS empleado,
                c.nombreCategoria AS categoria
            FROM 
                actividades a
            JOIN 
                estadoActividad es ON a.idEstado = es.idEstado
            JOIN 
                empleados e ON a.idEmpleado = e.idEmpleado
            JOIN 
                categoriasactividades c ON a.idCategoria = c.idCategoria
            ORDER BY 
                a.fechaInicio
        ";

        $result = $this->db->getConnection()->query($query);

        if (!$result) {
            throw new Exception("Error al ejecutar la consulta: " . $this->db->getConnection()->error);
        }

        $actividades = [];
        while ($row = $result->fetch_assoc()) {
            $actividades[] = $row;
        }

        return $actividades;
    } catch (Exception $e) {
        throw new Exception("Error al obtener actividades para calendario: " . $e->getMessage());
    }
}

    public function obtenerEstadisticasTrimestrales($fechaInicio, $fechaFin) {
        try {
            if (!strtotime($fechaInicio) || !strtotime($fechaFin)) {
                throw new Exception("Fechas no válidas");
            }

            $stmt = $this->db->getConnection()->prepare("
                SELECT 
                    es.nombreEstado AS estado,
                    COUNT(a.idActividad) AS cantidad,
                    DATE_FORMAT(a.fechaInicio, '%Y-%m') AS mes
                FROM 
                    actividades a
                JOIN 
                    estadoActividad es ON a.idEstado = es.idEstado
                WHERE 
                    a.fechaInicio BETWEEN ? AND ?
                GROUP BY 
                    es.nombreEstado, DATE_FORMAT(a.fechaInicio, '%Y-%m')
                ORDER BY 
                    mes, es.nombreEstado
            ");

            if (!$stmt) {
                throw new Exception("Error al preparar la consulta: " . $this->db->getConnection()->error);
            }

            $stmt->bind_param("ss", $fechaInicio, $fechaFin);
            $stmt->execute();
            $result = $stmt->get_result();

            $datos = [];
            while ($row = $result->fetch_assoc()) {
                $datos[] = $row;
            }

            $stmt->close();

            $trimestres = [];
            foreach ($datos as $dato) {
                $fecha = DateTime::createFromFormat('Y-m', $dato['mes']);
                $anio = $fecha->format('Y');
                $trimestre = ceil($fecha->format('m') / 3);
                $clave = $anio . '-T' . $trimestre;

                if (!isset($trimestres[$clave])) {
                    $trimestres[$clave] = [
                        'trimestre' => $clave,
                        'Completada' => 0,
                        'Cancelada' => 0,
                        'En progreso' => 0,
                        'total' => 0
                    ];
                }

                $trimestres[$clave][$dato['estado']] = (int)$dato['cantidad'];
                $trimestres[$clave]['total'] += (int)$dato['cantidad'];
            }

            return array_values($trimestres);
        } catch (Exception $e) {
            throw new Exception("Error al obtener estadísticas trimestrales: " . $e->getMessage());
        }
    }
}