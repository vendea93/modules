/*
 * FlowQuest Office Theme JavaScript
 * Compatible with Perfex CRM 3.4.0+
 */

// Document ready function
document.addEventListener('DOMContentLoaded', function() {
    // Initialize theme
    FlowQuestTheme.init();
});

// FlowQuest Theme object
var FlowQuestTheme = {
    // Initialize theme
    init: function() {
        this.applyColorScheme();
        this.initializeComponents();
        this.bindEvents();
    },
    
    // Apply color scheme based on system preference or user setting
    applyColorScheme: function() {
        // Check for user preference in localStorage
        const userPref = localStorage.getItem('fq-theme');
        if (userPref) {
            document.documentElement.setAttribute('data-theme', userPref);
            return;
        }
        
        // Check system preference
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.documentElement.setAttribute('data-theme', 'dark');
        } else {
            document.documentElement.setAttribute('data-theme', 'light');
        }
    },
    
    // Initialize theme components
    initializeComponents: function() {
        // Initialize tooltips
        this.initTooltips();
        
        // Initialize charts
        this.initCharts();
        
        // Initialize form enhancements
        this.initFormEnhancements();
    },
    
    // Initialize tooltips
    initTooltips: function() {
        // Find all elements with data-toggle="tooltip"
        const tooltipElements = document.querySelectorAll('[data-toggle="tooltip"]');
        
        tooltipElements.forEach(function(element) {
            // Create tooltip
            const tooltip = new bootstrap.Tooltip(element);
        });
    },
    
    // Initialize charts
    initCharts: function() {
        // Find all chart containers
        const chartContainers = document.querySelectorAll('.fq-chart');
        
        chartContainers.forEach(function(container) {
            // Initialize chart based on data attributes
            const chartType = container.getAttribute('data-chart-type');
            const chartData = JSON.parse(container.getAttribute('data-chart-data'));
            
            // Create chart (this is a simplified example)
            if (chartType === 'line') {
                FlowQuestTheme.createLineChart(container, chartData);
            } else if (chartType === 'bar') {
                FlowQuestTheme.createBarChart(container, chartData);
            } else if (chartType === 'pie') {
                FlowQuestTheme.createPieChart(container, chartData);
            }
        });
    },
    
    // Create line chart
    createLineChart: function(container, data) {
        // This is a placeholder for actual chart implementation
        // In a real implementation, you would use Chart.js or similar library
        console.log('Creating line chart:', data);
    },
    
    // Create bar chart
    createBarChart: function(container, data) {
        // This is a placeholder for actual chart implementation
        console.log('Creating bar chart:', data);
    },
    
    // Create pie chart
    createPieChart: function(container, data) {
        // This is a placeholder for actual chart implementation
        console.log('Creating pie chart:', data);
    },
    
    // Initialize form enhancements
    initFormEnhancements: function() {
        // Initialize custom selects
        this.initCustomSelects();
        
        // Initialize switches
        this.initSwitches();
    },
    
    // Initialize custom selects
    initCustomSelects: function() {
        // Find all custom select elements
        const customSelects = document.querySelectorAll('.fq-select');
        
        customSelects.forEach(function(select) {
            // Enhance select with custom styling
            select.classList.add('form-control');
        });
    },
    
    // Initialize switches
    initSwitches: function() {
        // Find all switch elements
        const switches = document.querySelectorAll('.fq-switch input');
        
        switches.forEach(function(switchElement) {
            // Add event listener for change
            switchElement.addEventListener('change', function() {
                FlowQuestTheme.handleSwitchChange(this);
            });
        });
    },
    
    // Handle switch change
    handleSwitchChange: function(element) {
        // Get the switch value
        const isChecked = element.checked;
        const switchName = element.name;
        
        // Trigger custom event
        const event = new CustomEvent('fqSwitchChange', {
            detail: {
                name: switchName,
                value: isChecked
            }
        });
        
        document.dispatchEvent(event);
    },
    
    // Bind events
    bindEvents: function() {
        // Theme toggle button
        const themeToggle = document.getElementById('theme-toggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', function(e) {
                e.preventDefault();
                FlowQuestTheme.toggleTheme();
            });
        }
        
        // Initialize sidebar toggle for mobile
        this.initMobileSidebar();
    },
    
    // Toggle theme between light and dark
    toggleTheme: function() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('fq-theme', newTheme);
        
        // Dispatch theme change event
        const event = new CustomEvent('fqThemeChange', {
            detail: {
                theme: newTheme
            }
        });
        
        document.dispatchEvent(event);
    },
    
    // Initialize mobile sidebar
    initMobileSidebar: function() {
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        
        if (sidebarToggle && sidebar && overlay) {
            sidebarToggle.addEventListener('click', function(e) {
                e.preventDefault();
                sidebar.classList.toggle('active');
                overlay.classList.toggle('active');
            });
            
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            });
        }
    },
    
    // Show notification
    showNotification: function(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show`;
        notification.role = 'alert';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        // Add to notification container
        const container = document.getElementById('notification-container');
        if (container) {
            container.appendChild(notification);
            
            // Auto dismiss after 5 seconds
            setTimeout(function() {
                notification.classList.remove('show');
                setTimeout(function() {
                    notification.remove();
                }, 150);
            }, 5000);
        }
    },
    
    // Confirm action
    confirm: function(message, callback) {
        if (confirm(message)) {
            callback();
        }
    }
};

// Add theme toggle functionality to window object for global access
window.FlowQuestTheme = FlowQuestTheme;

// Theme change event listener
document.addEventListener('fqThemeChange', function(e) {
    console.log('Theme changed to:', e.detail.theme);
    // You can add additional logic here when theme changes
});

// Switch change event listener
document.addEventListener('fqSwitchChange', function(e) {
    console.log('Switch changed:', e.detail.name, 'to', e.detail.value);
    // You can add additional logic here when switch changes
});