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

const initializeWishlist = () => {
    const buttons = Array.from(document.querySelectorAll('[data-wishlist-button]'));

    if (!buttons.length) {
        return;
    }

    const wishlistEndpoint = document.body.dataset.wishlistEndpoint;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    const updateButton = (button, wishlisted) => {
        button.dataset.wishlisted = String(wishlisted);
        button.setAttribute('aria-pressed', String(wishlisted));
        button.setAttribute(
            'aria-label',
            `${wishlisted ? 'Remove' : 'Add'} ${button.closest('article')?.querySelector('h3')?.textContent?.trim() || 'product'} ${wishlisted ? 'from' : 'to'} wishlist`,
        );
    };

    const syncButtons = (productIds) => {
        const wishlistedIds = new Set(productIds.map(String));

        buttons.forEach((button) => updateButton(button, wishlistedIds.has(button.dataset.productId)));
    };

    if (wishlistEndpoint) {
        fetch(wishlistEndpoint, { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
            .then((response) => {
                if (!response.ok) {
                    throw new Error('Unable to load wishlist.');
                }

                return response.json();
            })
            .then(({ product_ids: productIds }) => syncButtons(productIds || []))
            .catch(() => {
                // Server-rendered state remains available while the request is retried on the next page load.
            });
    }

    buttons.forEach((button) => {
        button.addEventListener('click', async () => {
            if (!wishlistEndpoint) {
                return;
            }

            const productSlug = button.dataset.productSlug;

            if (!productSlug || button.disabled) {
                return;
            }

            if (needsAuthentication()) {
                promptLogin('Please sign in to manage your wishlist.');
                return;
            }

            const wishlisted = button.dataset.wishlisted === 'true';
            const template = document.body.dataset[wishlisted ? 'wishlistDestroyUrl' : 'wishlistStoreUrl'];

            if (!template) {
                return;
            }

            button.disabled = true;
            button.classList.add('is-loading');

            try {
                const response = await fetch(template.replace('__product__', productSlug), {
                    method: wishlisted ? 'DELETE' : 'POST',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                });

                if (redirectedToLogin(response)) {
                    promptLogin('Please sign in to manage your wishlist.');
                    return;
                }

                if (!response.ok) {
                    throw new Error('Unable to update wishlist.');
                }

                const { wishlisted: isWishlisted } = await response.json();
                updateButton(button, isWishlisted);

                if (!isWishlisted && document.body.dataset.wishlistPage === 'true') {
                    button.closest('article')?.remove();

                    if (!document.querySelector('[data-wishlist-button]')) {
                        window.location.reload();
                    }
                }
            } catch {
                window.alert('We could not update your wishlist. Please try again.');
            } finally {
                button.disabled = false;
                button.classList.remove('is-loading');
            }
        });
    });
};

const updateCartCount = (count) => {
    document.querySelectorAll('[data-cart-count]').forEach((element) => {
        element.textContent = count;
    });
};

const showCartMessage = (message) => {
    const messageElement = document.createElement('div');
    messageElement.className = 'fixed bottom-6 right-6 z-50 rounded-lg bg-emerald-700 px-5 py-3 text-sm font-semibold text-white shadow-lg';
    messageElement.setAttribute('role', 'status');
    messageElement.textContent = message;
    document.body.append(messageElement);
    window.setTimeout(() => messageElement.remove(), 3500);
};

const promptLogin = (message) => {
    window.alert(message);

    const loginUrl = document.body.dataset.loginUrl;

    if (loginUrl) {
        window.location.assign(loginUrl);
    }
};

const needsAuthentication = () => document.body.dataset.authenticated !== 'true';

const redirectedToLogin = (response) => {
    const loginUrl = document.body.dataset.loginUrl;

    return response.status === 401 ||
        response.status === 403 ||
        (loginUrl && response.redirected && response.url.includes(loginUrl));
};

const initializeCart = () => {
    const summaryUrl = document.body.dataset.cartSummaryUrl;
    const storeTemplate = document.body.dataset.cartStoreUrl;
    const clearUrl = document.body.dataset.cartClearUrl;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    if (summaryUrl) {
        fetch(summaryUrl, { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
            .then((response) => response.ok ? response.json() : null)
            .then((data) => data && updateCartCount(data.count))
            .catch(() => {});
    }

    document.querySelectorAll('[data-variant-pill]').forEach((pill) => {
        pill.addEventListener('click', () => {
            const container = pill.closest('[data-variant-selector]');
            if (!container) return;

            container.querySelectorAll('[data-variant-pill]').forEach((p) => {
                p.classList.remove('border-zinc-950', 'bg-zinc-950', 'text-white');
                p.classList.add('border-zinc-300', 'bg-white', 'text-zinc-800');
            });

            pill.classList.remove('border-zinc-300', 'bg-white', 'text-zinc-800');
            pill.classList.add('border-zinc-950', 'bg-zinc-950', 'text-white');

            const weight = pill.dataset.weight;
            const formattedPrice = pill.dataset.formattedPrice;
            const priceDisplay = document.querySelector('[data-product-price-display]');
            const unitDisplay = document.querySelector('[data-product-unit-display]');
            const addToCartBtn = document.querySelector('[data-add-to-cart][data-selected-weight]');

            if (priceDisplay && formattedPrice) priceDisplay.textContent = formattedPrice;
            if (unitDisplay && weight) unitDisplay.textContent = `per ${weight}`;
            if (addToCartBtn && weight) addToCartBtn.dataset.selectedWeight = weight;
        });
    });

    document.querySelectorAll('[data-add-to-cart]').forEach((button) => {
        button.addEventListener('click', async () => {
            if (!storeTemplate) {
                return;
            }

            const productSlug = button.dataset.productSlug;
            if (!productSlug || button.disabled) {
                return;
            }

            if (needsAuthentication()) {
                promptLogin('Please sign in to add items to your cart.');
                return;
            }

            const selectedWeight = button.dataset.selectedWeight || '100g';
            button.disabled = true;

            try {
                const response = await fetch(storeTemplate.replace('__product__', productSlug), {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({
                        quantity: 1,
                        selected_options: { weight: selectedWeight },
                    }),
                });

                if (redirectedToLogin(response)) {
                    promptLogin('Please sign in to add items to your cart.');
                    return;
                }

                if (!response.ok) {
                    throw new Error('Unable to add to cart.');
                }
                const data = await response.json();
                updateCartCount(data.count);
                showCartMessage(data.message || 'Added to your cart.');
            } catch {
                window.alert('We could not add this product to your cart. Please try again.');
            } finally {
                button.disabled = false;
            }
        });
    });

    const updateTemplate = document.body.dataset.cartUpdateUrl;
    const destroyTemplate = document.body.dataset.cartDestroyUrl;
    document.querySelectorAll('[data-cart-item]').forEach((item) => {
        const productSlug = item.dataset.cartSlug;
        const quantityElement = item.querySelector('[data-cart-quantity]');

        const removeItem = async () => {
            try {
                const response = await fetch(destroyTemplate.replace('__product__', productSlug), {
                    method: 'DELETE', credentials: 'same-origin', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
                });
                if (!response.ok) throw new Error('Unable to remove cart item.');
                const data = await response.json();
                updateCartCount(data.count);
                document.querySelectorAll('[data-cart-subtotal], [data-cart-total]').forEach((element) => {
                    element.textContent = `Rs. ${data.subtotal}`;
                });
                item.remove();

                if (!document.querySelector('[data-cart-item]')) {
                    const cartContent = document.getElementById('cart-content');
                    const emptyMsg = document.getElementById('cart-empty-message');
                    const clearBtn = document.querySelector('[data-cart-clear]');
                    if (cartContent) cartContent.remove();
                    if (emptyMsg) emptyMsg.classList.remove('hidden');
                    if (clearBtn) clearBtn.remove();
                }
            } catch {
                window.alert('We could not remove this product from your cart.');
            }
        };

        const setQuantity = async (quantity) => {
            if (quantity < 1) {
                await removeItem();
                return;
            }
            if (!updateTemplate) {
                return;
            }
            const response = await fetch(updateTemplate.replace('__product__', productSlug), {
                method: 'PATCH', credentials: 'same-origin',
                headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ quantity }),
            });
            if (!response.ok) throw new Error('Unable to update cart.');
            const data = await response.json();
            quantityElement.textContent = data.quantity;
            item.querySelector('[data-cart-line-total]').textContent = `Rs. ${data.line_total}`;
            document.querySelectorAll('[data-cart-subtotal], [data-cart-total]').forEach((element) => element.textContent = `Rs. ${data.subtotal}`);
            updateCartCount(data.count);
        };

        item.querySelector('[data-cart-increase]')?.addEventListener('click', () => setQuantity(Number(quantityElement.textContent) + 1).catch(() => window.alert('We could not update your cart.')));
        item.querySelector('[data-cart-decrease]')?.addEventListener('click', () => setQuantity(Number(quantityElement.textContent) - 1).catch(() => window.alert('We could not update your cart.')));
        item.querySelector('[data-cart-remove]')?.addEventListener('click', () => removeItem());
    });

    const clearModal = document.getElementById('clear-cart-modal');
    const clearTriggerBtn = document.querySelector('[data-cart-clear-trigger], [data-cart-clear]');
    const closeModalBtns = document.querySelectorAll('[data-close-clear-modal]');
    const clearForm = document.querySelector('[data-cart-clear-form]');

    const openModal = () => {
        if (clearModal) {
            clearModal.classList.remove('hidden');
            clearModal.classList.add('flex');
            clearModal.setAttribute('aria-hidden', 'false');
        }
    };

    const closeModal = () => {
        if (clearModal) {
            clearModal.classList.add('hidden');
            clearModal.classList.remove('flex');
            clearModal.setAttribute('aria-hidden', 'true');
        }
    };

    if (clearTriggerBtn) {
        clearTriggerBtn.addEventListener('click', (e) => {
            e.preventDefault();
            openModal();
        });
    }

    closeModalBtns.forEach((btn) => {
        btn.addEventListener('click', () => closeModal());
    });

    if (clearModal) {
        clearModal.addEventListener('click', (e) => {
            if (e.target === clearModal) {
                closeModal();
            }
        });
    }

    if (clearForm) {
        clearForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const actionUrl = clearForm.getAttribute('action') || clearUrl || '/cart';

            try {
                const response = await fetch(actionUrl, {
                    method: 'DELETE',
                    credentials: 'same-origin',
                    headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
                });

                if (!response.ok) {
                    throw new Error('Unable to clear cart.');
                }

                const data = await response.json();
                updateCartCount(0);
                document.querySelectorAll('[data-cart-subtotal], [data-cart-total]').forEach((el) => el.textContent = 'Rs. 0.00');

                const cartContent = document.getElementById('cart-content');
                const emptyMsg = document.getElementById('cart-empty-message');
                if (cartContent) cartContent.remove();
                if (emptyMsg) emptyMsg.classList.remove('hidden');
                if (clearTriggerBtn) clearTriggerBtn.remove();
                closeModal();
                showCartMessage(data.message || 'Cart has been cleared.');
            } catch {
                closeModal();
                clearForm.submit();
            }
        });
    }
};

const initializeProfileContactUpdates = () => {
    const buttons = Array.from(document.querySelectorAll('[data-profile-contact-button]'));

    if (!buttons.length) {
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const setMessage = (field, message, type = 'neutral') => {
        const messageElement = document.querySelector(`[data-profile-contact-message="${field}"]`);

        if (!messageElement) {
            return;
        }

        messageElement.textContent = message;
        messageElement.classList.remove('text-amber-700', 'text-emerald-700', 'text-red-700');
        messageElement.classList.add({
            loading: 'text-amber-700',
            success: 'text-emerald-700',
            error: 'text-red-700',
            neutral: 'text-amber-700',
        }[type]);
    };
    const validationMessage = (data, field, fallback) => data?.errors?.[field]?.[0] || data?.message || fallback;

    buttons.forEach((button) => {
        button.addEventListener('click', async () => {
            const field = button.dataset.profileContactField;
            const url = button.dataset.profileContactUrl;
            const container = button.closest('[data-profile-contact]');
            const input = container?.querySelector(`[data-profile-contact-input="${field}"]`);
            const originalLabel = button.textContent.trim();

            if (!field || !url || !input || button.disabled) {
                return;
            }

            if (!input.reportValidity()) {
                setMessage(field, input.validationMessage, 'error');
                return;
            }

            button.disabled = true;
            button.textContent = button.dataset.profileContactLoading || 'Updating...';
            input.disabled = true;
            setMessage(field, 'Saving your change...', 'loading');

            try {
                const response = await fetch(url, {
                    method: 'PATCH',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ [field]: input.value }),
                });
                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(validationMessage(data, field, 'We could not update this detail.'));
                }

                const value = data.profile?.[field] || input.value;
                input.value = value;
                document.querySelectorAll(`[data-profile-contact-value="${field}"]`).forEach((element) => {
                    element.textContent = value || 'Not added yet';
                });
                setMessage(field, data.message || 'Updated successfully.', 'success');
            } catch (error) {
                setMessage(field, error.message || 'We could not update this detail. Please try again.', 'error');
            } finally {
                input.disabled = false;
                button.disabled = false;
                button.textContent = originalLabel;
            }
        });
    });
};

const initializeTrustDialog = () => {
    const dialog = document.querySelector('[data-trust-dialog]');
    const badges = Array.from(document.querySelectorAll('[data-trust-badge]'));
    const closeButton = dialog?.querySelector('[data-trust-close]');
    const titleElement = dialog?.querySelector('#trust-dialog-title');
    const descriptionElement = dialog?.querySelector('#trust-dialog-description');
    const labelElement = dialog?.querySelector('[data-trust-label]');

    if (!dialog || !badges.length || !titleElement || !descriptionElement || !labelElement) {
        return;
    }

    const openDialog = (badge) => {
        titleElement.textContent = badge.dataset.trustTitle || badge.dataset.trustLabel || 'Trust signal';
        descriptionElement.textContent = badge.dataset.trustDescription || '';
        labelElement.textContent = badge.dataset.trustLabel || '';

        dialog.classList.remove('hidden');
        dialog.classList.add('flex');
        dialog.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
    };

    const closeDialog = () => {
        dialog.classList.add('hidden');
        dialog.classList.remove('flex');
        dialog.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
    };

    badges.forEach((badge) => {
        badge.addEventListener('click', () => openDialog(badge));
    });

    closeButton?.addEventListener('click', closeDialog);

    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            closeDialog();
        }
    });

    window.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !dialog.classList.contains('hidden')) {
            closeDialog();
        }
    });
};

const initializeAdminSidebar = () => {
    const sidebar = document.querySelector('[data-admin-sidebar]');
    const overlay = document.querySelector('[data-admin-sidebar-overlay]');
    const toggle = document.querySelector('[data-admin-sidebar-toggle]');

    if (!sidebar || !overlay || !toggle) {
        return;
    }

    const openSidebar = () => {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        toggle.setAttribute('aria-expanded', 'true');
    };

    const closeSidebar = () => {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        toggle.setAttribute('aria-expanded', 'false');
    };

    toggle.addEventListener('click', () => {
        if (sidebar.classList.contains('-translate-x-full')) {
            openSidebar();
        } else {
            closeSidebar();
        }
    });

    overlay.addEventListener('click', closeSidebar);

    sidebar.querySelectorAll('a, button[type="submit"]').forEach((element) => {
        element.addEventListener('click', () => {
            if (window.innerWidth < 1024) {
                closeSidebar();
            }
        });
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1024) {
            overlay.classList.add('hidden');
            sidebar.classList.remove('-translate-x-full');
            document.body.classList.remove('overflow-hidden');
            toggle.setAttribute('aria-expanded', 'false');
        } else if (!sidebar.classList.contains('-translate-x-full')) {
            overlay.classList.remove('hidden');
        }
    });
};

const initializeSiteNav = () => {
    const toggle = document.querySelector('[data-site-nav-toggle]');
    const panel = document.querySelector('[data-site-nav-panel]');

    if (!toggle || !panel) {
        return;
    }

    const closePanel = () => {
        panel.classList.add('hidden');
        toggle.setAttribute('aria-expanded', 'false');
    };

    toggle.addEventListener('click', () => {
        const isHidden = panel.classList.contains('hidden');

        if (isHidden) {
            panel.classList.remove('hidden');
            toggle.setAttribute('aria-expanded', 'true');
        } else {
            closePanel();
        }
    });

    panel.querySelectorAll('a, button').forEach((element) => {
        element.addEventListener('click', () => {
            if (window.innerWidth < 768) {
                closePanel();
            }
        });
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 768) {
            closePanel();
        }
    });
};

const initializeAdminOrderNotifications = () => {
    const toast = document.querySelector('[data-admin-order-notification]');
    const badge = document.querySelector('[data-admin-orders-badge]');

    if (!toast && !badge) {
        return;
    }

    const summaryUrl = toast?.dataset.summaryUrl;
    const ordersUrl = toast?.dataset.ordersUrl || '/admin/orders';
    const messageEl = toast?.querySelector('[data-admin-order-notification-message]');
    const closeBtn = toast?.querySelector('[data-admin-order-notification-close]');
    const card = toast?.querySelector('[data-admin-order-notification-card]');

    let dismissedCount = 0;
    let isFetching = false;

    const updateUI = (data) => {
        const hasUnread = Boolean(data?.has_unread && data?.order_count > 0);
        const orderCount = Number(data?.order_count) || 0;

        if (badge) {
            if (hasUnread) {
                badge.textContent = orderCount > 99 ? '99+' : `${orderCount}`;
                badge.classList.remove('hidden');
            } else {
                badge.textContent = '';
                badge.classList.add('hidden');
            }
        }

        if (toast && messageEl) {
            if (hasUnread) {
                messageEl.textContent = data.message || `You have ${orderCount} new orders. Tap to view orders.`;

                if (dismissedCount !== orderCount) {
                    toast.classList.remove('hidden');
                }
            } else {
                toast.classList.add('hidden');
                dismissedCount = 0;
            }
        }
    };

    const fetchSummary = async () => {
        if (!summaryUrl || isFetching) {
            return;
        }

        try {
            isFetching = true;
            const response = await fetch(summaryUrl, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                cache: 'no-store',
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();
            updateUI(data);
        } catch {
            // Keep existing UI state if fetch fails temporarily
        } finally {
            isFetching = false;
        }
    };

    if (closeBtn) {
        closeBtn.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            if (toast) {
                toast.classList.add('hidden');
            }
            if (badge && badge.textContent) {
                dismissedCount = parseInt(badge.textContent, 10) || 0;
            }
        });
    }

    if (card) {
        card.addEventListener('click', (event) => {
            if (event.target.closest('[data-admin-order-notification-close]')) {
                return;
            }
            window.location.href = ordersUrl;
        });
    }

    fetchSummary();

    const intervalId = window.setInterval(fetchSummary, 8000);

    window.addEventListener('focus', fetchSummary);

    window.addEventListener('beforeunload', () => {
        window.clearInterval(intervalId);
    });
};

restoreCachedBrandTheme();

window.addEventListener('DOMContentLoaded', () => {
    deriveBrandThemeFromLogo();
    initializeWishlist();
    initializeCart();
    initializeProfileContactUpdates();
    initializeTrustDialog();
    initializeAdminSidebar();
    initializeSiteNav();
    initializeAdminOrderNotifications();
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

    const backToTop = document.querySelector('[data-back-to-top]');

    if (backToTop) {
        const toggleBackToTop = () => {
            const isVisible = window.scrollY > 420;

            backToTop.classList.toggle('is-visible', isVisible);
            backToTop.setAttribute('aria-hidden', isVisible ? 'false' : 'true');
            backToTop.tabIndex = isVisible ? 0 : -1;
        };

        let scrollFrame = false;

        const onScroll = () => {
            if (scrollFrame) {
                return;
            }

            scrollFrame = true;

            window.requestAnimationFrame(() => {
                toggleBackToTop();
                scrollFrame = false;
            });
        };

        backToTop.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: prefersReducedMotion ? 'auto' : 'smooth',
            });
        });

        toggleBackToTop();
        window.addEventListener('scroll', onScroll, { passive: true });
    }
});
