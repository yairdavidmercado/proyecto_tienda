document.addEventListener('DOMContentLoaded', () => {
  const alerts = document.querySelectorAll('.alert[data-auto-close="true"]');
  alerts.forEach((alert) => {
    setTimeout(() => {
      alert.classList.add('fade');
      alert.classList.remove('show');
    }, 3500);
  });

  const countrySelector = document.querySelector('#countrySelector');
  const priceTags = document.querySelectorAll('.js-product-price');
  const addButtons = document.querySelectorAll('.js-add-to-cart');
  const categoryFilterLinks = document.querySelectorAll('.js-category-filter');
  const categoryCards = document.querySelectorAll('.js-category-card');
  const productItems = document.querySelectorAll('.js-product-item');
  const productsEmptyState = document.querySelector('#productsEmptyState');
  const cartItemsBody = document.querySelector('#cartItemsBody');
  const cartTotalLabel = document.querySelector('#cartTotalLabel');
  const cartEmptyState = document.querySelector('#cartEmptyState');
  const cartTableWrap = document.querySelector('#cartTableWrap');
  const cartPanel = document.querySelector('#carrito');
  const cartOverlay = document.querySelector('#cartOverlay');
  const openCartBtn = document.querySelector('#openCartBtn');
  const closeCartBtn = document.querySelector('#closeCartBtn');
  const countryWhatsappLinks = document.querySelectorAll('.js-country-whatsapp-link');
  const sendCartWhatsappBtn = document.querySelector('#sendCartWhatsappBtn');
  const clearCartBtn = document.querySelector('#clearCartBtn');
  const cartCountBadge = document.querySelector('#cartCountBadge');
  const paymentMethodsDataNode = document.querySelector('#paymentMethodsByCountryData');
  const paymentMethodsContent = document.querySelector('#paymentMethodsContent');

  if (!countrySelector) {
    return;
  }

  const STORAGE_COUNTRY_KEY = 'tienda_country';
  const STORAGE_CART_KEY_PREFIX = 'tienda_cart_';

  const getCartStorageKey = () => `${STORAGE_CART_KEY_PREFIX}${countrySelector.value || 'CO'}`;

  const parseRate = (value, fallback) => {
    const parsed = Number.parseFloat(String(value || '').replace(',', '.'));
    if (!Number.isFinite(parsed) || parsed <= 0) {
      return fallback;
    }

    // Compatibility:
    // - Legacy format: 1 COP = 0.00026 USD
    // - Conventional format: 1 USD = 3850 COP
    return parsed > 1 ? (1 / parsed) : parsed;
  };

  const normalizePhone = (value) => String(value || '').replace(/\D+/g, '');

  const dynamicRates = {
    eur: parseRate(countrySelector.dataset.rateCopToEur, 0.00023),
    mxn: parseRate(countrySelector.dataset.rateCopToMxn, 0.0044),
    usd: parseRate(countrySelector.dataset.rateCopToUsd, 0.00026)
  };

  const countryConfig = {
    CO: { currency: 'COP', locale: 'es-CO', rate: 1, fractionDigits: 0, lang: 'es-CO' },
    ES: { currency: 'EUR', locale: 'es-ES', rate: dynamicRates.eur, fractionDigits: 2, lang: 'es-ES' },
    MX: { currency: 'MXN', locale: 'es-MX', rate: dynamicRates.mxn, fractionDigits: 2, lang: 'es-MX' },
    US: { currency: 'USD', locale: 'en-US', rate: dynamicRates.usd, fractionDigits: 2, lang: 'en-US' }
  };

  const whatsappByCountry = {
    CO: normalizePhone(countrySelector.dataset.whatsappCo),
    ES: normalizePhone(countrySelector.dataset.whatsappEs),
    MX: normalizePhone(countrySelector.dataset.whatsappMx),
    US: normalizePhone(countrySelector.dataset.whatsappUs)
  };

  const whatsappMessageByCountry = {
    CO: String(countrySelector.dataset.whatsappMessageCo || ''),
    ES: String(countrySelector.dataset.whatsappMessageEs || ''),
    MX: String(countrySelector.dataset.whatsappMessageMx || ''),
    US: String(countrySelector.dataset.whatsappMessageUs || '')
  };

  const parsePaymentMethodsByCountry = () => {
    if (!paymentMethodsDataNode) {
      return {};
    }

    try {
      const parsed = JSON.parse(paymentMethodsDataNode.textContent || '{}');
      return parsed && typeof parsed === 'object' ? parsed : {};
    } catch (error) {
      return {};
    }
  };

  const paymentMethodsByCountry = parsePaymentMethodsByCountry();

  const i18n = {
    'es-CO': {
      nav_categories: 'Categorias', nav_products: 'Productos', nav_contact: 'Contacto',
      country_select_label: 'Selecciona tu pais', country_co: '🇨🇴 Colombia', country_es: '🇪🇸 Espana', country_mx: '🇲🇽 Mexico', country_us: '🇺🇸 Estados Unidos',
      cart_nav: 'Carrito', login: 'Iniciar sesion', hero_badge: 'Catalogo digital automatizado',
      hero_title: 'Combos, cuentas y pantallas listas para vender', hero_subtitle: 'Pantallas streaming con entrega inmediata, precios claros y atencion directa por WhatsApp.',
      hero_slide2_eyebrow: 'Pantallas y cuentas', hero_slide2_title: 'Los mejores precios en streaming premium',
      hero_slide3_eyebrow: 'Colombia · Espana · Mexico · USA', hero_slide3_title: 'Disponible para todo el mundo',
      hero_cta_catalog: 'Ver catalogo', hero_cta_whatsapp: 'Pedir por WhatsApp', featured: 'Destacado',
      hero_side_title: 'Experiencia visual moderna', hero_side_text: 'Diseno limpio, elegante y claro con estilo premium.',
      categories: 'Categorias', catalog: 'Catalogo', products_available: 'Productos disponibles',
      products_help: 'Selecciona cantidades y agrega al carrito para enviar un pedido completo.',
      all: 'Todos', no_products: 'No hay productos en esta categoria', no_products_help: 'Agrega productos desde el panel administrativo.',
      popular: 'Popular', quantity: 'Cantidad', add_to_cart: 'Anadir al carrito', purchase: 'Compra',
      cart_title: 'Carrito de compras', cart_help: 'Ajusta cantidades y envia todo el pedido en un solo mensaje.',
      clear_cart: 'Vaciar carrito', send_cart_whatsapp: 'Enviar pedido por WhatsApp',
      cart_empty: 'Tu carrito esta vacio. Agrega productos para generar tu pedido.',
      table_product: 'Producto', table_unit_price: 'Precio unitario', table_quantity: 'Cantidad', table_subtotal: 'Subtotal',
      order_total: 'Total del pedido', explore: 'Explora', categories_help: 'Organiza el catalogo en bloques simples para que el cliente entienda rapido que puede comprar.',
      payment_methods: 'Medios de pago', payment_methods_title: 'Opciones de pago por pais',
      payment_methods_help: 'Cambia de pais para mostrar medios de pago locales e internacionales.',
      contact: 'Contacto', contact_title: 'Atencion inmediata', contact_help: 'Conecta al cliente con un mensaje precargado para acelerar la compra.',
      contact_cta: 'Hablar por WhatsApp', footer_dev: 'Desarrollado con Bootstrap 5, PHP y MySQL.',
      remove: 'Quitar', msg_empty_cart: 'Tu carrito esta vacio. Agrega productos antes de enviar el pedido.',
      msg_no_whatsapp: 'No hay un numero de WhatsApp configurado para este pais.',
      msg_order_header: 'Hola, quiero realizar este pedido:', msg_country: 'Pais',
      msg_qty: 'Cantidad', msg_unit: 'Unitario', msg_subtotal: 'Subtotal', msg_total: 'Total del pedido',
      uncategorized: 'Sin categoria'
    },
    'es-ES': {
      nav_categories: 'Categorias', nav_products: 'Productos', nav_contact: 'Contacto',
      country_select_label: 'Selecciona tu pais', country_co: '🇨🇴 Colombia', country_es: '🇪🇸 Espana', country_mx: '🇲🇽 Mexico', country_us: '🇺🇸 Estados Unidos',
      cart_nav: 'Carrito', login: 'Iniciar sesion', hero_badge: 'Catalogo digital automatizado',
      hero_title: 'Combos, cuentas y pantallas listas para vender', hero_subtitle: 'Pantallas streaming con entrega inmediata, precios claros y atencion directa por WhatsApp.',
      hero_slide2_eyebrow: 'Pantallas y cuentas', hero_slide2_title: 'Los mejores precios en streaming premium',
      hero_slide3_eyebrow: 'Colombia · Espana · Mexico · USA', hero_slide3_title: 'Disponible para todo el mundo',
      hero_cta_catalog: 'Ver catalogo', hero_cta_whatsapp: 'Pedir por WhatsApp', featured: 'Destacado',
      hero_side_title: 'Experiencia visual moderna', hero_side_text: 'Diseno limpio, elegante y claro con estilo premium.',
      categories: 'Categorias', catalog: 'Catalogo', products_available: 'Productos disponibles',
      products_help: 'Selecciona cantidades y anade al carrito para enviar un pedido completo.',
      all: 'Todos', no_products: 'No hay productos en esta categoria', no_products_help: 'Anade productos desde el panel administrativo.',
      popular: 'Popular', quantity: 'Cantidad', add_to_cart: 'Anadir al carrito', purchase: 'Compra',
      cart_title: 'Carrito de compra', cart_help: 'Ajusta cantidades y envia todo el pedido en un solo mensaje.',
      clear_cart: 'Vaciar carrito', send_cart_whatsapp: 'Enviar pedido por WhatsApp',
      cart_empty: 'Tu carrito esta vacio. Anade productos para generar tu pedido.',
      table_product: 'Producto', table_unit_price: 'Precio unitario', table_quantity: 'Cantidad', table_subtotal: 'Subtotal',
      order_total: 'Total del pedido', explore: 'Explora', categories_help: 'Organiza el catalogo para que el cliente entienda rapido que puede comprar.',
      payment_methods: 'Medios de pago', payment_methods_title: 'Opciones de pago por pais',
      payment_methods_help: 'Cambia de pais para mostrar medios de pago locales e internacionales.',
      contact: 'Contacto', contact_title: 'Atencion inmediata', contact_help: 'Conecta al cliente con un mensaje precargado para acelerar la compra.',
      contact_cta: 'Hablar por WhatsApp', footer_dev: 'Desarrollado con Bootstrap 5, PHP y MySQL.',
      remove: 'Quitar', msg_empty_cart: 'Tu carrito esta vacio. Anade productos antes de enviar el pedido.',
      msg_no_whatsapp: 'No hay un numero de WhatsApp configurado para este pais.',
      msg_order_header: 'Hola, quiero realizar este pedido:', msg_country: 'Pais',
      msg_qty: 'Cantidad', msg_unit: 'Unitario', msg_subtotal: 'Subtotal', msg_total: 'Total del pedido',
      uncategorized: 'Sin categoria'
    },
    'es-MX': {
      nav_categories: 'Categorias', nav_products: 'Productos', nav_contact: 'Contacto',
      country_select_label: 'Selecciona tu pais', country_co: '🇨🇴 Colombia', country_es: '🇪🇸 Espana', country_mx: '🇲🇽 Mexico', country_us: '🇺🇸 Estados Unidos',
      cart_nav: 'Carrito', login: 'Iniciar sesion', hero_badge: 'Catalogo digital automatizado',
      hero_title: 'Combos, cuentas y pantallas listas para vender', hero_subtitle: 'Pantallas streaming con entrega inmediata, precios claros y atencion directa por WhatsApp.',
      hero_slide2_eyebrow: 'Pantallas y cuentas', hero_slide2_title: 'Los mejores precios en streaming premium',
      hero_slide3_eyebrow: 'Colombia · Espana · Mexico · USA', hero_slide3_title: 'Disponible para todo el mundo',
      hero_cta_catalog: 'Ver catalogo', hero_cta_whatsapp: 'Pedir por WhatsApp', featured: 'Destacado',
      hero_side_title: 'Experiencia visual moderna', hero_side_text: 'Diseno limpio, elegante y claro con estilo premium.',
      categories: 'Categorias', catalog: 'Catalogo', products_available: 'Productos disponibles',
      products_help: 'Selecciona cantidades y agrega al carrito para enviar un pedido completo.',
      all: 'Todos', no_products: 'No hay productos en esta categoria', no_products_help: 'Agrega productos desde el panel administrativo.',
      popular: 'Popular', quantity: 'Cantidad', add_to_cart: 'Agregar al carrito', purchase: 'Compra',
      cart_title: 'Carrito de compras', cart_help: 'Ajusta cantidades y envia todo el pedido en un solo mensaje.',
      clear_cart: 'Vaciar carrito', send_cart_whatsapp: 'Enviar pedido por WhatsApp',
      cart_empty: 'Tu carrito esta vacio. Agrega productos para generar tu pedido.',
      table_product: 'Producto', table_unit_price: 'Precio unitario', table_quantity: 'Cantidad', table_subtotal: 'Subtotal',
      order_total: 'Total del pedido', explore: 'Explora', categories_help: 'Organiza el catalogo en bloques simples para que el cliente entienda rapido que puede comprar.',
      payment_methods: 'Medios de pago', payment_methods_title: 'Opciones de pago por pais',
      payment_methods_help: 'Cambia de pais para mostrar medios de pago locales e internacionales.',
      contact: 'Contacto', contact_title: 'Atencion inmediata', contact_help: 'Conecta al cliente con un mensaje precargado para acelerar la compra.',
      contact_cta: 'Hablar por WhatsApp', footer_dev: 'Desarrollado con Bootstrap 5, PHP y MySQL.',
      remove: 'Quitar', msg_empty_cart: 'Tu carrito esta vacio. Agrega productos antes de enviar el pedido.',
      msg_no_whatsapp: 'No hay un numero de WhatsApp configurado para este pais.',
      msg_order_header: 'Hola, quiero realizar este pedido:', msg_country: 'Pais',
      msg_qty: 'Cantidad', msg_unit: 'Unitario', msg_subtotal: 'Subtotal', msg_total: 'Total del pedido',
      uncategorized: 'Sin categoria'
    },
    'en-US': {
      nav_categories: 'Categories', nav_products: 'Products', nav_contact: 'Contact',
      country_select_label: 'Select your country', country_co: '🇨🇴 Colombia', country_es: '🇪🇸 Spain', country_mx: '🇲🇽 Mexico', country_us: '🇺🇸 United States',
      cart_nav: 'Cart', login: 'Sign in', hero_badge: 'Automated digital catalog',
      hero_title: 'Combos, accounts, and screens ready to sell', hero_subtitle: 'Streaming screens with instant delivery, clear pricing, and direct WhatsApp support.',
      hero_slide2_eyebrow: 'Screens and accounts', hero_slide2_title: 'Best prices for premium streaming',
      hero_slide3_eyebrow: 'Colombia · Spain · Mexico · USA', hero_slide3_title: 'Available worldwide',
      hero_cta_catalog: 'View catalog', hero_cta_whatsapp: 'Order via WhatsApp', featured: 'Featured',
      hero_side_title: 'Modern visual experience', hero_side_text: 'Clean, bright and premium design with soft transparency.',
      categories: 'Categories', catalog: 'Catalog', products_available: 'Available products',
      products_help: 'Choose quantities and add to cart to send one complete order.',
      all: 'All', no_products: 'No products in this category', no_products_help: 'Add products from the admin panel.',
      popular: 'Popular', quantity: 'Quantity', add_to_cart: 'Add to cart', purchase: 'Purchase',
      cart_title: 'Shopping cart', cart_help: 'Adjust quantities and send the full order in one message.',
      clear_cart: 'Clear cart', send_cart_whatsapp: 'Send order via WhatsApp',
      cart_empty: 'Your cart is empty. Add products to create your order.',
      table_product: 'Product', table_unit_price: 'Unit price', table_quantity: 'Quantity', table_subtotal: 'Subtotal',
      order_total: 'Order total', explore: 'Explore', categories_help: 'Organize your catalog so customers quickly understand what they can buy.',
      payment_methods: 'Payment methods', payment_methods_title: 'Country-based payment options',
      payment_methods_help: 'Switch country to display local and international payment methods.',
      contact: 'Contact', contact_title: 'Immediate support', contact_help: 'Connect customers with a preloaded message to speed up purchases.',
      contact_cta: 'Chat on WhatsApp', footer_dev: 'Built with Bootstrap 5, PHP, and MySQL.',
      remove: 'Remove', msg_empty_cart: 'Your cart is empty. Add products before sending your order.',
      msg_no_whatsapp: 'No WhatsApp number is configured for this country.',
      msg_order_header: 'Hello, I want to place this order:', msg_country: 'Country',
      msg_qty: 'Quantity', msg_unit: 'Unit price', msg_subtotal: 'Subtotal', msg_total: 'Order total',
      uncategorized: 'Uncategorized'
    }
  };

  const getCountry = (countryCode) => countryConfig[countryCode] || countryConfig.CO;
  const getLang = () => getCountry(countrySelector.value).lang;
  const categoryTranslations = {
    'en-US': {
      1: { name: 'Screen Combos', desc: 'Packages for clients who buy access by profile or screen.' },
      2: { name: 'Account Combos', desc: 'Complete options for those who need ready-to-use accounts.' },
      3: { name: 'Individual Screens', desc: 'Unit products for quick sales and low entry ticket.' },
      4: { name: 'Full Accounts', desc: 'Full plans for clients who prefer total access control.' }
    }
  };
  const productTranslations = {
    'en-US': {
      1: { name: 'Streaming Plus Combo', desc: 'Includes multiple platforms in one package with fast delivery.' },
      2: { name: 'Full Premium Account', desc: 'Full access ideal for users who want a dedicated account.' },
      3: { name: 'Individual Screen Pro', desc: 'Affordable option designed for quick purchases.' },
      4: { name: 'Complete Max Account', desc: 'Complete account focused on continuity and higher perceived value.' }
    }
  };
  const t = (key) => {
    const lang = getLang();
    return (i18n[lang] && i18n[lang][key]) || i18n['es-CO'][key] || key;
  };

  const applyCategoryTranslations = () => {
    const lang = getLang();
    const catMap = categoryTranslations[lang] || null;
    const catElements = document.querySelectorAll('[data-cat-id]');

    catElements.forEach((el) => {
      const catId = Number(el.dataset.catId);
      const field = el.dataset.catField || 'name';
      const original = el.dataset.original || el.textContent.trim();
      const translation = catMap && catMap[catId] ? catMap[catId][field] : null;
      el.textContent = translation || original;
    });
  };

  const applyProductTranslations = () => {
    const lang = getLang();
    const productMap = productTranslations[lang] || null;
    const productElements = document.querySelectorAll('[data-product-id][data-product-field]');

    productElements.forEach((el) => {
      const productId = Number(el.dataset.productId);
      const field = el.dataset.productField || 'name';
      const original = el.dataset.original || el.textContent.trim();
      const translation = productMap && productMap[productId] ? productMap[productId][field] : null;
      el.textContent = translation || original;
    });

    addButtons.forEach((button) => {
      const productId = Number(button.dataset.productId);
      const productTranslation = productMap && productMap[productId] ? productMap[productId] : null;
      const productName = productTranslation ? productTranslation.name : (button.dataset.originalProductName || button.dataset.productName || '');

      button.dataset.productName = productName;

      const categoryNameNode = button.closest('.product-card')?.querySelector('[data-cat-id][data-cat-field="name"]');
      if (categoryNameNode) {
        button.dataset.categoryName = categoryNameNode.textContent.trim();
      } else {
        button.dataset.categoryName = button.dataset.originalCategoryName || button.dataset.categoryName || '';
      }

    });
  };

  const getWhatsappPhone = (countryCode) => {
    return whatsappByCountry[countryCode] || '';
  };

  const buildWhatsappUrl = (phone, message) => {
    const cleanPhone = normalizePhone(phone);
    if (!cleanPhone) {
      return '';
    }
    return `https://wa.me/${cleanPhone}?text=${encodeURIComponent(message || '')}`;
  };

  const updateCountryWhatsappLinks = (countryCode) => {
    const phone = getWhatsappPhone(countryCode);

    countryWhatsappLinks.forEach((link) => {
      const fallbackMessage = whatsappMessageByCountry[countryCode] || '';
      const message = link.dataset.whatsappMessage || fallbackMessage;
      const url = buildWhatsappUrl(phone, message);
      link.href = url || '#';
      link.classList.toggle('disabled', !url);
      link.setAttribute('aria-disabled', url ? 'false' : 'true');
      if (!url) {
        link.title = t('msg_no_whatsapp');
      } else if (link.title) {
        link.removeAttribute('title');
      }
    });
  };

  const sanitizePaymentMethodsHtml = (rawHtml) => {
    const allowedTags = new Set(['I', 'SPAN', 'STRONG', 'B', 'EM', 'SMALL', 'BR', 'P', 'UL', 'OL', 'LI', 'DIV']);
    const allowedAttrs = new Set(['class', 'title', 'aria-label']);

    const template = document.createElement('template');
    template.innerHTML = String(rawHtml || '');

    const walk = (node) => {
      if (node.nodeType === Node.ELEMENT_NODE) {
        const element = node;

        if (!allowedTags.has(element.tagName)) {
          const parent = element.parentNode;
          if (parent) {
            while (element.firstChild) {
              parent.insertBefore(element.firstChild, element);
            }
            parent.removeChild(element);
          }
          return;
        }

        Array.from(element.attributes).forEach((attr) => {
          if (!allowedAttrs.has(attr.name.toLowerCase())) {
            element.removeAttribute(attr.name);
          }
        });
      }

      Array.from(node.childNodes).forEach((child) => walk(child));
    };

    Array.from(template.content.childNodes).forEach((child) => walk(child));
    return template.innerHTML;
  };

  const updatePaymentMethodsByCountry = (countryCode) => {
    if (!paymentMethodsContent) {
      return;
    }

    const countryText = paymentMethodsByCountry[countryCode] || paymentMethodsByCountry.CO || '';
    const normalized = String(countryText || '').trim();
    paymentMethodsContent.innerHTML = sanitizePaymentMethodsHtml(normalized);
  };

  const formatPrice = (amount, config) => {
    return new Intl.NumberFormat(config.locale, {
      style: 'currency',
      currency: config.currency,
      minimumFractionDigits: config.fractionDigits,
      maximumFractionDigits: config.fractionDigits
    }).format(amount);
  };

  const buildPriceLabel = (priceTag, countryCode) => {
    const basePriceCop = Number(priceTag.dataset.basePriceCop || 0);

    if (!basePriceCop || Number.isNaN(basePriceCop)) {
      return priceTag.dataset.defaultLabel || priceTag.textContent.trim();
    }

    const config = getCountry(countryCode);
    const convertedPrice = basePriceCop * config.rate;
    return formatPrice(convertedPrice, config);
  };

  const convertFromCop = (basePriceCop, countryCode) => {
    const config = getCountry(countryCode);
    return Number(basePriceCop || 0) * config.rate;
  };

  const formatWithSuffix = (amount, countryCode) => formatPrice(amount, getCountry(countryCode));

  const applyTranslations = () => {
    const elements = document.querySelectorAll('[data-i18n]');
    elements.forEach((el) => {
      const key = el.dataset.i18n;
      if (!key) return;
      const translated = t(key);
      if (translated !== key) {
        el.textContent = translated;
      }
    });

    const label = document.querySelector('label[for="countrySelector"]');
    if (label) {
      label.textContent = t('country_select_label');
    }
  };

  const openCartDrawer = () => {
    if (!cartPanel) {
      return;
    }
    cartPanel.classList.add('is-open');
    if (cartOverlay) {
      cartOverlay.classList.add('is-open');
    }
    document.body.classList.add('cart-open');
  };

  const closeCartDrawer = () => {
    if (!cartPanel) {
      return;
    }
    cartPanel.classList.remove('is-open');
    if (cartOverlay) {
      cartOverlay.classList.remove('is-open');
    }
    document.body.classList.remove('cart-open');
  };

  const getCart = () => {
    try {
      const parsed = JSON.parse(localStorage.getItem(getCartStorageKey()) || '[]');
      return Array.isArray(parsed) ? parsed : [];
    } catch (error) {
      return [];
    }
  };

  const saveCart = (items) => {
    try {
      localStorage.setItem(getCartStorageKey(), JSON.stringify(items));
    } catch (error) {
      // Ignore storage errors in restricted contexts.
    }
  };

  const filterCatalogByCountry = (countryCode) => {
    let visibleProducts = 0;

    categoryFilterLinks.forEach((link) => {
      const countryCodes = String(link.dataset.countryCodes || link.dataset.countryCode || 'CO')
        .split(',')
        .map((code) => code.trim().toUpperCase())
        .filter(Boolean);
      const isVisible = countryCodes.includes(countryCode);
      link.hidden = !isVisible;
    });

    categoryCards.forEach((card) => {
      const countryCodes = String(card.dataset.countryCodes || card.dataset.countryCode || 'CO')
        .split(',')
        .map((code) => code.trim().toUpperCase())
        .filter(Boolean);
      const isVisible = countryCodes.includes(countryCode);
      card.hidden = !isVisible;
    });

    productItems.forEach((item) => {
      const countryCodes = String(item.dataset.countryCodes || item.dataset.countryCode || 'CO')
        .split(',')
        .map((code) => code.trim().toUpperCase())
        .filter(Boolean);
      const isVisible = countryCodes.includes(countryCode);
      item.hidden = !isVisible;
      if (isVisible) {
        visibleProducts += 1;
      }
    });

    if (productsEmptyState) {
      productsEmptyState.style.display = visibleProducts > 0 ? 'none' : '';
    }
  };

  const updateCartBadge = (items) => {
    if (!cartCountBadge) {
      return;
    }
    const totalQty = items.reduce((sum, item) => sum + Number(item.qty || 0), 0);
    cartCountBadge.textContent = String(totalQty);
  };

  const renderCart = () => {
    if (!cartItemsBody || !cartTotalLabel) {
      return;
    }

    const selectedCountry = countrySelector.value;
    const items = getCart();

    if (items.length === 0) {
      cartItemsBody.innerHTML = '';
      cartTotalLabel.textContent = formatWithSuffix(0, selectedCountry);
      if (cartEmptyState) cartEmptyState.style.display = '';
      if (cartTableWrap) cartTableWrap.style.display = 'none';
      updateCartBadge(items);
      return;
    }

    if (cartEmptyState) cartEmptyState.style.display = 'none';
    if (cartTableWrap) cartTableWrap.style.display = '';

    let total = 0;
    cartItemsBody.innerHTML = items.map((item) => {
      const qty = Number(item.qty || 1);
      const translatedProduct = productTranslations[getLang()] && productTranslations[getLang()][Number(item.productId)]
        ? productTranslations[getLang()][Number(item.productId)]
        : null;
      const translatedCategory = categoryTranslations[getLang()] && categoryTranslations[getLang()][Number(item.categoryId || 0)]
        ? categoryTranslations[getLang()][Number(item.categoryId || 0)]
        : null;
      const itemName = translatedProduct ? translatedProduct.name : item.name;
      const itemCategoryName = translatedCategory ? translatedCategory.name : (item.categoryName || t('uncategorized'));
      const unitAmount = convertFromCop(item.basePriceCop, selectedCountry);
      const lineTotal = unitAmount * qty;
      total += lineTotal;

      const unitLabel = formatWithSuffix(unitAmount, selectedCountry);
      const totalLabel = formatWithSuffix(lineTotal, selectedCountry);

      return `
        <tr>
          <td>
            <div class="fw-semibold text-white">${itemName}</div>
            <div class="small text-secondary">${itemCategoryName}</div>
          </td>
          <td class="text-white">${unitLabel}</td>
          <td>
            <input type="number" min="1" class="form-control form-control-sm cart-qty-input" data-product-id="${item.productId}" value="${qty}">
          </td>
          <td class="text-white text-end">${totalLabel}</td>
          <td class="text-end">
            <button type="button" class="btn btn-sm btn-outline-light rounded-pill cart-remove-btn" data-product-id="${item.productId}">${t('remove')}</button>
          </td>
        </tr>
      `;
    }).join('');

    cartTotalLabel.textContent = formatWithSuffix(total, selectedCountry);
    updateCartBadge(items);
  };

  const setCartItemQty = (productId, qty) => {
    const items = getCart();
    const nextItems = items.map((item) => {
      if (String(item.productId) !== String(productId)) {
        return item;
      }
      return { ...item, qty: Math.max(1, Number(qty || 1)) };
    });
    saveCart(nextItems);
    renderCart();
  };

  const removeCartItem = (productId) => {
    const items = getCart();
    const nextItems = items.filter((item) => String(item.productId) !== String(productId));
    saveCart(nextItems);
    renderCart();
  };

  const addToCart = (payload) => {
    const items = getCart();
    const existing = items.find((item) => String(item.productId) === String(payload.productId));

    if (existing) {
      existing.qty = Math.max(1, Number(existing.qty || 1) + Number(payload.qty || 1));
    } else {
      items.push({
        productId: payload.productId,
        name: payload.name,
        categoryName: payload.categoryName,
        categoryId: payload.categoryId,
        basePriceCop: Number(payload.basePriceCop || 0),
        qty: Math.max(1, Number(payload.qty || 1))
      });
    }

    saveCart(items);
    renderCart();
  };

  const sendCartToWhatsapp = () => {
    const items = getCart();
    if (items.length === 0) {
      alert(t('msg_empty_cart'));
      return;
    }

    const selectedCountry = countrySelector.value;
    const phone = getWhatsappPhone(selectedCountry);
    if (!phone) {
      alert(t('msg_no_whatsapp'));
      return;
    }

    let total = 0;
    const lines = items.map((item, index) => {
      const qty = Math.max(1, Number(item.qty || 1));
      const translatedProduct = productTranslations[getLang()] && productTranslations[getLang()][Number(item.productId)]
        ? productTranslations[getLang()][Number(item.productId)]
        : null;
      const translatedCategory = categoryTranslations[getLang()] && categoryTranslations[getLang()][Number(item.categoryId || 0)]
        ? categoryTranslations[getLang()][Number(item.categoryId || 0)]
        : null;
      const itemName = translatedProduct ? translatedProduct.name : item.name;
      const itemCategoryName = translatedCategory ? translatedCategory.name : (item.categoryName || t('uncategorized'));
      const unitAmount = convertFromCop(item.basePriceCop, selectedCountry);
      const lineTotal = unitAmount * qty;
      total += lineTotal;

      const unitLabel = formatWithSuffix(unitAmount, selectedCountry);
      const lineLabel = formatWithSuffix(lineTotal, selectedCountry);
      return `${index + 1}. ${itemName} (${itemCategoryName})\n   ${t('msg_qty')}: ${qty}\n   ${t('msg_unit')}: ${unitLabel}\n   ${t('msg_subtotal')}: ${lineLabel}`;
    });

    const totalLabel = formatWithSuffix(total, selectedCountry);
    const countryLabel = countrySelector.options[countrySelector.selectedIndex]?.text || selectedCountry;
    const message = `${t('msg_order_header')}\n\n${t('msg_country')}: ${countryLabel}\n\n${lines.join('\n\n')}\n\n${t('msg_total')}: ${totalLabel}`;
    const whatsappUrl = `https://wa.me/${phone}?text=${encodeURIComponent(message)}`;
    window.open(whatsappUrl, '_blank', 'noopener');
  };

  const applyCountry = (countryCode) => {
    const selectedCountry = countryConfig[countryCode] ? countryCode : 'CO';

    countrySelector.value = selectedCountry;

    priceTags.forEach((priceTag) => {
      const label = buildPriceLabel(priceTag, selectedCountry);
      priceTag.textContent = label;
    });

    filterCatalogByCountry(selectedCountry);
    applyTranslations();
    applyCategoryTranslations();
    applyProductTranslations();
    updateCountryWhatsappLinks(selectedCountry);
    updatePaymentMethodsByCountry(selectedCountry);
    renderCart();

    try {
      localStorage.setItem(STORAGE_COUNTRY_KEY, selectedCountry);
    } catch (error) {
      // localStorage can be unavailable in private or restricted browser contexts.
    }
  };

  let initialCountry = countrySelector.value || 'CO';
  try {
    const storedCountry = localStorage.getItem(STORAGE_COUNTRY_KEY);
    if (storedCountry && countryConfig[storedCountry]) {
      initialCountry = storedCountry;
    }
  } catch (error) {
    // Keep selector default when storage access is restricted.
  }

  applyCountry(initialCountry);

  document.addEventListener('click', (event) => {
    const target = event.target;
    if (!(target instanceof HTMLElement)) {
      return;
    }

    if (!target.classList.contains('js-qty-increase') && !target.classList.contains('js-qty-decrease')) {
      return;
    }

    const stepper = target.closest('.qty-stepper');
    const input = stepper ? stepper.querySelector('.js-qty-input') : null;
    if (!(input instanceof HTMLInputElement)) {
      return;
    }

    const currentValue = Math.max(1, Number(input.value || 1));
    const nextValue = target.classList.contains('js-qty-increase') ? currentValue + 1 : Math.max(1, currentValue - 1);
    input.value = String(nextValue);
  });

  addButtons.forEach((button) => {
    button.addEventListener('click', () => {
      const card = button.closest('.product-card');
      const qtyInput = card ? card.querySelector('.js-qty-input') : null;
      const qty = Math.max(1, Number(qtyInput?.value || 1));

      addToCart({
        productId: button.dataset.productId,
        name: button.dataset.productName || '',
        categoryName: button.dataset.categoryName || '',
        categoryId: button.closest('.product-card')?.querySelector('[data-cat-id][data-cat-field="name"]')?.dataset.catId || '',
        basePriceCop: button.dataset.basePriceCop || 0,
        qty
      });

      if (qtyInput) {
        qtyInput.value = '1';
      }

      openCartDrawer();
    });
  });

  if (openCartBtn) {
    openCartBtn.addEventListener('click', (event) => {
      event.preventDefault();
      openCartDrawer();
    });
  }

  if (closeCartBtn) {
    closeCartBtn.addEventListener('click', () => {
      closeCartDrawer();
    });
  }

  if (cartOverlay) {
    cartOverlay.addEventListener('click', () => {
      closeCartDrawer();
    });
  }

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      closeCartDrawer();
    }
  });

  if (cartItemsBody) {
    cartItemsBody.addEventListener('change', (event) => {
      const target = event.target;
      if (!(target instanceof HTMLInputElement) || !target.classList.contains('cart-qty-input')) {
        return;
      }

      const qty = Math.max(1, Number(target.value || 1));
      target.value = String(qty);
      setCartItemQty(target.dataset.productId || '', qty);
    });

    cartItemsBody.addEventListener('click', (event) => {
      const target = event.target;
      if (!(target instanceof HTMLElement) || !target.classList.contains('cart-remove-btn')) {
        return;
      }
      removeCartItem(target.dataset.productId || '');
    });
  }

  if (sendCartWhatsappBtn) {
    sendCartWhatsappBtn.addEventListener('click', sendCartToWhatsapp);
  }

  if (clearCartBtn) {
    clearCartBtn.addEventListener('click', () => {
      saveCart([]);
      renderCart();
    });
  }

  countrySelector.addEventListener('change', () => {
    applyCountry(countrySelector.value);
  });
});
