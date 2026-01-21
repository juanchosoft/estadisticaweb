<?php

class MapaConfig
{
  /**
 * Arreglo de constantes de mapeo de departamentos.
 * Ordenado por el código del departamento (clave), de menor a mayor, para facilitar la lectura.
 */
private const MAPAS_DEPARTAMENTOS = [
    // 08: Atlántico
    8 => 'admin/mapa_atlantico/mapa.php',
    // 05: Antioquia 
    5 => 'admin/mapa_antioquia/mapa.php',
    // 11 Bogotá
    11 => 'admin/mapa_bogota/mapa.php',
    // 13: Bolívar
    13 => 'admin/mapa_bolivar/mapa.php',
    // 15: Boyacá
    15 => 'admin/mapa_boyaca/mapa.php',
    // 17: Caldas
    17 => 'admin/mapa_caldas/mapa.php',
    // 18: Caquetá
    18 => 'admin/mapa_caqueta/mapa.php',
    // 19: Cauca
    19 => 'admin/mapa_cauca/mapa.php',
    // 20: Cesar
    20 => 'admin/mapa_cesar/mapa.php',
    // 23: Córdoba
    23 => 'admin/mapa_cordoba/mapa.php',
    // 25: Cundinamarca
    25 => 'admin/mapa_cundinamarca/mapa.php',
    // 27: Chocó
    27 => 'admin/mapa_choco/mapa.php',
    // 41: Huila
    41 => 'admin/mapa_huila/mapa.php',
    // 44: La Guajira
    44 => 'admin/mapa_guajira/mapa.php',
    // 47: Magdalena
    47 => 'admin/mapa_magdalena/mapa.php',
    // 50: Meta
    50 => 'admin/mapa_meta/mapa.php',
    // 52: Nariño
    52 => 'admin/mapa_narino/mapa.php',
    // 54: Norte de Santander
    54 => 'admin/mapa_norte_santander/mapa.php',
    // 63: Quindío
    63 => 'admin/mapa_quindio/mapa.php',
    // 66: Risaralda
    66 => 'admin/mapa_risaralda/mapa.php',
    // 68: Santander
    68 => 'admin/mapa_santander/mapa.php',
    // 70: Sucre
    70 => 'admin/mapa_sucre/mapa.php',
    // 73: Tolima
    73 => 'admin/mapa_tolima/mapa.php',
    // 76: Valle del Cauca
    76 => 'admin/mapa_valle_del_cauca/mapa.php',
    // 81: Arauca
    81 => 'admin/mapa_arauca/mapa.php',
    // 86: Putumayo
    86 => 'admin/mapa_putumayo/mapa.php',
    // Amazonas
    91 => 'admin/mapa_amazonas/mapa.php',
    // 94: Guainía
    94 => 'admin/mapa_guainia/mapa.php',
    // 95: Guaviare
    95 => 'admin/mapa_guaviare/mapa.php',
    // 97: Vaupés
    97 => 'admin/mapa_vaupes/mapa.php',
    // 99: Vichada
    99 => 'admin/mapa_vichada/mapa.php',
];


  /**
   * Obtiene la ruta del mapa según el código del departamento
   *
   * @param int|null $codigoDepartamento
   * @return string Ruta del archivo del mapa
   * @throws InvalidArgumentException Si el departamento no es válido
   */
  public static function obtenerRutaMapa(?int $codigoDepartamento = null): string
  {
    $codigo = $codigoDepartamento ;

    if (empty($codigo)) {
      throw new InvalidArgumentException('Código de departamento no válido');
    }

    return self::MAPAS_DEPARTAMENTOS[$codigo] ?? self::MAPAS_DEPARTAMENTOS[Util::getDepartamentoPrincipal()];
  }

  /**
   * Verifica si existe un mapa para el departamento dado
   */
  public static function existeMapa(int $codigoDepartamento): bool
  {
    return isset(self::MAPAS_DEPARTAMENTOS[$codigoDepartamento]);
  }
}
