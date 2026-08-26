# Plan --- Actualización de `countries`: ISO y banderas

## Objetivo

Actualizar la implementación de países en el proyecto para adaptarla a
la nueva estructura de la tabla `countries`.

La base de datos ya fue actualizada manualmente mediante SQL.

**No crear una migración para modificar nuevamente la base de datos.**

Claude debe revisar y adaptar el código Laravel existente para utilizar
correctamente los nuevos campos.

## Nueva estructura relevante

La tabla `countries` ahora utiliza:

``` text
id
continent_id
name
iso2
iso3
flag_path
latitude
longitude
slug
video_lsfb
video_int
created_at
updated_at
```

El campo antiguo `iso_code` ya no debe utilizarse.

## 1. Revisar el modelo `Country`

Localizar `app/Models/Country.php`.

Revisar cualquier referencia a `iso_code` y reemplazarla según
corresponda por `iso2` o `iso3`.

No asumir que todos los registros tienen `iso2` o `iso3`. Algunas
regiones especiales tienen ambos campos en `NULL`.

## 2. Implementar la lógica de bandera

Para países normales, generar el emoji automáticamente a partir de
`iso2`.

Ejemplos:

``` text
Belgique    → BE → 🇧🇪
Costa Rica  → CR → 🇨🇷
France      → FR → 🇫🇷
Japon       → JP → 🇯🇵
```

No guardar el emoji en la base de datos.

Se puede implementar mediante un accessor en `Country`.

``` php
use Illuminate\Database\Eloquent\Casts\Attribute;

protected function flag(): Attribute
{
    return Attribute::get(function () {
        if (!$this->iso2) {
            return null;
        }

        return collect(str_split(strtoupper($this->iso2)))
            ->map(
                fn ($letter) =>
                    mb_chr(ord($letter) - ord('A') + 0x1F1E6)
            )
            ->implode('');
    });
}
```

Claude puede mejorar esta implementación si existe una solución más
adecuada a la arquitectura actual.

## 3. Regiones con SVG

Algunas entradas no poseen un código ISO 3166-1 propio. Para ellas se
utiliza `flag_path`.

``` text
Açores             → flags/acores.svg
Angleterre         → flags/angleterre.svg
Canaries           → flags/canaries.svg
Ceuta              → flags/ceuta.svg
Crète              → flags/crete.svg
Écosse             → flags/ecosse.svg
Grande-Bretagne    → flags/grande-bretagne.svg
Irlande du Nord    → flags/irlande-du-nord.svg
Kurdistan          → flags/kurdistan.svg
Madère             → flags/madere.svg
Melilla            → flags/melilla.svg
Pays de Galles     → flags/pays-de-galles.svg
Tchétchénie        → flags/tchetchenie.svg
Tibet              → flags/tibet.svg
Transnistrie       → flags/transnistrie.svg
```

Los SVG deben estar disponibles en una ubicación pública apropiada, por
ejemplo `public/flags/`.

## 4. Prioridad para mostrar una bandera

Usar esta lógica:

1.  Si `flag_path` tiene valor → mostrar el SVG.
2.  Si `flag_path` es `NULL` y existe `iso2` → generar emoji Unicode
    desde `iso2`.
3.  Si no existe ninguno → no mostrar bandera.

Una región con `flag_path` debe utilizar su SVG aunque tenga alguna
relación geográfica con otro país.

## 5. Revisar todas las referencias a `iso_code`

Buscar globalmente en el proyecto:

``` text
iso_code
```

Revisar especialmente:

``` text
app/
resources/views/
database/
tests/
```

También revisar componentes Livewire, Blade, API Resources, Controllers,
Services, Seeders, Factories, DTOs, formularios y validaciones.

No hacer reemplazos globales ciegos.

## 6. Actualizar vistas Blade

Buscar dónde se utiliza actualmente `$country->flag_path` o
`$country->iso_code`.

Adaptar las vistas para soportar ambos tipos de bandera.

``` blade
@if ($country->flag_path)
    <img
        src="{{ asset($country->flag_path) }}"
        alt=""
        aria-hidden="true"
        class="..."
    >
@elseif ($country->iso2)
    <span aria-hidden="true">
        {{ $country->flag }}
    </span>
@endif
```

Mantener las clases visuales existentes siempre que sea posible. No
rediseñar la interfaz innecesariamente.

## 7. Accesibilidad

Las banderas deben considerarse decorativas cuando el nombre del país
aparece al lado.

Para SVG:

``` blade
<img
    src="{{ asset($country->flag_path) }}"
    alt=""
    aria-hidden="true"
>
```

Para emoji:

``` blade
<span aria-hidden="true">
    {{ $country->flag }}
</span>

<span>
    {{ $country->name }}
</span>
```

Evitar información redundante para lectores de pantalla.

## 8. `video_lsfb` y `video_int`

Confirmar que el modelo y cualquier formulario relacionado soportan:

``` text
video_lsfb
video_int
```

Ambos pueden ser `NULL`.

No implementar nueva lógica de reproducción si todavía no existe un
requerimiento. Solo garantizar que estos campos no produzcan errores en
modelos, formularios, API o serialización.

## 9. Continentes

Mantener la relación actual:

``` text
1 = Afrique
2 = Amerique
3 = Asie
4 = Europe
5 = Oceanie
```

No cambiar `continent_id`.

Mantener la relación `belongsTo(Continent::class)` existente.

## 10. No modificar los datos actuales

La tabla ya contiene aproximadamente 230 entradas.

Claude no debe:

-   borrar `countries`;
-   ejecutar `migrate:fresh`;
-   recrear los países;
-   cambiar IDs;
-   cambiar `continent_id`;
-   duplicar registros;
-   modificar coordenadas.

El trabajo debe centrarse en adaptar el código Laravel a la nueva
estructura existente.

## 11. Tests

Agregar o actualizar tests para comprobar:

### País normal

``` text
iso2 = BE
flag_path = NULL
```

Debe producir `🇧🇪`.

### Región con SVG

``` text
name = Écosse
iso2 = NULL
flag_path = flags/ecosse.svg
```

Debe utilizar `flags/ecosse.svg`.

### Sin bandera

Con `iso2 = NULL` y `flag_path = NULL`, no debe producir una excepción.

### ISO

Comprobar que el código ya no depende de `iso_code`.

## 12. Verificación final

Antes de terminar:

``` bash
grep -R "iso_code" app resources tests
```

Revisar manualmente cualquier resultado restante.

Ejecutar los tests relevantes del proyecto.

Si el proyecto utiliza Pint:

``` bash
./vendor/bin/pint
```

No modificar código que no esté relacionado con esta tarea.

## Resultado esperado

-   `countries` funciona con `iso2` y `iso3`.
-   Los países normales muestran su bandera mediante emoji Unicode.
-   Las regiones especiales utilizan `flag_path` y SVG.
-   `iso_code` deja de utilizarse en el código de la aplicación.
-   `video_lsfb` y `video_int` son compatibles con el modelo.
-   No se alteran ni duplican los datos existentes.
-   Se mantiene la relación actual con `continents`.
-   La presentación de las banderas es accesible.
