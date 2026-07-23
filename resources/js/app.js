const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

document.documentElement.classList.add('js');

window.addEventListener('DOMContentLoaded', () => {
    requestAnimationFrame(() => document.body.classList.add('is-ready'));

    document.querySelectorAll('[data-card-delay]').forEach((element) => {
        element.style.setProperty('--card-delay', `${element.dataset.cardDelay}ms`);
    });

    document.querySelectorAll('[data-reveal-delay]').forEach((element) => {
        element.style.setProperty('--reveal-delay', `${element.dataset.revealDelay}ms`);
    });

    const revealElements = document.querySelectorAll('[data-reveal]');

    if (!prefersReducedMotion && 'IntersectionObserver' in window) {
        const revealObserver = new IntersectionObserver(
            (entries, observer) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) {
                        return;
                    }

                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                });
            },
            { threshold: 0.14 },
        );

        revealElements.forEach((element) => revealObserver.observe(element));
    } else {
        revealElements.forEach((element) => element.classList.add('is-visible'));
    }

    const hero = document.querySelector('[data-hero]');

    if (hero && !prefersReducedMotion) {
        hero.addEventListener('pointermove', (event) => {
            const x = (event.clientX / window.innerWidth - 0.5) * -12;
            const y = (event.clientY / window.innerHeight - 0.5) * -8;

            hero.style.setProperty('--pointer-x', `${x}px`);
            hero.style.setProperty('--pointer-y', `${y}px`);
        });
    }

    document.querySelectorAll('[data-tilt]').forEach((card) => {
        if (prefersReducedMotion) {
            return;
        }

        card.addEventListener('pointermove', (event) => {
            const bounds = card.getBoundingClientRect();
            const x = (event.clientX - bounds.left) / bounds.width - 0.5;
            const y = (event.clientY - bounds.top) / bounds.height - 0.5;

            card.style.setProperty('--tilt-x', `${y * -5}deg`);
            card.style.setProperty('--tilt-y', `${x * 6}deg`);
        });

        card.addEventListener('pointerleave', () => {
            card.style.setProperty('--tilt-x', '0deg');
            card.style.setProperty('--tilt-y', '0deg');
        });
    });

    document.querySelectorAll('[data-confirm-delete]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const productName = form.dataset.productName || 'this product';
            const isConfirmed = window.confirm(
                `Permanently delete ${productName}? This removes it from the database and cannot be undone.`,
            );

            if (!isConfirmed) {
                event.preventDefault();
            }
        });
    });
});
