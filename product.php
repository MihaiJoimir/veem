<?php
include 'includes/db.php';
include 'includes/header.php';

$slug = $_GET['slug'];

/* ------------------------------------------------------------------
   Fetch product + its category in one query so we can build a breadcrumb
   ------------------------------------------------------------------*/
$stmt = $conn->prepare("SELECT p.*, c.id   AS cat_id,
                               c.name AS cat_name,
                               c.slug AS cat_slug
                        FROM products  p
                        JOIN categories c ON p.category_id = c.id
                        WHERE p.slug = ?");
$stmt->bind_param('s', $slug);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    http_response_code(404);
    echo '<h1>Product not found</h1>';
    include 'includes/footer.php';
    exit;
}

// Decide which URL style to use in the breadcrumb
$catUrl = $product['cat_slug'] ? "/category/{$product['cat_slug']}" : "/category.php?id={$product['cat_id']}";
?>

<!-- Breadcrumbs -->
<div class="breadcrumbs">
    <a href="/">Home</a> / <a href="<?= $catUrl ?>"><?= htmlspecialchars($product['cat_name']) ?></a> / <?= htmlspecialchars($product['name']) ?>
</div>

<div class="product-container">
    <div class="product-image">
        <img src="/images/<?= $product['image'] ?>" alt="<?= htmlspecialchars($product['name']) ?>">
    </div>

    <div class="product-info">
        <h1><?= htmlspecialchars($product['name']) ?></h1>
        <p class="price">
            <?php if ($product['sale_price']) : ?>
                <span class="old-price">€<?= $product['price'] ?></span> €<?= $product['sale_price'] ?>
            <?php else : ?>
                €<?= $product['price'] ?>
            <?php endif; ?>
        </p>

        <!-- Attributes -->
        <div class="attributes">
            <?php
            $attrs = $conn->query("SELECT a.name, pa.value
                                     FROM product_attributes pa
                                     JOIN attributes a ON pa.attribute_id = a.id
                                     WHERE pa.product_id = {$product['id']}");
            while ($attr = $attrs->fetch_assoc()) {
                echo "<p><strong>{$attr['name']}:</strong> {$attr['value']}</p>";
            }
            ?>
        </div>

        <p class="description"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>