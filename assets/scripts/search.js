/**
 * Enhanced search functionality
 */

class MovieSearch {
    constructor() {
        this.searchInput = document.querySelector('input[name="q"]');
        this.searchForm = document.querySelector('form[action*="search"]');
        this.resultsContainer = document.querySelector('.search-results');
        this.debounceTimer = null;
        
        if (this.searchInput) {
            this.init();
        }
    }
    
    init() {
        // Add search suggestions (basic implementation)
        this.addSearchSuggestions();
        
        // Add search history
        this.addSearchHistory();
        
        // Enhanced keyboard navigation
        this.addKeyboardNavigation();
        
        console.log('Search functionality initialized');
    }
    
    addSearchSuggestions() {
        const suggestionsContainer = document.createElement('div');
        suggestionsContainer.className = 'search-suggestions list-group position-absolute w-100';
        suggestionsContainer.style.cssText = 'z-index: 1000; top: 100%; display: none;';
        
        const inputGroup = this.searchInput.closest('.input-group') || this.searchInput.parentNode;
        inputGroup.style.position = 'relative';
        inputGroup.appendChild(suggestionsContainer);
        
        // Popular search terms (could be dynamic from backend)
        const popularSearches = [
            'Marvel', 'Disney', 'Action', 'Comedy', 'Horror',
            'Robert Downey Jr', 'Christopher Nolan', 'Spielberg'
        ];
        
        this.searchInput.addEventListener('focus', () => {
            if (!this.searchInput.value) {
                this.showSuggestions(popularSearches, 'Recherches populaires', suggestionsContainer);
            }
        });
        
        this.searchInput.addEventListener('input', (e) => {
            clearTimeout(this.debounceTimer);
            const query = e.target.value.trim();
            
            if (query.length < 2) {
                suggestionsContainer.style.display = 'none';
                return;
            }
            
            this.debounceTimer = setTimeout(() => {
                const filteredSuggestions = popularSearches.filter(term => 
                    term.toLowerCase().includes(query.toLowerCase())
                );
                
                if (filteredSuggestions.length > 0) {
                    this.showSuggestions(filteredSuggestions, 'Suggestions', suggestionsContainer);
                } else {
                    suggestionsContainer.style.display = 'none';
                }
            }, 300);
        });
        
        // Hide suggestions when clicking outside
        document.addEventListener('click', (e) => {
            if (!inputGroup.contains(e.target)) {
                suggestionsContainer.style.display = 'none';
            }
        });
    }
    
    showSuggestions(suggestions, title, container) {
        container.innerHTML = `
            <div class="list-group-item bg-light border-0 py-1 px-2">
                <small class="text-muted fw-bold">${title}</small>
            </div>
        `;
        
        suggestions.slice(0, 5).forEach(suggestion => {
            const item = document.createElement('button');
            item.className = 'list-group-item list-group-item-action border-0 py-2';
            item.textContent = suggestion;
            item.type = 'button';
            
            item.addEventListener('click', () => {
                this.searchInput.value = suggestion;
                container.style.display = 'none';
                this.searchForm.submit();
            });
            
            container.appendChild(item);
        });
        
        container.style.display = 'block';
    }
    
    addSearchHistory() {
        const history = JSON.parse(localStorage.getItem('movieSearchHistory') || '[]');
        
        this.searchForm.addEventListener('submit', (e) => {
            const query = this.searchInput.value.trim();
            if (query && !history.includes(query)) {
                history.unshift(query);
                if (history.length > 10) history.pop(); // Keep only last 10
                localStorage.setItem('movieSearchHistory', JSON.stringify(history));
            }
        });
        
        // Show history on focus if no value
        this.searchInput.addEventListener('focus', () => {
            if (!this.searchInput.value && history.length > 0) {
                const suggestionsContainer = this.searchInput.parentNode.querySelector('.search-suggestions');
                if (suggestionsContainer && history.length > 0) {
                    setTimeout(() => {
                        if (!this.searchInput.value) {
                            this.showSuggestions(history, 'Recherches récentes', suggestionsContainer);
                        }
                    }, 100);
                }
            }
        });
    }
    
    addKeyboardNavigation() {
        this.searchInput.addEventListener('keydown', (e) => {
            const suggestionsContainer = this.searchInput.parentNode.querySelector('.search-suggestions');
            const suggestions = suggestionsContainer?.querySelectorAll('.list-group-item-action');
            
            if (!suggestions || suggestions.length === 0) return;
            
            let activeIndex = Array.from(suggestions).findIndex(item => 
                item.classList.contains('active')
            );
            
            switch (e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    if (activeIndex < suggestions.length - 1) {
                        if (activeIndex >= 0) suggestions[activeIndex].classList.remove('active');
                        suggestions[++activeIndex].classList.add('active');
                    }
                    break;
                    
                case 'ArrowUp':
                    e.preventDefault();
                    if (activeIndex > 0) {
                        suggestions[activeIndex].classList.remove('active');
                        suggestions[--activeIndex].classList.add('active');
                    } else if (activeIndex === 0) {
                        suggestions[0].classList.remove('active');
                        activeIndex = -1;
                    }
                    break;
                    
                case 'Enter':
                    if (activeIndex >= 0) {
                        e.preventDefault();
                        suggestions[activeIndex].click();
                    }
                    break;
                    
                case 'Escape':
                    suggestionsContainer.style.display = 'none';
                    break;
            }
        });
    }
}

// Initialize search when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new MovieSearch();
});