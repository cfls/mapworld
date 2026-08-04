# Plan de Proyecto: CountryWorld — Señas de los Países del Mundo

## 1. Objetivo general

Crear una plataforma web accesible donde una persona sorda pueda seleccionar cualquier país del mundo (mediante lista o mapa interactivo) y visualizar dos videos:

- **Video en LSFB** (Langue des Signes Francophone de Belgique)
- **Video en Señas Internacionales (International Sign)**

Los países se organizan por continente: **África, América, Asia, Europa, Oceanía**.

---

## 2. Alcance funcional (Requisitos)

### Funcionalidades principales
1. **Filtro por continente**: el usuario elige un continente (África, América, Asia, Europa, Oceanía o "Todos") y la vista se actualiza reactivamente (Livewire) mostrando solo los países de ese continente, tanto en la lista como resaltados/filtrados en el mapa.
2. Listado de países filtrable por continente.
3. Buscador de países (autocompletado).
4. Mapa mundial interactivo (Leaflet) donde el usuario hace clic sobre un país y accede a su ficha.
4. Ficha de país con:
   - Nombre del país (y nombre en el idioma local, opcional)
   - Bandera
   - Continente
   - Video LSFB (reproductor embebido)
   - Video Señas Internacionales (reproductor embebido)
5. Reactividad total sin recargar la página (Livewire).
6. Panel de administración (CRUD) para gestionar países, continentes y videos.
7. Accesibilidad: subtítulos/transcripciones opcionales, navegación por teclado, contraste alto, textos alternativos.

### Funcionalidades opcionales (fase 2)
- Favoritos / países marcados por el usuario.
- Modo "quiz" para practicar señas de países.
- Estadísticas de videos más vistos.

> Nota: se **omite el multilenguaje** de este proyecto (la web funcionará en un único idioma).

---

## 3. Arquitectura tecnológica

| Capa | Tecnología |
|---|---|
| Backend | Laravel 13 |
| Interactividad | Livewire 3 |
| Mapa | Leaflet.js + GeoJSON (world countries) |
| Base de datos | MySQL (o MariaDB) — `CountryWorld` |
| Almacenamiento de videos | **Cloudinary** (subida, transformación y streaming de video en la nube) |
| Frontend/estilos | Tailwind CSS (integra bien con Livewire) |
| Autenticación admin | Laravel Breeze o Jetstream (con Livewire) |

---

## 4. Diseño de la base de datos: `CountryWorld`

### Tablas principales

**`continents`**
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| name | string | África, América, Asia, Europa, Oceanía |
| slug | string | único |

**`countries`**
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| continent_id | FK → continents | |
| name | string | Nombre del país |
| iso_code | string(3) | Código ISO (ej: BEL, FRA) |
| flag_path | string | Ruta o URL de la bandera |
| latitude | decimal | Para centrar el mapa |
| longitude | decimal | |
| geojson_id | string | ID que coincide con el GeoJSON de Leaflet |
| slug | string | único, para URLs amigables |

**`sign_videos`**
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| country_id | FK → countries | |
| type | enum('lsfb','international') | Distingue los dos tipos de video |
| cloudinary_public_id | string | ID público del recurso en Cloudinary (para gestionarlo/borrarlo vía API) |
| cloudinary_url | string | URL segura (`secure_url`) devuelta por Cloudinary, lista para el `<video>` |
| thumbnail_url | string | nullable — Cloudinary puede generar automáticamente una miniatura del video |
| duration_seconds | integer | nullable |
| created_at / updated_at | timestamps | |

> Paquete recomendado: `cloudinary-labs/cloudinary-laravel`. Variables en `.env`:
> ```
> CLOUDINARY_URL=cloudinary://<api_key>:<api_secret>@<cloud_name>
> ```

> Usar `type` como enum permite mantener **una sola tabla de videos** en vez de duplicar columnas (`video_lsfb`, `video_intl`), lo cual es más limpio y escalable si en el futuro se añaden más tipos de seña (ej. ASL, LSF de Francia, etc.)

### Relaciones (Eloquent)

```php
// Continent.php
public function countries() {
    return $this->hasMany(Country::class);
}

// Country.php
public function continent() {
    return $this->belongsTo(Continent::class);
}
public function signVideos() {
    return $this->hasMany(SignVideo::class);
}
public function lsfbVideo() {
    return $this->hasOne(SignVideo::class)->where('type', 'lsfb');
}
public function internationalVideo() {
    return $this->hasOne(SignVideo::class)->where('type', 'international');
}

// SignVideo.php
public function country() {
    return $this->belongsTo(Country::class);
}
```

---

## 5. Componentes Livewire

| Componente | Función |
|---|---|
| `WorldMap` | Renderiza Leaflet, escucha clics sobre países, emite evento a Laravel |
| `CountryList` | Lista de países filtrable por continente + buscador |
| `ContinentFilter` | Tabs/botones (África, América, Asia, Europa, Oceanía, Todos). Al seleccionar, emite `continentSelected` y filtra en vivo tanto la lista (`CountryList`) como los países visibles/resaltados en el mapa (`WorldMap`) |
| `CountryDetail` | Muestra ficha del país con los 2 videos |
| `Admin\CountryManager` | CRUD de países (panel admin) |
| `Admin\VideoUploader` | Subida y asociación de videos LSFB / Internacional |

### Flujo de interacción (mapa)
1. Leaflet se inicializa en un contenedor HTML dentro del componente Livewire (usando `wire:ignore` para que Livewire no reescriba el DOM del mapa).
2. Al hacer clic en un país del GeoJSON, se dispara un evento JS.
3. Ese evento llama a `Livewire.dispatch('countrySelected', { id: countryId })`.
4. El componente `CountryDetail` escucha el evento y carga los datos vía Livewire (`#[On('countrySelected')]`).

### Flujo de interacción (filtro por continente)
1. El usuario hace clic en un tab/botón de continente en `ContinentFilter`.
2. Livewire actualiza una propiedad pública `selectedContinent` (ej. `'europa'` o `null` para "Todos").
3. `CountryList` recalcula la colección de países mostrada (`Country::where('continent_id', ...)`).
4. Se emite `continentSelected` hacia `WorldMap`, que aplica estilo diferenciado en el GeoJSON:
   - Países del continente seleccionado → color activo.
   - Resto de países → atenuados (opacidad baja) pero siguen siendo clicables.
5. Todo ocurre **sin recargar la página**, gracias a `wire:model.live` / eventos de Livewire.

```php
// ContinentFilter.php
class ContinentFilter extends Component
{
    public ?int $selectedContinentId = null;

    public function selectContinent(?int $continentId): void
    {
        $this->selectedContinentId = $continentId;
        $this->dispatch('continentSelected', continentId: $continentId);
    }

    public function render()
    {
        return view('livewire.continent-filter', [
            'continents' => Continent::all(),
        ]);
    }
}
```

---

## 6. Integración de Leaflet

- Usar un archivo **GeoJSON de fronteras mundiales** (ej. Natural Earth o el dataset público `world-countries.json`).
- Cada `feature.id` del GeoJSON debe coincidir con el campo `geojson_id` de la tabla `countries` para hacer el match.
- Estilizar el mapa: países con color neutro, hover con highlight, click con color de selección.
- Mostrar tooltip con el nombre del país al pasar el mouse.

```js
L.geoJSON(worldData, {
  style: defaultStyle,
  onEachFeature: (feature, layer) => {
    layer.on({
      mouseover: highlightFeature,
      mouseout: resetHighlight,
      click: (e) => {
        Livewire.dispatch('countrySelected', { id: feature.properties.id });
      }
    });
  }
}).addTo(map);
```

---

## 7. Diseño visual (Blade) — Mapa responsive

El mapa debe ocupar **todo el ancho disponible** (`w-full`) y adaptar su alto según el dispositivo, usando Tailwind CSS.

### Estructura de layout general (`resources/views/livewire/world-map.blade.php`)

```blade
<div class="w-full flex flex-col gap-4">

    {{-- Filtro de continentes --}}
    <livewire:continent-filter />

    {{-- Contenedor del mapa: ancho completo, alto adaptable --}}
    <div
        wire:ignore
        id="map"
        class="w-full rounded-xl shadow-md
               h-[300px]      {{-- mobile --}}
               sm:h-[400px]   {{-- tablet pequeño --}}
               md:h-[500px]   {{-- tablet --}}
               lg:h-[650px]   {{-- desktop --}}
               xl:h-[750px]"  {{-- desktop grande --}}
    ></div>

    {{-- Ficha del país seleccionado (aparece debajo en mobile, al lado en desktop si se desea 2 columnas) --}}
    <livewire:country-detail />
</div>
```

### Comportamiento por dispositivo

| Dispositivo | Ancho (Tailwind) | Comportamiento |
|---|---|---|
| Mobile (`< sm`, ~375–640px) | `w-full`, `h-[300px]` | Mapa arriba, lista/ficha del país debajo apiladas (`flex-col`). Zoom táctil habilitado en Leaflet. |
| Tablet (`sm`–`md`, 640–1024px) | `w-full`, `h-[400–500px]` | Igual disposición vertical, mapa más alto, filtro de continentes en fila horizontal con scroll si es necesario. |
| Desktop (`lg`+, 1024px+) | `w-full`, `h-[650–750px]` | Opción de layout en 2 columnas: mapa a la izquierda (`lg:w-2/3`) y ficha/lista a la derecha (`lg:w-1/3`), usando `lg:flex-row`. |

### Ejemplo de layout de 2 columnas en desktop

```blade
<div class="w-full flex flex-col lg:flex-row gap-4">
    <div class="w-full lg:w-2/3">
        <livewire:world-map />
    </div>
    <div class="w-full lg:w-1/3">
        <livewire:country-detail />
    </div>
</div>
```

### Detalles técnicos importantes para Leaflet responsive

- Leaflet necesita conocer el tamaño real de su contenedor. Si el contenedor cambia de tamaño por un breakpoint (por ejemplo al rotar el dispositivo), llamar a:
  ```js
  map.invalidateSize();
  ```
  Esto se debe ejecutar en un `resize` listener o tras cambios de layout con Livewire (`Livewire.hook('morph.updated', ...)`).
- Usar `wire:ignore` en el `<div id="map">` es obligatorio: evita que Livewire destruya el DOM que Leaflet controla al hacer re-renders.
- El reproductor de video (LSFB / Internacional) dentro de `CountryDetail` también debe ser `w-full` con `aspect-video` (Tailwind) para mantener proporción en cualquier dispositivo. El `src` apunta directamente a la `secure_url` de Cloudinary:
  ```blade
  <video class="w-full aspect-video rounded-lg" controls preload="metadata"
         poster="{{ $lsfbVideo->thumbnail_url }}">
      <source src="{{ $lsfbVideo->cloudinary_url }}" type="video/mp4">
  </video>
  ```

---

## 8. Estructura de carpetas (resumen Laravel)

```
app/
 ├── Livewire/
 │    ├── WorldMap.php
 │    ├── CountryList.php
 │    ├── CountryDetail.php
 │    └── Admin/
 │         ├── CountryManager.php
 │         └── VideoUploader.php
 ├── Models/
 │    ├── Continent.php
 │    ├── Country.php
 │    └── SignVideo.php
database/
 ├── migrations/
 │    ├── create_continents_table.php
 │    ├── create_countries_table.php
 │    └── create_sign_videos_table.php
 └── seeders/
      ├── ContinentSeeder.php
      └── CountrySeeder.php
resources/
 ├── views/livewire/
 └── js/leaflet-map.js
public/
 └── geojson/world-countries.json
storage/app/public/videos/
```

---

## 9. Fases del proyecto (Roadmap)

**Fase 1 — Base técnica**
- Instalar Laravel + Livewire + Tailwind.
- Crear migraciones y modelos (`continents`, `countries`, `sign_videos`).
- Seeder inicial con los ~195 países y sus continentes.

**Fase 2 — Mapa interactivo**
- Integrar Leaflet con GeoJSON mundial.
- Conectar clics del mapa con Livewire.

**Fase 3 — Ficha de país**
- Vista de detalle con reproductor de video (LSFB + Internacional).
- Fallback si un país aún no tiene video cargado.

**Fase 4 — Panel de administración**
- CRUD de países y continentes.
- Subida de videos (con validación de formato/tamaño).

**Fase 5 — Accesibilidad y pulido**
- Subtítulos/transcripciones.
- Navegación por teclado.
- Pruebas con usuarios sordos reales (muy recomendable).

**Fase 6 — Despliegue**
- Configurar storage en la nube (S3) para videos.
- Optimización de carga (lazy load de videos, CDN).

---

## 10. Consideraciones de accesibilidad (clave para este proyecto)

- Todo el contenido informativo debe ser visual, no depender del audio.
- Los videos deben tener buena resolución de manos y expresión facial (ambas son gramaticalmente importantes en LSFB).
- Evitar autoplay con sonido (no aplica aquí, pero sí autoplay silencioso está bien).
- Etiquetas ARIA y `alt` descriptivos en banderas e íconos.
- Contraste de color alto y tipografía legible.

---

## 11. Siguientes pasos sugeridos

1. Confirmar el dataset de países/continentes a usar (ISO 3166).
2. Decidir si los videos se alojan localmente o en un servicio externo (YouTube privado, Vimeo, S3).
3. Empezar por Fase 1: migraciones + seeders.
