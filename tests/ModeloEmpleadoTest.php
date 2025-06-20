<?php
// filepath: tests/modelos/ModeloEmpleadoTest.php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../modelos/modeloEmpleado.php';

class ModeloEmpleadoTest extends TestCase
{
    private $modelo;

    protected function setUp(): void
    {
        $this->modelo = new modeloEmpleado();
    }

    public function testCrearEmpleado()
    {
        $data = [
            'nombres' => 'Test',
            'apellidos' => 'User',
            'cedula' => '99999999',
            'idCargo' => 1,
            'idDepartamento' => 1,
            'usuarioEmpleado' => 'testuser_' . uniqid(),
            'contrasena' => password_hash('123456', PASSWORD_DEFAULT),
            'idRol' => 1,
            'idEstado' => 1
        ];
        $result = $this->modelo->crearEmpleado($data);
        $this->assertTrue($result);
    }

    public function testObtenerEmpleados()
    {
        $empleados = $this->modelo->obtenerEmpleados();
        $this->assertIsArray($empleados);
    }

    public function testVerificarUsuarioExistente()
    {
        $usuario = 'usuario_no_existente_' . uniqid();
        $result = $this->modelo->verificarUsuarioExistente($usuario);
        $this->assertFalse($result);
    }

    public function testObtenerCargosPorDepartamento()
    {
        $cargos = $this->modelo->obtenerCargosPorDepartamento(1);
        $this->assertIsArray($cargos);
    }

    public function testObtenerCargos()
    {
        $cargos = $this->modelo->obtenerCargos();
        $this->assertIsArray($cargos);
    }

    public function testObtenerDepartamentos()
    {
        $departamentos = $this->modelo->obtenerDepartamentos();
        $this->assertIsArray($departamentos);
    }

    public function testObtenerEmpleadoPorId()
{
    $empleados = $this->modelo->obtenerEmpleados();
    if (!empty($empleados)) {
        $empleado = $this->modelo->obtenerEmpleadoPorId($empleados[0]['idEmpleado']);
        $this->assertIsObject($empleado); 
    } else {
        $this->markTestSkipped('No hay empleados para probar.');
    }
}
    public function testActualizarEmpleado()
    {
        $empleados = $this->modelo->obtenerEmpleados();
        if (!empty($empleados)) {
            $empleado = $empleados[0];
            $empleado['nombres'] = 'Actualizado';
            $result = $this->modelo->actualizarEmpleado($empleado);
            $this->assertTrue($result);
        } else {
            $this->markTestSkipped('No hay empleados para probar.');
        }
    }

    public function testObtenerEstadosEmpleados()
    {
        $estados = $this->modelo->obtenerEstadosEmpleados();
        $this->assertIsArray($estados);
    }

    public function testObtenerTodasActividadesPorEmpleado()
    {
        $empleados = $this->modelo->obtenerEmpleados();
        if (!empty($empleados)) {
            $actividades = $this->modelo->obtenerTodasActividadesPorEmpleado($empleados[0]['idEmpleado']);
            $this->assertIsArray($actividades);
        } else {
            $this->markTestSkipped('No hay empleados para probar.');
        }
    }

    public function testObtenerActividadesPorEmpleado()
    {
        $empleados = $this->modelo->obtenerEmpleados();
        if (!empty($empleados)) {
            $actividades = $this->modelo->obtenerActividadesPorEmpleado($empleados[0]['idEmpleado']);
            $this->assertIsArray($actividades);
        } else {
            $this->markTestSkipped('No hay empleados para probar.');
        }
    }

    public function testVerificarCedula()
    {
        $result = $this->modelo->verificarCedula('00000000');
        $this->assertIsArray($result);
    }

    public function testVerificarUsuario()
    {
        $usuario = 'usuario_no_existente_' . uniqid();
        $result = $this->modelo->verificarUsuario($usuario);
        $this->assertFalse($result);
    }
}