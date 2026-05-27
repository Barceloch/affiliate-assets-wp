# CHANGELOG

## [1.0.0] - 2024-XX-XX

### Added
- **Core del Plugin**
  - Arquitectura base inspirada en SliceWP
  - Sistema de autoloader PSR-4
  - Clases singleton para gestión principal
  - Sistema de hooks y filtros centralizado

- **Sistema de Afiliados**
  - Tabla `wp_aa_affiliates` con estados (pending, active, inactive, rejected)
  - Generación automática de códigos de referido únicos
  - URLs amigables opcionales (`/referido/CODE/`)
  - Aprobación manual o automática configurable
  - Funciones helper: `aa_create_affiliate()`, `aa_is_affiliate()`, etc.

- **Tracking de Visitas**
  - Tabla `wp_aa_visits` para registro de visitas
  - Cookies persistentes con duración configurable (default 30 días)
  - Detección de parámetro `?aa_ref=CODE`
  - Registro de IP, user agent, URL y fuente de referencia
  - Tipos de visita: direct, referral, qr_scan

- **Códigos QR**
  - Clase `Class_QR_Generator` con integración phpqrcode
  - Personalización completa (tamaño, colores, margen)
  - Cacheo con transients para mejor performance
  - Endpoints AJAX para generación y descarga
  - Tracking automático de escaneos QR

- **Panel de Administración**
  - Menú principal "AffiliateAssets" con icono dashicons-networking
  - Dashboard con widgets de estadísticas
  - Página de afiliados con tabla, filtros y búsqueda
  - Página de visitas con detalles completos
  - Configuración por pestañas (General, Tracking, QR, Emails)
  - Acciones: aprobar, rechazar, eliminar afiliados

- **Panel del Afiliado (Frontend)**
  - Shortcodes: `[aa_affiliate_area]`, `[aa_affiliate_dashboard]`, `[aa_affiliate_link]`, `[aa_affiliate_qr]`
  - Sistema de tabs estilo SliceWP
  - Vista previa de estadísticas
  - Enlaces copiables al portapapeles
  - Códigos QR descargables

- **Integración WooCommerce**
  - Hook `woocommerce_order_status_completed` para detección de membresías
  - Meta `_aa_referrer_id` en órdenes para tracking
  - Selección de producto de membresía desde admin

- **Sistema de Emails**
  - Plantillas personalizables desde admin
  - Placeholders: `{{affiliate_name}}`, `{{referral_link}}`, `{{qr_code_url}}`, `{{site_name}}`
  - Emails de: bienvenida, aprobación, rechazo

- **Base de Datos**
  - Tabla `wp_aa_affiliates` - Datos de afiliados
  - Tabla `wp_aa_visits` - Tracking de visitas
  - Tabla `wp_aa_commissions` - Estructura base para Fase 2

### Changed
- Nada (versión inicial)

### Deprecated
- Nada

### Removed
- Nada

### Fixed
- Nada

### Security
- Nonces en todas las acciones admin
- Verificación de capacidades con `current_user_can()`
- Prepared statements en todas las consultas SQL
- Escape completo de output
- Cookies httponly y secure

---

## Próximas Versiones (Roadmap)

### [2.0.0] - Sistema de Inventario Virtual
- Stock virtual por afiliado
- Descuento automático por venta
- Sistema de recarga de activos
- Integración completa con productos WooCommerce

### [2.1.0] - Comisiones Avanzadas
- Cálculo automático de comisiones
- Múltiples niveles de comisión
- Historial detallado de ganancias

### [2.2.0] - Pagos y Payouts
- Sistema de retiro de fondos
- Métodos de pago múltiples
- Historial de pagos

### [3.0.0] - Características Avanzadas
- Transferencias entre afiliados
- Marcado de recepción física
- Reportes financieros avanzados
- API REST para integraciones externas
