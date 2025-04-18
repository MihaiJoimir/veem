<?php
include 'includes/db.php';
include 'includes/header.php';

$slug = $_GET['slug'];
$product = $conn->query("SELECT * FROM products WHERE slug = '$slug'")->fetch_assoc();
?>
<div class="breadcrumbs">Home / <?= $product['name'] ?></div>

<div class="product-container">
    <div class="product-image">
        <img src="/images/<?= $product['image'] ?>" alt="<?= $product['name'] ?>">
    </div>
    
    <div class="product-info">
        <h1><?= $product['name'] ?></h1>
        <div class="prices">
            <?php if($product['sale_price']): ?>
                <span class="sale-price">€<?= $product['sale_price'] ?></span>
                <span class="old-price">€<?= $product['price'] ?></span>
            <?php else: ?>
                <span class="price">€<?= $product['price'] ?></span>
            <?php endif; ?>
        </div>
        
        <div class="attributes">
            <h3>Specifications</h3>
            <?php
            $attrs = $conn->query("
                SELECT a.name, pa.value 
                FROM product_attributes pa
                JOIN attributes a ON pa.attribute_id = a.id
                WHERE pa.product_id = {$product['id']}
            ");
            
            while($attr = $attrs->fetch_assoc()) {
                echo "<p><strong>{$attr['name']}:</strong> {$attr['value']}</p>";
            }
            ?>
        </div>
        
        <p class="description"><?= $product['description'] ?></p>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
