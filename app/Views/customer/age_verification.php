<?= $this->include('customer/partials/header') ?>

<style>
    .age-panel {
        background: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        max-width: 760px;
        margin: 0 auto;
    }

    .age-panel h1 {
        font-size: 1.35rem;
        color: #333333;
        font-weight: 800;
        margin-bottom: .35rem;
    }

    .age-panel p {
        color: #666666;
        line-height: 1.6;
        margin-bottom: 1.25rem;
    }

    .field-label {
        display: block;
        font-weight: 700;
        margin-bottom: .5rem;
        color: #333333;
    }

    .input {
        width: 100%;
        padding: .85rem 1rem;
        border-radius: 12px;
        border: 1px solid #e0e0e0;
        background: #ffffff;
        font-size: 0.95rem;
        color: #333333;
    }

    .actions {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        margin-top: 1.25rem;
        flex-wrap: wrap;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        border-radius: 10px;
        padding: .75rem 1.15rem;
        text-transform: uppercase;
        letter-spacing: .3px;
        font-size: .74rem;
        font-weight: 800;
        cursor: pointer;
        border: 2px solid transparent;
        transition: all 0.2s ease;
    }

    .btn-primary {
        background: #27c56f;
        border-color: #27c56f;
        color: #ffffff;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(39, 197, 111, 0.3);
    }

    .btn-outline {
        background: transparent;
        border-color: #00bcd4;
        color: #00bcd4;
    }
    
    .small-note {
        margin-top: .75rem;
        color: #666666;
        font-size: .9rem;
    }

    @media print {
        .actions, .small-note { display: none; }
    }
</style>

<div class="age-panel">
    <h1>18+ Age Verification</h1>
    <p>
        To purchase vape products, you must be at least 18 years old.
        Please confirm your age using your date of birth.
    </p>

    <form method="post" action="<?= site_url('customer/age-verification') ?>">
        <div style="margin-bottom: 1rem;">
            <label class="field-label" for="birth_date">Date of Birth</label>
            <input
                class="input"
                type="date"
                id="birth_date"
                name="birth_date"
                max="<?= date('Y-m-d') ?>"
                required
            >
        </div>

        <div class="actions">
            <a href="<?= site_url('customer/products') ?>" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-primary">Verify 18+</button>
        </div>

        <div class="small-note">
            This check updates your account verification so you can proceed with purchasing.
        </div>
    </form>
</div>

<?= $this->include('customer/partials/footer') ?>

