<div>
    <!-- Month Selector Dropdown -->
    <flux:select wire:model.live="selectedMonth" class="w-48">
        @foreach($availableMonths as $month)
            <option value="{{ $month['value'] }}">{{ $month['label'] }}</option>
        @endforeach
    </flux:select>
    
    <!-- Hidden data container for JavaScript -->
    <div 
        id="monthly-attendance-data" 
        data-stats='@json($dailyStats)'
        style="display: none;"
        wire:key="monthly-attendance-data-{{ $selectedMonth }}"
    ></div>
    
    <script>
        // Function to update monthly attendance chart
        function updateMonthlyAttendanceChart() {
            const monthlyAttendanceCanvas = document.getElementById('monthlyAttendanceChart');
            if (!monthlyAttendanceCanvas) {
                return;
            }
            
            // Get data from hidden element
            const dataElement = document.getElementById('monthly-attendance-data');
            const dailyData = dataElement ? JSON.parse(dataElement.getAttribute('data-stats') || '[]') : [];
            
            // Destroy existing chart if it exists
            if (window.charts && window.charts.monthlyAttendance) {
                window.charts.monthlyAttendance.destroy();
                window.charts.monthlyAttendance = null;
            }
            
            // Prepare data for stacked bar chart
            const labels = dailyData.map(item => item.label || '');
            const presentData = dailyData.map(item => item.present || 0);
            const absentData = dailyData.map(item => item.absent || 0);
            const offDaysData = dailyData.map(item => item.off_days || 0);
            
            const monthlyAttendanceCtx = monthlyAttendanceCanvas.getContext('2d');
            window.charts.monthlyAttendance = new Chart(monthlyAttendanceCtx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Present',
                            data: presentData,
                            backgroundColor: '#10b981', // Green
                            borderRadius: 4,
                            borderSkipped: false,
                        },
                        {
                            label: 'Absent',
                            data: absentData,
                            backgroundColor: '#ef4444', // Red
                            borderRadius: 4,
                            borderSkipped: false,
                        },
                        {
                            label: 'Off Days',
                            data: offDaysData,
                            backgroundColor: '#6b7280', // Gray
                            borderRadius: 4,
                            borderSkipped: false,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 10,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                afterLabel: function(context) {
                                    const total = dailyData[context.dataIndex]?.total || 0;
                                    if (context.datasetIndex === 0) {
                                        return `Total: ${total} day${total !== 1 ? 's' : ''}`;
                                    }
                                    return '';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            stacked: true,
                            grid: {
                                display: false
                            },
                            ticks: {
                                maxRotation: 45,
                                minRotation: 45,
                                font: {
                                    size: 10
                                }
                            }
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                callback: function(value) {
                                    return value;
                                }
                            },
                            grid: {
                                color: document.documentElement.classList.contains('dark') ? '#27272a' : '#f4f4f5'
                            }
                        }
                    }
                }
            });
        }
        
        // Expose function globally
        window.updateMonthlyAttendanceChart = updateMonthlyAttendanceChart;
        
        // Function to initialize chart when ready
        function initMonthlyAttendanceChart() {
            const canvas = document.getElementById('monthlyAttendanceChart');
            const dataElement = document.getElementById('monthly-attendance-data');
            
            if (canvas && dataElement) {
                try {
                    const dailyData = JSON.parse(dataElement.getAttribute('data-stats') || '[]');
                    // Only initialize if we have data
                    if (dailyData.length > 0) {
                        updateMonthlyAttendanceChart();
                        return true;
                    }
                } catch (e) {
                    console.error('Error parsing monthly attendance data:', e);
                }
            }
            return false;
        }
        
        // Expose init function globally for dashboard script
        window.initMonthlyAttendanceChart = initMonthlyAttendanceChart;
        
        // Initialize when this script loads (component rendered)
        (function() {
            function tryInit() {
                if (!initMonthlyAttendanceChart()) {
                    // Retry after delay
                    setTimeout(tryInit, 100);
                }
            }
            // Start trying after a small delay to ensure DOM is ready
            setTimeout(tryInit, 100);
        })();
        
        // Listen for Livewire initialization
        if (typeof Livewire !== 'undefined') {
            document.addEventListener('livewire:init', () => {
                setTimeout(() => {
                    initMonthlyAttendanceChart();
                }, 400);
            });
            
            // Listen for Livewire component loaded (when data is ready)
            Livewire.hook('morph.updated', ({ el, component }) => {
                // Check if this is our component
                const ourComponent = el.querySelector('#monthly-attendance-data');
                if (ourComponent) {
                    setTimeout(() => {
                        initMonthlyAttendanceChart();
                    }, 100);
                }
            });
        }
        
        // Listen for Livewire updates (when month changes)
        document.addEventListener('livewire:init', () => {
            Livewire.on('monthly-attendance-updated', () => {
                setTimeout(() => {
                    updateMonthlyAttendanceChart();
                }, 100);
            });
        });
        
        // Watch for data attribute changes
        const dataElement = document.getElementById('monthly-attendance-data');
        if (dataElement) {
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'data-stats') {
                        setTimeout(() => {
                            updateMonthlyAttendanceChart();
                        }, 50);
                    }
                });
            });
            
            observer.observe(dataElement, {
                attributes: true,
                attributeFilter: ['data-stats']
            });
        }
        
        // Also listen for Livewire DOM updates as fallback
        document.addEventListener('livewire:update', () => {
            setTimeout(() => {
                updateMonthlyAttendanceChart();
            }, 100);
        });
    </script>
</div>
