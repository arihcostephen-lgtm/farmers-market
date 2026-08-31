<?php
session_start();
require_once __DIR__ . '/admin/inc/db.php';
require_once __DIR__ . '/inc/mobile_money.php';

if (empty($_SESSION['user_id']) || (int)$_SESSION['role'] !== 2) {
    header('Location: login.php');
    exit;
}

$farmerId = (int) $_SESSION['user_id'];
$message = '';
$error = '';
$subscription = null;
$subscriptionAmount = 0;

// Get the farmer's approved subscription
$subscriptionQuery = mysqli_prepare($db, "SELECT fs.id, fs.plan_id, fs.subscription_name, fs.amount, fs.status, fs.payment_reference, sp.description, sp.duration_days FROM farmer_subscriptions fs LEFT JOIN subscription_plans sp ON sp.plan_id = fs.plan_id WHERE fs.farmer_id = ? AND fs.status = 1 LIMIT 1");
if ($subscriptionQuery) {
    mysqli_stmt_bind_param($subscriptionQuery, 'i', $farmerId);
    mysqli_stmt_execute($subscriptionQuery);
    $subscription = mysqli_stmt_get_result($subscriptionQuery)->fetch_assoc();
    mysqli_stmt_close($subscriptionQuery);
}

if (!$subscription) {
    $error = 'You do not have an approved subscription to pay for.';
} else {
    $subscriptionAmount = (float) ($subscription['amount'] ?? 0);
}

// Handle payment form submission
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && $subscription && $subscriptionAmount > 0) {
    $provider = $_POST['provider'] ?? '';
    $phone = uganda_phone($_POST['phone'] ?? $_SESSION['user_phone'] ?? '');
    $amount = $subscriptionAmount;

    if (!in_array($provider, ['mtn_uganda', 'airtel_uganda', 'ussd'], true) || $phone === '') {
        $error = 'Select a payment method and enter a valid Ugandan phone number.';
    } else {
        $reference = 'SUB-' . strtoupper(bin2hex(random_bytes(4))) . '-' . time();
        $status = 'pending';

        $insert = mysqli_prepare($db, "INSERT INTO payment_transactions (order_id, user_id, provider, amount, phone, reference, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $subscriptionId = (int) $subscription['id'];
        
        if ($insert) {
            mysqli_stmt_bind_param($insert, 'iisdsss', $subscriptionId, $farmerId, $provider, $amount, $phone, $reference, $status);
            if (mysqli_stmt_execute($insert)) {
                if ($provider === 'ussd') {
                    $message = 'USSD request recorded. Dial ' . payment_config('USSD_SHORT_CODE', '*165#') . ' and complete payment using reference ' . $reference . '.';
                } else {
                    $result = $provider === 'mtn_uganda' ? start_mtn_payment($amount, $phone, $reference) : start_airtel_payment($amount, $phone, $reference);
                    if (($result['status'] < 200 || $result['status'] >= 300) && $result['status'] !== 202) {
                        $error = 'The provider could not start the payment. Please try again or use USSD.';
                    } else {
                        mysqli_query($db, "UPDATE farmer_subscriptions SET payment_reference='$reference' WHERE id=$subscriptionId");
                        $message = 'Payment prompt sent to ' . htmlspecialchars($phone) . '. Approve it on your phone; this page will update after confirmation.';
                    }
                }
            } else {
                $error = 'Unable to create the payment request: ' . mysqli_error($db);
            }
        } else {
            $error = 'Unable to prepare payment: ' . mysqli_error($db);
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Subscription payment | Farmers Market</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --ink: #10231c; --muted: #60776b; --green: #087f5b; --green-dark: #07553f; --mint: #e8f7ee; --line: #d5e6da; }
        * { box-sizing: border-box; }
        body { min-height: 100vh; margin: 0; color: var(--ink); background: linear-gradient(135deg, #f3faf5 0%, #e3f4e9 48%, #f9fcf8 100%); font-family: Georgia, 'Times New Roman', serif; }
        .payment-shell { min-height: 100vh; display: grid; place-items: center; padding: 32px 16px; }
        .payment-card { width: min(100%, 960px); overflow: hidden; background: #fff; border: 1px solid var(--line); border-radius: 18px; box-shadow: 0 24px 70px rgba(7, 85, 63, .14); }
        .payment-intro { color: #eafff2; background: linear-gradient(145deg, #063c2d, #087f5b); padding: clamp(28px, 5vw, 56px); position: relative; }
        .payment-intro::after { content: ''; position: absolute; width: 190px; height: 190px; right: -65px; bottom: -80px; border: 24px solid rgba(255,255,255,.1); border-radius: 50%; }
        .eyebrow { color: #b8f2ca; font: 700 .74rem/1.2 Arial, sans-serif; letter-spacing: .14em; text-transform: uppercase; }
        .payment-intro h1 { max-width: 360px; margin: 14px 0 12px; font-size: clamp(2rem, 4vw, 3.5rem); line-height: 1.05; font-weight: 500; }
        .payment-intro p { max-width: 340px; margin: 0; color: #d7f7e2; font: 1rem/1.6 Arial, sans-serif; }
        .amount-panel { margin-top: 42px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,.2); }
        .amount-label { color: #b8f2ca; font: 700 .72rem Arial, sans-serif; letter-spacing: .1em; text-transform: uppercase; }
        .amount { margin-top: 7px; font: 700 clamp(1.8rem, 4vw, 2.7rem)/1.1 Arial, sans-serif; }
        .plan-details { margin-top: 28px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,.2); }
        .plan-detail-item { margin-bottom: 12px; color: #d7f7e2; font: .9rem/1.4 Arial, sans-serif; }
        .plan-detail-label { color: #b8f2ca; font: 700 .75rem Arial, sans-serif; text-transform: uppercase; letter-spacing: .05em; }
        .payment-form { padding: clamp(28px, 5vw, 56px); }
        .payment-form h2 { margin-bottom: 7px; font-size: 1.55rem; font-weight: 600; }
        .form-intro { margin-bottom: 26px; color: var(--muted); font: .95rem/1.5 Arial, sans-serif; }
        .form-label { margin-bottom: 8px; color: var(--ink); font: 700 .85rem Arial, sans-serif; }
        .form-control { min-height: 48px; border-color: var(--line); border-radius: 8px; font: 1rem Arial, sans-serif; }
        .form-control:focus, .form-select:focus { border-color: var(--green); box-shadow: 0 0 0 3px rgba(8,127,91,.14); }
        .provider-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
        .provider-option { position: relative; }
        .provider-option input { position: absolute; opacity: 0; }
        .provider-option label { display: flex; min-height: 86px; cursor: pointer; flex-direction: column; justify-content: center; gap: 5px; padding: 12px; border: 1px solid var(--line); border-radius: 9px; background: #fbfefc; font: 700 .82rem/1.25 Arial, sans-serif; transition: .18s ease; }
        .provider-option small { color: var(--muted); font: .72rem Arial, sans-serif; }
        .provider-option input:checked + label { border-color: var(--green); background: var(--mint); box-shadow: inset 0 0 0 1px var(--green); }
        .provider-option input:focus-visible + label { outline: 3px solid rgba(8,127,91,.2); outline-offset: 2px; }
        .phone-help { color: var(--muted); font: .78rem Arial, sans-serif; }
        .payment-alert { border: 0; border-radius: 9px; font: .9rem/1.45 Arial, sans-serif; }
        .btn-pay { min-height: 50px; border: 0; border-radius: 8px; background: var(--green); color: #fff; font: 700 .95rem Arial, sans-serif; transition: background .18s ease, transform .18s ease; }
        .btn-pay:hover { background: var(--green-dark); color: #fff; transform: translateY(-1px); }
        .btn-pay:disabled { opacity: .75; transform: none; }
        .back-link { color: var(--green-dark); font: 700 .85rem Arial, sans-serif; text-decoration: none; }
        .back-link:hover { color: var(--green); text-decoration: underline; }
        @media (max-width: 640px) { .payment-shell { padding: 0; } .payment-card { min-height: 100vh; border: 0; border-radius: 0; } .provider-grid { grid-template-columns: 1fr; } .provider-option label { min-height: 58px; } .amount-panel { margin-top: 28px; } }
    </style>
</head>
<body>
    <main class="payment-shell">
        <div class="payment-card row g-0">
            <section class="payment-intro col-lg-5">
                <div class="eyebrow">Farmers Market subscription</div>
                <h1>Activate your subscription.</h1>
                <p>Choose a Ugandan mobile money option and confirm the payment from your phone.</p>
                <?php if ($subscription): ?>
                    <div class="amount-panel">
                        <div class="amount-label">Subscription plan</div>
                        <div class="amount">UGX <?php echo number_format($subscriptionAmount, 2); ?></div>
                        <div class="plan-details">
                            <div class="plan-detail-item">
                                <div class="plan-detail-label">Plan name</div>
                                <div><?php echo htmlspecialchars($subscription['subscription_name'] ?? 'Plan'); ?></div>
                            </div>
                            <?php if (!empty($subscription['description'])): ?>
                                <div class="plan-detail-item">
                                    <div class="plan-detail-label">Details</div>
                                    <div><?php echo htmlspecialchars($subscription['description']); ?></div>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($subscription['duration_days'])): ?>
                                <div class="plan-detail-item">
                                    <div class="plan-detail-label">Duration</div>
                                    <div><?php echo (int) $subscription['duration_days']; ?> days</div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </section>
            <section class="payment-form col-lg-7">
                <?php if (!$subscription): ?>
                    <div class="alert alert-danger payment-alert"><?php echo htmlspecialchars($error); ?></div>
                    <a href="farmerDashboard.php?do=Home" class="back-link">&larr; Return to dashboard</a>
                <?php else: ?>
                    <h2>Payment details</h2>
                    <p class="form-intro">Your payment request will be sent to the number below.</p>
                    <?php if ($message): ?><div class="alert alert-success payment-alert"><?php echo htmlspecialchars(strip_tags($message)); ?></div><?php endif; ?>
                    <?php if ($error): ?><div class="alert alert-danger payment-alert"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
                    <form method="post" id="paymentForm" novalidate>
                        <div class="mb-4">
                            <label class="form-label" for="phone">Ugandan mobile number</label>
                            <input class="form-control" id="phone" name="phone" inputmode="tel" autocomplete="tel" value="<?php echo htmlspecialchars($_POST['phone'] ?? $_SESSION['user_phone'] ?? ''); ?>" placeholder="07XXXXXXXX" pattern="(?:0|\+?256)7[0-9]{8}" required>
                            <div class="phone-help mt-2">Use a number beginning with 07 or +256 7.</div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label d-block">Payment method</label>
                            <div class="provider-grid">
                                <div class="provider-option"><input type="radio" id="mtn" name="provider" value="mtn_uganda" <?php echo ($_POST['provider'] ?? '') === 'mtn_uganda' || empty($_POST['provider']) ? 'checked' : ''; ?> required><label for="mtn">MTN MoMo<small>Mobile prompt</small></label></div>
                                <div class="provider-option"><input type="radio" id="airtel" name="provider" value="airtel_uganda" <?php echo ($_POST['provider'] ?? '') === 'airtel_uganda' ? 'checked' : ''; ?>><label for="airtel">Airtel Money<small>Mobile prompt</small></label></div>
                                <div class="provider-option"><input type="radio" id="ussd" name="provider" value="ussd" <?php echo ($_POST['provider'] ?? '') === 'ussd' ? 'checked' : ''; ?>><label for="ussd">USSD fallback<small>Dial a code</small></label></div>
                            </div>
                        </div>
                        <button class="btn btn-pay w-100" id="payButton" type="submit"><span id="payButtonText">Continue payment</span></button>
                        <div class="text-center mt-3">
                            <a href="farmerDashboard.php?do=Home" class="back-link">&larr; Return to dashboard</a>
                        </div>
                    </form>
                <?php endif; ?>
            </section>
        </div>
    </main>
</body>
</html>
