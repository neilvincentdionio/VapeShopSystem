<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - Vape Shop System</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/background.css') ?>">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            padding: 2rem 1rem;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .register-shell {
            width: 100%;
            max-width: 760px;
            margin: 0 auto;
            display: block;
        }

        .form-panel {
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.24);
        }

        .form-panel {
            background: #f9fbfa;
            border: 1px solid rgba(14, 27, 22, 0.08);
            padding: 2rem;
        }

        .form-header h2 {
            font-size: 1.9rem;
            color: #1a2a24;
            margin-bottom: 0.45rem;
        }

        .form-header p {
            color: #61726b;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .alert,
        .validation-errors {
            margin-bottom: 1rem;
            padding: 0.9rem 1rem;
            border-radius: 14px;
            font-size: 0.95rem;
        }

        .alert-success {
            background: #dff5e6;
            color: #175e32;
            border: 1px solid #bfe3ca;
        }

        .alert-error,
        .validation-errors {
            background: #fbe5e8;
            color: #8a2435;
            border: 1px solid #f4c3cc;
        }

        .validation-errors ul {
            margin: 0.55rem 0 0 1.2rem;
        }

        .form-section {
            margin-bottom: 1.25rem;
            padding: 1.2rem;
            border-radius: 20px;
            background: #ffffff;
            border: 1px solid #e3ebe6;
        }

        .section-tag {
            display: inline-block;
            margin-bottom: 0.5rem;
            color: #27c56f;
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .form-section h3 {
            font-size: 1.15rem;
            color: #1a2a24;
            margin-bottom: 0.25rem;
        }

        .section-copy {
            color: #677973;
            font-size: 0.92rem;
            line-height: 1.55;
            margin-bottom: 1rem;
        }

        .input-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .input-grid.single {
            grid-template-columns: minmax(0, 1fr);
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.45rem;
        }

        .form-group label {
            color: #23352e;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.82rem 0.9rem;
            border-radius: 12px;
            border: 1px solid #d5dfd9;
            background: #fdfefe;
            color: #1f2e28;
            font-size: 0.98rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #27c56f;
            box-shadow: 0 0 0 4px rgba(39, 197, 111, 0.12);
            transform: translateY(-1px);
        }

        .password-input-wrap {
            position: relative;
        }

        .password-input-wrap input {
            padding-right: 2.8rem;
        }

        .password-toggle {
            position: absolute;
            right: 0.65rem;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            cursor: pointer;
            color: #666666;
            font-size: 1rem;
            line-height: 1;
        }

        .form-group small {
            color: #6c8077;
            line-height: 1.45;
            font-size: 0.84rem;
        }

        .submit-btn {
            width: 100%;
            margin-top: 0.5rem;
            padding: 0.95rem 1rem;
            border: none;
            border-radius: 14px;
            background: linear-gradient(135deg, #27c56f, #1d9f57);
            color: #ffffff;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
        }

        .submit-btn:hover {
            filter: brightness(1.02);
            transform: translateY(-2px);
            box-shadow: 0 10px 24px rgba(29, 159, 87, 0.28);
        }

        .signin-link {
            margin-top: 1rem;
            text-align: center;
        }

        .signin-link a {
            color: #1d9f57;
            text-decoration: none;
            font-weight: 600;
        }

        .signin-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 640px) {
            body {
                padding: 1rem;
            }

            .form-panel {
                padding: 1.35rem;
            }

            .input-grid {
                grid-template-columns: minmax(0, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="register-shell">
        <main class="form-panel">
            <div class="form-header">
                <h2>Create Account</h2>
                <p>Set up a customer account for the vape shop storefront. Complete all sections below, then submit your registration.</p>
            </div>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success">
                    <?= esc(session()->getFlashdata('success')) ?>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-error">
                    <?= esc(session()->getFlashdata('error')) ?>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('errors')): ?>
                <div class="validation-errors">
                    <strong>Please fix the following before creating your account:</strong>
                    <ul>
                        <?php foreach (session()->getFlashdata('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="<?= site_url('auth/register') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <section class="form-section">
                    <span class="section-tag">Section 1</span>
                    <h3>Account Info</h3>
                    <p class="section-copy">Use the details you want tied to your VapeShop customer login.</p>

                    <div class="input-grid">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                value="<?= esc(old('name')) ?>"
                                autocomplete="name"
                                required
                                placeholder="Enter your full name"
                            >
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="<?= esc(old('email')) ?>"
                                autocomplete="email"
                                required
                                placeholder="Enter your email address"
                            >
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="password-input-wrap">
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    autocomplete="new-password"
                                    required
                                    placeholder="Minimum 8 characters"
                                >
                                <button type="button" class="password-toggle" data-target="password" aria-label="Show password">&#128065;</button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <div class="password-input-wrap">
                                <input
                                    type="password"
                                    id="confirm_password"
                                    name="confirm_password"
                                    autocomplete="new-password"
                                    required
                                    placeholder="Re-enter your password"
                                >
                                <button type="button" class="password-toggle" data-target="confirm_password" aria-label="Show password">&#128065;</button>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="form-section">
                    <span class="section-tag">Section 2</span>
                    <h3>ID Verification</h3>
                    <p class="section-copy">Upload a verification ID for age verification to confirm that the customer is of legal age to purchase vape products.</p>

                    <div class="input-grid single">
                        <div class="form-group">
                            <label for="verification_id_image">Verification ID</label>
                            <input
                                type="file"
                                id="verification_id_image"
                                name="verification_id_image"
                                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                required
                            >
                            <small>Upload a clear photo of a valid government-issued ID. This will be used for age verification only. Accepted formats: JPG, JPEG, PNG, or WEBP. Maximum file size: 3 MB.</small>
                        </div>
                    </div>
                </section>

                <section class="form-section">
                    <span class="section-tag">Section 3</span>
                    <h3>Contact</h3>
                    <p class="section-copy">Provide a phone number the shop can use for delivery or order-related updates.</p>

                    <div class="input-grid single">
                        <div class="form-group">
                            <label for="phone_number">Phone Number</label>
                            <input
                                type="tel"
                                id="phone_number"
                                name="phone_number"
                                value="<?= esc(old('phone_number')) ?>"
                                autocomplete="tel"
                                required
                                placeholder="e.g. +63 912 345 6789"
                            >
                        </div>
                    </div>
                </section>

                <section class="form-section">
                    <span class="section-tag">Section 4</span>
                    <h3>Address Information</h3>
                    <p class="section-copy">Add the customer delivery address for shipping, fulfillment, and order coordination.</p>

                    <div class="input-grid single">
                        <div class="form-group">
                            <label for="address_line">Street Address</label>
                            <input
                                type="text"
                                id="address_line"
                                name="address_line"
                                value="<?= esc(old('address_line')) ?>"
                                autocomplete="street-address"
                                required
                                placeholder="House number, street, subdivision, or building"
                            >
                        </div>
                    </div>

                    <div class="input-grid" style="margin-top: 1rem;">
                        <div class="form-group">
                            <label for="city">City</label>
                            <input
                                type="text"
                                id="city"
                                name="city"
                                value="<?= esc(old('city')) ?>"
                                autocomplete="address-level2"
                                required
                                placeholder="Enter city or municipality"
                            >
                        </div>

                        <div class="form-group">
                            <label for="province">Province</label>
                            <input
                                type="text"
                                id="province"
                                name="province"
                                value="<?= esc(old('province')) ?>"
                                autocomplete="address-level1"
                                required
                                placeholder="Enter province or state"
                            >
                        </div>

                        <div class="form-group">
                            <label for="postal_code">Postal Code</label>
                            <input
                                type="text"
                                id="postal_code"
                                name="postal_code"
                                value="<?= esc(old('postal_code')) ?>"
                                autocomplete="postal-code"
                                required
                                placeholder="Enter postal code"
                            >
                        </div>
                    </div>
                </section>

                <button type="submit" class="submit-btn">Create Account</button>
            </form>

            <div class="signin-link">
                <a href="<?= site_url('login') ?>">Back to Login</a>
            </div>
        </main>
    </div>

    <script>
        setTimeout(function () {
            const successAlert = document.querySelector('.alert-success');
            if (successAlert) {
                successAlert.style.display = 'none';
            }
        }, 5000);

        document.querySelectorAll('.password-toggle').forEach(function (button) {
            button.addEventListener('click', function () {
                const targetId = button.getAttribute('data-target');
                const input = document.getElementById(targetId);
                if (!input) {
                    return;
                }

                const show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            });
        });
    </script>
</body>
</html>
