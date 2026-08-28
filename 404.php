<?php
http_response_code(404);
include __DIR__ . '/inc/header.php';
?>
<style>
    .not-found-page {
        --not-found-ink: #183b2a;
        --not-found-green: #16834f;
        --not-found-soft: #edf8f0;
        min-height: 62vh;
        display: flex;
        align-items: center;
        position: relative;
        overflow: hidden;
        padding: 5.5rem 0;
        background-color: #f7fbf8;
        background-image: linear-gradient(rgba(22, 131, 79, .07) 1px, transparent 1px), linear-gradient(90deg, rgba(22, 131, 79, .07) 1px, transparent 1px);
        background-size: 42px 42px;
    }

    .not-found-page::before,
    .not-found-page::after {
        content: '';
        position: absolute;
        width: 18rem;
        height: 18rem;
        border: 1px solid rgba(22, 131, 79, .16);
        transform: rotate(45deg);
        pointer-events: none;
    }

    .not-found-page::before {
        top: -12rem;
        left: -7rem;
    }

    .not-found-page::after {
        right: -8rem;
        bottom: -12rem;
    }

    .not-found-content {
        position: relative;
        z-index: 1;
        max-width: 720px;
        margin: 0 auto;
        opacity: 0;
        transform: translateY(18px);
        transition: opacity .6s ease, transform .6s ease;
    }

    .not-found-content.is-visible {
        opacity: 1;
        transform: translateY(0);
    }

    .not-found-kicker {
        display: inline-flex;
        align-items: center;
        gap: .55rem;
        color: var(--not-found-green);
        font-size: .75rem;
        font-weight: 700;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .not-found-code {
        margin: 1.25rem 0 .5rem;
        color: var(--not-found-ink);
        font-size: clamp(6rem, 17vw, 11rem);
        font-weight: 800;
        line-height: .78;
        letter-spacing: 0;
        text-shadow: .08em .08em 0 #b9e4c8;
    }

    .not-found-page h1 {
        color: var(--not-found-ink);
        font-size: clamp(1.75rem, 4vw, 2.75rem);
        font-weight: 700;
    }

    .not-found-copy {
        max-width: 520px;
        margin: 0 auto;
        color: #5b6d62;
        font-size: 1.05rem;
    }

    .not-found-route {
        display: inline-flex;
        align-items: center;
        gap: .65rem;
        max-width: 100%;
        margin: 1.5rem auto 2rem;
        padding: .7rem 1rem;
        overflow: hidden;
        border: 1px solid #d7e9dc;
        border-radius: 4px;
        background: rgba(255, 255, 255, .82);
        color: #6c7d73;
        font-family: monospace;
        font-size: .82rem;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .not-found-actions {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: .75rem;
    }

    .not-found-actions .btn {
        min-width: 170px;
        border-radius: 4px;
    }

    @media (max-width: 575px) {
        .not-found-page {
            min-height: 68vh;
            padding: 4rem 1rem;
        }

        .not-found-route {
            max-width: 92vw;
        }

        .not-found-actions .btn {
            width: 100%;
        }
    }
</style>
<main class="not-found-page">
    <div class="container text-center not-found-content" data-not-found-content>
        <div class="not-found-kicker"><i class="fas fa-compass" aria-hidden="true"></i><span>Route unavailable</span></div>
        <div class="not-found-code" aria-hidden="true">404</div>
        <h1 class="mb-3">This market path is closed</h1>
        <p class="not-found-copy">The page you requested may have moved, been removed, or never existed.</p>
        <div class="not-found-route" title="Requested address"><i class="fas fa-location-arrow" aria-hidden="true"></i><span data-requested-path>Loading requested path...</span></div>
        <div class="not-found-actions">
            <a class="btn btn-success px-4 py-3" href="index.php"><i class="fas fa-house me-2" aria-hidden="true"></i>Return to the market</a>
            <button class="btn btn-outline-success px-4 py-3" type="button" data-go-back><i class="fas fa-arrow-left me-2" aria-hidden="true"></i>Go back</button>
        </div>
    </div>
</main>
<script>
    (function () {
        var content = document.querySelector('[data-not-found-content]');
        var requestedPath = document.querySelector('[data-requested-path]');
        var goBack = document.querySelector('[data-go-back]');

        if (requestedPath) {
            requestedPath.textContent = window.location.pathname + window.location.search;
        }

        if (content) {
            window.requestAnimationFrame(function () {
                content.classList.add('is-visible');
            });
        }

        if (goBack) {
            goBack.addEventListener('click', function () {
                if (window.history.length > 1) {
                    window.history.back();
                } else {
                    window.location.href = 'index.php';
                }
            });
        }
    }());
</script>
<?php include __DIR__ . '/inc/footer.php'; ?>