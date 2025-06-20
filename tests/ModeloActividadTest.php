<?php
// filepath: tests/modelos/ModeloActividadTest.php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../modelos/modeloActividad.php';
require_once __DIR__ . '/../modelos/modeloEmpleado.php';

class ModeloActividadTest extends TestCase
{
    private $modelo;

    protected function setUp(): void
    {
        $this->modelo = new modeloActividad();
    }

    public function testInsertarActividad()
    {
        // First get a valid employee ID
        $empleados = (new modeloEmpleado())->obtenerEmpleados();
        if (empty($empleados)) {
            $this->markTestSkipped('No hay empleados para probar.');
            return;
        }

        $nombre = 'Prueba ' . uniqid();
        $idEmpleado = $empleados[0]['idEmpleado']; // Use a valid employee ID
        $idCategoria = 1;
        $id = $this->modelo->insertarActividad($nombre, 'desc', date('Y-m-d'), date('Y-m-d', strtotime('+1 day')), $idEmpleado, $idCategoria);
        $this->assertIsInt($id);
    }

    public function testEditarActividad()
    {
        $actividades = $this->modelo->obtenerActividades();
        if ($actividades) {
            $empleados = (new modeloEmpleado())->obtenerEmpleados();
            if (empty($empleados)) {
                $this->markTestSkipped('No hay empleados para probar.');
                return;
            }

            $act = $actividades[0];
            $result = $this->modelo->editarActividad($act['idActividad'], 'desc editada', $act['fechaInicio'], $act['fechaCulminacion'], $empleados[0]['idEmpleado'], 1);
            $this->assertTrue($result);
        } else {
            $this->markTestSkipped('No hay actividades para editar.');
        }
    }

    public function testObtenerActividades()
    {
        $result = $this->modelo->obtenerActividades();
        $this->assertIsArray($result);
    }

    public function testCancelarActividad()
    {
        $actividades = $this->modelo->obtenerActividades();
        if ($actividades) {
            $act = $actividades[0];
            $result = $this->modelo->cancelarActividad($act['idActividad'], 'Motivo de prueba');
            $this->assertTrue($result);
        } else {
            $this->markTestSkipped('No hay actividades para cancelar.');
        }
    }

    public function testCulminarActividad()
    {
        $actividades = $this->modelo->obtenerActividades();
        if ($actividades) {
            $act = $actividades[0];
            $result = $this->modelo->culminarActividad($act['idActividad'], 'Culminada por prueba');
            $this->assertTrue($result);
        } else {
            $this->markTestSkipped('No hay actividades para culminar.');
        }
    }

    public function testObtenerDetallesActividad()
    {
        $actividades = $this->modelo->obtenerActividades();
        if ($actividades) {
            $act = $actividades[0];
            $result = $this->modelo->obtenerDetallesActividad($act['idActividad']);
            $this->assertIsArray($result);
        } else {
            $this->markTestSkipped('No hay actividades para detalles.');
        }
    }

    public function testObtenerCategoriasPorDepartamento()
    {
        $result = $this->modelo->obtenerCategoriasPorDepartamento(1);
        $this->assertIsArray($result);
    }

    public function testObtenerDepartamentos()
    {
        $result = $this->modelo->obtenerDepartamentos();
        $this->assertIsArray($result);
    }

    public function testObtenerEstadisticasSemanalesMensuales()
    {
        $result = $this->modelo->obtenerEstadisticasSemanalesMensuales(date('Y-m-01'), date('Y-m-t'));
        $this->assertIsArray($result);
    }

    public function testActualizarEstadosActividades()
    {
        $result = $this->modelo->actualizarEstadosActividades();
        $this->assertTrue($result);
    }

    public function testContarActividadesActivasPorEmpleado()
    {
        $result = $this->modelo->contarActividadesActivasPorEmpleado(1);
        $this->assertIsInt($result);
    }

    public function testObtenerHistorialActividad()
    {
        $actividades = $this->modelo->obtenerActividades();
        if ($actividades) {
            $act = $actividades[0];
            $result = $this->modelo->obtenerHistorialActividad($act['idActividad']);
            $this->assertIsArray($result);
        } else {
            $this->markTestSkipped('No hay actividades para historial.');
        }
    }

    public function testObtenerLimiteActividadesPorEmpleado()
    {
        $empleados = (new modeloEmpleado())->obtenerEmpleados();
        if (empty($empleados)) {
            $this->markTestSkipped('No hay empleados para probar.');
            return;
        }

        $result = $this->modelo->obtenerLimiteActividadesPorEmpleado($empleados[0]['idEmpleado']);
        $this->assertIsNumeric($result);
    }

    public function testRegistrarCambioEnHistorial()
    {
        $actividades = $this->modelo->obtenerActividades();
        if ($actividades) {
            $act = $actividades[0];
            $this->modelo->registrarCambioEnHistorial($act['idActividad'], 'Evento prueba', 'Detalles prueba');
            $this->assertTrue(true); // Si no lanza excepción, pasa
        } else {
            $this->markTestSkipped('No hay actividades para historial.');
        }
    }

    public function testObtenerActividadesFiltradas()
    {
        $result = $this->modelo->obtenerActividadesFiltradas();
        $this->assertIsArray($result);
    }

    public function testObtenerTodasCategorias()
    {
        $result = $this->modelo->obtenerTodasCategorias();
        $this->assertIsArray($result);
    }

    public function testObtenerEstadisticasTrimestrales()
    {
        $result = $this->modelo->obtenerEstadisticasTrimestrales(date('Y-m-01', strtotime('-3 months')), date('Y-m-t'));
        $this->assertIsArray($result);
    }

    public function testObtenerEstadisticasActividades()
    {
        $result = $this->modelo->obtenerEstadisticasActividades();
        $this->assertIsArray($result);
    }

    public function testObtenerActividadesParaCalendario()
    {
        $result = $this->modelo->obtenerActividadesParaCalendario();
        $this->assertIsArray($result);
    }

    public function testActividadDuplicadaPorCategoriaEmpleado()
    {
        $result = $this->modelo->actividadDuplicadaPorCategoriaEmpleado(1, 1);
        $this->assertTrue(is_array($result) || $result === false);
    }

    public function testGuardarArchivoActividad()
    {
        $actividades = $this->modelo->obtenerActividades();
        if ($actividades) {
            $act = $actividades[0];
            $idArchivo = $this->modelo->guardarArchivoActividad($act['idActividad'], 'archivo.txt', 'text/plain', '123', '/tmp/archivo.txt');
            $this->assertIsInt($idArchivo);
        } else {
            $this->markTestSkipped('No hay actividades para archivos.');
        }
    }

    public function testObtenerArchivosPorActividad()
    {
        $actividades = $this->modelo->obtenerActividades();
        if ($actividades) {
            $act = $actividades[0];
            $result = $this->modelo->obtenerArchivosPorActividad($act['idActividad']);
            $this->assertIsArray($result);
        } else {
            $this->markTestSkipped('No hay actividades para archivos.');
        }
    }

    public function testEliminarArchivo()
    {
        // Este test requiere que exista un archivo válido en la BD y en el sistema de archivos
        $this->assertTrue(true); // Placeholder, implementa según tu entorno
    }

    public function testObtenerUltimoIdInsertado()
    {
        $result = $this->modelo->obtenerUltimoIdInsertado();
        $this->assertIsInt($result);
    }

    public function testActividadDuplicada()
    {
        $result = $this->modelo->actividadDuplicada('NoExiste', date('Y-m-d'), 1, 1);
        $this->assertFalse($result);
    }
}
