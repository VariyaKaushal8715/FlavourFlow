const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

document.documentElement.classList.add('js');

const redirectReloadedPublicPage = () => {
    if (!document.body || document.body.dataset.publicPage !== 'true') {
        return;
    }

    const navigationEntry = window.performance.getEntriesByType('navigation')[0];
    const shouldReturnHome =
        navigationEntry?.type === 'reload' &&
        document.body.dataset.refreshPolicy === 'home';
    const homeUrl = document.body.dataset.homeUrl;

    if (shouldReturnHome && homeUrl && window.location.href !== homeUrl) {
        window.location.replace(homeUrl);
    }
};

redirectReloadedPublicPage();

window.addEventListener('pageshow', (event) => {
    if (event.persisted && document.body?.dataset.adminSecurePage === 'true') {
        window.location.reload();
    }
});

const rgbToHex = ({ red, green, blue }) =>
    `#${[red, green, blue]
        .map((channel) => Math.round(channel).toString(16).padStart(2, '0'))
        .join('')}`;

const colorProfile = ({ red, green, blue }) => {
    const normalized = [red, green, blue].map((channel) => channel / 255);
    const maximum = Math.max(...normalized);
    const minimum = Math.min(...normalized);
    const delta = maximum - minimum;
    const lightness = (maximum + minimum) / 2;
    let hue = 0;

    if (delta > 0) {
        if (maximum === normalized[0]) {
            hue = 60 * (((normalized[1] - normalized[2]) / delta) % 6);
        } else if (maximum === normalized[1]) {
            hue = 60 * ((normalized[2] - normalized[0]) / delta + 2);
        } else {
            hue = 60 * ((normalized[0] - normalized[1]) / delta + 4);
        }
    }

    return {
        hue: hue < 0 ? hue + 360 : hue,
        lightness,
        saturation: delta === 0 ? 0 : delta / (1 - Math.abs(2 * lightness - 1)),
    };
};

const hslToRgb = ({ hue, saturation, lightness }) => {
    const chroma = (1 - Math.abs(2 * lightness - 1)) * saturation;
    const hueSegment = hue / 60;
    const secondary = chroma * (1 - Math.abs((hueSegment % 2) - 1));
    let channels;

    if (hueSegment < 1) {
        channels = [chroma, secondary, 0];
    } else if (hueSegment < 2) {
        channels = [secondary, chroma, 0];
    } else if (hueSegment < 3) {
        channels = [0, chroma, secondary];
    } else if (hueSegment < 4) {
        channels = [0, secondary, chroma];
    } else if (hueSegment < 5) {
        channels = [secondary, 0, chroma];
    } else {
        channels = [chroma, 0, secondary];
    }

    const match = lightness - chroma / 2;

    return {
        red: (channels[0] + match) * 255,
        green: (channels[1] + match) * 255,
        blue: (channels[2] + match) * 255,
    };
};

const applyBrandTheme = (theme) => {
    if (!document.body) {
        return;
    }

    document.body.style.setProperty('--brand-primary', theme.primary);
    document.body.style.setProperty('--brand-accent', theme.accent);
    document.body.style.setProperty('--brand-ink', theme.ink);
    document.body.style.setProperty('--brand-surface', theme.surface);
    document.body.dataset.themeSource = theme.source;
};

const cachedThemeKey = () => `brand-theme-v2:${document.body?.dataset.brandLogo || 'default'}`;

const restoreCachedBrandTheme = () => {
    if (!document.body || document.body.dataset.autoTheme !== 'true') {
        return;
    }

    try {
        const cachedTheme = JSON.parse(window.localStorage.getItem(cachedThemeKey()));

        if (cachedTheme?.primary && cachedTheme?.accent) {
            applyBrandTheme({ ...cachedTheme, source: 'logo-cache' });
        }
    } catch {
        // The configured fallback palette remains active when storage is unavailable.
    }
};

const deriveBrandThemeFromLogo = () => {
    const logoUrl = document.body?.dataset.brandLogo;

    if (!logoUrl || document.body.dataset.autoTheme !== 'true') {
        return;
    }

    const logo = new Image();
    logo.crossOrigin = 'anonymous';
    logo.decoding = 'async';

    logo.addEventListener('load', () => {
        const canvas = document.createElement('canvas');
        const size = 56;
        canvas.width = size;
        canvas.height = size;

        const context = canvas.getContext('2d', { willReadFrequently: true });

        if (!context) {
            return;
        }

        context.drawImage(logo, 0, 0, size, size);

        const pixels = context.getImageData(0, 0, size, size).data;
        const colorBuckets = new Map();

        for (let index = 0; index < pixels.length; index += 4) {
            if (pixels[index + 3] < 180) {
                continue;
            }

            const color = {
                red: Math.min(255, Math.round(pixels[index] / 24) * 24),
                green: Math.min(255, Math.round(pixels[index + 1] / 24) * 24),
                blue: Math.min(255, Math.round(pixels[index + 2] / 24) * 24),
            };
            const profile = colorProfile(color);

            if (profile.lightness < 0.12 || profile.lightness > 0.9 || profile.saturation < 0.28) {
                continue;
            }

            const key = `${color.red}-${color.green}-${color.blue}`;
            const bucket = colorBuckets.get(key) || {
                ...color,
                count: 0,
                hue: profile.hue,
                lightness: profile.lightness,
                saturation: profile.saturation,
            };

            bucket.count += 1;
            colorBuckets.set(key, bucket);
        }

        const candidates = Array.from(colorBuckets.values())
            .sort(
                (first, second) =>
                    second.count * (0.6 + second.saturation) -
                    first.count * (0.6 + first.saturation),
            )
            .slice(0, 24);

        if (candidates.length < 2) {
            return;
        }

        const primaryCandidate =
            candidates.find(
                (candidate) =>
                    candidate.saturation >= 0.5 &&
                    candidate.lightness >= 0.2 &&
                    candidate.lightness <= 0.58,
            ) || candidates[0];
        const accentCandidate =
            candidates.find((candidate) => {
                const hueDistance = Math.abs(candidate.hue - primaryCandidate.hue);

                return (
                    candidate.saturation >= 0.5 &&
                    candidate.lightness >= 0.32 &&
                    Math.min(hueDistance, 360 - hueDistance) >= 18
                );
            }) ||
            candidates.find(
                (candidate) =>
                    candidate !== primaryCandidate && candidate.lightness > primaryCandidate.lightness,
            ) ||
            candidates[1];
        const primary = hslToRgb({
            hue: primaryCandidate.hue,
            saturation: Math.max(primaryCandidate.saturation, 0.68),
            lightness: Math.min(0.48, Math.max(primaryCandidate.lightness, 0.38)),
        });
        const accent = hslToRgb({
            hue: accentCandidate.hue,
            saturation: Math.max(accentCandidate.saturation, 0.72),
            lightness: Math.min(0.68, Math.max(accentCandidate.lightness, 0.56)),
        });

        const ink = {
            red: primary.red * 0.14 + 9 * 0.86,
            green: primary.green * 0.14 + 9 * 0.86,
            blue: primary.blue * 0.14 + 11 * 0.86,
        };
        const surface = {
            red: accent.red * 0.07 + 255 * 0.93,
            green: accent.green * 0.07 + 255 * 0.93,
            blue: accent.blue * 0.07 + 255 * 0.93,
        };
        const theme = {
            primary: rgbToHex(primary),
            accent: rgbToHex(accent),
            ink: rgbToHex(ink),
            surface: rgbToHex(surface),
            source: 'logo',
        };

        applyBrandTheme(theme);

        try {
            window.localStorage.setItem(cachedThemeKey(), JSON.stringify(theme));
        } catch {
            // Theme detection still works when storage is unavailable.
        }
    });

    logo.src = logoUrl;
};

restoreCachedBrandTheme();

window.addEventListener('DOMContentLoaded', () => {
    deriveBrandThemeFromLogo();
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

    document.querySelectorAll('[data-offer-stage]').forEach((stage) => {
        const panels = Array.from(stage.querySelectorAll('[data-offer-panel]'));
        const tabs = Array.from(stage.querySelectorAll('[data-offer-tab]'));

        if (!panels.length || panels.length !== tabs.length) {
            return;
        }

        let activeIndex = Math.max(
            0,
            panels.findIndex((panel) => panel.classList.contains('is-active')),
        );
        let rotationTimer;

        const activateOffer = (nextIndex) => {
            activeIndex = (nextIndex + panels.length) % panels.length;

            panels.forEach((panel, index) => {
                const isActive = index === activeIndex;

                panel.classList.toggle('is-active', isActive);
                panel.setAttribute('aria-hidden', isActive ? 'false' : 'true');
            });

            tabs.forEach((tab, index) => {
                const isActive = index === activeIndex;

                tab.classList.remove('is-active');
                tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });

            stage.dataset.activeMotion = panels[activeIndex].dataset.offerMotion || 'slide';
            void stage.offsetWidth;
            tabs[activeIndex].classList.add('is-active');
        };

        const stopRotation = () => {
            window.clearInterval(rotationTimer);
        };

        const startRotation = () => {
            stopRotation();

            if (panels.length < 2) {
                return;
            }

            rotationTimer = window.setInterval(() => activateOffer(activeIndex + 1), 3200);
        };

        tabs.forEach((tab, index) => {
            tab.addEventListener('click', () => {
                activateOffer(index);
                startRotation();
            });
        });

        activateOffer(activeIndex);
        startRotation();
    });

    document.querySelectorAll('[data-confirm-delete]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const itemName = form.dataset.itemName || 'this item';
            const isConfirmed = window.confirm(
                `Permanently delete ${itemName}? This removes it from the database and cannot be undone.`,
            );

            if (!isConfirmed) {
                event.preventDefault();
            }
        });
    });
});
