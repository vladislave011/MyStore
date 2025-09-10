// DOM Elements
const cartModal = document.getElementById('cartModal');
const floatingCart = document.getElementById('floatingCart');
const loginBtn = document.getElementById('loginBtn');
const startShoppingBtn = document.getElementById('startShoppingBtn');
const closeModalButtons = document.querySelectorAll('.close-modal');
const productDetailModal = document.getElementById('productDetailModal');
const categoryButtons = document.querySelectorAll('.category-btn');
const productCards = document.querySelectorAll('.product-card');
const cartCountElement = document.querySelector('.cart-count');

// Текущий выбранный товар для детального просмотра
let currentProduct = null;

// Toast notification system
function showToast(message, type = 'success', productName = '') {
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;

    const successIcon = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>`;
    const errorIcon = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>`;

    toast.innerHTML = `
        <div class="toast-icon">
            ${type === 'success' ? successIcon : errorIcon}
        </div>
        <div class="toast-content">
            <div class="toast-title">${type === 'success' ? 'Успешно!' : 'Ошибка!'}</div>
            <div class="toast-message">${message}</div>
            ${productName ? `<div class="toast-product">${productName}</div>` : ''}
        </div>
        <button class="toast-close">&times;</button>
        <div class="toast-progress">
            <div class="toast-progress-bar"></div>
        </div>
    `;

    container.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('show');
        toast.classList.add('toast-item-added');
    }, 100);

    const closeBtn = toast.querySelector('.toast-close');
    closeBtn.addEventListener('click', () => {
        closeToast(toast);
    });

    const autoClose = setTimeout(() => {
        closeToast(toast);
    }, 5000);

    toast.addEventListener('mouseenter', () => {
        clearTimeout(autoClose);
        toast.querySelector('.toast-progress-bar').style.animationPlayState = 'paused';
    });

    toast.addEventListener('mouseleave', () => {
        toast.querySelector('.toast-progress-bar').style.animationPlayState = 'running';
        setTimeout(() => {
            closeToast(toast);
        }, 2000);
    });

    function closeToast(toastElement) {
        toastElement.classList.remove('show');
        toastElement.classList.add('hiding');
        setTimeout(() => {
            if (toastElement.parentNode) {
                toastElement.parentNode.removeChild(toastElement);
            }
        }, 500);
    }
}

// Add to Cart with Animation
function addToCart(productId, productCard = null) {
    let size = 36; // Размер по умолчанию

    // Определяем, откуда вызвана функция: с карточки или из модального окна
    if (productCard) {
        // Вызов с карточки товара
        const sizeSelect = productCard.querySelector('.size-select');
        if (sizeSelect) {
            size = sizeSelect.value;
        }
    } else {
        // Вызов из модального окна деталей товара
        const sizeRadio = document.querySelector('input[name="productSize"]:checked');
        if (sizeRadio) {
            size = sizeRadio.value;
        }
    }

    const addButton = productCard ? productCard.querySelector('.add-to-cart-btn') : document.getElementById('detailProductAddBtn');
    const productImage = productCard ? productCard.querySelector('.product-image') : document.getElementById('detailProductImage');

    // Блокируем кнопку на время запроса
    if (addButton) {
        addButton.disabled = true;
        addButton.innerHTML = '<span>Добавляем...</span>';
    }

    // Создаем летающее изображение
    if (productImage) {
        const flyingImage = productImage.cloneNode();
        flyingImage.classList.add('flying-image');
        const imgRect = productImage.getBoundingClientRect();
        const cartRect = floatingCart.getBoundingClientRect();
        // Начальная позиция
        flyingImage.style.position = 'fixed';
        flyingImage.style.width = `${imgRect.width}px`;
        flyingImage.style.height = `${imgRect.height}px`;
        flyingImage.style.left = `${imgRect.left}px`;
        flyingImage.style.top = `${imgRect.top}px`;
        flyingImage.style.zIndex = '10000';
        flyingImage.style.transition = 'all 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
        flyingImage.style.borderRadius = '10px';
        flyingImage.style.boxShadow = '0 10px 30px rgba(0,0,0,0.3)';
        document.body.appendChild(flyingImage);
        setTimeout(() => {
            // Конечная позиция (корзина)
            flyingImage.style.width = '40px';
            flyingImage.style.height = '40px';
            flyingImage.style.left = `${cartRect.left + cartRect.width/2 - 20}px`;
            flyingImage.style.top = `${cartRect.top + cartRect.height/2 - 20}px`;
            flyingImage.style.opacity = '0.7';
            flyingImage.style.transform = 'scale(0.5) rotate(360deg)';
        }, 50);
        setTimeout(() => {
            if (flyingImage.parentNode) {
                document.body.removeChild(flyingImage);
            }
        }, 800);
    }

    // Отправляем AJAX запрос с ID товара и размером
    fetch('api/add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}&size=${size}`
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Обновляем счетчик корзины
                cartCountElement.textContent = data.cart_count;
                // Анимация корзины
                floatingCart.classList.add('bounce');
                setTimeout(() => {
                    floatingCart.classList.remove('bounce');
                }, 1000);
                // Показываем уведомление
                showToast(data.message, 'success', data.product_name);
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            showToast('Ошибка при добавлении в корзину', 'error');
            console.error('Error:', error);
        })
        .finally(() => {
            // Восстанавливаем кнопку
            if (addButton) {
                addButton.disabled = false;
                addButton.innerHTML = '<span>В корзину</span>';
            }
        });
}

// Open Product Detail Modal
function openProductDetail(productData) {
    currentProduct = productData;

    document.getElementById('detailProductImage').src = 'assets/images/' + productData.image;
    document.getElementById('detailProductName').textContent = productData.name;
    document.getElementById('detailProductDescription').textContent = productData.description || 'Стильные и удобные кроссовки для повседневной носки.';
    document.getElementById('detailProductPrice').textContent = new Intl.NumberFormat('ru-RU').format(productData.price) + '₸';
    document.getElementById('detailProductAddBtn').setAttribute('data-product-id', productData.id);

    openModal(productDetailModal);
}

// Modal Functions
function openModal(modal) {
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeModal(modal) {
    modal.classList.remove('show');
    document.body.style.overflow = 'auto';
}




// Анимации при скролле
function initAnimations() {
    const observerOptions = {
        threshold: 0.1,
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate');
            }
        });
    }, observerOptions);

    document.querySelectorAll('.slide-in').forEach(el => {
        observer.observe(el);
    });

    // Анимация героя
    setTimeout(() => {
        document.querySelector('.hero-title')?.classList.add('animate');
    }, 300);

    setTimeout(() => {
        document.querySelector('.hero-subtitle')?.classList.add('animate');
    }, 600);

    setTimeout(() => {
        document.querySelector('.hero-btn')?.classList.add('animate');
    }, 900);
}

// Инициализация обработчиков событий
document.addEventListener('DOMContentLoaded', function() {
    // Floating Cart с проверкой авторизации
    floatingCart.addEventListener('click', () => {
        if (!isLoggedIn) {
            showToast('Сначала войдите в аккаунт, чтобы управлять корзиной!', 'error');
            return;
        }
        openModal(cartModal);
    });

    // Обработчики для кнопок добавления в корзину с проверкой авторизации
    document.querySelectorAll('.add-to-cart-btn').forEach(button => {
        button.addEventListener('click', function() {
            if (!isLoggedIn) {
                showToast('Сначала войдите в аккаунт, чтобы добавлять товары в корзину!', 'error');
                return;
            }
            const productId = this.getAttribute('data-product-id');
            const productCard = this.closest('.product-card');
            addToCart(productId, productCard);
        });
    });

    // Обработчики для кнопок подробнее
    document.querySelectorAll('.view-details-btn').forEach(button => {
        button.addEventListener('click', function() {
            const productData = JSON.parse(this.getAttribute('data-product'));
            openProductDetail(productData);
        });
    });

    // Кнопка добавления из модального окна товара с проверкой авторизации
    document.getElementById('detailProductAddBtn').addEventListener('click', function() {
        if (!isLoggedIn) {
            showToast('Сначала войдите в аккаунт, чтобы добавлять товары в корзину!', 'error');
            return;
        }
        const productId = this.getAttribute('data-product-id');
        addToCart(productId);
    });

    // Фильтрация по категориям
    categoryButtons.forEach(button => {
        button.addEventListener('click', function() {
            categoryButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');

            const category = this.getAttribute('data-category');
            productCards.forEach(card => {
                if (category === 'all' || card.dataset.category === category) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    // Модальные окна
    startShoppingBtn.addEventListener('click', () => {
        document.querySelector('#products').scrollIntoView({ behavior: 'smooth' });
    });

    closeModalButtons.forEach(button => {
        button.addEventListener('click', () => closeModal(button.closest('.modal')));
    });

    window.addEventListener('click', (e) => {
        if (e.target.classList.contains('modal')) {
            closeModal(e.target);
        }
    });

    // Кнопка входа
    if (loginBtn) {
        loginBtn.addEventListener('click', function() {
            window.location.href = 'login.php';
        });
    }

    // Инициализация анимаций
    initAnimations();
});