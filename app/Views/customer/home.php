<?= $this->include('customer/partials/header') ?>

<style>
    .promo-hero {
        position: relative;
        border-radius: 20px;
        overflow: hidden;
        min-height: 420px;
        border: 1px solid rgba(255, 255, 255, 0.22);
        background:
            linear-gradient(100deg, rgba(21, 67, 102, 0.95) 0%, rgba(23, 106, 150, 0.78) 48%, rgba(9, 33, 61, 0.42) 100%),
            url('<?= base_url('assets/img/smokebg.jpg') ?>') center/cover no-repeat;
        box-shadow: 0 16px 40px rgba(0, 0, 0, 0.35);
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
        border: 1px solid rgba(255, 255, 255, 0.34);
        color: #e7f0ff;
        margin-bottom: .7rem;
    }

    .promo-content h1 {
        font-size: clamp(2rem, 5.1vw, 3.45rem);
        line-height: 1.04;
        margin-bottom: .78rem;
        letter-spacing: .2px;
        text-shadow: 0 6px 20px rgba(0, 0, 0, 0.28);
    }

    .promo-content p {
        color: #e4ecff;
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
        border-radius: 2px;
        padding: .7rem 1.15rem;
        min-width: 124px;
        text-transform: uppercase;
        letter-spacing: .55px;
        font-size: .74rem;
        font-weight: 700;
        border: 1px solid rgba(255, 255, 255, 0.6);
        color: #ffffff;
        background: transparent;
    }

    .promo-btn.primary {
        background: #ffffff;
        color: #153f62;
        border-color: #ffffff;
    }

    .promo-footnote {
        margin-top: 1rem;
        color: #d6e6ff;
        font-size: .92rem;
        opacity: .95;
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
        </div>
        <div class="promo-footnote">Quality vape devices and premium e-liquids.</div>
    </div>
</section>

<?= $this->include('customer/partials/footer') ?>
