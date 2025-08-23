/**
 * Taste Profile JavaScript
 */

class NaniMadeTasteProfile {
    constructor() {
        this.selectedFlavor = null;
        this.spiceLevel = 25; // Default 25%
    }
    
    init() {
        this.bindEvents();
        this.initFlavorWheel();
        this.initSpiceMeter();
    }
    
    bindEvents() {
        // Flavor wheel interactions
        document.addEventListener('click', (e) => {
            if (e.target.closest('.nanimade-flavor-segment')) {
                this.handleFlavorSelection(e.target.closest('.nanimade-flavor-segment'));
            }
        });
        
        // Pairing item interactions
        document.addEventListener('click', (e) => {
            if (e.target.closest('.nanimade-pairing-item')) {
                this.handlePairingSelection(e.target.closest('.nanimade-pairing-item'));
            }
        });
        
        // Heat quiz
        document.addEventListener('click', (e) => {
            if (e.target.closest('#nanimadeHeatQuiz')) {
                e.preventDefault();
                this.startHeatQuiz();
            }
        });
    }
    
    initFlavorWheel() {
        const wheel = document.getElementById('nanimadeFlavorWheel');
        if (!wheel) return;
        
        // Add rotation interaction
        let isRotating = false;
        let startAngle = 0;
        let currentRotation = 0;
        
        wheel.addEventListener('mousedown', (e) => {
            isRotating = true;
            startAngle = this.getAngle(e, wheel);
        });
        
        document.addEventListener('mousemove', (e) => {
            if (!isRotating) return;
            
            const currentAngle = this.getAngle(e, wheel);
            const deltaAngle = currentAngle - startAngle;
            currentRotation += deltaAngle;
            
            wheel.style.transform = `rotate(${currentRotation}deg)`;
            startAngle = currentAngle;
        });
        
        document.addEventListener('mouseup', () => {
            isRotating = false;
        });
    }
    
    initSpiceMeter() {
        const meter = document.getElementById('nanimadeSpiceMeter');
        const handle = document.getElementById('nanimadeMeterHandle');
        const fill = document.getElementById('nanimadeMeterFill');
        
        if (!meter || !handle || !fill) return;
        
        let isDragging = false;
        
        handle.addEventListener('mousedown', (e) => {
            isDragging = true;
            e.preventDefault();
        });
        
        document.addEventListener('mousemove', (e) => {
            if (!isDragging) return;
            
            const rect = meter.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const percentage = Math.max(0, Math.min(100, (x / rect.width) * 100));
            
            this.updateSpiceLevel(percentage);
        });
        
        document.addEventListener('mouseup', () => {
            isDragging = false;
        });
    }
    
    getAngle(event, element) {
        const rect = element.getBoundingClientRect();
        const centerX = rect.left + rect.width / 2;
        const centerY = rect.top + rect.height / 2;
        
        return Math.atan2(event.clientY - centerY, event.clientX - centerX) * 180 / Math.PI;
    }
    
    handleFlavorSelection(segment) {
        // Remove active class from all segments
        document.querySelectorAll('.nanimade-flavor-segment').forEach(el => {
            el.classList.remove('active');
        });
        
        // Add active class to selected
        segment.classList.add('active');
        this.selectedFlavor = segment.dataset.flavor;
        
        // Update center display
        const selectedFlavorElement = document.getElementById('nanimadeSelectedFlavor');
        if (selectedFlavorElement) {
            const flavorName = selectedFlavorElement.querySelector('.flavor-name');
            if (flavorName) {
                flavorName.textContent = this.selectedFlavor.charAt(0).toUpperCase() + this.selectedFlavor.slice(1);
            }
        }
        
        // Update recommendations based on flavor
        this.updateRecommendations();
    }
    
    handlePairingSelection(item) {
        // Add selection feedback
        item.classList.add('selected');
        setTimeout(() => item.classList.remove('selected'), 300);
        
        // Show pairing details
        const pairingType = item.dataset.pairing;
        this.showPairingDetails(pairingType);
    }
    
    updateSpiceLevel(percentage) {
        this.spiceLevel = percentage;
        
        const handle = document.getElementById('nanimadeMeterHandle');
        const fill = document.getElementById('nanimadeMeterFill');
        
        if (handle) {
            handle.style.left = percentage + '%';
        }
        
        if (fill) {
            fill.style.width = percentage + '%';
        }
        
        // Update intensity display
        const selectedFlavorElement = document.getElementById('nanimadeSelectedFlavor');
        if (selectedFlavorElement) {
            const intensityElement = selectedFlavorElement.querySelector('.flavor-intensity');
            if (intensityElement) {
                intensityElement.textContent = Math.round(percentage) + '%';
            }
        }
    }
    
    updateRecommendations() {
        // Update pairing recommendations based on selected flavor
        const pairingItems = document.querySelectorAll('.nanimade-pairing-item');
        
        pairingItems.forEach(item => {
            const pairing = item.dataset.pairing;
            const compatibility = this.getFlavorCompatibility(this.selectedFlavor, pairing);
            
            const stars = item.querySelector('.stars');
            if (stars) {
                stars.textContent = '⭐'.repeat(compatibility);
            }
        });
    }
    
    getFlavorCompatibility(flavor, pairing) {
        const compatibility = {
            'tangy': { 'rice': 5, 'roti': 4, 'curd': 5, 'dal': 4 },
            'spicy': { 'rice': 5, 'roti': 5, 'curd': 5, 'dal': 5 },
            'sweet': { 'rice': 4, 'roti': 5, 'curd': 3, 'dal': 4 },
            'salty': { 'rice': 5, 'roti': 4, 'curd': 4, 'dal': 5 },
            'sour': { 'rice': 4, 'roti': 3, 'curd': 5, 'dal': 3 },
            'aromatic': { 'rice': 5, 'roti': 5, 'curd': 4, 'dal': 5 }
        };
        
        return compatibility[flavor]?.[pairing] || 3;
    }
    
    showPairingDetails(pairingType) {
        const details = {
            'rice': 'Perfect with steamed basmati rice for a complete meal',
            'roti': 'Enhances the flavor of fresh wheat rotis',
            'curd': 'Balances the spice with cooling yogurt',
            'dal': 'Complements lentil dishes beautifully'
        };
        
        if (window.nanimadeSuite) {
            window.nanimadeSuite.showNotification(details[pairingType] || 'Great pairing choice!', 'info');
        }
    }
    
    startHeatQuiz() {
        const quiz = document.createElement('div');
        quiz.className = 'nanimade-heat-quiz-modal';
        quiz.innerHTML = `
            <div class="quiz-content">
                <h3>Heat Tolerance Quiz</h3>
                <div class="quiz-question">
                    <p>How do you usually handle spicy food?</p>
                    <div class="quiz-options">
                        <button class="quiz-option" data-level="0">I avoid spicy food</button>
                        <button class="quiz-option" data-level="25">I like mild spice</button>
                        <button class="quiz-option" data-level="50">I enjoy medium spice</button>
                        <button class="quiz-option" data-level="75">I love hot food</button>
                        <button class="quiz-option" data-level="100">Bring on the fire!</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(quiz);
        setTimeout(() => quiz.classList.add('show'), 100);
        
        // Handle quiz answers
        quiz.addEventListener('click', (e) => {
            if (e.target.matches('.quiz-option')) {
                const level = parseInt(e.target.dataset.level);
                this.updateSpiceLevel(level);
                
                quiz.classList.remove('show');
                setTimeout(() => quiz.remove(), 300);
                
                if (window.nanimadeSuite) {
                    window.nanimadeSuite.showNotification('Spice level updated based on your preference!', 'success');
                }
            }
        });
    }
}

// Make it globally available
window.NaniMadeTasteProfile = NaniMadeTasteProfile;

// Add quiz modal styles
const quizStyles = `
    .nanimade-heat-quiz-modal {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.8);
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease-out;
    }
    
    .nanimade-heat-quiz-modal.show {
        opacity: 1;
        visibility: visible;
    }
    
    .quiz-content {
        background: white;
        padding: 32px;
        border-radius: 16px;
        max-width: 400px;
        width: 90%;
        text-align: center;
    }
    
    .quiz-content h3 {
        margin: 0 0 24px;
        color: #333;
    }
    
    .quiz-options {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    
    .quiz-option {
        padding: 12px 20px;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        background: white;
        cursor: pointer;
        transition: all 0.2s ease-out;
        font-weight: 600;
    }
    
    .quiz-option:hover {
        border-color: #ff6b35;
        background: rgba(255, 107, 53, 0.1);
        transform: translateY(-1px);
    }
`;

const quizStyleSheet = document.createElement('style');
quizStyleSheet.textContent = quizStyles;
document.head.appendChild(quizStyleSheet);