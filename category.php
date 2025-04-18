<?php include 'includes/header.php';
include 'includes/db.php';

$category_id = $_GET['id'] ?? 1;
?>
<div class="category-container">
    <aside class="sidebar">
        <h3>Filters</h3>
        <?php
        $attributes = $conn->query("SELECT * FROM attributes");
        while($attr = $attributes->fetch_assoc()) {
            echo "<div class='filter-group'>
                    <h4>{$attr['name']}</h4>";
            $values = $conn->query("SELECT DISTINCT value FROM product_attributes WHERE attribute_id = {$attr['id']}");
            while($val = $values->fetch_assoc()) {
                echo "<label><input type='checkbox'> {$val['value']}</label>";
            }
            echo "</div>";
        }
        ?>
    </aside>
    
    <div class="product-grid">
        <?php
        $result = $conn->query("SELECT * FROM products WHERE category_id = $category_id");
        while($row = $result->fetch_assoc()) {
            echo "<div class='product-item'>
                    <a href='/{$row['slug']}'>
                        <img src='/images/{$row['image']}' alt='{$row['name']}'>
                        <h3>{$row['name']}</h3>
                        <p class='price'>".
                        ($row['sale_price'] ? 
                        "<span class='old-price'>€{$row['price']}</span> €{$row['sale_price']}" : 
                        "€{$row['price']}").
                        "</p>
                    </a>
                  </div>";
        }
        ?>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
