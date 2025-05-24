<?php

namespace App\Common\Libs;

class UnitConverter
{
    const KG_TO_LBS = 2.20462;
    const LBS_TO_KG = 1 / 2.20462; // Approximately 0.45359237

    /**
     * Converts a mass value between metric (kg) and imperial (lbs) systems.
     *
     * @param float|null $value The numerical mass value to convert.
     * @param string $targetUnitSystem The target unit system ('metric' or 'imperial').
     * @param string $currentUnitSystem The current unit system of the value ('metric' or 'imperial'). Defaults to 'metric'.
     * @return array An associative array with 'value' (rounded to 2 decimal places or null) and 'unit' symbol.
     */
    public static function convertMass($value, $targetUnitSystem, $currentUnitSystem = 'metric')
    {
        if ($value === null || !is_numeric($value)) {
            return ['value' => null, 'unit' => ''];
        }

        $value = (float)$value;
        $convertedValue = $value;
        $unitSymbol = '';

        if ($currentUnitSystem === 'metric' && $targetUnitSystem === 'imperial') {
            $convertedValue = $value * self::KG_TO_LBS;
            $unitSymbol = 'lbs';
        } elseif ($currentUnitSystem === 'imperial' && $targetUnitSystem === 'metric') {
            $convertedValue = $value * self::LBS_TO_KG;
            $unitSymbol = 'kg';
        } elseif ($currentUnitSystem === $targetUnitSystem) {
            if ($currentUnitSystem === 'imperial') {
                $unitSymbol = 'lbs';
            } else { // metric
                $unitSymbol = 'kg';
            }
        } else {
            // This case should ideally not be reached if inputs are validated,
            // but as a fallback, return current system's unit.
            $unitSymbol = self::getMassUnitSymbol($currentUnitSystem);
        }
        
        return ['value' => round($convertedValue, 2), 'unit' => $unitSymbol];
    }

    /**
     * Gets the appropriate mass unit symbol for a given unit system.
     *
     * @param string $unitSystem The unit system ('metric' or 'imperial').
     * @return string The unit symbol ('kg' or 'lbs').
     */
    public static function getMassUnitSymbol($unitSystem)
    {
        if ($unitSystem === 'imperial') {
            return 'lbs';
        }
        return 'kg'; // Default to metric
    }
}
?>
