<?php
include 'includes/db.php';

// Get slug and filter parameters
$slug              = $_GET['slug'] ?? null;
$filter_categories = $_GET['filter_categories'] ?? [];
$filtering         = isset($_GET['filtering']);

// Determine if slug is a category; otherwise treat as product
$category_id = null;
if ($slug) {
    $stmt = $conn->prepare('SELECT id FROM categories WHERE slug = ?');
    $stmt->bind_param('s', $slug);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $category_id = (int)$row['id'];
    } else {
        // Not a category → show product page
        require 'product.php';
        exit;
    }
}

// Initial visit to a category slug: auto‑select that one category
if ($slug && !$filtering && empty($filter_categories)) {
    $filter_categories = [$category_id];
}

include 'includes/header.php';
?>

<div class="category-container">
    <aside class="sidebar">
        <h3>Filters</h3>
        <form method="GET" action="">
            <!-- Flag to detect empty‑checkbox submission -->
            <input type="hidden" name="filtering" value="1">
            <?php if ($slug): ?>
                <input type="hidden" name="slug" value="<?= htmlspecialchars($slug, ENT_QUOTES) ?>">
            <?php endif; ?>

            <div class="filter-group">
                <h4>Categories</h4>
                <?php
                $cats = $conn->query('SELECT id, name FROM categories ORDER BY name ASC');
                while ($cat = $cats->fetch_assoc()) {
                    $checked = in_array($cat['id'], $filter_categories) ? 'checked' : '';
                    echo "<label>\n"
                       .  "  <input type='checkbox' name='filter_categories[]' value='{$cat['id']}' {$checked}> {$cat['name']}\n"
                       .  "</label>";
                }
                ?>
            </div>

            <?php
            // Existing attribute filters
            $attributes = $conn->query('SELECT id, name FROM attributes');
            while ($attr = $attributes->fetch_assoc()) {
                echo "<div class='filter-group'>\n<h4>{$attr['name']}</h4>";
                $vals = $conn->query("SELECT DISTINCT value FROM product_attributes WHERE attribute_id = {$attr['id']}");
                while ($v = $vals->fetch_assoc()) {
                    $key     = 'attr_' . $attr['id'];
                    $checked = isset($_GET[$key]) && in_array($v['value'], $_GET[$key]) ? 'checked' : '';
                    echo "<label>\n  <input type='checkbox' name='{$key}[]' value='{$v['value']}' {$checked}> {$v['value']}\n</label>";
                }
                echo "</div>";
            }
            ?>

            <div class="buy-now">
                <button type="submit" class="btn">
                    FILTREAZĂ
                </button>
            </div>
        </form>
    </aside>

    <div class="product-grid">
        <?php
        // Build WHERE parts
        $whereParts = [];

        if (!empty($filter_categories)) {
            // explicit category filters
            $ids = array_map('intval', $filter_categories);
            $whereParts[] = 'category_id IN (' . implode(',', $ids) . ')';
        } elseif ($filtering) {
            // form submitted with none selected → show all (no filter)
        } elseif ($category_id !== null) {
            // initial or slug‑only load
            $whereParts[] = 'category_id = ' . $category_id;
        }

        // Attribute filters
        foreach ($_GET as $k => $vals) {
            if (strpos($k, 'attr_') === 0 && is_array($vals) && !empty($vals)) {
                $aid       = intval(substr($k, 5));
                $safe_vals = array_map([$conn, 'real_escape_string'], $vals);
                $list      = "'" . implode("','", $safe_vals) . "'";
                $whereParts[] = "id IN (SELECT product_id FROM product_attributes WHERE attribute_id = {$aid} AND value IN ({$list}))";
            }
        }

        $where = $whereParts ? 'WHERE ' . implode(' AND ', $whereParts) : '';
        $res   = $conn->query("SELECT * FROM products {$where}");
        while ($row = $res->fetch_assoc()) {
            echo "<div class='product-item'>\n"
               .  "  <a href='/{$row['slug']}'>\n"
               .  "    <img src='/images/{$row['image']}' alt='{$row['name']}'>\n"
               .  "    <h3>{$row['name']}</h3>\n"
               .  "    <p class='price'>" .
                      ($row['sale_price']
                          ? "<span class='old-price'>€{$row['price']}</span> €{$row['sale_price']}"
                          : "€{$row['price']}") .
                   "</p>\n"
               .  "  </a>\n"
               .  "</div>";
        }
        ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>