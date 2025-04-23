<?php
include 'includes/db.php';
include 'includes/header.php';

// Get the product slug from the URL
$slug = $_GET['slug'] ?? null;

// Fetch product and its category details
$stmt = $conn->prepare(
    "SELECT p.*, c.id   AS cat_id,
               c.name AS cat_name,
               c.slug AS cat_slug
     FROM products p
     JOIN categories c ON p.category_id = c.id
     WHERE p.slug = ?"
);
$stmt->bind_param('s', $slug);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    http_response_code(404);
    echo '<h1>Product not found</h1>';
    include 'includes/footer.php';
    exit;
}

// Prepare breadcrumb URL for category
$catUrl = '/' . ($product['cat_slug'] ?: 'category.php?id=' . $product['cat_id']);
?>

<!-- Breadcrumbs -->
<div class="breadcrumbs">
    <a href="/">Home</a> /
    <a href="<?= htmlspecialchars($catUrl, ENT_QUOTES, 'UTF-8') ?>">
      <?= htmlspecialchars($product['cat_name'], ENT_QUOTES, 'UTF-8') ?>
    </a> /
    <?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>
</div>

<div class="product-container">
    <div class="product-image">
        <img src="/images/<?= htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8') ?>"
             alt="<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>">
    </div>

    <div class="product-info">
        <h1><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></h1>

        <div class="attributes">
            <?php
            $attrs = $conn->query(
                "SELECT a.name, pa.value
                 FROM product_attributes pa
                 JOIN attributes a ON pa.attribute_id = a.id
                 WHERE pa.product_id = {$product['id']}"
            );
            while ($attr = $attrs->fetch_assoc()) {
                echo '<p><strong>'
                     . htmlspecialchars($attr['name'], ENT_QUOTES, 'UTF-8')
                     . ':</strong> '
                     . htmlspecialchars($attr['value'], ENT_QUOTES, 'UTF-8')
                     . '</p>';
            }
            ?>
        </div>

        <p class="price product">
            <?php if ($product['sale_price']) : ?>
                <span class="old-price">
                  €<?= htmlspecialchars($product['price'], ENT_QUOTES, 'UTF-8') ?>
                </span>
                €<?= htmlspecialchars($product['sale_price'], ENT_QUOTES, 'UTF-8') ?>
            <?php else : ?>
                €<?= htmlspecialchars($product['price'], ENT_QUOTES, 'UTF-8') ?>
            <?php endif; ?>
        </p>
        
        <fieldset class="card-livrare-din-stoc">
            <legend>Livrare din stoc</legend>
            <ul class="livrare-stoc">
                <li class="ast-custom-payment">
                    <!-- credit‑card SVG -->
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">
                        <path d="M0 432c0 26.5 21.5 48 48 48h480c26.5 0 48-21.5 48-48V256H0v176zm192-68c0-6.6 5.4-12 12-12h136c6.6 0 12 5.4 12 12v40c0 6.6-5.4 12-12 12H204c-6.6 0-12-5.4-12-12v-40zm-128 0c0-6.6 5.4-12 12-12h72c6.6 0 12 5.4 12 12v40c0 6.6-5.4 12-12 12H76c-6.6 0-12-5.4-12-12v-40zM576 80v48H0V80c0-26.5 21.5-48 48-48h480c26.5 0 48 21.5 48 48z"/>
                    </svg>
                </li>
                <li class="ast-custom-payment">
                    <!-- truck SVG -->
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512">
                        <path d="M621.3 237.3l-58.5-58.5c-12-12-28.3-18.7-45.3-18.7H480V64c0-17.7-14.3-32-32-32H32C14.3 32 0 46.3 0 64v336c0 44.2 35.8 80 80 80 26.3 0 49.4-12.9 64-32.4 14.6 19.6 37.7 32.4 64 32.4 44.2 0 80-35.8 80-80 0-5.5-.6-10.8-1.6-16h163.2c-1.1 5.2-1.6 10.5-1.6 16 0 44.2 35.8 80 80 80s80-35.8 80-80c0-5.5-.6-10.8-1.6-16H624c8.8 0 16-7.2 16-16v-85.5c0-17-6.7-33.2-18.7-45.2zM80 432c-17.6 0-32-14.4-32-32s14.4-32 32-32 32 14.4 32 32-14.4 32-32 32zm128 0c-17.6 0-32-14.4-32-32s14.4-32 32-32 32 14.4 32 32-14.4 32-32 32z"/>
                    </svg>
                </li>
                <li class="ast-custom-payment">
                    <!-- arrow SVG -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                        <path d="M5 3l3.057-3 11.943 12-11.943 12-3.057-3 9-9z"/>
                    </svg>
                </li>
                <li class="ast-custom-payment">
                    <!-- box SVG -->
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <path d="M509.5 184.6L458.9 32.8C452.4 13.2 434.1 0 413.4 0H272v192h238.7c-.4-2.5-.4-5-1.2-7.4zM240 0H98.6c-20.7 0-39 13.2-45.5 32.8L2.5 184.6c-.8 2.4-.8 4.9-1.2 7.4H240V0zM0 224v240c0 26.5 21.5 48 48 48h416c26.5 0 48-21.5 48-48V224H0z"/>
                    </svg>
                </li>
            </ul>
        </fieldset>
        <?php if (!empty($product['url'])): ?>
            <p class="buy-now">
                <a href="<?= htmlspecialchars($product['url'], ENT_QUOTES, 'UTF-8') ?>"
                   class="btn btn-primary" target="_blank" rel="noopener">
                    COMANDĂ
                </a>
            </p>
        <?php endif; ?>
        <div class="attributes">
    </div>
    </div>
</div>
<?php
// Description accordion above the product card
$rawDesc  = $product['description'];
if (strlen($rawDesc) > 300) {
    $short  = nl2br(htmlspecialchars(substr($rawDesc, 0, 300), ENT_QUOTES, 'UTF-8'));
    $rest   = nl2br(htmlspecialchars(substr($rawDesc, 300), ENT_QUOTES, 'UTF-8'));
    ?>
    <div class="accordion-card">
        <div class="accordion-header">
            Descriere
            <span class="accordion-toggle">+</span>
        </div>
        <div class="accordion-body">
            <p><?= $short ?><span class="more-text"><?= $rest ?></span></p>
        </div>
    </div>
<?php } else {
    ?>
    <div class="accordion-card open">
        <div class="accordion-header">
            Descriere
            <span class="accordion-toggle">+</span>
        </div>
        <div class="accordion-body">
            <p><?= nl2br(htmlspecialchars($rawDesc, ENT_QUOTES, 'UTF-8')) ?></p>
        </div>
    </div>
<?php } ?>

<?php include 'includes/footer.php'; ?>