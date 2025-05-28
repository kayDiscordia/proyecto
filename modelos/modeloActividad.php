<?php
require_once 'Database.php';

class modeloActividad
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    // Método para insertar una actividad
    public function insertarActividad($nombreActividad, $descripcionActividad, $fechaInicio, $fechaCulminacion, $idEmpleado, $idCategoria)
    {
        try {
            $idEstado = 2; // ID del estado "En progreso"

            $stmt = $this->db->getConnection()->prepare("
            INSERT INTO actividades (nombreActividad, descripcionActividad, fechaInicio, fechaCulminacion, idEmpleado, idCategoria, idEstado)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

            if (!$stmt) {
                throw new Exception("Error al preparar la consulta: " . $this->db->getConnection()->error);
            }

            $stmt->bind_param(
                "ssssiii",
                $nombreActividad,
                $descripcionActividad,
                $fechaInicio,
                $fechaCulminacion,
                $idEmpleado,
                $idCategoria,
                $idEstado
            );

            if ($stmt->execute()) {
                $idActividad = $this->db->getConnection()->insert_id;

                // Registrar el evento en el historial
                $evento = "Creación de actividad";
                $detalles = "Nombre: '$nombreActividad'. Descripción: '$descripcionActividad'";
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

    public function editarActividad($idActividad, $descripcionActividad, $fechaInicio, $fechaCulminacion, $idEmpleado, $idCategoria)
    {
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

    public function obtenerActividades()
    {
        try {
            $query = "
                SELECT                     
                    a.idActividad,
                    a.nombreActividad,
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

    public function cancelarActividad($idActividad, $descripcionCancelacion)
    {
        try {
            $stmtEstado = $this->db->getConnection()->prepare("SELECT idEstado FROM actividades WHERE idActividad = ?");
            $stmtEstado->bind_param("i", $idActividad);
            $stmtEstado->execute();
            $result = $stmtEstado->get_result();
            $estadoActual = $result->fetch_assoc()['idEstado'];
            $stmtEstado->close();

            $stmtNombre = $this->db->getConnection()->prepare("SELECT nombreEstado FROM estadoActividad WHERE idEstado = ?");
            $stmtNombre->bind_param("i", $estadoActual);
            $stmtNombre->execute();
            $resultNombre = $stmtNombre->get_result();
            $nombreEstado = $resultNombre->fetch_assoc()['nombreEstado'];
            $stmtNombre->close();

            $stmt = $this->db->getConnection()->prepare("
            UPDATE actividades 
            SET idEstado = (SELECT idEstado FROM estadoActividad WHERE nombreEstado = 'Cancelada'),
                descripcionCancelacion = ?
            WHERE idActividad = ?
        ");
            $stmt->bind_param("si", $descripcionCancelacion, $idActividad);

            if ($stmt->execute()) {
                $evento = "Actividad cancelada";
                $detalles = "Motivo de cancelación: $descripcionCancelacion";
                if (trim(strtolower($nombreEstado)) === "por iniciar") {
                    $detalles .= " (La actividad fue cancelada antes de iniciar)";
                }
                $this->registrarCambioEnHistorial($idActividad, $evento, $detalles);
                return true;
            } else {
                throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
            }
        } catch (Exception $e) {
            throw new Exception("Error al cancelar la actividad: " . $e->getMessage());
        } finally {
            if (isset($stmt)) $stmt->close();
        }
    }

    public function culminarActividad($idActividad, $descripcionCulminacion)
    {
        try {
            // Verificar estado actual ANTES de actualizar
            $stmtEstado = $this->db->getConnection()->prepare("SELECT idEstado FROM actividades WHERE idActividad = ?");
            $stmtEstado->bind_param("i", $idActividad);
            $stmtEstado->execute();
            $result = $stmtEstado->get_result();
            $estadoActual = $result->fetch_assoc()['idEstado'];
            $stmtEstado->close();

            $stmtNombre = $this->db->getConnection()->prepare("SELECT nombreEstado FROM estadoActividad WHERE idEstado = ?");
            $stmtNombre->bind_param("i", $estadoActual);
            $stmtNombre->execute();
            $resultNombre = $stmtNombre->get_result();
            $nombreEstado = $resultNombre->fetch_assoc()['nombreEstado'];
            $stmtNombre->close();

            // Actualizar estado a Completada
            $stmt = $this->db->getConnection()->prepare("
            UPDATE actividades 
            SET idEstado = (SELECT idEstado FROM estadoActividad WHERE nombreEstado = 'Completada'),
                descripcionCulminacion = ?
            WHERE idActividad = ?
        ");
            $stmt->bind_param("si", $descripcionCulminacion, $idActividad);

            if ($stmt->execute()) {
                $evento = "Actividad culminada";
                $detalles = "Descripción de culminación: $descripcionCulminacion";
                // Normaliza el nombre del estado para evitar problemas de espacios o mayúsculas
                if (trim(strtolower($nombreEstado)) === "por iniciar") {
                    $detalles .= " (La actividad fue culminada antes de tiempo)";
                }
                $this->registrarCambioEnHistorial($idActividad, $evento, $detalles);
                return true;
            } else {
                throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
            }
        } catch (Exception $e) {
            throw new Exception("Error al culminar la actividad: " . $e->getMessage());
        } finally {
            if (isset($stmt)) $stmt->close();
        }
    }

    public function obtenerDetallesActividad($idActividad)
    {
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

    public function obtenerCategoriasPorDepartamento($idDepartamento)
    {
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
    } // <-- Cierra correctamente el método aquí

    public function obtenerDepartamentos()
    {
        try {
            $query = "SELECT idDepartamentos, nombreDepartamentos FROM departamentos";
            $result = $this->db->getConnection()->query($query);

            $departamentos = [];
            while ($row = $result->fetch_assoc()) {
                $departamentos[] = $row;
            }

            return $departamentos;
        } catch (Exception $e) {
            throw new Exception("Error al obtener departamentos: " . $e->getMessage());
        }
    }

    public function obtenerEstadisticasSemanalesMensuales($fechaInicio, $fechaFin, $idDepartamento = null)
    {
        try {
            if (!strtotime($fechaInicio)) { // <-- Corrige el paréntesis aquí
                $fechaInicio = date('Y-m-01');
            }
            if (!strtotime($fechaFin)) {
                $fechaFin = date('Y-m-t');
            }

            // Determinar si el rango de fechas es menor a 3 meses (mostrar por semana) o mayor (mostrar por mes)
            $diff = (strtotime($fechaFin) - strtotime($fechaInicio)) / (60 * 60 * 24);
            $agruparPor = ($diff <= 90) ? 'SEMANA' : 'MES'; // Si el rango es <= 90 días, agrupar por semana

            $query = "
                SELECT 
                    es.nombreEstado AS estado,
                    COUNT(a.idActividad) AS cantidad,
                    " . ($agruparPor === 'SEMANA' ?
                "CONCAT('Semana ', WEEK(a.fechaInicio, 1), ' ', YEAR(a.fechaInicio)) AS periodo" :
                "CONCAT(MONTHNAME(a.fechaInicio), ' ', YEAR(a.fechaInicio)) AS periodo") . ",
                    " . ($agruparPor === 'SEMANA' ?
                "WEEK(a.fechaInicio, 1) AS orden" :
                "YEAR(a.fechaInicio) * 100 + MONTH(a.fechaInicio) AS orden") . "
                FROM 
                    actividades a
                JOIN 
                    estadoActividad es ON a.idEstado = es.idEstado
                JOIN 
                    categoriasactividades c ON a.idCategoria = c.idCategoria
                WHERE 
                    a.fechaInicio BETWEEN ? AND ?
            ";

            $params = [$fechaInicio, $fechaFin];
            $types = "ss";

            if ($idDepartamento !== null) {
                $query .= " AND c.idDepartamento = ?";
                $params[] = $idDepartamento;
                $types .= "i";
            }

            $query .= "
                GROUP BY 
                    es.nombreEstado, periodo, orden
                ORDER BY 
                    orden, es.nombreEstado
            ";

            $stmt = $this->db->getConnection()->prepare($query);
            if (!$stmt) {
                throw new Exception("Error al preparar la consulta: " . $this->db->getConnection()->error);
            }

            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();

            $datos = [];
            while ($row = $result->fetch_assoc()) {
                $datos[] = $row;
            }

            $stmt->close();

            // Mapeo de nombres de estado de BD a lo que espera la vista
            $mapeoEstados = [
                'Pendiente' => 'Por iniciar',
                'Por Iniciar' => 'Por iniciar',
                'Retraso' => 'En retraso',
                'Completada' => 'Completada',
                'Cancelada' => 'Cancelada',
                'En progreso' => 'En progreso'
            ];

            $periodos = [];
            foreach ($datos as $dato) {
                $periodo = $dato['periodo'];

                if (!isset($periodos[$periodo])) {
                    $periodos[$periodo] = [
                        'periodo' => $periodo,
                        'Completada' => 0,
                        'Cancelada' => 0,
                        'En progreso' => 0,
                        'Por iniciar' => 0,
                        'En retraso' => 0,
                        'total' => 0
                    ];
                }

                $estadoVista = $mapeoEstados[$dato['estado']] ?? null;

                if ($estadoVista && isset($periodos[$periodo][$estadoVista])) {
                    $periodos[$periodo][$estadoVista] = (int)$dato['cantidad'];
                    $periodos[$periodo]['total'] += (int)$dato['cantidad'];
                }
            }

            return array_values($periodos);
        } catch (Exception $e) {
            throw new Exception("Error al obtener estadísticas semanales/mensuales: " . $e->getMessage());
        }
    }


    public function actualizarEstadosActividades()
    {
        try {
            $fechaActual = date('Y-m-d');

            // 1. Detectar actividades que pasarán a "Retraso"
            $stmtRetraso = $this->db->getConnection()->prepare("
            SELECT idActividad FROM actividades 
            WHERE idEstado = (SELECT idEstado FROM estadoActividad WHERE nombreEstado = 'En progreso')
            AND fechaCulminacion < ?
        ");
            $stmtRetraso->bind_param("s", $fechaActual);
            $stmtRetraso->execute();
            $resultRetraso = $stmtRetraso->get_result();
            $actividadesRetraso = [];
            while ($row = $resultRetraso->fetch_assoc()) {
                $actividadesRetraso[] = $row['idActividad'];
            }
            $stmtRetraso->close();

            // Cambiar estado a "Retraso"
            $stmtUpdateRetraso = $this->db->getConnection()->prepare("
            UPDATE actividades 
            SET idEstado = (SELECT idEstado FROM estadoActividad WHERE nombreEstado = 'Retraso')
            WHERE idEstado = (SELECT idEstado FROM estadoActividad WHERE nombreEstado = 'En progreso')
            AND fechaCulminacion < ?
        ");
            $stmtUpdateRetraso->bind_param("s", $fechaActual);
            $stmtUpdateRetraso->execute();
            $stmtUpdateRetraso->close();

            // Registrar en historial el cambio a "Retraso"
            foreach ($actividadesRetraso as $idActividad) {
                $this->registrarCambioEnHistorial(
                    $idActividad,
                    "Cambio de estado",
                    "La actividad pasó a estado 'Retraso' por superar la fecha de culminación."
                );
            }

            // 2. Cambiar estado a "En progreso" si la fecha de inicio ya llegó y no está completada/cancelada/retrasada
            $stmtEnProgreso = $this->db->getConnection()->prepare("
            UPDATE actividades 
            SET idEstado = (SELECT idEstado FROM estadoActividad WHERE nombreEstado = 'En progreso')
            WHERE idEstado = (SELECT idEstado FROM estadoActividad WHERE nombreEstado = 'Por iniciar')
            AND fechaInicio <= ?
            AND idEstado NOT IN (
                SELECT idEstado FROM estadoActividad WHERE nombreEstado IN ('Completada', 'Cancelada', 'Retraso')
            )
        ");
            $stmtEnProgreso->bind_param("s", $fechaActual);
            $stmtEnProgreso->execute();
            $stmtEnProgreso->close();

            // 3. Cambiar estado a "Por iniciar" si la fecha de inicio aún no ha llegado y no está completada/cancelada/retrasada
            $stmtPorIniciar = $this->db->getConnection()->prepare("
            UPDATE actividades 
            SET idEstado = (SELECT idEstado FROM estadoActividad WHERE nombreEstado = 'Por iniciar')
            WHERE fechaInicio > ?
            AND idEstado NOT IN (
                SELECT idEstado FROM estadoActividad WHERE nombreEstado IN ('Completada', 'Cancelada', 'Retraso')
            )
        ");
            $stmtPorIniciar->bind_param("s", $fechaActual);
            $stmtPorIniciar->execute();
            $stmtPorIniciar->close();

            return true;
        } catch (Exception $e) {
            throw new Exception("Error al actualizar estados de actividades: " . $e->getMessage());
        }
    }

    public function contarActividadesActivasPorEmpleado($idEmpleado)
    {
        try {
            $stmt = $this->db->getConnection()->prepare("
            SELECT COUNT(*) as total 
            FROM actividades 
            WHERE idEmpleado = ? 
            AND idEstado IN (
                SELECT idEstado FROM estadoActividad 
                WHERE nombreEstado IN ('Por iniciar', 'En progreso', 'Retraso')
            )
        ");

            if (!$stmt) {
                throw new Exception("Error al preparar la consulta: " . $this->db->getConnection()->error);
            }

            $stmt->bind_param("i", $idEmpleado);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            return (int)$row['total'];
        } catch (Exception $e) {
            throw new Exception("Error al contar actividades del empleado: " . $e->getMessage());
        } finally {
            if (isset($stmt)) {
                $stmt->close();
            }
        }
    }

    public function obtenerHistorialActividad($idActividad)
    {
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

    public function obtenerLimiteActividadesPorEmpleado($idEmpleado)
    {
        try {
            $query = "
            SELECT c.limiteActividades
            FROM empleados e
            JOIN cargos c ON e.idCargo = c.idCargo
            WHERE e.idEmpleado = ?
        ";

            $stmt = $this->db->getConnection()->prepare($query);
            if (!$stmt) {
                throw new Exception("Error al preparar la consulta: " . $this->db->getConnection()->error);
            }

            $stmt->bind_param("i", $idEmpleado);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                throw new Exception("No se encontró el límite de actividades para el empleado.");
            }

            return $result->fetch_assoc()['limiteActividades'];
        } catch (Exception $e) {
            throw new Exception("Error al obtener el límite de actividades: " . $e->getMessage());
        }
    }

    public function registrarCambioEnHistorial($idActividad, $evento, $detalles)
    {
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

    public function obtenerActividadesFiltradas($estado = 'todos', $fechaInicio = '', $fechaFin = '', $categoria = 'todas', $idDepartamento = null)
    {
        try {
            $query = "
            SELECT 
                a.idActividad,
                a.nombreActividad,
                a.descripcionActividad,
                a.fechaInicio,
                a.fechaCulminacion,
                a.idEmpleado,           -- <--- AGREGA ESTO
                a.idCategoria,          -- <--- Y ESTO
                e.nombres AS nombreEmpleado,
                es.nombreEstado AS estadoActividad,
                c.nombreCategoria AS categoriaActividad,
                a.descripcionCancelacion,
                a.descripcionCulminacion,
                c.idDepartamento
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

            // Filtro por departamento si se especifica
            if ($idDepartamento !== null) {
                $conditions[] = "c.idDepartamento = ?";
                $params[] = $idDepartamento;
                $types .= 'i';
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

    public function obtenerTodasCategorias()
    {
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
    public function obtenerEstadisticasTrimestrales($fechaInicio, $fechaFin, $idDepartamento = null)
    {
        try {
            if (!strtotime($fechaInicio)) {
                $fechaInicio = date('Y-m-01', strtotime('-3 months'));
            }
            if (!strtotime($fechaFin)) {
                $fechaFin = date('Y-m-t');
            }

            $query = "
                SELECT 
                    es.nombreEstado AS estado,
                    COUNT(a.idActividad) AS cantidad,
                    CONCAT('T', QUARTER(a.fechaInicio), ' ', YEAR(a.fechaInicio)) AS periodo,
                    YEAR(a.fechaInicio) * 100 + QUARTER(a.fechaInicio) AS orden
                FROM 
                    actividades a
                JOIN 
                    estadoActividad es ON a.idEstado = es.idEstado
                JOIN 
                    categoriasactividades c ON a.idCategoria = c.idCategoria
                WHERE 
                    a.fechaInicio BETWEEN ? AND ?
            ";

            $params = [$fechaInicio, $fechaFin];
            $types = "ss";

            if ($idDepartamento !== null) {
                $query .= " AND c.idDepartamento = ?";
                $params[] = $idDepartamento;
                $types .= "i";
            }

            $query .= "
                GROUP BY 
                    es.nombreEstado, periodo, orden
                ORDER BY 
                    orden, es.nombreEstado
            ";

            $stmt = $this->db->getConnection()->prepare($query);
            if (!$stmt) {
                throw new Exception("Error al preparar la consulta: " . $this->db->getConnection()->error);
            }

            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();

            $datos = [];
            while ($row = $result->fetch_assoc()) {
                $datos[] = $row;
            }

            $stmt->close();

            // Mapeo de nombres de estado de BD a lo que espera la vista
            $mapeoEstados = [
                'Pendiente' => 'Por iniciar',
                'Por Iniciar' => 'Por iniciar',
                'Retraso' => 'En retraso',
                'Completada' => 'Completada',
                'Cancelada' => 'Cancelada',
                'En progreso' => 'En progreso'
            ];

            $periodos = [];
            foreach ($datos as $dato) {
                $periodo = $dato['periodo'];

                if (!isset($periodos[$periodo])) {
                    $periodos[$periodo] = [
                        'periodo' => $periodo,
                        'Completada' => 0,
                        'Cancelada' => 0,
                        'En progreso' => 0,
                        'Por iniciar' => 0,
                        'En retraso' => 0,
                        'total' => 0
                    ];
                }

                $estadoVista = $mapeoEstados[$dato['estado']] ?? null;

                if ($estadoVista && isset($periodos[$periodo][$estadoVista])) {
                    $periodos[$periodo][$estadoVista] = (int)$dato['cantidad'];
                    $periodos[$periodo]['total'] += (int)$dato['cantidad'];
                }
            }

            return array_values($periodos);
        } catch (Exception $e) {
            throw new Exception("Error al obtener estadísticas trimestrales: " . $e->getMessage());
        }
    }

    public function obtenerEstadisticasActividades($idDepartamento = null)
    {
        try {
            $query = "
            SELECT 
                es.nombreEstado,
                COUNT(a.idActividad) as cantidad
            FROM 
                actividades a
            JOIN 
                estadoActividad es ON a.idEstado = es.idEstado
            JOIN 
                categoriasactividades c ON a.idCategoria = c.idCategoria
        ";

            $conditions = [];
            $params = [];
            $types = '';

            if ($idDepartamento !== null) {
                $conditions[] = "c.idDepartamento = ?";
                $params[] = $idDepartamento;
                $types = 'i';
            }

            if (!empty($conditions)) {
                $query .= " WHERE " . implode(" AND ", $conditions);
            }

            $query .= " GROUP BY es.nombreEstado";

            $stmt = $this->db->getConnection()->prepare($query);

            if (!$stmt) {
                throw new Exception("Error al preparar la consulta: " . $this->db->getConnection()->error);
            }

            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }

            $stmt->execute();
            $result = $stmt->get_result();

            $estadisticas = [];
            $total = 0;

            while ($row = $result->fetch_assoc()) {
                $estadisticas[$row['nombreEstado']] = (int)$row['cantidad'];
                $total += (int)$row['cantidad'];
            }

            $estadisticas['total'] = $total;

            return $estadisticas;
        } catch (Exception $e) {
            throw new Exception("Error al obtener estadísticas de actividades: " . $e->getMessage());
        }
    }

    public function obtenerActividadesParaCalendario($idDepartamento = null)
    {
        try {
            $query = "
            SELECT 
                a.idActividad,
                a.nombreActividad,
                a.descripcionActividad,
                a.fechaInicio,
                a.fechaCulminacion,
                CONCAT(e.nombres, ' ', e.apellidos) as nombreEmpleado,
                es.nombreEstado,
                c.nombreCategoria,
                c.idDepartamento
            FROM 
                actividades a
            JOIN 
                empleados e ON a.idEmpleado = e.idEmpleado
            JOIN 
                estadoActividad es ON a.idEstado = es.idEstado
            JOIN 
                categoriasactividades c ON a.idCategoria = c.idCategoria
        ";

            $conditions = [];
            $params = [];
            $types = '';

            if ($idDepartamento !== null) {
                $conditions[] = "c.idDepartamento = ?";
                $params[] = $idDepartamento;
                $types = 'i';
            }

            if (!empty($conditions)) {
                $query .= " WHERE " . implode(" AND ", $conditions);
            }

            $query .= " ORDER BY a.fechaInicio";

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
            throw new Exception("Error al obtener actividades para calendario: " . $e->getMessage());
        }
    }
}
