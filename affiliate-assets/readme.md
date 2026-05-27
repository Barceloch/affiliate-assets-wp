# AffiliateAssets

**Versión:** 1.0.0  
**Requiere WordPress:** 6.8+  
**Requiere PHP:** 7.4+  
**Requiere WooCommerce:** 5.0+  

## Descripción

AffiliateAssets es un sistema de gestión de afiliados e inversores para WordPress con tracking avanzado, generación de códigos QR y panel de afiliado personalizado. 

Inspirado arquitectónicamente en el plugin SliceWP (https://github.com/WordPressBugBounty/plugins-slicewp), AffiliateAssets implementa patrones de diseño similares pero con una lógica de negocio propia orientada a programas de inversión.

## Características Principales

### 🎯 Sistema de Afiliados
- Registro automático al comprar producto de membresía ($500)
- Estados: Pendiente, Activo, Inactivo, Rechazado
- Aprobación manual o automática configurable
- Códigos de referido únicos generados automáticamente

### 🔗 Tracking de Referidos
- Cookies persistentes (duración configurable, default 30 días)
- Detección de parámetro `?aa_ref=CODE` en URLs
- URLs amigables opcionales: `/referido/CODE/`
- Registro automático de visitas con IP, user agent y fuente

### 📱 Códigos QR
- Generación automática de QR para enlaces de referido
- Personalización: tamaño, colores frente/fondo
- Descarga en PNG/SVG
- Tracking de escaneos QR como tipo de visita especial
- Cacheo con transients para mejor performance

### 📊 Panel de Administración
- Dashboard con estadísticas principales
- Gestión de afiliados con filtros y búsqueda
- Lista de visitas con detalles completos
- Configuración por pestañas (General, Tracking, QR, Emails)
- Plantillas de email personalizables

### 👤 Panel del Afiliado
- Sistema de tabs estilo SliceWP
- Dashboard con overview de estadísticas
- Enlaces de referido copiables
- Códigos QR descargables
- Generador de deep links a productos/páginas
- Botones de compartir en redes sociales

## Instalación

### Requisitos Previos
1. WordPress 6.8 o superior
2. PHP 7.4 o superior
3. WooCommerce 5.0 o superior instalado y activado

### Pasos de Instalación

1. **Subir el plugin**
   - Sube la carpeta `affiliate-assets` a `/wp-content/plugins/`
   - O usa el instalador de plugins de WordPress

2. **Activar el plugin**
   - Ve a Plugins en el admin de WordPress
   - Haz clic en "Activar" en AffiliateAssets

3. **Configurar**
   - Ve a AffiliateAssets → Configuración
   - Configura el producto de membresía de WooCommerce
   - Ajusta las opciones de tracking y QR según necesites

4. **Crear página de panel**
   - Crea una página nueva llamada "Panel de Afiliado"
   - Añade el shortcode `[aa_affiliate_area]`
   - Publica la página

## Configuración Inicial

### 1. Producto de Membresía
En WooCommerce, crea un producto con:
- Precio: $500 (o tu moneda local)
- Tipo: Producto simple o Suscripción
- Nombre: "Membresía de Afiliado"

Luego en AffiliateAssets → Configuración → General:
- Selecciona el producto creado en "Producto de Membresía"

### 2. Aprobación de Afiliados
- **Aprobación Automática:** Los afiliados se activan inmediatamente
- **Aprobación Manual:** Debes aprobar cada solicitud desde Admin → Afiliados

### 3. URLs Amigables (Opcional)
Para usar URLs tipo `/referido/CODE/`:
1. Activa "URLs Amigables" en Configuración → Tracking
2. Define el slug (default: "referido")
3. Ve a Ajustes → Enlaces permanentes y guarda cambios

## Shortcodes Disponibles

| Shortcode | Descripción |
|-----------|-------------|
| `[aa_affiliate_area]` | Panel completo de afiliado con tabs |
| `[aa_affiliate_dashboard]` | Solo el dashboard del panel |
| `[aa_affiliate_link]` | Enlace de referido copiable |
| `[aa_affiliate_qr]` | Código QR descargable |

### Ejemplos de Uso

```php
// Panel completo en una página
[aa_affiliate_area]

// Solo dashboard
[aa_affiliate_dashboard]

// Enlace específico para un afiliado
[aa_affiliate_link affiliate_id="123"]

// QR con tamaño personalizado
[aa_affiliate_qr size="400"]
```

## Funciones PHP Principales

```php
// Obtener afiliado actual
$affiliate = aa_get_current_affiliate();

// Verificar si usuario es afiliado
if (aa_is_affiliate($user_id)) { ... }

// Crear afiliado manualmente
aa_create_affiliate($user_id);

// Obtener settings
$settings = aa_get_settings();

// Obtener tasa de comisión
$rate = aa_get_commission_rate($product_id);
```

## Estructura de Base de Datos

El plugin crea 3 tablas con prefijo `wp_aa_`:

- `wp_aa_affiliates` - Datos de afiliados
- `wp_aa_visits` - Tracking de visitas
- `wp_aa_commissions` - Comisiones (Fase 2)

## Próximas Funcionalidades (Fase 2)

- 📦 Sistema de Inventario Virtual
- 📉 Descuento de Stock por Ventas
- 💰 Cálculo Automático de Comisiones
- 🔄 Transferencias entre Afiliados
- 🏷️ Marcado de Recepción Física
- 💸 Sistema de Pagos/Payouts

## Créditos

Este plugin está inspirado arquitectónicamente en **SliceWP** (https://github.com/WordPressBugBounty/plugins-slicewp). 
Los patrones de diseño, estructura de carpetas y organización del código siguen las mejores prácticas observadas en SliceWP, 
pero la lógica de negocio es completamente original y diferente.

## Licencia

GPL v2 o posterior

## Soporte

Para soporte técnico, reportar bugs o solicitar características:
- Email: support@example.com
- Web: https://example.com/support

---

*Desarrollado con ❤️ para WordPress*
