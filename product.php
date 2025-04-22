<?php
include 'includes/db.php';
include 'includes/header.php';

// Get product slug from URL
$slug = $_GET['slug'] ?? null;

// Fetch product and its category in one query
$stmt = $conn->prepare(
    "SELECT p.*, c.id AS cat_id, c.name AS cat_name, c.slug AS cat_slug
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

// Build category URL for breadcrumb
$catUrl = '/' . ($product['cat_slug'] ?: 'category.php?id=' . $product['cat_id']);
?>

<!-- Breadcrumbs -->
<div class="breadcrumbs">
    <a href="/">Home</a> /
    <a href="<?= htmlspecialchars($catUrl, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($product['cat_name'], ENT_QUOTES, 'UTF-8') ?></a> /
    <?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>
</div>

<div class="product-container">
    <div class="product-image">
        <img src="/images/<?= htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8') ?>"
             alt="<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>">
    </div>

    <div class="product-info">
        <h1><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="price">
            <?php if ($product['sale_price']): ?>
                <span class="old-price">€<?= htmlspecialchars($product['price'], ENT_QUOTES, 'UTF-8') ?></span>
                €<?= htmlspecialchars($product['sale_price'], ENT_QUOTES, 'UTF-8') ?>
            <?php else: ?>
                €<?= htmlspecialchars($product['price'], ENT_QUOTES, 'UTF-8') ?>
            <?php endif; ?>
        </p>

        <div class="attributes">
            <?php
            $attrs = $conn->query(
                "SELECT a.name, pa.value
                 FROM product_attributes pa
                 JOIN attributes a ON pa.attribute_id = a.id
                 WHERE pa.product_id = {$product['id']}"
            );
            while ($attr = $attrs->fetch_assoc()) {
                echo '<p><strong>' . htmlspecialchars($attr['name'], ENT_QUOTES, 'UTF-8') . ':</strong> ' . htmlspecialchars($attr['value'], ENT_QUOTES, 'UTF-8') . '</p>';
            }
            ?>
        </div>

        <?php if (!empty($product['url'])): ?>
            <p class="buy-now">
                <a href="<?= htmlspecialchars($product['url'], ENT_QUOTES, 'UTF-8') ?>"
                   class="btn btn-primary" target="_blank" rel="noopener">
                    COMANDĂ
                </a>
            </p>
        <?php endif; ?>

        
    </div>
</div>
<!-- Description Accordion Card -->
<div class="card accordion-card" id="descAccordion">
    <div class="card-header accordion-header">
        Descriere
        <span class="accordion-toggle">&#x2795;</span>
    </div>
    <div class="card-body accordion-body">
        <?= nl2br(htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8')) ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>