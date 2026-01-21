<?php

function getClaseColorVeredas($color) {
    $clases = [
        "#DC143C" => 'critico',
        "#FFA500" => 'alto',
        "#FEE300" => 'bajo',
        "#62af0a" => 'estable'
    ];

    return $clases[$color] ?? ''; // Retorna la clase correspondiente o una cadena vacía si no coincide
}

function getClasePorcentaje($porcentaje) {
    if ($porcentaje > 0 && $porcentaje <= 0.25) {
        return "medio";
    } elseif ($porcentaje >= 0.26 && $porcentaje <= 0.5) {
        return "bajo";
    } elseif ($porcentaje > 0.51) {
        return "estable";
    }

    return "neutro"; // Valor predeterminado
}

function getColorByNum($num) {
    if ($num >= 0 && $num <= 0) {
        return "#FFFFFF"; // Blanco
    } elseif ($num >= 1 && $num <= 2) {
        return "#DC143C"; // Rojo
    } elseif ($num >= 3 && $num <= 4) {
        return "#FFA500"; // Naranja
    } elseif ($num >= 5 && $num <= 6) {
        return "#4169E1"; // Azul
    } elseif ($num >= 7 && $num <= 99999) {
        return '#62af0a'; // Verde
    }

    return ""; // Color predeterminado si no coincide con ningún rango
}

function getColorOpcion($num) {
    return $num > 0 ? '#62af0a' : Util::getColorNeutroMapa();
}

function getColorByNumPAE($num) {
    if ($num == 0) {
        return Util::getColorNeutroMapa(); // Blanco
    } elseif ($num >= 1 && $num <= 3) {
        return "#62af0a"; // Verde
    } elseif ($num >= 4 && $num <= 6) {
        return "#4169E1"; // Azul
    } elseif ($num >= 7 && $num <= 10) {
        return "#FFFF00"; // Amarillo
    } elseif ($num >= 11 && $num <= 20) {
        return "#FFA500"; // Naranja
    } elseif ($num >= 21) {
        return "#DC143C"; // Rojo
    }
}


?>
