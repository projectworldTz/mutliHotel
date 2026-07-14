/**
 * Public-facing UI animations & effects.
 * Only runs on pages that extend layouts/app.blade.php (body[data-public]).
 */

export function initPublicAnimations() {

    // ── 1. Page entrance fade-in ──────────────────────────────────────────────
    document.body.classList.add('page-loaded');


    // ── 2. Scroll-reveal with IntersectionObserver ────────────────────────────
    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            const el    = entry.target;
            const delay = parseInt(el.dataset.revealDelay || '0', 10);
            setTimeout(() => {
                el.classList.add('revealed');
            }, delay);
            revealObserver.unobserve(el);
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

    document.querySelectorAll('[data-reveal]').forEach(el => revealObserver.observe(el));


    // ── 3. Stagger children of data-stagger parents ───────────────────────────
    document.querySelectorAll('[data-stagger]').forEach(parent => {
        const children = parent.children;
        const base     = parseInt(parent.dataset.stagger || '60', 10);
        Array.from(children).forEach((child, i) => {
            child.dataset.reveal      = '';
            child.dataset.revealDelay = String(i * base);
            revealObserver.observe(child);
        });
    });


    // ── 4. Count-up animation for number elements ─────────────────────────────
    const countObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            const el  = entry.target;
            const end = parseFloat(el.dataset.count);
            if (isNaN(end)) return;
            countObserver.unobserve(el);
            animateCount(el, end);
        });
    }, { threshold: 0.5 });

    document.querySelectorAll('[data-count]').forEach(el => countObserver.observe(el));

    function animateCount(el, end) {
        const duration  = 1400;
        const start     = 0;
        const decimals  = (end % 1 !== 0) ? 1 : 0;
        const prefix    = el.dataset.countPrefix || '';
        const suffix    = el.dataset.countSuffix || '';
        let startTime   = null;

        const step = (timestamp) => {
            if (!startTime) startTime = timestamp;
            const progress = Math.min((timestamp - startTime) / duration, 1);
            const eased    = 1 - Math.pow(1 - progress, 3); // ease-out cubic
            el.textContent = prefix + (start + (end - start) * eased).toFixed(decimals) + suffix;
            if (progress < 1) requestAnimationFrame(step);
        };
        requestAnimationFrame(step);
    }


    // ── 5. Hotel card 3D tilt effect ─────────────────────────────────────────
    document.querySelectorAll('[data-tilt]').forEach(card => {
        card.addEventListener('mousemove', e => {
            const rect    = card.getBoundingClientRect();
            const x       = e.clientX - rect.left;
            const y       = e.clientY - rect.top;
            const cx      = rect.width  / 2;
            const cy      = rect.height / 2;
            const rotateX = ((y - cy) / cy) * -5;
            const rotateY = ((x - cx) / cx) *  5;
            card.style.transform = `perspective(800px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-2px)`;
        });
        card.addEventListener('mouseleave', () => {
            card.style.transform = '';
        });
    });


    // ── 6. Hero floating orbs (home page only) ────────────────────────────────
    const heroOrbs = document.getElementById('hero-orbs');
    if (heroOrbs) {
        const orbs = heroOrbs.querySelectorAll('.hero-orb');
        orbs.forEach((orb, i) => {
            const speed = 6 + i * 2.5;
            const xAmp  = 30 + i * 10;
            const yAmp  = 20 + i * 8;
            let t = i * 1.8;
            const tick = () => {
                t += 0.008;
                orb.style.transform =
                    `translate(${Math.sin(t * 0.7) * xAmp}px, ${Math.cos(t * 0.5) * yAmp}px)`;
                requestAnimationFrame(tick);
            };
            tick();
        });
    }


    // ── 7. Hero parallax on scroll ────────────────────────────────────────────
    const heroSection = document.getElementById('hero-section');
    if (heroSection) {
        window.addEventListener('scroll', () => {
            const y = window.scrollY;
            heroSection.style.backgroundPositionY = `${y * 0.3}px`;

            const heroContent = heroSection.querySelector('.hero-content');
            if (heroContent) {
                heroContent.style.transform = `translateY(${y * 0.15}px)`;
                heroContent.style.opacity   = `${1 - y / 400}`;
            }
        }, { passive: true });
    }


    // ── 8. Gallery image crossfade ────────────────────────────────────────────
    // Wraps all gallery switchers to add fade-out/in between image changes.
    document.querySelectorAll('[data-gallery-img]').forEach(img => {
        // The parent Alpine component switches :src; we intercept Alpine's update
        // via a MutationObserver on the src attribute.
        const observer = new MutationObserver(() => {
            img.classList.add('gallery-fade-out');
            setTimeout(() => img.classList.remove('gallery-fade-out'), 200);
        });
        observer.observe(img, { attributes: true, attributeFilter: ['src'] });
    });


    // ── 9. Smooth scroll for in-page anchor links ─────────────────────────────
    document.querySelectorAll('a[href^="#"]:not(#scroll-to-demo)').forEach(anchor => {
        anchor.addEventListener('click', e => {
            const target = document.querySelector(anchor.getAttribute('href'));
            if (!target) return;
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });


    // ── 10. Search input focus glow ───────────────────────────────────────────
    document.querySelectorAll('[data-search-glow]').forEach(input => {
        input.addEventListener('focus', () => {
            input.parentElement?.classList.add('search-glow-active');
        });
        input.addEventListener('blur', () => {
            input.parentElement?.classList.remove('search-glow-active');
        });
    });


    // ── 11. Favorite button heart-beat animation ──────────────────────────────
    document.querySelectorAll('[data-favorite-btn]').forEach(btn => {
        btn.addEventListener('click', () => {
            btn.classList.add('fav-pop');
            setTimeout(() => btn.classList.remove('fav-pop'), 500);
        });
    });


    // ── 12. Image lazy-load shimmer ───────────────────────────────────────────
    document.querySelectorAll('img[data-lazy]').forEach(img => {
        img.classList.add('img-loading');
        img.addEventListener('load', () => img.classList.remove('img-loading'), { once: true });
    });


    // ── 13. "Scroll down to get demo credentials" hero prompt ────────────────
    const scrollToDemo = document.getElementById('scroll-to-demo');
    if (scrollToDemo) {
        const label       = document.getElementById('scroll-to-demo-text');
        const targetId    = scrollToDemo.getAttribute('href');
        const targetPulse = () => label?.classList.add('scroll-hint-pulse');
        targetPulse();

        scrollToDemo.addEventListener('click', e => {
            e.preventDefault();
            const target = document.querySelector(targetId);
            if (!target) return;
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            target.classList.add('demo-credentials-highlight');
            target.addEventListener('animationend', () => {
                target.classList.remove('demo-credentials-highlight');
            }, { once: true });
        });
    }

}


// ── CSS injected at runtime (keeps Tailwind from purging) ─────────────────────
(function injectStyles() {
    const style = document.createElement('style');
    style.textContent = `

/* Page entrance */
body { opacity: 0; transition: opacity 0.35s ease; }
body.page-loaded { opacity: 1; }

/* ── Reveal system ── */
[data-reveal] {
    opacity: 0;
    transform: translateY(24px);
    transition: opacity 0.55s ease, transform 0.55s ease;
}
[data-reveal="left"]  { transform: translateX(-28px); }
[data-reveal="right"] { transform: translateX(28px); }
[data-reveal="zoom"]  { transform: scale(0.94); }
[data-reveal="none"]  { transform: none; }

[data-reveal].revealed {
    opacity: 1;
    transform: none;
}

/* ── Gallery crossfade ── */
[data-gallery-img] {
    transition: opacity 0.25s ease;
}
[data-gallery-img].gallery-fade-out {
    opacity: 0;
}

/* ── Favorite heart-beat ── */
@keyframes fav-pop {
    0%   { transform: scale(1); }
    40%  { transform: scale(1.35); }
    70%  { transform: scale(0.88); }
    100% { transform: scale(1); }
}
.fav-pop { animation: fav-pop 0.45s ease forwards; }

/* ── Image shimmer ── */
@keyframes shimmer {
    0%   { background-position: -400px 0; }
    100% { background-position:  400px 0; }
}
.img-loading {
    background: linear-gradient(90deg, #e2e8f0 25%, #f8fafc 50%, #e2e8f0 75%);
    background-size: 800px 100%;
    animation: shimmer 1.4s infinite linear;
}

/* ── Card tilt smoothing ── */
[data-tilt] {
    transition: box-shadow 0.3s ease;
    will-change: transform;
}
[data-tilt]:hover { box-shadow: 0 20px 40px rgba(0,0,0,0.12); }

/* ── Search glow ── */
.search-glow-active {
    box-shadow: 0 0 0 3px rgba(27,58,107,0.18);
    border-radius: 0.75rem;
    transition: box-shadow 0.2s ease;
}

/* ── Hero orbs ── */
.hero-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(60px);
    opacity: 0.25;
    pointer-events: none;
    will-change: transform;
}

/* ── Scroll-to-demo hint ── */
@keyframes scroll-hint-pulse {
    0%, 100% { opacity: 1;    transform: scale(1); }
    50%      { opacity: 0.6;  transform: scale(1.07); }
}
.scroll-hint-pulse {
    display: inline-block;
    animation: scroll-hint-pulse 1.6s ease-in-out infinite;
}

/* ── Demo credentials section highlight-on-arrival ── */
@keyframes demo-credentials-highlight {
    0%   { box-shadow: 0 0 0 0 rgba(201,162,39,0.45); }
    50%  { box-shadow: 0 0 0 12px rgba(201,162,39,0); }
    100% { box-shadow: 0 0 0 0 rgba(201,162,39,0); }
}
.demo-credentials-highlight { animation: demo-credentials-highlight 1.2s ease-out; }

    `;
    document.head.appendChild(style);
})();
