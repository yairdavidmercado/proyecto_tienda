# Proyecto tienda digital

Proyecto base desarrollado con **Bootstrap 5, HTML, CSS, JavaScript, PHP y MySQL**.

## Qué incluye

- Landing page pública moderna en colores negro, blanco y gris.
- Estilo premium con transparencias, curvas y look tipo Mac.
- Navegación por categorías.
- Catálogo filtrado por país según la selección del cliente.
- Tarjetas de productos con botón directo a WhatsApp.
- Panel administrativo básico para:
  - iniciar sesión
  - crear categorías por país
  - crear productos por país
  - editar textos generales y WhatsApp

## Estructura

- `index.php`: sitio público.
- `admin/`: panel administrativo.
- `config/db.php`: conexión PDO.
- `sql/database.sql`: estructura y datos base.
- `sql/seed_combos.sql`: actualización incremental de estructura y datos base por país.
- `assets/css/style.css`: estilos principales.
- `assets/js/main.js`: scripts básicos.

## Instalación

1. Copia la carpeta del proyecto a tu hosting o servidor local.
2. Crea una base de datos MySQL llamada `tienda_digital` o cambia los datos en `config/db.php`.
3. Importa el archivo `sql/database.sql`.
4. Si ya tienes una base existente y solo quieres agregar la categoría Combos, Pantallas individuales y los productos base, ejecuta `sql/seed_combos.sql`.
5. Verifica usuario, contraseña y host en `config/db.php`.
6. Abre el sitio desde el navegador.
7. Accede al panel en `admin/login.php`.

## Acceso demo

- Correo: `admin@demo.com`
- Contraseña: `admin123`

## Recomendaciones para producción

- Cambiar credenciales del administrador.
- Agregar carga real de imágenes en lugar de URL externas.
- Añadir edición y no solo creación/eliminación de productos.
- Implementar CSRF y validaciones más estrictas.
- Agregar módulo de registro/login de clientes si luego lo necesitas.
