<?php
declare(strict_types=1);

// --- Load gallery data ---
$jsonPath = __DIR__ . '/galleries.json';
$galleries = json_decode(file_get_contents($jsonPath), true);

// Build slug index for O(1) lookups
$photoIndex = [];
foreach ($galleries as $gi => $gallery) {
    foreach ($gallery['photos'] as $pi => $photo) {
        $photoIndex[$photo['slug']] = [
            'name'    => $photo['name'],
            'image'   => $photo['image'],
            'gallery' => $gallery['title'],
            'gi'      => $gi,
            'pi'      => $pi,
        ];
    }
}

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
$baseUrl = '/publicaciones/memorias-geograficas/fotos';

// --- Helper: escape for HTML output ---
function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

// --- Shared layout pieces ---
function renderHead(string $title): void { ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= e($title) ?> — Víctor Hugo Vallejos</title>
  <link rel="shortcut icon" href="/img/favicon2.png" type="image/png" />
  <link rel="apple-touch-icon" href="/img/favicon2.png" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="/css/styles.css" />
  <link rel="stylesheet" href="/css/fotos.css" />
</head>
<?php }

function renderNav(): void { ?>
<body>
  <a href="#main-content" class="skip-link">Saltar al contenido principal</a>
  <input type="checkbox" id="nav-toggle" class="nav-toggle" aria-hidden="true" />
  <header class="site-header" role="banner">
    <div class="container header-inner">
      <a href="/index.html" class="site-logo" aria-label="VH Vallejos — Inicio">
        <img src="/img/vhvalle5.png" alt="VH Vallejos" class="logo-img" />
      </a>
      <label for="nav-toggle" class="nav-toggle-label" aria-label="Abrir menú de navegación">
        <span></span><span></span><span></span>
      </label>
      <nav aria-label="Navegación principal">
        <ul class="main-nav" role="list">
          <li><a href="/index.html">Inicio</a></li>
          <li><a href="/biografia.html">Biografía</a></li>
          <li><a href="/geografia.html">Geografía</a></li>
          <li><a href="/musica.html">Música</a></li>
          <li><a href="/libros.html">Libros</a></li>
          <li><a href="/contacto.html">Contacto</a></li>
        </ul>
      </nav>
    </div>
  </header>
<?php }

function renderFooter(): void { ?>
  <footer class="site-footer" role="contentinfo">
    <div class="container">
      <div class="footer-inner">
        <div class="footer-brand">
          <a href="/index.html" class="footer-logo">
            <img src="/img/vhvalle5.png" alt="VH Vallejos" class="footer-logo-img" />
          </a>
          <p>Geógrafo, músico y escritor argentino.</p>
        </div>
        <div class="footer-col">
          <h4>Secciones</h4>
          <ul>
            <li><a href="/biografia.html">Biografía</a></li>
            <li><a href="/geografia.html">Geografía</a></li>
            <li><a href="/musica.html">Música</a></li>
            <li><a href="/libros.html">Libros</a></li>
            <li><a href="/contacto.html">Contacto</a></li>
          </ul>
        </div>
        <div class="footer-col footer-contact">
          <h4>Contacto</h4>
          <p>Para consultas o colaboraciones:</p>
          <p><a href="/contacto.html">Formulario de contacto</a></p>
        </div>
      </div>
      <div class="footer-bottom">
        <p>&copy; 2025 Víctor Hugo Vallejos. Todos los derechos reservados.</p>
      </div>
    </div>
  </footer>
</body>
</html>
<?php }

// ============================================================
// ROUTE: Individual photo — /fotos/{slug}
// ============================================================
if ($slug !== '' && isset($photoIndex[$slug])) {
    $photo = $photoIndex[$slug];

    // Find prev/next within same gallery
    $gallery = $galleries[$photo['gi']];
    $photos  = $gallery['photos'];
    $pi      = $photo['pi'];
    $prev    = $pi > 0 ? $photos[$pi - 1] : null;
    $next    = ($pi < count($photos) - 1) ? $photos[$pi + 1] : null;
    $galleryId = trim(mb_strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $gallery['title']), 'UTF-8'), '-');

    renderHead($photo['name']);
    renderNav();
?>
  <main id="main-content">
    <section class="photo-view">
      <div class="container">

        <nav class="breadcrumb" aria-label="Navegación de ubicación">
          <a href="/libros.html">Libros</a> ›
          <a href="<?= $baseUrl ?>/">Memorias Geográficas — Fotos</a> ›
          <span><?= e($photo['name']) ?></span>
        </nav>

        <h1 class="photo-title"><?= e($photo['name']) ?></h1>
        <p class="photo-gallery-label">Galería: <?= e($photo['gallery']) ?></p>

        <figure class="photo-figure">
          <img src="<?= $baseUrl ?>/<?= e($photo['image']) ?>" alt="<?= e($photo['name']) ?>" />
        </figure>

        <nav class="photo-nav" aria-label="Navegación entre fotos">
          <?php if ($prev): ?>
            <a href="<?= $baseUrl ?>/<?= e($prev['slug']) ?>/" class="photo-nav-link photo-nav-prev">
              ← <?= e($prev['name']) ?>
            </a>
          <?php else: ?>
            <span class="photo-nav-link photo-nav-disabled"></span>
          <?php endif; ?>

          <a href="<?= $baseUrl ?>/#<?= e($galleryId) ?>" class="btn-outline">Volver a la galería</a>

          <?php if ($next): ?>
            <a href="<?= $baseUrl ?>/<?= e($next['slug']) ?>/" class="photo-nav-link photo-nav-next">
              <?= e($next['name']) ?> →
            </a>
          <?php else: ?>
            <span class="photo-nav-link photo-nav-disabled"></span>
          <?php endif; ?>
        </nav>

      </div>
    </section>
  </main>
<?php
    renderFooter();
    exit;
}

// ============================================================
// ROUTE: 404 — slug provided but not found
// ============================================================
if ($slug !== '') {
    http_response_code(404);
    renderHead('Foto no encontrada');
    renderNav();
?>
  <main id="main-content">
    <section class="photo-view">
      <div class="container" style="text-align:center; padding: 80px 0;">
        <h1 class="photo-title">Foto no encontrada</h1>
        <p style="margin-bottom:2rem;">La foto solicitada no existe en nuestra galería.</p>
        <a href="<?= $baseUrl ?>/" class="btn-outline">Ir a la galería</a>
      </div>
    </section>
  </main>
<?php
    renderFooter();
    exit;
}

// ============================================================
// ROUTE: Gallery index — /fotos/
// ============================================================
renderHead('Memorias Geográficas — Fotos');
renderNav();
?>
  <main id="main-content">

    <section class="page-hero">
      <div class="container">
        <h1>Memorias Geográficas</h1>
        <p class="page-subtitle">Galería de fotos</p>
      </div>
    </section>

    <section class="gallery-toc">
      <div class="container">
        <nav aria-label="Índice de galerías">
          <h2 class="toc-title">Galerías</h2>
          <ul class="toc-list">
            <?php foreach ($galleries as $gallery):
                $id = mb_strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $gallery['title']), 'UTF-8');
                $id = trim($id, '-');
                $count = count($gallery['photos']);
            ?>
              <li><a href="#<?= e($id) ?>"><?= e($gallery['title']) ?> <span class="toc-count">(<?= $count ?>)</span></a></li>
            <?php endforeach; ?>
          </ul>
        </nav>
      </div>
    </section>

    <div class="galleries-container">
      <div class="container">
        <?php foreach ($galleries as $gallery):
            $id = mb_strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $gallery['title']), 'UTF-8');
            $id = trim($id, '-');
        ?>
          <section class="gallery-section" id="<?= e($id) ?>">
            <h2 class="gallery-section-title"><?= e($gallery['title']) ?></h2>
            <div class="gallery-grid">
              <?php foreach ($gallery['photos'] as $photo): ?>
                <a href="<?= $baseUrl ?>/<?= e($photo['slug']) ?>/" class="gallery-item">
                  <img src="<?= $baseUrl ?>/<?= e($photo['image']) ?>"
                       alt="<?= e($photo['name']) ?>"
                       loading="lazy" />
                  <span class="gallery-item-label"><?= e($photo['name']) ?></span>
                </a>
              <?php endforeach; ?>
            </div>
          </section>
        <?php endforeach; ?>
      </div>
    </div>

  </main>
<?php renderFooter(); ?>
