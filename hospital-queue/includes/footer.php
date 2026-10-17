</main>
<footer class="footer">
  <div class="wrap foot-grid">
    <section>
      <h4>CityCare Queue</h4>
      <p>Take a token, watch the board, walk in when called. Numbers restart daily per doctor.</p>
    </section>
    <?php if ($user): ?>
    <section>
      <h4>Staff</h4>
      <p><a href="history.php">History &amp; details</a> · <a href="staff-queue.php">Today's queue</a> · <a href="staff-doctors.php">Doctors</a><?php if ($user['role'] === 'admin'): ?> · <a href="staff-accounts.php">Accounts</a><?php endif; ?> · <a href="queue-live.php">Display board</a></p>
    </section>
    <?php else: ?>
    <section>
      <h4>Patients</h4>
      <p><a href="book-token.php">Get a token</a> · <a href="queue-live.php">Display board</a></p>
    </section>
    <section>
      <h4>Staff</h4>
      <p><a href="login.php">Staff login</a></p>
    </section>
    <?php endif; ?>
  </div>
  <div class="foot-base">Mini-project · Web Technologies (102506) · PHP + MySQL on XAMPP</div>
</footer>
<script src="js/main.js"></script>
</body>
</html>
