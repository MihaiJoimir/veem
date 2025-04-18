<?php
include 'includes/header.php';
include 'includes/db.php';

/* -------------------------------------------------------------------
   CATEGORY IDENTIFICATION
   -------------------------------------------------------------------
   Accept either numeric ?id=3 or slug via ?slug=laptops to enable
   /category/laptops URLs.  Falls back to all products if none given.
--------------------------------------------------------------------*/

$category_id   = null;
$category_slug = $_GET['slug'] ?? null; // pretty URL, e.g. /category/laptops

if ($category_slug) {
    // Look up numeric id from the slug
    $stmt = $conn->prepare('SELECT id FROM categories WHERE slug = ?');
    $stmt->bind_param('s', $category_slug);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $category_id = $row['id'];
    } else {
        http_response_code(404);
        echo '<h1>Category not found</h1>';
        include 'includes/footer.php';
        exit;
    }
} elseif (isset($_GET['id'])) {
    $category_id = intval($_GET['id']); // legacy numeric parameter
}

// Checkbox filters
$filter_categories = $_GET['filter_categories'] ?? [];
?>

<div class="category-container">
    <aside class="sidebar">
        <h3>Filters</h3>
        <!-- Important: absolute path so it works from /category/slug URL  -->
        <form method="GET" action="/category.php">
            <?php
            /* Preserve whichever identifier was used so re‑submitting the
               form (when ticking checkboxes) doesn’t lose the context. */
            if ($category_slug) {
                echo "<input type='hidden' name='slug' value='" .
                     htmlspecialchars($category_slug, ENT_QUOTES, 'UTF-8') .
                     "'>";
            } elseif ($category_id !== null) {
                echo "<input type='hidden' name='id' value='{$category_id}'>";
            }
            ?>

            <!-- Category Filter -->
            <div class="filter-group">
                <h4>Categories</h4>
                <?php
                $categories = $conn->query('SELECT id, name FROM categories ORDER BY name ASC');
                while ($cat = $categories->fetch_assoc()) {
                    $checked = in_array($cat['id'], $filter_categories) ? 'checked' : '';
                    echo "<label><input type='checkbox' name='filter_categories[]' value='{$cat['id']}' {$checked}> {$cat['name']}</label>";
                }
                ?>
            </div>

            <!-- Attribute Filters (optional) -->
            <?php
            $attributes = $conn->query('SELECT * FROM attributes');
            while ($attr = $attributes->fetch_assoc()) {
                echo "<div class='filter-group'>\n                        <h4>{$attr['name']}</h4>";

                $values = $conn->query("SELECT DISTINCT value FROM product_attributes WHERE attribute_id = {$attr['id']}");
                while ($val = $values->fetch_assoc()) {
                    $name     = "attr_{$attr['id']}";
                    $value    = $val['value'];
                    $checked  = isset($_GET[$name]) && is_array($_GET[$name]) && in_array($value, $_GET[$name]) ? 'checked' : '';
                    echo "<label><input type='checkbox' name='{$name}[]' value='{$value}' {$checked}> {$value}</label>";
                }
                echo "</div>";
            }
            ?>

            <button type="submit">Apply Filters</button>
        </form>
    </aside>

    <div class="product-grid">
        <?php
        //-------------------------------------------------------------
        // Build SQL WHERE clause
        //-------------------------------------------------------------
        $whereParts = [];

        // Category logic
        if (!empty($filter_categories)) {
            $ids = array_map('intval', $filter_categories);
            $whereParts[] = 'category_id IN (' . implode(',', $ids) . ')';
        } elseif ($category_id !== null) {
            $whereParts[] = 'category_id = ' . $category_id;
        }

        // Attribute logic
        foreach ($_GET as $key => $vals) {
            if (strpos($key, 'attr_') === 0 && is_array($vals) && !empty($vals)) {
                $attr_id   = intval(substr($key, 5));
                $safe_vals = array_map([$conn, 'real_escape_string'], $vals);
                $vals_list = "'" . implode("','", $safe_vals) . "'";
                $whereParts[] = "id IN (SELECT product_id FROM product_attributes WHERE attribute_id = {$attr_id} AND value IN ({$vals_list}))";
            }
        }

        $where = $whereParts ? implode(' AND ', $whereParts) : '1';

        // Fetch products
        $result = $conn->query("SELECT * FROM products WHERE {$where}");
        while ($row = $result->fetch_assoc()) {
            echo "<div class='product-item'>
                    <a href='/{$row['slug']}'>
                        <img src='/images/{$row['image']}' alt='{$row['name']}'>
                        <h3>{$row['name']}</h3>" .
                        "<p class='price'>" .
                            ($row['sale_price'] ?
                                "<span class='old-price'>€{$row['price']}</span> €{$row['sale_price']}" :
                                "€{$row['price']}") .
                        "</p>
                    </a>
                  </div>";
        }
        ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
