<?php
// ============================================================
// Site Footer — includes/footer.php
// ============================================================
?>
</main><!-- /#main-content -->

<!-- ===== SITE FOOTER ===== -->
<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">

      <!-- Brand -->
      <div class="footer-brand">
        <div class="navbar-brand" style="margin-bottom:1rem;">
          <div class="brand-logo-wrap" style="background:rgba(255,255,255,0.08);">
            <img src="<?= SITE_URL ?>/assets/img/logo-placeholder.svg" alt="Logo PPKD" width="32" height="32">
          </div>
          <div class="brand-text">
            <span class="brand-name"><?= SITE_NAME ?></span>
            <span class="brand-sub">Platform Alumni Resmi</span>
          </div>
        </div>
        <p class="footer-desc">
          Wadah resmi bagi keluarga besar alumni PPKD untuk tetap terhubung,
          berbagi informasi, dan membangun hubungan yang lebih erat.
        </p>
        <div class="social-links">
          <a href="#" class="social-link" title="Instagram" aria-label="Instagram">
            <i data-lucide="instagram" style="width:16px;height:16px"></i>
          </a>
          <a href="#" class="social-link" title="Facebook" aria-label="Facebook">
            <i data-lucide="facebook" style="width:16px;height:16px"></i>
          </a>
          <a href="#" class="social-link" title="YouTube" aria-label="YouTube">
            <i data-lucide="youtube" style="width:16px;height:16px"></i>
          </a>
          <a href="#" class="social-link" title="WhatsApp" aria-label="WhatsApp">
            <i data-lucide="message-circle" style="width:16px;height:16px"></i>
          </a>
        </div>
      </div>

      <!-- Navigasi -->
      <div class="footer-col">
        <h5>Navigasi</h5>
        <ul class="footer-links">
          <li><a href="<?= SITE_URL ?>">Beranda</a></li>
          <li><a href="<?= SITE_URL ?>/alumni.php">Direktori Alumni</a></li>
          <li><a href="<?= SITE_URL ?>/berita.php">Berita &amp; Informasi</a></li>
          <li><a href="<?= SITE_URL ?>/tentang.php">Tentang Kami</a></li>
          <li><a href="<?= SITE_URL ?>/kontak.php">Hubungi Kami</a></li>
        </ul>
      </div>

      <!-- Layanan -->
      <div class="footer-col">
        <h5>Layanan</h5>
        <ul class="footer-links">
          <li><a href="<?= SITE_URL ?>/register.php">Daftar sebagai Alumni</a></li>
          <li><a href="<?= SITE_URL ?>/login.php">Masuk Akun</a></li>
          <li><a href="<?= SITE_URL ?>/berita.php?kategori=Beasiswa">Informasi Beasiswa</a></li>
          <li><a href="<?= SITE_URL ?>/berita.php?kategori=Event">Event &amp; Kegiatan</a></li>
          <li><a href="<?= SITE_URL ?>/kontak.php">Layanan Pengaduan</a></li>
        </ul>
      </div>

      <!-- Kontak -->
      <div class="footer-col">
        <h5>Kontak</h5>
        <div class="footer-contact-item">
          <i data-lucide="map-pin" style="width:15px;height:15px;color:rgba(255,255,255,0.4)"></i>
          <span><?= SITE_ADDRESS ?></span>
        </div>
        <div class="footer-contact-item">
          <i data-lucide="phone" style="width:15px;height:15px;color:rgba(255,255,255,0.4)"></i>
          <span><?= SITE_PHONE ?></span>
        </div>
        <div class="footer-contact-item">
          <i data-lucide="mail" style="width:15px;height:15px;color:rgba(255,255,255,0.4)"></i>
          <span><?= SITE_EMAIL ?></span>
        </div>
        <div class="footer-contact-item">
          <i data-lucide="clock" style="width:15px;height:15px;color:rgba(255,255,255,0.4)"></i>
          <span>Senin – Jumat, 08.00 – 16.00 WIB</span>
        </div>
      </div>

    </div><!-- /.footer-grid -->

    <div class="footer-bottom">
      <span>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. Seluruh hak cipta dilindungi.</span>
      <span>Dibuat dengan <span style="color:#ef4444">♥</span> untuk keluarga besar Alumni PPKD</span>
    </div>
  </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Lucide Icons -->
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

<!-- Main JS -->
<script src="<?= SITE_URL ?>/assets/js/main.js"></script>

</body>
</html>
