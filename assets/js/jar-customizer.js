/**
 * Jar Customizer JavaScript
 */

class NaniMadeJarCustomizer {
    constructor() {
        this.basePrice = 120;
        this.extrasPrice = 0;
        this.selectedSpice = 'mild';
        this.selectedSize = 'medium';
        this.selectedExtras = [];
    }
    
    init() {
        this.bindEvents();
        this.updateDisplay();
    }
    
    bindEvents() {
        // Spice level selection
        document.addEventListener('click', (e) => {
            if (e.target.closest('.nanimade-spice-level')) {
                this.handleSpiceSelection(e.target.closest('.nanimade-spice-level'));
            }
        });
        
        // Jar size selection
        document.addEventListener('click', (e) => {
            if (e.target.closest('.nanimade-jar-size')) {
                this.handleSizeSelection(e.target.closest('.nanimade-jar-size'));
            }
        });
        
        // Ingredient selection
        document.addEventListener('change', (e) => {
            if (e.target.matches('.nanimade-ingredient-option input[type="checkbox"]')) {
                this.handleIngredientSelection(e.target);
            }
        });
        
        // Custom message
        document.addEventListener('input', (e) => {
            if (e.target.matches('.nanimade-custom-message')) {
                this.handleCustomMessage(e.target);
            }
        });
        
        // Add to cart
        document.addEventListener('click', (e) => {
            if (e.target.closest('#nanimadeAddCustomJar')) {
                e.preventDefault();
                this.addCustomJarToCart();
            }
        });
    }
    
    handleSpiceSelection(element) {
        // Remove active class from all spice levels
        document.querySelectorAll('.nanimade-spice-level').forEach(el => {
            el.classList.remove('active');
        });
        
        // Add active class to selected
        element.classList.add('active');
        this.selectedSpice = element.dataset.spice;
        
        this.updateJarVisual();
        this.updatePrice();
    }
    
    handleSizeSelection(element) {
        // Remove active class from all sizes
        document.querySelectorAll('.nanimade-jar-size').forEach(el => {
            el.classList.remove('active');
        });
        
        // Add active class to selected
        element.classList.add('active');
        this.selectedSize = element.dataset.size;
        
        this.updateJarVisual();
        this.updatePrice();
    }
    
    handleIngredientSelection(checkbox) {
        const ingredientName = checkbox.name;
        const ingredientPrice = parseInt(checkbox.closest('.nanimade-ingredient-option').querySelector('.nanimade-ingredient-price').textContent.replace(/[^\d]/g, ''));
        
        if (checkbox.checked) {
            this.selectedExtras.push({
                name: ingredientName,
                price: ingredientPrice
            });
        } else {
            this.selectedExtras = this.selectedExtras.filter(extra => extra.name !== ingredientName);
        }
        
        this.updatePrice();
    }
    
    handleCustomMessage(input) {
        const customLabel = document.getElementById('nanimadeLabelCustom');
        if (customLabel) {
            customLabel.textContent = input.value || 'Special Gift';
        }
    }
    
    updateJarVisual() {
        const jarContents = document.getElementById('nanimadeJarContents');
        const jarLiquid = document.getElementById('nanimadeJarLiquid');
        
        if (!jarContents || !jarLiquid) return;
        
        // Update jar contents based on spice level
        const spiceColors = {
            'mild': 'linear-gradient(180deg, #90EE90 0%, #32CD32 100%)',
            'medium': 'linear-gradient(180deg, #FFD700 0%, #FFA500 100%)',
            'hot': 'linear-gradient(180deg, #FF6347 0%, #DC143C 100%)',
            'extra-hot': 'linear-gradient(180deg, #FF4500 0%, #8B0000 100%)',
            'nuclear': 'linear-gradient(180deg, #8A2BE2 0%, #4B0082 100%)'
        };
        
        jarContents.style.background = spiceColors[this.selectedSpice] || spiceColors['mild'];
        
        // Update jar size visual
        const jarDisplay = document.getElementById('nanimadeJarDisplay');
        if (jarDisplay) {
            jarDisplay.className = `nanimade-jar-display size-${this.selectedSize}`;
        }
    }
    
    updatePrice() {
        this.extrasPrice = this.selectedExtras.reduce((total, extra) => total + extra.price, 0);
        const totalPrice = this.basePrice + this.extrasPrice;
        
        // Update price display
        const extrasElement = document.getElementById('nanimadeExtrasPrice');
        const totalElement = document.getElementById('nanimadeTotalPrice');
        
        if (extrasElement) {
            extrasElement.textContent = '₹' + this.extrasPrice;
        }
        
        if (totalElement) {
            totalElement.textContent = '₹' + totalPrice;
        }
    }
    
    updateDisplay() {
        this.updateJarVisual();
        this.updatePrice();
    }
    
    addCustomJarToCart() {
        const customData = {
            spice_level: this.selectedSpice,
            jar_size: this.selectedSize,
            extra_ingredients: this.selectedExtras,
            custom_message: document.querySelector('.nanimade-custom-message')?.value || '',
            total_price: this.basePrice + this.extrasPrice
        };
        
        // Show loading state
        const button = document.getElementById('nanimadeAddCustomJar');
        if (button) {
            button.classList.add('loading');
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding to Cart...';
        }
        
        // Simulate API call (replace with actual AJAX call)
        setTimeout(() => {
            if (button) {
                button.classList.remove('loading');
                button.innerHTML = '<i class="fas fa-check"></i> Added to Cart!';
                
                setTimeout(() => {
                    button.innerHTML = '<i class="fas fa-plus-circle"></i> Add Custom Jar to Cart';
                }, 2000);
            }
            
            // Show success notification
            if (window.nanimadeSuite) {
                window.nanimadeSuite.showNotification('Custom jar added to cart!', 'success');
            }
        }, 1000);
    }
}

// Make it globally available
window.NaniMadeJarCustomizer = NaniMadeJarCustomizer;