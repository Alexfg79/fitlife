# 📖 Documentación Exhaustiva de Componentes, Vistas e Interacciones — FitLife

Este documento detalla sistemáticamente cada pantalla, vista, sección y componente de la interfaz de usuario del sistema **FitLife**, explicando su ubicación en el código, su propósito, implementación técnica, eventos e interacciones, y comportamiento responsivo.

---

# Índice de Pantallas y Módulos

1. [Home Page (Landing Page Pública)](#1-home-page-landing-page-pública)
2. [Dashboard Operativo y Centro de Mando](#2-dashboard-operativo-y-centro-de-mando)
3. [Módulo de Autenticación y Cuentas](#3-módulo-de-autenticación-y-cuentas)
4. [Módulo de Socios](#4-módulo-de-socios)
5. [Módulo de Asistencias y Control de Acceso](#5-módulo-de-asistencias-y-control-de-acceso)
6. [Módulo de Rutinas de Entrenamiento](#6-módulo-de-rutinas-de-entrenamiento)
7. [Módulo de Clases Grupales](#7-módulo-de-clases-grupales)
8. [Módulo de Configuración de Usuario](#8-módulo-de-configuración-de-usuario)
9. [Layouts, Navegación Global y Primitivos de UI](#9-layouts-navegación-global-y-primitivos-de-ui)

---

# 1. Home Page (Landing Page Pública)

La página de inicio pública está diseñada para atraer clientes potenciales, exhibir las instalaciones y servicios de la cadena, comparar los planes de membresía e incentivar el registro.

---

### Barra Superior de Contacto (Top Header Bar)
- **Ubicación en el código:** `resources/views/home.blade.php` (Líneas 75-116). Estilos: Tailwind CSS en el mismo archivo. Pruebas: `tests/Feature/HomePageTest.php`.
- **Descripción y Propósito:** Barra delgada superior fijada en el tope visual que presenta canales inmediatos de contacto (teléfono, correo), horario general de operación y enlaces a redes sociales. Genera confianza y facilita soporte inmediato sin necesidad de navegar al pie de página.
- **Cómo se construyó (Implementación):**
  - **Tecnologías:** HTML semántico y utilidades de Tailwind CSS (`bg-lime-500`, `text-zinc-950`). Iconos vectoriales SVG optimizados inline.
  - **Datos dinámicos:** Contenido informativo institucional estructurado.
- **Interacciones y Eventos:**
  - Clic en el número telefónico: Ejecuta protocolo `tel:+18003485433` abriendo el marcador en dispositivos móviles.
  - Clic en el correo: Abre el cliente de correo predeterminado mediante enlace `mailto:contacto@fitlife.test`.
  - Efectos visuales: Transición suave de color en hover (`hover:text-black transition-colors`).
- **Comportamiento Responsivo / Casos Límite:**
  - En pantallas móviles pequeñas (`< sm`), el correo electrónico se oculta con `hidden sm:flex`.
  - Los iconos de redes sociales se ocultan en pantallas menores a 1024px (`hidden lg:flex`) para evitar desbordamientos horizontales.

---

### Barra de Navegación Principal (Navigation Bar)
- **Ubicación en el código:** `resources/views/home.blade.php` (Líneas 118-309). Lógica interactiva en líneas 1091-1223.
- **Descripción y Propósito:** Menú principal flotante y pegajoso (*sticky*) que permite la navegación fluida entre las diferentes secciones mediante ScrollSpy, alternar el tema visual (oscuro/claro) y acceder a la plataforma (Iniciar Sesión / Registro o Acceso al Dashboard/Perfil si ya hay sesión activa).
- **Cómo se construyó (Implementación):**
  - **Tecnologías:** Alpine.js (`x-data="{ menuMovilAbierto: false, esOscuro: ... }"`, `@click`, `x-show`, `x-cloak`). Clases utilitarias de Tailwind CSS para efecto de cristal esmerilado (`backdrop-blur-md`, `bg-zinc-950/90`). Condicionales Blade (`@auth` / `@else`).
  - **Estado:** Controla la visibilidad del cajón móvil (`menuMovilAbierto`) y la preferencia de color (`esOscuro`).
- **Interacciones y Eventos:**
  - Clic en enlaces de ancla (`#nosotros`, `#servicios`, `#sucursales`, `#membresias`, `#clases`): Ejecuta desplazamiento suave (*smooth scroll*) con centrado compensado respecto a la altura del navbar pegajoso.
  - Clic en el interruptor de tema (Sol/Luna): Alterna la clase `dark` en `document.documentElement` y almacena la preferencia en `localStorage.theme`.
  - Clic en el botón hamburguesa: Abre/cierra el menú desplegable móvil con transición de opacidad y escala de Alpine.js.
  - Clic en "Acceder" o en el avatar del usuario: Redirige a `route('login')` o a `route('dashboard')` / `route('mi-perfil')`.
- **Comportamiento Responsivo / Casos Límite:**
  - En pantallas grandes (`lg` en adelante), los enlaces se presentan horizontalmente en una barra central con efecto pastilla.
  - En móviles (`< lg`), se oculta la lista horizontal y se renderiza el botón hamburguesa. Si el nombre del usuario autenticado es extenso, se trunca con `truncate max-w-[120px]`.

---

### Sección Principal (Hero Section)
- **Ubicación en el código:** `resources/views/home.blade.php` (Líneas 311-400).
- **Descripción y Propósito:** Primera impresión visual de alto impacto que comunica la propuesta de valor del gimnasio ("Transforma tu cuerpo, eleva tu vida"), muestra indicadores de confianza (KPIs) y ofrece dos llamados a la acción primarios (CTA).
- **Cómo se construyó (Implementación):**
  - **Tecnologías:** Contenedor relativo con imagen deportiva de alta resolución de Unsplash, superposición de gradientes oscuros (`bg-gradient-to-t`, `mix-blend-multiply`) y tipografía con variantes de escala `text-4xl sm:text-6xl md:text-7xl`.
  - **Datos dinámicos:** El botón principal evalúa con `@auth` si redirige al panel interno (`route('mi-perfil')`) o al formulario de registro (`route('register')`).
- **Interacciones y Eventos:**
  - Hover sobre el botón principal "Comenzar Ahora": Despliega sombra neón lima (`hover:shadow-[0_0_30px_rgba(132,204,22,0.4)]`) y traslación vertical negativa (`hover:-translate-y-0.5`).
  - Hover sobre "Ver Clases": Transición de borde y fondo a blanco/lima tenue. Scroll suave a `#clases`.
- **Comportamiento Responsivo / Casos Límite:**
  - Los botones de acción pasan de apilado vertical (`flex-col`) en móviles a distribución horizontal (`sm:flex-row`).
  - La rejilla de métricas de confianza (4 estadísticas: Atletas Activos, Sedes, Coaches, Clases) conmuta de 2 columnas en móvil a 4 en monitores medianos.

---

### Sección Nosotros
- **Ubicación en el código:** `resources/views/home.blade.php` (Líneas 402-520).
- **Descripción y Propósito:** Explica la filosofía y pilares del gimnasio (entrenamiento de alto rendimiento, tecnología de vanguardia, coaches certificados y comunidad activa) combinando una composición multimedia con tarjetas informativas.
- **Cómo se construyó (Implementación):**
  - **Tecnologías:** Grid de 12 columnas en Tailwind (`grid lg:grid-cols-12`). Marcos con gradientes y tarjetas translúcidas.
  - **Datos:** Tarjeta flotante con distintivo de "+10 Años de Experiencia".
- **Interacciones y Eventos:**
  - Hover sobre la imagen: Efecto de zoom suave sobre la imagen del atleta (`group-hover:scale-105 transition-transform duration-700`).
  - Hover sobre cada pilar: El borde pasa de gris a `hover:border-lime-500/50` con iluminación de fondo.
- **Comportamiento Responsivo / Casos Límite:**
  - En móviles se colapsa a una única columna. El badge de experiencia flotante se oculta en pantallas muy pequeñas (`hidden sm:block`) para no bloquear la imagen.

---

### Sección Servicios
- **Ubicación en el código:** `resources/views/home.blade.php` (Líneas 522-771).
- **Descripción y Propósito:** Despliega el catálogo de las 6 áreas de servicio disponibles: Musculación Pesada, Clases Grupales, Asesoría Nutricional, Área Cardiovascular, Zona Funcional / CrossFit y Recuperación / Sauna.
- **Cómo se construyó (Implementación):**
  - **Tecnologías:** Rejilla CSS responsiva (`sm:grid-cols-2 lg:grid-cols-3`). Tarjetas con altura calculada completa (`flex flex-col justify-between`) y badges por servicio.
- **Interacciones y Eventos:**
  - Hover sobre cada tarjeta: Elevación (`hover:-translate-y-1`), aumento de sombra (`shadow-xl`) y escalado del icono vectorial (`group-hover:scale-110`).
- **Comportamiento Responsivo / Casos Límite:**
  - Rejilla adaptativa fluida (1 columna en móviles, 2 en tablets y 3 en pantallas de escritorio).

---

### Sección Sucursales
- **Ubicación en el código:** `resources/views/home.blade.php` (Líneas 773-835).
- **Descripción y Propósito:** Presenta las sedes físicas de la cadena (Central, Norte, Premium) con su dirección, ciudad, horario de atención y estado de apertura en tiempo real.
- **Cómo se construyó (Implementación):**
  - **Tecnologías:** Iteración dinámica de Blade `@foreach ($sucursales as $sucursal)`. Indicadores de actividad animados con `animate-ping` de Tailwind.
  - **Datos:** Inyectados desde `HomeController` (`$sucursales = Sucursal::where('activa', true)->get()`).
- **Interacciones y Eventos:**
  - Hover sobre la tarjeta: Realce del borde en tono lima.
  - Clic en "Conocer Sede / Registrarse": Redirige al registro preseleccionando la sucursal.
- **Comportamiento Responsivo / Casos Límite:**
  - Cuadrícula adaptable. Si el listado de sucursales estuviese vacío en la base de datos, el contenedor simplemente no muestra elementos sin generar errores.

---

### Sección Membresías (Planes de Precios)
- **Ubicación en el código:** `resources/views/home.blade.php` (Líneas 837-892).
- **Descripción y Propósito:** Tabla comparativa de los planes comerciales (Plan Básico a $29, Plan Completo a $49, Plan Premium a $79), listando los beneficios, acceso a clases y horarios permitidos.
- **Cómo se construyó (Implementación):**
  - **Tecnologías:** Iteración Blade `@foreach ($planes as $plan)`. Evaluación lógica de condiciones `$esCompleto` para aplicar el distintivo "MÁS POPULAR" y escala física destacada.
  - **Datos:** Modelos `Plan` obtenidos desde la base de datos con precios formateados con `number_format`.
- **Interacciones y Eventos:**
  - Clic en "Elegir Plan": Redirige a `route('register', ['plan' => $plan->id])`, preseleccionando el plan en el formulario de registro.
  - Hover: La tarjeta eleva su posición y destaca su borde.
- **Comportamiento Responsivo / Casos Límite:**
  - En móviles se despliegan en columna vertical individual. En pantallas de escritorio (`lg`), el Plan Completo aplica `scale-102 lg:-translate-y-2` para destacarse visualmente entre los tres.

---

### Sección Clases Grupales
- **Ubicación en el código:** `resources/views/home.blade.php` (Líneas 894-1004).
- **Descripción y Propósito:** Exhibe la programación de clases colectivas (CrossFit, Spinning, Yoga, Boxeo, Pilates), el instructor asignado, horario, sede y aforo.
- **Cómo se construyó (Implementación):**
  - **Tecnologías:** Directiva `@forelse ($clasesGrupales as $clase)` con extracción de relaciones Eloquent (`$clase->profesor->nombre`, `$clase->sucursal->nombre`).
- **Interacciones y Eventos:**
  - Hover sobre la tarjeta: Despliegue de resplandor sutil.
  - Botón "Inscribirme": Conduce a la cartelera interna o al registro según el estado de sesión.
- **Comportamiento Responsivo / Casos Límite:**
  - En caso de no existir clases cargadas en el sistema, la cláusula `@empty` muestra un estado vacío decorado: "No hay clases grupales registradas por el momento".
  - Los nombres extensos de sucursales o disciplinas se recortan con `truncate max-w-[160px]`.

---

### Pie de Página (Footer)
- **Ubicación en el código:** `resources/views/home.blade.php` (Líneas 1006-1089).
- **Descripción y Propósito:** Cierre corporativo con enlaces a navegación secundaria, políticas de privacidad, términos de servicio, horario de atención y derechos de autor.
- **Cómo se construyó (Implementación):**
  - **Tecnologías:** Cuadrícula de 4 columnas en desktop (`grid-cols-1 md:grid-cols-2 lg:grid-cols-4`) con fondo oscuro `bg-zinc-950` y borde superior fino `border-zinc-800`.
- **Interacciones y Eventos:** Enlaces hipertextuales que dirigen a secciones locales o correos de soporte.
- **Comportamiento Responsivo / Casos Límite:** Colapsa en una sola columna en pantallas móviles sin desbordamientos.

---

### Script Interactivo ScrollSpy & Navigation
- **Ubicación en el código:** `resources/views/home.blade.php` (Líneas 1091-1223).
- **Descripción y Propósito:** Motor en JavaScript Vanilla que detecta la posición del scroll para iluminar automáticamente el enlace activo en la barra de navegación y realizar un desplazamiento suave centrado al hacer clic.
- **Cómo se construyó (Implementación):**
  - **Tecnologías:** JavaScript puro, `IntersectionObserver`, `window.addEventListener('scroll')`, `getBoundingClientRect()`.
- **Interacciones y Eventos:** Intercepta clics en cualquier `a[href^="#"]`, descuenta la altura del navbar flotante y centra verticalmente la sección si su altura es menor a la ventana.
- **Comportamiento Responsivo / Casos Límite:** Si la sección excede la altura del viewport, alinea la cabecera en el tope visible debajo del navbar para no ocultar contenido crítico.

---

# 2. Dashboard Operativo y Centro de Mando

El Dashboard (`resources/js/pages/dashboard.tsx`) es el núcleo operativo de FitLife para administradores y recepcionistas. Contiene ~1,700 líneas de código y centraliza control de acceso, métricas, socios en sala, alertas de facturación y gráficos.

---

### Barra Superior de Aforo en Sala (Occupancy Gauge Bar)
- **Ubicación en el código:** `resources/js/pages/dashboard.tsx` (Líneas 434-452).
- **Descripción y Propósito:** Barra de estado visual en la cabecera del panel que muestra en tiempo real la cantidad de atletas presentes en el gimnasio frente al aforo máximo de seguridad (120 personas).
- **Cómo se construyó (Implementación):**
  - **Tecnologías:** React con Tailwind CSS. Utiliza un estilo dinámico en línea para el ancho de la barra:
    `width: `${Math.max(6, Math.min(100, Math.round(((metricas.total_en_sala ?? 0) / 120) * 100)))}%``
  - **Props:** `metricas.total_en_sala`.
- **Interacciones y Eventos:** Visualización de lectura en tiempo real conectada a los datos de sesión.
- **Comportamiento Responsivo / Casos Límite:** Se oculta en teléfonos móviles con `hidden sm:flex` para maximizar el espacio útil en pantalla reducida. El porcentaje está protegido entre un mínimo del 6% y un máximo del 100%.

---

### Barra de Atajos de Automatización Rápida 1-Click
- **Ubicación en el código:** `resources/js/pages/dashboard.tsx` (Líneas 508-575).
- **Descripción y Propósito:** Conjunto de tres botones de acción rápida ("⚡ Socio Auto", "⚡ Asistencia Auto", "⚡ Rutina Auto") diseñados para poblar la base de datos con datos sintéticos realistas durante demostraciones o pruebas.
- **Cómo se construyó (Implementación):**
  - **Tecnologías:** React `useState` (`generandoSocio`, `generandoAsistencia`, `generandoRutina`), Inertia `router.post`.
  - **Endpoints:** `/socios/generar-automatico`, `/asistencias/generar-automatica`, `/rutinas/generar-automatica`.
- **Interacciones y Eventos:**
  - Clic en cualquiera de los botones: Desactiva temporalmente el botón, sustituye el icono por un spinner giratorio (`Loader2 animate-spin`) y ejecuta una mutación POST conservando la posición de desplazamiento (`preserveScroll: true`).
- **Comportamiento Responsivo / Casos Límite:** Los tres botones se apilan en una fila flexible con `flex-wrap` y consumen el ancho completo en pantallas móviles pequeñas mediante `flex-1`.

---

### Tarjetas de Métricas KPI (4 Indicadores Clave)
- **Ubicación en el código:** `resources/js/pages/dashboard.tsx` (Líneas 578-693).
- **Descripción y Propósito:** Fichas de indicadores clave de rendimiento (KPIs):
  1. **Socios Activos:** Total de socios y porcentaje sobre la base histórica.
  2. **Recaudación Mensual:** Facturación en dólares con comparativa frente al mes anterior.
  3. **Afluencia del Día:** Conteo total de accesos registrados en la fecha.
  4. **Cuotas en Vencimiento:** Membresías próximas a vencer en menos de 5 días.
- **Cómo se construyó (Implementación):**
  - **Tecnologías:** CSS Grid (`grid-cols-1 sm:grid-cols-2 lg:grid-cols-4`). Iconografía de Lucide React (`Users`, `DollarSign`, `Activity`, `Clock`). Clases de sombreado y bordes con efecto de cristal (`glass-card`).
  - **Props:** Objeto `metricas`.
- **Interacciones y Eventos:**
  - Hover sobre las tarjetas: Iluminación de bordes, sombra coloreada y escalado de icono (`group-hover:scale-110 transition-transform`).
- **Comportamiento Responsivo / Casos Límite:** Pasa de 1 columna en móvil a 2 en tablets y 4 en pantallas panorámicas. Si los valores llegan nulos o indefinidos, aplican coalescencia predeterminada (`0` o `$0.00`).

---

### Simulador de Torniquete y Control de Acceso Biométrico
- **Ubicación en el código:** `resources/js/pages/dashboard.tsx` (Líneas 708-1175).
- **Descripción y Propósito:** Terminal virtual para recepcionistas que simula un torniquete físico o lector óptico. Permite ingresar el código o DNI de un socio, seleccionar si es Entrada o Salida, vincularlo a una clase grupal y validar su derecho de acceso en menos de 200 ms.
- **Cómo se construyó (Implementación):**
  - **Tecnologías:** React `useState` (`codigoAcceso`, `procesandoAcceso`, `resultadoAcceso`, `modoPaso`, `tipoAcceso`, `metodoAcceso`, `claseSeleccionadaId`, `modoEmergencia`, `torniqueteAbierto`), Inertia `router.post`.
  - **Endpoints:** `route('asistencias.acceso')` y `route('asistencias.checkout')`.
- **Interacciones y Eventos:**
  - Escribir código/DNI y presionar Enter o "Validar Acceso": Despacha validación al servidor.
  - Al autorizarse el paso: El estado `torniqueteAbierto` pasa a `true`, la barra se ilumina en verde neón, se reproduce el mensaje de éxito y un temporizador `setTimeout` de 5 segundos cierra el torniquete automáticamente.
  - Al denegarse el paso: Se ilumina en rojo escarlata (`rose-500`), expone la causa del rechazo (ej. "Membresía vencida hace 3 días", "2 cuotas pendientes") y presenta un botón de acción rápida para abrir el modal de renovación o cobro.
  - Botones de "Test Rápido": Inyectan códigos de prueba con 1 solo clic.
- **Comportamiento Responsivo / Casos Límite:** Cuadrícula adaptable. Si el socio no existe, emite advertencia de código erróneo con sonido/animación visual sin alterar el estado general de la página.

---

### Credencial Digital 3D (CredencialDigitalSocio)
- **Ubicación en el código:** `resources/js/components/credencial-digital-socio.tsx` (Líneas 1-184). Invocado en `dashboard.tsx` (Línea 1155).
- **Descripción y Propósito:** Carnet digital interactivo en 3D del atleta escaneado. Emula un carnet físico con chip dorado de seguridad, código QR, avatar, fecha de vencimiento y reflejos holográficos al mover el ratón.
- **Cómo se construyó (Implementación):**
  - **Tecnologías:** React con matemáticas de perspectiva CSS en línea (`transform: perspective(1000px) rotateX(...) rotateY(...) scale3d(...)`). Hook `useState` para rotaciones `rotX`, `rotY` y posición del brillo `brillo`.
  - **Props:** `socio` (datos del miembro), `onRenovar` (callback opcional para renovar membresía).
- **Interacciones y Eventos:**
  - `onMouseMove`: Calcula la distancia relativa del cursor al centro geométrico de la tarjeta `(e.clientX - rect.left - rect.width / 2)` para aplicar la inclinación 3D y desplazar la capa de gradiente holográfico.
  - `onMouseLeave`: Restablece suavemente los ángulos de rotación a 0.
  - Clic en "Renovar Ahora" sobre la tarjeta: Invoca la función `onRenovar`.
- **Comportamiento Responsivo / Casos Límite:** Si el socio no dispone de fotografía, solicita dinámicamente un avatar tipográfico desde la API de UI-Avatars. Si la membresía está vencida, el color del carnet muta de verde lima a rojo degradado.

---

### Pestaña de Socios en Sala (Live Room Roster)
- **Ubicación en el código:** `resources/js/pages/dashboard.tsx` (Líneas 1177-1248).
- **Descripción y Propósito:** Listado en tiempo real de todos los socios presentes dentro de las instalaciones (con check-in registrado pero sin check-out).
- **Cómo se construyó (Implementación):**
  - **Tecnologías:** Iteración sobre el array `socios_en_sala`. Contenedor con `max-h-[360px]` y desplazamiento vertical suave `overflow-y-auto`.
- **Interacciones y Eventos:**
  - Botón individual "Registrar Salida": Ejecuta `ejecutarCheckOutSocio` con un POST a `asistencias.checkout` eliminando inmediatamente al socio de la lista de sala.
- **Comportamiento Responsivo / Casos Límite:** Si la sala está vacía (`socios_en_sala.length === 0`), renderiza un estado vacío centrado con icono de gimnasio en reposo.

---

### Pestaña de Alertas de Membresías por Vencer
- **Ubicación en el código:** `resources/js/pages/dashboard.tsx` (Líneas 1317-1594).
- **Descripción y Propósito:** Panel de control de retención de clientes que identifica y agrupa a los socios cuyas membresías vencen en los próximos 5 días o ya se encuentran vencidas.
- **Cómo se construyó (Implementación):**
  - **Tecnologías:** Renderizado dual de Tailwind: tabla semántica para escritorio (`hidden md:table`) y vista de tarjetas apiladas para móvil (`block md:hidden`). Incorpora el componente `BadgeVencimiento`.
  - **Props:** `alertas_membresias`.
- **Interacciones y Eventos:**
  - Clic en "Renovar": Abre el modal emergente de renovación preseleccionando al socio correspondiente (`setSocioSeleccionado(socio)`).
- **Comportamiento Responsivo / Casos Límite:** Adaptación completa entre tabla de 6 columnas en desktop y tarjetas táctiles con botones anchos en dispositivos móviles.

---

### Modal Flotante de Renovación Rápida de Membresía
- **Ubicación en el código:** `resources/js/pages/dashboard.tsx` (Líneas 1596-1694).
- **Descripción y Propósito:** Cuadro de diálogo modal superpuesto que permite a la recepción registrar un nuevo pago de cuota o renovar un plan en menos de 10 segundos sin abandonar el dashboard.
- **Cómo se construyó (Implementación):**
  - **Tecnologías:** Modal flotante renderizado condicionalmente cuando `socioSeleccionado !== null`. Capa de desenfoque de fondo `backdrop-blur-sm bg-black/60`. Manejo de estado con `planRenovacionId`, `metodoPagoRenovacion` y `procesandoRenovacion`.
  - **Endpoints:** Dispara `router.post(route('membresias.store'))`.
- **Interacciones y Eventos:**
  - Clic en los botones de método de pago (Efectivo, Transferencia, Tarjeta, Pago Móvil): Actualiza el selector activo.
  - Clic en "Confirmar Renovación": Envía la solicitud y, tras confirmarse, cierra el modal y limpia el formulario.
  - Clic en la cruz de cierre o en el fondo: Cierra el modal cancelando la operación.
- **Comportamiento Responsivo / Casos Límite:** Altura máxima protegida con `max-h-[90vh]` y desplazamiento interno para garantizar operabilidad en pantallas pequeñas de tablets o teléfonos.

---

### Gráfico de Concurrencia Horaria (GraficoConcurrencia)
- **Ubicación en el código:** `resources/js/components/graficos/grafico-concurrencia.tsx` (Líneas 1-238).
- **Descripción y Propósito:** Gráfico de área fluido que representa la curva de asistencia hora por hora (de 06:00 a 22:00), identificando de forma automática la "Hora Pico" de mayor congestión en la sede.
- **Cómo se construyó (Implementación):**
  - **Tecnologías:** Chart.js con carga dinámica vía CDN en `useEffect`. Envoltorio Canvas referenciado mediante React `useRef`. Adaptación al modo oscuro detectando la clase `.dark` en `document.documentElement`.
  - **Props:** `datos` (etiquetas horarias y valores numéricos de visitas).
- **Interacciones y Eventos:**
  - Puntero sobre los puntos del gráfico: Despliega tooltips flotantes personalizados con estilo oscuro y color lima indicando la cantidad exacta de accesos en esa franja horaria.
- **Comportamiento Responsivo / Casos Límite:** `maintainAspectRatio: false` con altura adaptable controlada por el contenedor padre de Tailwind. Si no existen datos de asistencias en el día, utiliza datos de respaldo (*fallbacks*) sintéticos para no romper el renderizado.

---

### Gráfico de Ingresos por Membresía (GraficoIngresosMembresias)
- **Ubicación en el código:** `resources/js/components/graficos/grafico-ingresos-membresias.tsx` (Líneas 1-255).
- **Descripción y Propósito:** Gráfico interactivo tipo Dona (*Doughnut*) que expone la distribución de la facturación mensual según la categoría de plan contratado (Básico, Completo, Premium).
- **Cómo se construyó (Implementación):**
  - **Tecnologías:** Chart.js (Doughnut controller). Paleta de colores de marca (`#84cc16` Lima, `#06b6d4` Cyan, `#a855f7` Púrpura). Métrica central flotante con posicionamiento absoluto (`absolute inset-0 flex items-center justify-center`).
- **Interacciones y Eventos:**
  - Hover sobre los segmentos: Revela porcentajes exactos calculados matemáticamente con formateo monetario internacional `Intl.NumberFormat`.
- **Comportamiento Responsivo / Casos Límite:** La leyenda de categorías está construida con elementos HTML/Tailwind externos en lugar de usar la leyenda de lienzo de Chart.js, lo que permite que en móviles se apile limpiamente bajo la dona y en desktop se ubique a un costado.

---

### Botón y Protocolo de Evacuación de Emergencia
- **Ubicación en el código:** `resources/js/pages/dashboard.tsx` (Líneas 771-783).
- **Descripción y Propósito:** Mecanismo de seguridad física para abrir de par en par todos los torniquetes virtuales de la sede en caso de incendio, terremoto o contingencia médica, habilitando la salida libre e inmediata.
- **Cómo se construyó (Implementación):**
  - **Tecnologías:** Botón de advertencia con estado booleano `modoEmergencia` (`useState`). Estilos de pulsación con `animate-pulse` en color ámbar/rojo.
- **Interacciones y Eventos:**
  - Clic en "MODO EVACUACIÓN": Alterna el estado activando la apertura irrestricta de torniquetes y emitiendo un banner visual de alerta máxima en todo el centro de control.
- **Comportamiento Responsivo / Casos Límite:** Visible y accesible en cualquier resolución con prioridad visual mediante contraste cromático alto.

---

### Componente BadgeVencimiento
- **Ubicación en el código:** `resources/js/components/badge-vencimiento.tsx` (Líneas 1-61).
- **Descripción y Propósito:** Etiqueta visual de alerta que clasifica con código de colores la cercanía del vencimiento de una suscripción.
- **Cómo se construyó (Implementación):**
  - **Tecnologías:** Componente funcional de React con Tailwind CSS. Calcula internamente si el vencimiento es inminente (`diasRestantes <= 3`).
- **Interacciones y Eventos:** Puramente visual y no interactivo.
- **Comportamiento Responsivo / Casos Límite:**
  - Si la membresía ya caducó: Fondo rojo (`bg-rose-500/10 text-rose-400 border-rose-500/20`) con texto "Vencida hace X días".
  - Si vence hoy: Texto explícito "Vence Hoy" con halo intermitente (`animate-ping`).
  - Si vence en ≤ 3 días: Fondo ámbar parpadeante.
  - Si tiene vigencia normal: Verde oliva sin animación intrusiva.

---

# 3. Módulo de Autenticación y Cuentas

La autenticación de FitLife incluye inicio de sesión, registro unificado en una sola vista con pestañas, medidor de seguridad de clave, selector de membresía y flujos de recuperación de cuenta.

---

### Columna Split Branding (Escritorio)
- **Ubicación en el código:** `resources/js/pages/auth/login.tsx` (Líneas 160-243).
- **Descripción y Propósito:** Panel visual de marca ubicado a la izquierda de la pantalla de autenticación en resoluciones grandes. Presenta fotografía de entrenamiento de alta resolución, frases de disciplina deportiva y tres sellos de confianza institucional (+1,500 Atletas, 99.9% Uptime, <0.2s Validación).
- **Cómo se construyó (Implementación):**
  - **Tecnologías:** Flexbox de Tailwind con visualización condicional de escritorio (`hidden lg:flex lg:w-1/2`). Imagen de fondo con gradientes de oscurecimiento.
- **Interacciones y Eventos:** Animación de pulsación sutil en badges y enlaces interactivos.
- **Comportamiento Responsivo / Casos Límite:** Se oculta por completo en resoluciones inferiores a 1024px (`< lg`), dejando todo el ancho de pantalla disponible para el formulario en teléfonos y tablets.

---

### Cabecera Móvil y Navegación de Retorno
- **Ubicación en el código:** `resources/js/pages/auth/login.tsx` (Líneas 253-280).
- **Descripción y Propósito:** Proporciona un enlace directo para volver a la página de inicio pública y muestra el logotipo del gimnasio en dispositivos móviles donde la columna de branding está ausente.
- **Cómo se construyó (Implementación):**
  - **Tecnologías:** Componente `Link` de Inertia. Icono `Dumbbell` de Lucide. Clases `lg:hidden` vs `hidden lg:flex`.
- **Interacciones y Eventos:** Clic en "Volver al inicio": Redirige a la ruta principal `/`.
- **Comportamiento Responsivo / Casos Límite:** Ajusta automáticamente márgenes para no solaparse con los inputs en teléfonos pequeños.

---

### Conmutador de Pestañas (Tab Switcher Login / Registro)
- **Ubicación en el código:** `resources/js/pages/auth/login.tsx` (Líneas 282-311).
- **Descripción y Propósito:** Selector de doble estado en la parte superior del formulario que permite alternar entre "Iniciar Sesión" y "Crear Cuenta" de forma instantánea sin recargar la página.
- **Cómo se construyó (Implementación):**
  - **Tecnologías:** Estado React `pestañaActiva` (`'login' | 'register'`). Botones estilizados con efecto de interruptor deslizante mediante clases dinámicas de Tailwind.
- **Interacciones y Eventos:**
  - Clic en cualquiera de las pestañas: Invoca `setPestañaActiva(...)`, modificando el estado local y renderizando condicionalmente el formulario respectivo con una animación suave de opacidad.
- **Comportamiento Responsivo / Casos Límite:** Se expande al 100% del ancho del contenedor en cualquier tamaño de pantalla.

---

### Formulario de Inicio de Sesión (Login Form)
- **Ubicación en el código:** `resources/js/pages/auth/login.tsx` (Líneas 321-459).
- **Descripción y Propósito:** Interfaz de acceso para usuarios registrados mediante correo electrónico y contraseña, con opción de recordar sesión y enlace de recuperación de credenciales.
- **Cómo se construyó (Implementación):**
  - **Tecnologías:** Hook `useForm` de Inertia.js (`formLogin`), inputs estilizados con Tailwind, iconos de Lucide (`Mail`, `Lock`, `Eye`, `EyeOff`, `AlertCircle`).
- **Interacciones y Eventos:**
  - Clic en el icono del ojo: Conmuta la visibilidad del texto de la contraseña alternando el estado booleano `mostrarContrasenaLogin`.
  - Envío del formulario: Dispara `formLogin.post(route('login'))`. Durante el procesamiento, desactiva los controles y muestra un spinner de carga.
  - Si existen errores de autenticación: Pinta los bordes del input en rojo y despliega mensajes de error descriptivos devueltos por Laravel.
- **Comportamiento Responsivo / Casos Límite:** Ancho adaptable a un contenedor centrado con límites en `max-w-md`.

---

### Botones de Acceso Rápido por Rol (Demo Login Buttons)
- **Ubicación en el código:** `resources/js/pages/auth/login.tsx` (Líneas 461-528).
- **Descripción y Propósito:** Tres botones de acceso instantáneo (1-Click) para demostraciones académicas o defensas de proyecto:
  - **Admin:** `admin@fitlife.test` (Control total).
  - **Coach:** `coach@fitlife.test` (Gestión deportiva).
  - **Socio:** `socio@fitlife.test` (Portal de autoservicio).
- **Cómo se construyó (Implementación):**
  - **Tecnologías:** Cuadrícula de 3 columnas (`grid grid-cols-3 gap-2`). Función interna `loginRapidoPorRol(email, rol)` que inyecta credenciales y ejecuta `router.post(route('login'))`.
- **Interacciones y Eventos:**
  - Clic en un rol: Inhabilita los demás botones para evitar condiciones de carrera (*race conditions*) y muestra un indicador de carga animado en el botón seleccionado.
- **Comportamiento Responsivo / Casos Límite:** Cada botón incluye micro-etiquetas con el rol y contraseña predefinida para facilitar la evaluación al examinador.

---

### Formulario de Registro de Usuario y Selección de Plan (Register Form)
- **Ubicación en el código:** `resources/js/pages/auth/login.tsx` (Líneas 532-864).
- **Descripción y Propósito:** Captura de datos completa para nuevos clientes: Nombre, Apellido, DNI, Correo, Teléfono, Contraseña, Confirmación, Selección del Plan de Membresía inicial y aceptación de términos de servicio.
- **Cómo se construyó (Implementación):**
  - **Tecnologías:** Hook `useForm` de Inertia (`formRegistro`). Rejilla de 2 columnas (`sm:grid-cols-2`). Función de cálculo de robustez de clave `calcularFortaleza(password)` que clasifica la seguridad de 1 a 3.
  - **Props:** `planes` (listado dinámico de planes cargados desde la base de datos).
- **Interacciones y Eventos:**
  - Al tipear la contraseña: La barra de fortaleza reacciona dinámicamente pintándose en rojo (débil), ámbar (media) o verde lima (fuerte) con textos orientativos.
  - Selector de Plan: Menú desplegable preseleccionado con el plan que el usuario eligió en la Landing Page si venía con el parámetro `?plan=X`.
  - Envío: Ejecuta `formRegistro.post(route('register'))`. Al completarse, el backend crea el registro `User`, la ficha `Socio` vinculada y redirige al portal `/mi-perfil`.
- **Comportamiento Responsivo / Casos Límite:** La rejilla colapsa a una única columna en teléfonos móviles.

---

### Wrapper de Registro
- **Ubicación en el código:** `resources/js/pages/auth/register.tsx` (Líneas 1-27).
- **Descripción y Propósito:** Componente de entrada que reutiliza la vista unificada de `Login` forzando la propiedad `initialTab="register"`.
- **Cómo se construyó (Implementación):** Componente funcional que reenvía props (`planes`, `status`, `canResetPassword`) al componente `<Login>`.
- **Interacciones y Eventos:** Hereda íntegramente la lógica del formulario de registro.
- **Comportamiento Responsivo:** Idéntico a `login.tsx`.

---

### Formulario de Recuperación de Contraseña
- **Ubicación en el código:** `resources/js/pages/auth/forgot-password.tsx` (Líneas 1-64).
- **Descripción y Propósito:** Permite a los usuarios solicitar un enlace firmado por correo para reestablecer su clave olvidada.
- **Cómo se construyó (Implementación):** Envuelta en `AuthLayout`. Componentes de formulario de Shadcn UI (`Input`, `Label`, `Button`, `InputError`). Hook `useForm` enviando a `password.email`.
- **Interacciones y Eventos:** Envío por POST. Si el backend responde con estado exitoso, muestra un mensaje verde confirmando el despacho del correo.
- **Comportamiento Responsivo:** Centrado y restringido a `max-w-sm`.

---

### Formulario de Restablecimiento de Contraseña
- **Ubicación en el código:** `resources/js/pages/auth/reset-password.tsx` (Líneas 1-100).
- **Descripción y Propósito:** Pantalla de destino del correo de recuperación donde el usuario define su nueva clave.
- **Cómo se construyó (Implementación):** Formulario con campo de correo electrónico en solo lectura (`readOnly`), nuevos campos de contraseña y confirmación, y un token criptográfico oculto en el estado.
- **Interacciones y Eventos:** Envía solicitud POST a `password.store` y redirige a la pantalla de login con notificación de éxito.
- **Comportamiento Responsivo:** Centrado en layout de tarjeta minimalista.

---

### Formulario de Confirmación de Contraseña
- **Ubicación en el código:** `resources/js/pages/auth/confirm-password.tsx` (Líneas 1-61).
- **Descripción y Propósito:** Barrera de seguridad intermedia que solicita reconfirmar la contraseña antes de acceder a configuraciones sensibles (como eliminar cuenta).
- **Cómo se construyó (Implementación):** Input único de contraseña con autofocus y envío a `password.confirm`.
- **Interacciones y Eventos:** Envío estándar de confirmación de sesión activa.
- **Comportamiento Responsivo:** Adaptado al layout base de autenticación.

---

### Pantalla de Verificación de Correo Electrónico
- **Ubicación en el código:** `resources/js/pages/auth/verify-email.tsx` (Líneas 1-42).
- **Descripción y Propósito:** Notifica al usuario recién registrado que debe confirmar su dirección de correo electrónico para desbloquear la totalidad de las funciones del sistema.
- **Cómo se construyó (Implementación):** Mensaje informativo, botón de reenvío de enlace de verificación por POST a `verification.send` y botón de cierre de sesión.
- **Interacciones y Eventos:** Clic en "Reenviar": Genera una notificación emergente indicando que un nuevo enlace ha sido despachado.
- **Comportamiento Responsivo:** Tarjeta centrada compatible con cualquier pantalla móvil.

---

### Layouts de Autenticación (`resources/js/layouts/auth/*`)
- **`auth-layout.tsx`:** Exporta de manera predeterminada el `AuthSimpleLayout` como contenedor modular para vistas de autenticación secundarias.
- **`auth-simple-layout.tsx`:** Diseño centrado con logotipo superior de FitLife y contenedor de anchura máxima `max-w-sm`.
- **`auth-split-layout.tsx`:** Plantilla alternativa de pantalla dividida 50/50 para escritorio con panel oscuro a la izquierda.
- **`auth-card-layout.tsx`:** Envoltorio que encapsula los formularios en una tarjeta flotante estilizada con `CardHeader` y `CardContent`.

---

# 4. Módulo de Socios

Este módulo gestiona el ciclo de vida completo de los atletas: catálogo, altas, fichas técnicas, renovaciones y portal de autoservicio.

---

### Catálogo de Socios: Barra de Atajos y Acciones Rápidas
- **Ubicación en el código:** `resources/js/pages/socios/index.tsx` (Líneas 187-208).
- **Descripción y Propósito:** Controles de cabecera para acelerar el trabajo administrativo: botón de creación manual de socio y botón para generar un socio de prueba con 1 solo clic.
- **Cómo se construyó (Implementación):** React `useState` (`generandoAuto`, `modalCrearAbierto`), Inertia `router.post(route('socios.generar-automatico'))`.
- **Interacciones y Eventos:**
  - Clic en "Generar Socio Auto": Despacha generación sintética con membresía activa y rutina asignada, mostrando animación de carga.
  - Clic en "Nuevo Socio": Despliega el modal emergente de registro manual.
- **Comportamiento Responsivo / Casos Límite:** Los botones se apilan horizontalmente con `flex-wrap` en dispositivos móviles.

---

### Catálogo de Socios: Filtros y Barra de Búsqueda Reactiva
- **Ubicación en el código:** `resources/js/pages/socios/index.tsx` (Líneas 213-259).
- **Descripción y Propósito:** Formulario de filtrado multicriterio que permite buscar atletas por nombre, DNI, código de acceso o correo, así como filtrar por su estado contractual (Todos, Activos, Inactivos, Morosos).
- **Cómo se construyó (Implementación):** Formulario controlado de React con `useState` para `busqueda` y `estadoFiltro`. Invoca `router.get(route('socios.index'))` conservando el scroll.
- **Interacciones y Eventos:**
  - Submit o tecleo en el buscador: Actualiza los resultados del catálogo en tiempo real.
  - Clic en "Limpiar": Restablece los filtros a su estado base y recarga el catálogo completo.
- **Comportamiento Responsivo / Casos Límite:** Conmuta de 1 columna vertical en móvil a una rejilla de 4 columnas en desktop (`sm:grid-cols-4`).

---

### Catálogo de Socios: Tabla de Socios para Escritorio
- **Ubicación en el código:** `resources/js/pages/socios/index.tsx` (Líneas 264-400).
- **Descripción y Propósito:** Cuadrícula tabular que presenta los datos clave de los miembros: Código, Atleta (avatar y correo), DNI, Estado (Badge), Membresía Actual y Acciones.
- **Cómo se construyó (Implementación):** Tabla semántica HTML (`table`, `thead`, `tbody`) con clases de Tailwind. Renderizado condicional exclusivo para pantallas medianas y grandes con `hidden md:block`.
- **Interacciones y Eventos:**
  - Botón "Ver Expediente" (Ojo): Navega a `route('socios.show', socio.id)`.
  - Botón "Editar" (Lápiz): Navega a `route('socios.edit', socio.id)`.
  - Botón "Renovar": Abre el modal de renovación rápida para ese socio.
  - Botón "Eliminar" (Papelera): Dispara alerta de confirmación y posterior soft delete vía `router.delete`.
- **Comportamiento Responsivo / Casos Límite:** Se oculta por completo en móviles (`< md`). Si la consulta devuelve 0 registros, muestra una fila con mensaje explicativo de búsqueda sin resultados.

---

### Catálogo de Socios: Tarjetas Adaptativas para Dispositivos Móviles
- **Ubicación en el código:** `resources/js/pages/socios/index.tsx` (Líneas 402-525).
- **Descripción y Propósito:** Presentación alternativa de los socios en formato de tarjetas individuales táctiles diseñadas específicamente para smartphones.
- **Cómo se construyó (Implementación):** Contenedor visible únicamente en pantallas pequeñas (`block md:hidden`). Distribuye la información del socio en bloques verticales y expone los botones de acción en una cuadrícula inferior de dos columnas.
- **Interacciones y Eventos:** Ofrece exactamente las mismas acciones que la tabla de escritorio (ver, editar, renovar, eliminar), pero con áreas táctiles ampliadas (`min-h-[44px]`) que cumplen estándares de accesibilidad en móviles.
- **Comportamiento Responsivo / Casos Límite:** Sustituye íntegramente a la tabla en resoluciones móviles.

---

### Catálogo de Socios: Paginador Compacto (PaginadorCompacto)
- **Ubicación en el código:** `resources/js/components/paginador-compacto.tsx` (Líneas 1-96). Invocado en `socios/index.tsx` (Línea 527).
- **Descripción y Propósito:** Componente de paginación reutilizable que interpreta los metadatos de paginación de Laravel (`current_page`, `last_page`, `prev_page_url`, `next_page_url`).
- **Cómo se construyó (Implementación):** Función `irA(url)` que dispara `router.get(url, {}, { preserveState: true, preserveScroll: true })`.
- **Interacciones y Eventos:** Clics en flechas Anterior / Siguiente actualizan la página sin recargar la pantalla completa.
- **Comportamiento Responsivo / Casos Límite:** Si el total de registros es menor o igual al límite por página, el componente retorna `null` y se auto-oculta automáticamente.

---

### Catálogo de Socios: Modal de Creación de Nuevo Socio
- **Ubicación en el código:** `resources/js/pages/socios/index.tsx` (Líneas 533-672).
- **Descripción y Propósito:** Ventana modal flotante para registrar un nuevo atleta capturando nombre, apellido, DNI, correo, teléfono, sucursal de pertenencia y asignación de membresía inicial.
- **Cómo se construyó (Implementación):** Diálogo emergente con estado `guardandoSocio` y envío POST a `route('socios.store')`.
- **Interacciones y Eventos:**
  - Submit: Valida datos en servidor y actualiza la lista.
  - Botón "Cancelar" o tecla Escape: Cierra el modal y restablece el formulario.
- **Comportamiento Responsivo / Casos Límite:** Altura máxima protegida (`max-h-[90vh]`) con scroll interno para pantallas reducidas.

---

### Catálogo de Socios: Modal de Renovación Rápida desde Catálogo
- **Ubicación en el código:** `resources/js/pages/socios/index.tsx` (Líneas 674-760).
- **Descripción y Propósito:** Modal compacto que permite asignar un nuevo plan y registrar el pago a un socio seleccionado directamente desde la tabla o tarjetas del catálogo.
- **Cómo se construyó (Implementación):** Se activa cuando `socioParaRenovar !== null`. Muestra información en solo lectura del socio y selectores para nuevo plan y método de pago.
- **Interacciones y Eventos:** Submit envía solicitud POST a `route('membresias.store')`.
- **Comportamiento Responsivo:** Modal centrado con ancho restringido a `max-w-md`.

---

### Expediente del Socio: Barra de Navegación Rápida Secuencial
- **Ubicación en el código:** `resources/js/pages/socios/show.tsx` (Líneas 159-202).
- **Descripción y Propósito:** Barra superior en la ficha del socio que permite al administrador avanzar o retroceder secuencialmente entre socios sin tener que volver al catálogo principal ("Indagación Continua").
- **Cómo se construyó (Implementación):** Enlaces `<Link>` de Inertia basados en los identificadores recibidos en las props `anterior_socio_id` y `siguiente_socio_id`.
- **Interacciones y Eventos:** Clics navegan a `route('socios.show', id)`.
- **Comportamiento Responsivo / Casos Límite:** Si no existe un socio previo o posterior (ej. primer o último registro), el botón se deshabilita visualmente con `opacity-50 pointer-events-none`.

---

### Expediente del Socio: Ficha Principal y Encabezado del Atleta
- **Ubicación en el código:** `resources/js/pages/socios/show.tsx` (Líneas 204-274).
- **Descripción y Propósito:** Cabecera de la ficha técnica con avatar en alta resolución, nombre completo, código identificador, chip de estado (Activo, Moroso, Inactivo), fecha de incorporación y botones de acción principal (Editar, Renovar, Dar de Baja).
- **Cómo se construyó (Implementación):** Componente con fondo de gradiente deportivo oscuro. El botón "Dar de baja" invoca un cuadro de confirmación nativo antes de ejecutar `router.delete`.
- **Interacciones y Eventos:**
  - Clic en "Editar Socio": Conduce a `socios.edit`.
  - Clic en "Renovar Membresía": Abre el modal de renovación.
  - Clic en "Dar de baja": Solicita confirmación y ejecuta soft delete.
- **Comportamiento Responsivo / Casos Límite:** Los botones y el avatar se reorganizan de distribución en columna en móviles a fila horizontal en escritorio (`md:flex-row`).

---

### Expediente del Socio: Tarjeta de Contacto y Observaciones Médicas
- **Ubicación en el código:** `resources/js/pages/socios/show.tsx` (Líneas 279-326).
- **Descripción y Propósito:** Despliega datos de contacto (email, teléfono, dirección, fecha de nacimiento) y advertencias médicas (alergias, lesiones, patologías cardíacas).
- **Cómo se construyó (Implementación):** Tarjeta con cuadrícula de información. Incluye advertencia destacada en amarillo si existen notas médicas registradas.
- **Interacciones y Eventos:** Consulta pasiva de datos.
- **Comportamiento Responsivo / Casos Límite:** Si las observaciones médicas vienen vacías, el sistema muestra "Sin observaciones médicas registradas", evitando huecos visuales.

---

### Expediente del Socio: Tarjeta de Estado de Membresía
- **Ubicación en el código:** `resources/js/pages/socios/show.tsx` (Líneas 328-378).
- **Descripción y Propósito:** Resumen del plan contratado: nombre del plan, fecha de inicio, fecha de expiración, cuotas pagadas y saldo pendiente.
- **Cómo se construyó (Implementación):** Renderizado condicional basado en si `socio.membresia_activa` existe. Si existe, muestra marco en verde con detalles; si no existe, muestra marco en rojo de alerta.
- **Interacciones y Eventos:** Botón "+ Asignar Membresía" en caso de encontrarse inactiva.
- **Comportamiento Responsivo:** Se adapta al ancho de columna del grid de la ficha técnica.

---

### Expediente del Socio: Histórico de Asistencias Recientes
- **Ubicación en el código:** `resources/js/pages/socios/show.tsx` (Líneas 383-461).
- **Descripción y Propósito:** Tabla de auditoría con las últimas asistencias del socio: fecha, hora de entrada, hora de salida, tiempo de permanencia calculado en minutos, método (QR, DNI, biométrico) y estado de acceso.
- **Cómo se construyó (Implementación):** Tabla con scroll horizontal protegido (`overflow-x-auto`).
- **Interacciones y Eventos:** Enlace superior a la vista de auditoría global de asistencias pre-filtrando por este socio.
- **Comportamiento Responsivo / Casos Límite:** Si la asistencia no tiene hora de salida, despliega la etiqueta pulsante "En Sala". Si no hay asistencias históricas, presenta un estado vacío.

---

### Expediente del Socio: Panel de Rutinas y Programación de Entrenamiento
- **Ubicación en el código:** `resources/js/pages/socios/show.tsx` (Líneas 463-521).
- **Descripción y Propósito:** Lista las rutinas de ejercicio activas e históricas del atleta, exponiendo nivel de dificultad, días programados y cantidad de ejercicios asignados.
- **Cómo se construyó (Implementación):** Lista de tarjetas deportivas con enlaces directos a la vista detallada de la rutina (`route('rutinas.show', rutina.id)`).
- **Interacciones y Eventos:** Clic en cualquier rutina abre su ficha técnica completa con desglose de series, repeticiones y descansos.
- **Comportamiento Responsivo / Casos Límite:** Si el atleta no tiene rutinas, muestra un marco punteado con opción para diseñar o asignarle una rutina con 1 clic.

---

### Expediente del Socio: Modal de Renovación de Membresía
- **Ubicación en el código:** `resources/js/pages/socios/show.tsx` (Líneas 526-585).
- **Descripción y Propósito:** Modal de renovación idéntico al del catálogo que permite extender la suscripción del atleta desde su propio expediente.
- **Cómo se construyó (Implementación):** Formulario modal controlado por el estado booleano `modalRenovarAbierto`.
- **Interacciones y Eventos:** Envía solicitud POST a `route('membresias.store')`.
- **Comportamiento Responsivo:** Totalmente adaptado a dispositivos móviles mediante anchura proporcional `w-[95%] sm:max-w-md`.

---

### Formulario Completo de Edición de Socio
- **Ubicación en el código:** `resources/js/pages/socios/edit.tsx` (Líneas 74-245).
- **Descripción y Propósito:** Formulario administrativo integral para actualizar datos personales, médicos y de asignación de sucursal de un socio.
- **Cómo se construyó (Implementación):** Formulario controlado con estado `valores` (`useState`), manejo de errores de validación de Laravel con estado `errores` y envío PUT vía `router.put(route('socios.update', socio.id))`.
- **Interacciones y Eventos:**
  - `onChange`: Actualiza el campo correspondiente en el estado local.
  - Submit: Envía los cambios al backend. Si hay errores de validación (ej. DNI duplicado o email inválido), destaca los campos en rojo y renderiza el texto de error respectivo.
- **Comportamiento Responsivo / Casos Límite:** Rejilla que pasa de 1 columna en teléfonos a 2 y 3 columnas en pantallas de escritorio (`sm:grid-cols-2`, `sm:grid-cols-3`).

---

### Portal Autoservicio del Socio: Cabecera y Banner de Bienvenida
- **Ubicación en el código:** `resources/js/pages/socios/mi-perfil.tsx` (Líneas 101-172).
- **Descripción y Propósito:** Pantalla de autoservicio exclusiva para el socio cuando inicia sesión. Muestra una cabecera con el logotipo, botón de cierre de sesión y un banner que confirma si el socio tiene acceso autorizado hoy o si debe regularizar su membresía.
- **Cómo se construyó (Implementación):** Cabecera pegajosa con desenfoque de cristal (`backdrop-blur-xl`). Variables computadas `membresiaActual` y `esActiva`.
- **Interacciones y Eventos:** Botón "Cerrar Sesión": Ejecuta `router.post(route('logout'))`.
- **Comportamiento Responsivo / Casos Límite:** En móviles el nombre completo se resume para no saturar la cabecera.

---

### Portal Autoservicio del Socio: Credencial Digital 3D y Resumen de Membresía
- **Ubicación en el código:** `resources/js/pages/socios/mi-perfil.tsx` (Líneas 177-239).
- **Descripción y Propósito:** Despliega en primer plano la Credencial Digital 3D interactiva del socio para que pueda mostrarla o escanearla en el torniquete desde su teléfono, acompañada por una tarjeta de resumen de su plan contratado y fecha de vencimiento.
- **Cómo se construyó (Implementación):** Incorporación del componente `<CredencialDigitalSocio />` centrado y tarjeta de detalles con badges.
- **Interacciones y Eventos:** El socio puede interactuar con el efecto 3D táctil o con el cursor.
- **Comportamiento Responsivo / Casos Límite:** Ocupa el ancho completo en smartphones y 5 columnas de rejilla en pantallas de escritorio (`lg:col-span-5`).

---

### Portal Autoservicio del Socio: Conmutador de Pestañas y Rutina de Entrenamiento
- **Ubicación en el código:** `resources/js/pages/socios/mi-perfil.tsx` (Líneas 244-367).
- **Descripción y Propósito:** Selector de doble pestaña ("Mi Rutina de Entrenamiento" vs "Mi Historial de Asistencias") y visualizador interactivo de ejercicios con series, repeticiones, descansos y notas técnicas del coach.
- **Cómo se construyó (Implementación):** Estado React `seccionActiva` (`'rutina' | 'asistencias'`). Mapeo iterativo sobre `socio.rutinas` y sobre el pivote `rutina.ejercicios`.
- **Interacciones y Eventos:** Clic en las pestañas conmuta el contenido inmediatamente sin peticiones de red adicionales.
- **Comportamiento Responsivo / Casos Límite:** Si el socio no tiene rutinas asignadas, despliega un estado vacío motivacional invitándolo a solicitar una a su entrenador de cabecera.

---

### Portal Autoservicio del Socio: Historial Personal de Asistencias
- **Ubicación en el código:** `resources/js/pages/socios/mi-perfil.tsx` (Líneas 369-432).
- **Descripción y Propósito:** Lista cronológica de las visitas del propio socio al gimnasio para que pueda auditar su frecuencia de entrenamiento.
- **Cómo se construyó (Implementación):** Mapeo de `socio.asistencias` con formato de fecha localizado (`toLocaleDateString`).
- **Interacciones y Eventos:** Visualización pasiva de lectura.
- **Comportamiento Responsivo / Casos Límite:** Si no hay asistencias registradas, muestra un estado vacío con icono de calendario.

---

# 5. Módulo de Asistencias y Control de Acceso

Este módulo abarca la auditoría de entradas y salidas, métricas de permanencia y el terminal Kiosco táctil para recepción.

---

### Registro de Asistencias: Cabecera y Tarjetas de Métricas Rápidas
- **Ubicación en el código:** `resources/js/pages/asistencias/index.tsx` (Líneas 188-228).
- **Descripción y Propósito:** Título del módulo de control de accesos, botón de asistencia automática simulada y tres fichas de conteo rápido: Atletas en Sala, Accesos Permitidos Hoy y Accesos Denegados Hoy.
- **Cómo se construyó (Implementación):** Flexbox de Tailwind con props `en_sala_ahora`, `total_hoy`, `denegadas_hoy`. Mutación POST con `router.post(route('asistencias.generar-automatica'))`.
- **Interacciones y Eventos:** Clic en "Asistencia Automática": Simula un acceso instantáneo con feedback de carga.
- **Comportamiento Responsivo / Casos Límite:** Las tres tarjetas se adaptan fluidamente de columna vertical en móviles a fila horizontal en escritorio.

---

### Registro de Asistencias: Banner de Indagación de Socio Individual
- **Ubicación en el código:** `resources/js/pages/asistencias/index.tsx` (Líneas 231-269).
- **Descripción y Propósito:** Banner de alerta que se hace visible exclusivamente cuando el listado de asistencias está filtrado por un socio en particular, mostrando su nombre, DNI, botón para ver su expediente completo y botón para quitar el filtro.
- **Cómo se construyó (Implementación):** Renderizado condicional evaluando `socio_seleccionado !== null`.
- **Interacciones y Eventos:**
  - Clic en "Ver Expediente": Redirige a `route('socios.show', socio_seleccionado.id)`.
  - Clic en la "X" (Quitar Filtro): Ejecuta `router.get(route('asistencias.index'))` eliminando el parámetro de la URL.
- **Comportamiento Responsivo:** Conmuta de columna en móvil a fila en escritorio con `sm:flex-row`.

---

### Registro de Asistencias: Formulario de Filtros de Auditoría
- **Ubicación en el código:** `resources/js/pages/asistencias/index.tsx` (Líneas 271-334).
- **Descripción y Propósito:** Panel para filtrar el registro histórico de asistencias por fecha exacta, texto de búsqueda (nombre, DNI, código) o estado (Permitido, Denegado).
- **Cómo se construyó (Implementación):** Formulario controlado de React con `useState`. Envía los parámetros mediante `router.get`.
- **Interacciones y Eventos:** Submit aplica los filtros; botón "Limpiar" resetea la consulta a la fecha de hoy.
- **Comportamiento Responsivo / Casos Límite:** Rejilla que pasa de 1 columna en móvil a 2 en tablets y 4 en pantallas de escritorio (`lg:grid-cols-4`).

---

### Registro de Asistencias: Tabla de Registros y Lista Táctil Móvil
- **Ubicación en el código:** `resources/js/pages/asistencias/index.tsx` (Líneas 336-634).
- **Descripción y Propósito:** Despliegue de los accesos registrados: Fecha/Hora, Socio (código, nombre, DNI), Método, Estado del Paso, Mensaje y Acciones.
- **Cómo se construyó (Implementación):** Renderizado dual de Tailwind: tabla semántica para desktop (`hidden md:block`) y tarjetas táctiles para móvil (`block md:hidden`).
- **Interacciones y Eventos:**
  - Botón "Marcar Salida": Registra el check-out del socio y calcula los minutos de permanencia.
  - Botón "Editar": Abre el modal de edición de asistencia.
  - Botón "Eliminar": Elimina el registro del log tras confirmación.
- **Comportamiento Responsivo / Casos Límite:** En caso de no existir registros bajo los filtros aplicados, despliega un contenedor de estado vacío con sugerencia de cambio de criterios.

---

### Registro de Asistencias: Modal de Edición de Registro de Asistencia
- **Ubicación en el código:** `resources/js/pages/asistencias/index.tsx` (Líneas 642-745).
- **Descripción y Propósito:** Diálogo emergente que permite al administrador corregir un registro de asistencia (ajustar la hora de entrada, fijar la hora de salida manual o corregir el estado de validación).
- **Cómo se construyó (Implementación):** Modal flotante con fondo desenfocado y formulario PUT vía `router.put(route('asistencias.update', id))`.
- **Interacciones y Eventos:** Submit envía los datos actualizados y cierra el diálogo.
- **Comportamiento Responsivo:** Ancho adaptable restringido a `max-w-md` con relleno interno protegido.

---

### Terminal Kiosco de Recepción: Cabecera, Reloj e Identidad de Sede
- **Ubicación en el código:** `resources/js/pages/asistencias/kiosco.tsx` (Líneas 36-120).
- **Descripción y Propósito:** Vista de pantalla completa inmersiva diseñada para tablets o tótems en recepción. Muestra un reloj digital gigante en tiempo real, el nombre de la sucursal activa y un enlace discreto para volver al panel administrativo.
- **Cómo se construyó (Implementación):** React `useEffect` con intervalo de 1,000 ms para actualizar la hora del reloj digital. Tema oscuro con resplandor neón lima.
- **Interacciones y Eventos:** El reloj se sincroniza segundo a segundo de forma autónoma.
- **Comportamiento Responsivo:** Tipografía fluida que escala según la resolución del tótem.

---

### Terminal Kiosco de Recepción: Conmutador de Modo Entrada / Salida
- **Ubicación en el código:** `resources/js/pages/asistencias/kiosco.tsx` (Líneas 122-175).
- **Descripción y Propósito:** Interruptor de gran tamaño para que el socio elija con un toque en pantalla si está registrando su "Ingreso" o su "Salida".
- **Cómo se construyó (Implementación):** Botones con estilo pastilla táctil y estado booleano `modo` (`'entrada' | 'salida'`).
- **Interacciones y Eventos:** Toque conmuta el modo cambiando los colores del teclado de lima (entrada) a cyan (salida).
- **Comportamiento Responsivo:** Ocupa el ancho completo con altura táctil mínima de 56px.

---

### Terminal Kiosco de Recepción: Teclado Numérico Táctil y Selector de Clases
- **Ubicación en el código:** `resources/js/pages/asistencias/kiosco.tsx` (Líneas 177-310).
- **Descripción y Propósito:** Teclado numérico táctil en pantalla (dígitos 0 al 9, Borrar y Limpiar) para que el socio tipee su DNI o código sin requerir teclado físico. Incluye un selector desplegable opcional para vincular el ingreso a una clase grupal.
- **Cómo se construyó (Implementación):** Cuadrícula de 3 columnas de botones redondos grandes con respuesta táctil. Estado React `identificador`.
- **Interacciones y Eventos:**
  - Toque en dígitos: Concatena el número a la pantalla.
  - Toque en "Borrar": Elimina el último dígito ingresado.
  - Toque en "Confirmar": Dispara la función `procesarMarcaje` llamando a los endpoints API `/api/asistencias/escanear` o `/api/asistencias/check-out`.
- **Comportamiento Responsivo / Casos Límite:** Botones sobredimensionados para facilitar el uso con el dedo en pantallas táctiles.

---

### Terminal Kiosco de Recepción: Banner de Resultado y Síntesis de Sonido
- **Ubicación en el código:** `resources/js/pages/asistencias/kiosco.tsx` (Líneas 312-474).
- **Descripción y Propósito:** Pantalla de respuesta instantánea que muestra un banner gigante verde (Acceso Permitido) o rojo (Acceso Denegado con el motivo exacto), acompañada de síntesis de sonido mediante la API Web Audio de HTML5 y cuenta regresiva de auto-reseteo de 6 segundos.
- **Cómo se construyó (Implementación):** Web Audio API (`AudioContext`) para generar un tono agudo armónico de éxito o un zumbido grave de rechazo. Hook `useEffect` con temporizador decreciente para limpiar la pantalla para el siguiente socio.
- **Interacciones y Eventos:** Se auto-reinicia a los 6 segundos o inmediatamente si se presiona la pantalla.
- **Comportamiento Responsivo:** Centrado geométrico con tipografía visible a varios metros de distancia del tótem.

---

# 6. Módulo de Rutinas de Entrenamiento

Este módulo abarca el diseño, asignación y consulta de programas de ejercicios personalizados según nivel de dificultad y grupo muscular.

---

### Catálogo de Rutinas: Cabecera y Acciones Rápidas
- **Ubicación en el código:** `resources/js/pages/rutinas/index.tsx` (Líneas 70-175).
- **Descripción y Propósito:** Encabezado con título del módulo, métricas de programas registrados, botón para generar una rutina automática con 1 clic y botón para abrir el panel de diseño de nueva rutina.
- **Cómo se construyó (Implementación):** React `useState` para controlar la apertura del drawer (`drawerAbierto`) y mutación POST a `route('rutinas.generar-automatica')`.
- **Interacciones y Eventos:**
  - Clic en "Crear Nueva Rutina": Abre el componente `DrawerAsignarRutina`.
  - Clic en "Rutina Automática": Genera un plan de ejercicios aleatorio con 4-6 ejercicios balanceados por grupo muscular.
- **Comportamiento Responsivo:** Reorganiza los controles de columna a fila en pantallas de escritorio.

---

### Catálogo de Rutinas: Parrilla de Tarjetas de Rutinas
- **Ubicación en el código:** `resources/js/pages/rutinas/index.tsx` (Líneas 177-334).
- **Descripción y Propósito:** Cuadrícula de tarjetas deportivas que presenta cada rutina registrada: nombre del programa, nivel de dificultad (Principiante, Intermedio, Avanzado), días sugeridos, socio asignado y vista previa de los ejercicios incluidos.
- **Cómo se construyó (Implementación):** Cuadrícula responsiva de 2 columnas (`grid md:grid-cols-2`). Badges temáticos por nivel y lista condensada de ejercicios.
- **Interacciones y Eventos:**
  - Clic en el icono de Ojo: Navega al expediente detallado en `route('rutinas.show', rutina.id)`.
  - Clic en el Lápiz: Abre el Drawer pre-cargado con la rutina para modificarla.
  - Clic en la Papelera: Elimina la rutina tras confirmación.
- **Comportamiento Responsivo / Casos Límite:** Si no hay rutinas creadas, despliega un estado vacío explicativo con llamada a la acción.

---

### Detalle de Rutina: Ficha Técnica y Desglose de Ejercicios
- **Ubicación en el código:** `resources/js/pages/rutinas/show.tsx` (Líneas 67-327).
- **Descripción y Propósito:** Vista en profundidad de un programa de entrenamiento. Expone la información del coach creador, socio asignado, nivel y una tabla completa de ejercicios con series, repeticiones, descansos en segundos, tempo y notas de ejecución.
- **Cómo se construyó (Implementación):** Layout estructurado con tarjeta superior de metadatos y lista vertical de ejercicios desglosados en bloques con iconos de reloj de descanso y mancuernas.
- **Interacciones y Eventos:** Botones superiores para editar la rutina en el Drawer o eliminarla permanentemente.
- **Comportamiento Responsivo / Casos Límite:** Los bloques de métricas (Series / Repeticiones / Descanso) se apilan en teléfonos móviles y se distribuyen horizontalmente en tablets y monitores.

---

### Panel Lateral Deslizante: Asignador y Diseñador de Rutinas (DrawerAsignarRutina)
- **Ubicación en el código:** `resources/js/components/drawer-asignar-rutina.tsx` (Líneas 62-446).
- **Descripción y Propósito:** Cajón lateral deslizante (*Slide-over drawer*) que permite a los entrenadores construir una rutina seleccionando el socio, asignando nivel y eligiendo ejercicios clasificados por pestañas de grupo muscular (Pecho, Espalda, Piernas, Hombros, Brazos, Core, Cardio).
- **Cómo se construyó (Implementación):**
  - **Tecnologías:** Componente con overlay oscuro y panel lateral fijo a la derecha (`fixed inset-y-0 right-0`). Pestañas de grupo muscular con `tabActiva`. Estado complejo `ejerciciosConfigurados` donde cada ejercicio seleccionado almacena sus propias series, repeticiones y descanso.
  - **Props:** `ejercicios`, `socios`, `abierto`, `onCerrar`, `onGuardar`, `rutinaEditar`.
- **Interacciones y Eventos:**
  - Clic en pestaña muscular: Filtra el catálogo de ejercicios visibles.
  - Clic en un ejercicio: Lo agrega o retira de la rutina activa.
  - Modificar inputs numéricos: Ajusta las series, repeticiones o descanso de ese ejercicio específico.
  - Clic en "Guardar Rutina": Valida la estructura y emite el objeto completo al controlador vía POST o PUT.
- **Comportamiento Responsivo / Casos Límite:** En pantallas móviles ocupa el 100% del ancho del viewport; en pantallas grandes se fija en `max-w-xl`. Dispone de scroll vertical independiente.

---

# 7. Módulo de Clases Grupales

Este módulo gestiona la cartelera de clases dirigidas, cupos máximos, control de asistencia por profesores y aplicación de políticas comerciales según el plan de membresía.

---

### Encabezado, Conmutador de Rol (Socio / Admin) y Filtros Dinámicos
- **Ubicación en el código:** `resources/js/pages/clases/index.tsx` (Líneas 115-506).
- **Descripción y Propósito:** Barra superior del módulo con selector de fecha, filtros rápidos por disciplina (CrossFit, Spinning, Yoga, Boxeo, Pilates), filtro por sucursal y un interruptor de doble perspectiva que permite alternar entre la "Cartelera de Socios" (reserva visual) y la "Gestión Administrativa" (tabla de aforo).
- **Cómo se construyó (Implementación):** Estados locales de React para `fechaFiltro`, `disciplinaFiltro`, `sucursalFiltro` y `vistaModo`. Función `aplicarFiltros` que muta la URL con `router.get`.
- **Interacciones y Eventos:**
  - Clic en botones de disciplina: Aplica estilos de color según el deporte (`getDisciplinaStyle`) y filtra la cartelera.
  - Cambio en el selector de fecha: Recarga las clases programadas para ese día específico.
  - Toggle "Socio / Admin": Conmuta instantáneamente la vista sin peticiones adicionales al servidor.
- **Comportamiento Responsivo:** Los botones de disciplinas se distribuyen con `flex-wrap` permitiendo múltiples filas en móviles sin romper márgenes.

---

### Cartelera de Clases Grupales para Socios
- **Ubicación en el código:** `resources/js/pages/clases/index.tsx` (Líneas 508-678).
- **Descripción y Propósito:** Vista orientada a la experiencia de usuario de los atletas. Despliega tarjetas atractivas con imagen alusiva, horario, profesor, sala y una barra de progreso que indica el porcentaje de ocupación del cupo.
- **Cómo se construyó (Implementación):** Tarjetas deportivas con barra de aforo en porcentaje (`(inscritos / cupo_maximo) * 100`).
- **Interacciones y Eventos:**
  - Clic en "Inscribirme": Abre el diálogo de confirmación de inscripción.
- **Comportamiento Responsivo / Casos Límite:** Si la clase alcanzó su aforo máximo (`esta_llena`), la barra se colorea en rojo, el botón se deshabilita y muestra la etiqueta "CUPO LLENO".

---

### Tabla de Gestión y Auditoría de Cupos para Administradores
- **Ubicación en el código:** `resources/js/pages/clases/index.tsx` (Líneas 680-800).
- **Descripción y Propósito:** Vista tabular optimizada para recepcionistas y entrenadores que resume horario, disciplina, profesor titular, sucursal, cupos ocupados vs disponibles y acciones de gestión.
- **Cómo se construyó (Implementación):** Tabla semántica HTML envuelta en contenedor con desplazamiento horizontal fluido (`overflow-x-auto`).
- **Interacciones y Eventos:**
  - Botón "Inscribir Socio": Permite al recepcionista asignar un socio a la clase.
  - Botón "Asistentes": Abre la lista de atletas inscritos para marcar su presencia física en sala con un checkbox.
- **Comportamiento Responsivo:** Tabla desplazable en móviles sin cortes de información.

---

### Modal de Inscripción y Validación de Políticas de Plan
- **Ubicación en el código:** `resources/js/pages/clases/index.tsx` (Líneas 802-920).
- **Descripción y Propósito:** Diálogo de confirmación que aplica las reglas de negocio según el tipo de suscripción del socio:
  - **Plan Básico:** Muestra advertencia roja bloqueante: "Tu plan no incluye clases grupales".
  - **Plan Completo:** Muestra advertencia informativa indicando un recargo de $10 por sesión.
  - **Plan Premium:** Confirma acceso total con costo de $0.
- **Cómo se construyó (Implementación):** Evaluación de condiciones comerciales en cliente y backend antes de despachar `router.post(route('clases.inscribir', clase.id))`.
- **Interacciones y Eventos:** Botón "Confirmar Inscripción" procesa la reserva; botón "Cancelar" cierra el modal.
- **Comportamiento Responsivo:** Modal centrado compatible con dispositivos móviles.

---

# 8. Módulo de Configuración de Usuario

Este módulo permite a los usuarios autenticados gestionar su perfil personal, credenciales de acceso y preferencias visuales.

---

### Layout Maestro de Configuración (SettingsLayout)
- **Ubicación en el código:** `resources/js/layouts/settings/layout.tsx` (Líneas 26-63).
- **Descripción y Propósito:** Estructura de dos columnas (Master/Detail) compartida por todas las vistas de ajustes. Presenta un menú lateral de navegación con tres destinos: Perfil (`/settings/profile`), Contraseña (`/settings/password`) y Apariencia (`/settings/appearance`).
- **Cómo se construyó (Implementación):** Contenedor flexbox con barra lateral `w-48` en desktop y lista horizontal separada por divisor en dispositivos móviles (`Separator className="md:hidden"`).
- **Interacciones y Eventos:** Clic en los enlaces navega con Inertia destacando el elemento activo.
- **Comportamiento Responsivo:** En teléfonos móviles el menú se reposiciona verticalmente encima del formulario de contenido.

---

### Pantalla de Perfil de Usuario (Profile)
- **Ubicación en el código:** `resources/js/pages/settings/profile.tsx` (Líneas 22-121).
- **Descripción y Propósito:** Permite modificar el nombre de usuario y correo electrónico, reenviar la verificación de email si fue alterado y solicitar la eliminación definitiva de la cuenta.
- **Cómo se construyó (Implementación):** Hook `useForm` de Inertia enviando PATCH a `profile.update`. Componentes de Shadcn UI (`Input`, `Label`, `Button`). Mensaje animado de confirmación con `Transition` de Headless UI al completarse con éxito.
- **Interacciones y Eventos:** Envío del formulario actualiza los datos. Sección inferior incluye el componente `DeleteUser` con cuadro de diálogo destructor.
- **Comportamiento Responsivo:** Se adapta fluidamente al contenedor del `SettingsLayout`.

---

### Pantalla de Cambio de Contraseña (Password)
- **Ubicación en el código:** `resources/js/pages/settings/password.tsx` (Líneas 21-129).
- **Descripción y Propósito:** Permite actualizar la contraseña del usuario requiriendo la clave actual, la nueva clave y su confirmación.
- **Cómo se construyó (Implementación):** Formulario controlado con `useForm` enviando PUT a `password.update`. Uso de `useRef` para enfocar automáticamente el campo con error tras una validación fallida.
- **Interacciones y Eventos:** Submit valida en servidor y limpia los campos en caso de éxito.
- **Comportamiento Responsivo:** Hereda el comportamiento responsivo del layout de configuración.

---

### Pantalla de Personalización de Apariencia (Appearance)
- **Ubicación en el código:** `resources/js/pages/settings/appearance.tsx` (Líneas 17-31).
- **Descripción y Propósito:** Permite al usuario seleccionar su esquema visual favorito: Claro (*Light*), Oscuro (*Dark*) o Sincronizado con el Sistema Operativo (*System*).
- **Cómo se construyó (Implementación):** Incorpora el subcomponente `AppearanceTabs`, el cual interactúa con `localStorage` y conmuta la clase `.dark` en el documento HTML.
- **Interacciones y Eventos:** Clic en las opciones aplica el cambio cromático en tiempo real sin requerir recargar la página.
- **Comportamiento Responsivo:** Botones en pastilla adaptable a cualquier pantalla.

---

# 9. Layouts, Navegación Global y Primitivos de UI

Esta sección documenta la infraestructura estructural compartida por todos los módulos del sistema.

---

### Layout Base del Sistema (AppLayout & AppSidebarLayout)
- **Ubicación en el código:** `resources/js/layouts/app-layout.tsx` y `resources/js/layouts/app/app-sidebar-layout.tsx`.
- **Descripción y Propósito:** Marco general que envuelve a todas las vistas autenticadas de FitLife. Orquesta la barra lateral de navegación para pantallas grandes, la cabecera superior con breadcrumbs y el menú de navegación inferior para teléfonos móviles.
- **Cómo se construyó (Implementación):**
  - **Tecnologías:** Componentes estructurales de Shadcn UI (`AppShell`, `AppSidebar`, `AppContent`).
  - **Padding de seguridad:** Incorpora `pb-20 lg:pb-4` en el contenedor principal para que el contenido no quede tapado por la barra flotante inferior en móviles.
- **Interacciones y Eventos:** Soporta colapsar/expandir la barra lateral mediante el botón del sidebar.
- **Comportamiento Responsivo:** Oculta el sidebar en resoluciones móviles y despliega el `MobileBottomNav`.

---

### Barra Lateral de Navegación (AppSidebar)
- **Ubicación en el código:** `resources/js/components/app-sidebar.tsx`.
- **Descripción y Propósito:** Barra de navegación vertical permanente para computadoras de escritorio. Contiene el logotipo de FitLife, los accesos a los módulos primarios (Inicio, Dashboard, Socios, Asistencias, Rutinas, Clases) y el menú de usuario inferior con enlace al perfil y cierre de sesión.
- **Cómo se construyó (Implementación):** Componentes `Sidebar` de Shadcn UI con acordeones colapsables y elementos interactivos `Link` de Inertia.
- **Interacciones y Eventos:** Ilumina con borde neón y fondo oscuro la ruta activa actual.
- **Comportamiento Responsivo:** Solo visible en pantallas grandes (`lg` en adelante).

---

### Barra de Navegación Móvil Inferior (MobileBottomNav)
- **Ubicación en el código:** `resources/js/components/mobile-bottom-nav.tsx`.
- **Descripción y Propósito:** Barra fija inferior inspirada en aplicaciones nativas de smartphone (iOS / Android) que ubica los destinos más frecuentes al alcance del pulgar del operador.
- **Cómo se construyó (Implementación):**
  - **Tecnologías:** Contenedor fijo inferior `fixed bottom-0 inset-x-0 z-50` con desenfoque de cristal (`backdrop-blur-xl bg-zinc-950/90 border-t border-zinc-800`). Cuadrícula de 5 columnas.
  - **Botón Central Elevado:** El botón central correspondiente a "Acceso / Asistencias" flota por encima de la barra (`relative -top-3`) con fondo neón lima y sombra proyectada para facilitar el escaneo inmediato en recepción.
- **Interacciones y Eventos:** Clics navegan a Dashboard, Socios, Asistencias, Clases y Rutinas.
- **Comportamiento Responsivo:** Visible exclusivamente en teléfonos y tablets (`block lg:hidden`). Incluye espaciado seguro para el home-bar de dispositivos iPhone (`pb-safe`).

---

### Sistema de Diseño y Tokens CSS
- **Ubicación en el código:** `resources/css/app.css` (Líneas 1-178).
- **Descripción y Propósito:** Define la identidad gráfica de FitLife, basada en Tailwind CSS v4, tipografía deportiva moderna y efectos de resplandor atlético.
- **Cómo se construyó (Implementación):**
  - **Tipografía:** Fuente principal `Instrument Sans`.
  - **Tokens de color:** Mapeo de variables nativas CSS (`--background`, `--foreground`, `--card`, `--primary`, `--accent`, `--border`).
  - **Clases de utilidad personalizadas (`@layer utilities`):**
    - `.glass-card`: Contenedor translúcido con desenfoque `backdrop-blur-xl`, fondo `bg-slate-900/60` y bordes degradados sutiles.
    - `.glass-glow-lime`: Sombra proyectada verde lima fluorescente (`box-shadow: 0 0 35px rgba(132, 204, 22, 0.25)`).
    - `.glass-glow-rose`: Sombra de advertencia roja (`box-shadow: 0 0 35px rgba(244, 63, 94, 0.25)`).
    - `.custom-scrollbar`: Barras de desplazamiento estilizadas y delgadas.

---

### Catálogo de Primitivos de UI (`resources/js/components/ui/*`)
Componentes base independientes de la lógica de negocio, construidos con accesibilidad y diseño consistente:
- **`button.tsx`:** Botón con variantes de estilo (default, destructive, outline, secondary, ghost, link) mediante `class-variance-authority` (CVA).
- **`input.tsx`:** Campo de texto accesible con foco coloreado y soporte para estados de error.
- **`label.tsx`:** Etiqueta asociada a inputs con tipografía semántica.
- **`card.tsx`:** Bloques de contenido modular (`Card`, `CardHeader`, `CardTitle`, `CardDescription`, `CardContent`, `CardFooter`).
- **`dialog.tsx`:** Modales emergentes construidos sobre primitivos de Radix UI con gestión de foco y bloqueo de scroll.
- **`dropdown-menu.tsx`:** Menús desplegables para selección de opciones y acciones contextuales.
- **`sheet.tsx`:** Paneles deslizantes laterales utilizados como base para drawers.
- **`table.tsx`:** Componentes semánticos de tabla (`Table`, `TableHeader`, `TableBody`, `TableRow`, `TableCell`).
- **`badge.tsx`:** Pastillas o etiquetas de estado con variantes cromáticas.
- **`avatar.tsx`:** Renderizador de fotografías de perfil con soporte para fallback de iniciales tipográficas.
- **`tooltip.tsx`:** Mensajes emergentes al posar el cursor sobre elementos gráficos o iconos.

---

### Definiciones de Tipos Globales
- **Ubicación en el código:** `resources/js/types/index.ts` (Líneas 1-41).
- **Descripción y Propósito:** Centraliza las interfaces y tipos de TypeScript compartidos en todo el frontend.
- **Estructuras principales:**
  - `User`: Definición del usuario del sistema con su rol, sucursal y datos de perfil.
  - `Auth`: Objeto de autenticación inyectado por Inertia en todas las páginas.
  - `SharedData`: Props globales compartidas por Laravel vía middleware (`HandleInertiaRequests`).
  - `BreadcrumbItem`: Elemento para barras de navegación de migas de pan.
  - `NavItem` y `NavGroup`: Estructura para ítems y secciones de barras de navegación.

---

*Documentación técnica de componentes e interfaces completada para la defensa del proyecto FitLife.*
