</main>
    <footer>
        <div class="footer-container">
            <div class="footer-column">
                <h4>Categories</h4>
                <ul>
                    <?php
                    include 'includes/db.php';
                    $result = $conn->query("SELECT * FROM categories LIMIT 5");
                    while($row = $result->fetch_assoc()) {
                        echo "<li><a href='/category.php?id={$row['id']}'>{$row['name']}</a></li>";
                    }
                    ?>
                </ul>
            </div>
            
            <div class="footer-column">
                <h4>Policies</h4>
                <ul>
                    <li><a href="/cookie-policy">Cookie Policy</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h4>About</h4>
                <p>&copy; <?= date('Y') ?> Veem. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>