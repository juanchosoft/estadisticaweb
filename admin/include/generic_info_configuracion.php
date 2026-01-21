
<?php
// Informacion del proyecto
$configuracionAplicacion = Util::getInformacionConfiguracion();
$nombreProyecto = '';
$logo = '';
$codigo_departamento = '';
$codigoMunicipioConfiguracion = '';
$pilarConfiguracion = '';
if (!empty($configuracionAplicacion[0])) {
  $nombreProyecto = $configuracionAplicacion[0]['nombre_proyecto'] ?? '';
  $logo = $configuracionAplicacion[0]['logo'] ?? '';
  $codigo_departamento = $configuracionAplicacion[0]['codigo_departamento'] ?? '';
  $codigoMunicipioConfiguracion = $configuracionAplicacion[0]['codigo_municipio'] ?? '';
}
?>


<input type="hidden"  value="<?php echo $codigo_departamento; ?>" id="departamentoConfiguracionInput" name="departamentoConfiguracionInput">