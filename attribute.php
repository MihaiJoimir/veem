<?php
/*───────────────────────────────────────────────────────────────
   Generic attribute page  –  /color/blue, /size/large, etc.
────────────────────────────────────────────────────────────────*/
include 'includes/header.php';
include 'includes/db.php';

$attr_slug  = $_GET['attr']  ?? '';
$value_slug = $_GET['value'] ?? '';

if ($attr_slug === '' || $value_slug === '') {
    http_response_code(404);
    echo '<h1>Page not found</h1>';
    include 'includes/footer.php';
    exit;
}

// -------------------------------------------------------------
// 1. Resolve attribute by slugifying each attributes.name
// -------------------------------------------------------------
$attribute = null;
$res = $conn->query('SELECT id, name FROM attributes');
while ($row = $res->fetch_assoc()) {
    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $row['name']));
    if ($slug === $attr_slug) {
        $attribute = $row;
        break;
    }
}

if (!$attribute) {
    http_response_code(404);
    echo '<h1>Attribute not found</h1>';
    include 'includes/footer.php';
    exit;
}

$attr_id   = (int) $attribute['id'];
$attr_name = $attribute['name'];

// -------------------------------------------------------------
// 2. Prepare value variants for matching
// -------------------------------------------------------------
$value_for_like = str_replace('-', ' ', $value_slug);
$value_param1   = strtolower($value_for_like);
$value_param2   = strtolower(str_replace(' ', '-', $value_for_like));

// -------------------------------------------------------------
// 3. Fetch matching products
// -------------------------------------------------------------
$sql = "SELECT DISTINCT p.*
        FROM products p
        JOIN product_attributes pa ON p.id = pa.product_id
        WHERE pa.attribute_id = ?
          AND (LOWER(pa.value) = ?
               OR LOWER(REPLACE(pa.value, ' ', '-')) = ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('iss', $attr_id, $value_param1, $value_param2);
$stmt->execute();
$products = $stmt->get_result();
?>

<div class="breadcrumbs">
    <a href="/">Home</a> /
    <?= htmlspecialchars(ucfirst($attr_name)) ?> /
    <?= htmlspecialchars(ucfirst($value_for_like)) ?>
</div>

<h2 class="page-title">
    <?= htmlspecialchars(ucfirst($attr_name)) ?>:
    <em><?= htmlspecialchars(ucfirst($value_for_like)) ?></em>
</h2>

<?php if ($products->num_rows === 0): ?>
    <p>No products match this selection.</p>
<?php else: ?>
    <div class="product-grid">
        <?php while ($row = $products->fetch_assoc()): ?>
            <div class="product-item">
                <a href="/<?= htmlspecialchars($row['slug']) ?>">
                    <img
                      src="/images/<?= htmlspecialchars($row['image']) ?>"
                      alt="<?= htmlspecialchars($row['name']) ?>">
                    <h3><?= htmlspecialchars($row['name']) ?></h3>
                    <p class="price">
                        <?php if ($row['sale_price']): ?>
                            <span class="old-price">€<?= htmlspecialchars($row['price']) ?></span>
                            €<?= htmlspecialchars($row['sale_price']) ?>
                        <?php else: ?>
                            €<?= htmlspecialchars($row['price']) ?>
                        <?php endif; ?>
                    </p>
                </a>
            </div>
        <?php endwhile; ?>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>