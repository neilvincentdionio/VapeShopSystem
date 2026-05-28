<?= $this->include('customer/partials/header') ?>

<style>
    .promo-hero {
        position: relative;
        border-radius: 20px;
        overflow: hidden;
        min-height: 420px;
        border: 1px solid #e0e0e0;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        margin: 0 auto;
        max-width: 900px;
    }

    .promo-content {
        position: relative;
        z-index: 2;
        width: min(560px, 100%);
        padding: 2.2rem 2.1rem;
    }

    .promo-kicker {
        display: inline-block;
        font-size: .74rem;
        letter-spacing: .85px;
        text-transform: uppercase;
        padding: .2rem .68rem;
        border-radius: 999px;
        border: 1px solid #27c56f;
        color: #27c56f;
        margin-bottom: .7rem;
        background: rgba(39, 197, 111, 0.1);
        font-weight: 600;
    }

    .promo-content h1 {
        font-size: clamp(2rem, 5.1vw, 3.45rem);
        line-height: 1.04;
        margin-bottom: .78rem;
        letter-spacing: .2px;
        color: #333333;
        font-weight: 700;
    }

    .promo-content p {
        color: #666666;
        font-size: clamp(1.3rem, 2.1vw, 1.75rem);
        line-height: 1.35;
        font-weight: 500;
        margin-bottom: 1.2rem;
        max-width: 30ch;
    }

    .promo-actions {
        display: flex;
        gap: .65rem;
        flex-wrap: wrap;
    }

    .promo-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        border-radius: 8px;
        padding: .7rem 1.15rem;
        min-width: 124px;
        text-transform: uppercase;
        letter-spacing: .55px;
        font-size: .74rem;
        font-weight: 700;
        border: 2px solid #27c56f;
        color: #27c56f;
        background: transparent;
        transition: all 0.2s ease;
    }

    .promo-btn.primary {
        background: #27c56f;
        color: #ffffff;
        border-color: #27c56f;
    }

    .promo-btn:hover {
        background: #27c56f;
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(39, 197, 111, 0.3);
    }

    .promo-footnote {
        margin-top: 1rem;
        color: #666666;
        font-size: .92rem;
        opacity: .95;
        font-weight: 500;
    }
    .promo-btn.secondary-download {
        border-color: #1f6feb;
        color: #1f6feb;
        background: #f7fbff;
    }
    .promo-btn.secondary-download:hover {
        background: #1f6feb;
        border-color: #1f6feb;
        color: #ffffff;
    }

    @media (max-width: 768px) {
        .promo-hero { min-height: 380px; }
        .promo-content { padding: 1.6rem 1.2rem; }
        .promo-content h1 { font-size: clamp(1.8rem, 8vw, 2.5rem); }
        .promo-content p { font-size: 1.15rem; }
    }
</style>

<section class="promo-hero">
    <div class="promo-content">
        <span class="promo-kicker">QuickPuff Vape Shop</span>
        <h1>Welcome to QuickPuff Vape Shop!</h1>
        <p>Discover the best selection of vape devices and e-liquids.</p>
        <div class="promo-actions">
            <a href="<?= site_url('customer/products') ?>" class="promo-btn primary">Shop Now</a>
            <a href="<?= site_url('customer/products') ?>" class="promo-btn">Find More</a>
            <a href="<?= base_url('downloads/QuickPuffMobile.apk') ?>" class="promo-btn secondary-download" download>Download APK</a>
        </div>
        <div class="promo-footnote">Quality vape devices and premium e-liquids.</div>
    </div>
</section>

<?= $this->include('customer/partials/footer') ?>
