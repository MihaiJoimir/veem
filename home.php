<?php include 'includes/header.php'; ?>
<section class="slider">
    <div class="slide">
        <img src="/images/slide1.jpg" alt="Slide 1">
    </div>
</section>

<section class="categories">
    <h2>Main Categories</h2>
    <div class="category-list">
        <?php
        include 'includes/db.php';
        $result = $conn->query("SELECT * FROM categories LIMIT 4");
        while($row = $result->fetch_assoc()) {
            echo "<a href='/category.php?id={$row['id']}' class='category-item'>{$row['name']}</a>";
        }
        ?>
    </div>
</section>

<section class="new-products">
    <h2>New Products</h2>
    <div class="product-grid">
        <?php
        $result = $conn->query("SELECT * FROM products ORDER BY id DESC LIMIT 6");
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
</section>
<?php include 'includes/footer.php'; ?>
