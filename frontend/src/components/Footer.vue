<template>
  <footer class="footer">
    <div class="container">
      <div class="footer-grid">
        <!-- Brand -->
        <div class="footer-brand">
          <div class="footer-logo">
            <span class="logo-icon">🍵</span>
            <span class="logo-text">MATCHA<span class="text-accent">ZUKI</span></span>
          </div>
          <p class="footer-desc">
            Premium Japanese Matcha สินค้ามัทฉะคุณภาพสูง นำเข้าจากประเทศญี่ปุ่น
          </p>
          <div class="social-links">
            <a href="#" class="social-link">📘</a>
            <a href="#" class="social-link">📸</a>
            <a href="#" class="social-link">🐦</a>
          </div>
        </div>

        <!-- Quick Links -->
        <div class="footer-section">
          <h4 class="footer-title">เมนู</h4>
          <ul class="footer-links">
            <li><router-link to="/">หน้าหลัก</router-link></li>
            <li><router-link to="/products">สินค้าทั้งหมด</router-link></li>
            <li><router-link to="/cart">ตะกร้าสินค้า</router-link></li>
          </ul>
        </div>

        <!-- Customer Service -->
        <div class="footer-section">
          <h4 class="footer-title">บริการลูกค้า</h4>
          <ul class="footer-links">
            <li><a href="#">การจัดส่งสินค้า</a></li>
            <li><a href="#">นโยบายการคืนสินค้า</a></li>
            <li><a href="#">คำถามที่พบบ่อย</a></li>
          </ul>
        </div>

        <!-- Contact -->
        <div class="footer-section">
          <h4 class="footer-title">ติดต่อเรา</h4>
          <ul class="footer-contact">
            <li>📧 hello@matchazuki.com</li>
            <li>📞 02-XXX-XXXX</li>
            <li>📍 กรุงเทพมหานคร</li>
          </ul>
        </div>
      </div>

      <div class="footer-bottom">
        <p>&copy; 2024 Matchazuki. All rights reserved.</p>
        <p class="footer-credit">Crafted with 💚 for matcha lovers</p>
      </div>
    </div>
  </footer>
</template>

<style scoped>
.footer {
  background: var(--bg-secondary-dark, #0a0a0a);
  border-top: 1px solid var(--border-subtle);
  padding: var(--space-3xl) 0 var(--space-xl);
  margin-top: auto;
}

.footer-grid {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr;
  gap: var(--space-2xl);
  margin-bottom: var(--space-2xl);
}

.footer-logo {
  display: flex;
  align-items: center;
  gap: var(--space-sm);
  font-size: var(--font-size-xl);
  font-weight: 700;
  letter-spacing: 2px;
  margin-bottom: var(--space-md);
}

.logo-icon {
  font-size: 2rem;
}

.footer-desc {
  color: var(--text-secondary);
  margin-bottom: var(--space-lg);
  line-height: 1.8;
}

.social-links {
  display: flex;
  gap: var(--space-md);
}

.social-link {
  width: 44px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg-glass);
  border: 1px solid var(--border-subtle);
  border-radius: var(--radius-md);
  font-size: 1.25rem;
  transition: all var(--transition-fast);
}

.social-link:hover {
  border-color: var(--color-primary);
  background: var(--color-primary);
  transform: translateY(-2px);
}

.footer-title {
  font-size: var(--font-size-lg);
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: var(--space-lg);
}

.footer-links li,
.footer-contact li {
  margin-bottom: var(--space-sm);
}

.footer-links a {
  color: var(--text-secondary);
  transition: color var(--transition-fast);
}

.footer-links a:hover {
  color: var(--color-accent);
}

.footer-contact li {
  color: var(--text-secondary);
}

.footer-bottom {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: var(--space-xl);
  border-top: 1px solid var(--border-subtle);
  color: var(--text-muted);
  font-size: var(--font-size-sm);
}

@media (max-width: 1024px) {
  .footer-grid {
    grid-template-columns: 1fr 1fr;
  }
}

@media (max-width: 640px) {
  .footer-grid {
    grid-template-columns: 1fr;
    text-align: center;
  }

  .social-links {
    justify-content: center;
  }

  .footer-bottom {
    flex-direction: column;
    gap: var(--space-sm);
  }
}
</style>
