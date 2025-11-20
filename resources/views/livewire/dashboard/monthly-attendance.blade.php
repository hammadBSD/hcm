<div>
    <!-- Filters -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end mb-3">
        @if($canViewOtherUsers)
            <div
                class="text-zinc-400 dark:text-zinc-500 hidden sm:flex items-center justify-center"
                wire:loading.flex
                wire:target="selectedUserId, selectedMonth"
            >
                <flux:icon name="arrow-path" class="w-5 h-5 animate-spin" />
            </div>
            <flux:select
                wire:model.live="selectedUserId"
                placeholder="{{ __('Select User') }}"
                class="w-64"
                wire:loading.attr="disabled"
                wire:target="selectedUserId, selectedMonth"
            >
                @if(!$selectedUserId)
                    <option value="">{{ auth()->user()->name ?? __('Current User') }}</option>
                @endif
                @foreach($availableUsers as $user)
                    <option value="{{ $user['id'] }}">{{ $user['name'] }}</option>
                @endforeach
            </flux:select>
        @endif
        <flux:select
            wire:model.live="selectedMonth"
            class="w-48"
            wire:loading.attr="disabled"
            wire:target="selectedUserId, selectedMonth"
        >
            @foreach($availableMonths as $month)
                <option value="{{ $month['value'] }}">{{ $month['label'] }}</option>
            @endforeach
        </flux:select>
    </div>

    <!-- Hidden data container for JavaScript -->
    <div 
        id="monthly-attendance-data" 
        data-stats='@json($dailyStats)'
        style="display: none;"
        wire:key="monthly-attendance-data-{{ $selectedMonth }}-{{ $selectedUserId ?? 'self' }}"
    ></div>
    
    <script>
        // Function to update monthly attendance chart
        function updateMonthlyAttendanceChart() {
            if (typeof Chart === 'undefined') {
                setTimeout(updateMonthlyAttendanceChart, 200);
                return;
            }

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
            
            // Prepare data for bar chart
            const labels = dailyData.map(item => item.label || '');
            
            // Find max hours for Y-axis scaling (calculate before using it)
            const maxHours = Math.max(
                ...dailyData.map(item => item.hours || 0),
                8 // Minimum 8 hours scale
            );
            const yAxisMax = Math.ceil(maxHours / 2) * 2; // Round up to nearest even number
            
            // Create single dataset with hours as values and conditional colors
            const hoursData = dailyData.map(item => {
                if (item.status === 'off') {
                    return 0.5; // Small bar for off days
                } else if (item.status === 'absent') {
                    // For absent, show a full-height bar (use max hours or 8 hours)
                    return Math.max(maxHours, 8); // Full height bar for absent
                } else if (item.status === 'on_leave') {
                    // For on leave, show a full-height bar (use max hours or 8 hours)
                    return Math.max(maxHours, 8); // Full height bar for on leave
                } else {
                    return item.hours || 0; // Actual hours for present/late
                }
            });
            
            // Create color array based on status
            const backgroundColors = dailyData.map(item => {
                if (item.status === 'off') {
                    return '#6b7280'; // Gray
                } else if (item.status === 'absent') {
                    return '#ef4444'; // Red
                } else if (item.status === 'on_leave') {
                    return '#3b82f6'; // Blue for on leave
                } else if (item.has_incomplete_attendance) {
                    return '#f97316'; // Orange for incomplete attendance (missing check-in or check-out)
                } else if (item.status === 'present_late_early') {
                    return '#dc2626'; // Dark red for late & early (both issues)
                } else if (item.status === 'present_late') {
                    return '#eab308'; // Yellow for late only
                } else if (item.status === 'present_early') {
                    return '#f59e0b'; // Amber/Orange for early only
                } else {
                    return '#10b981'; // Green for on-time
                }
            });
            
            const monthlyAttendanceCtx = monthlyAttendanceCanvas.getContext('2d');
            window.charts.monthlyAttendance = new Chart(monthlyAttendanceCtx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Attendance',
                            data: hoursData,
                            backgroundColor: backgroundColors,
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
                            display: false, // Hide the legend
                        },
                        tooltip: {
                            callbacks: {
                                title: function(context) {
                                    const dataIndex = context[0].dataIndex;
                                    const dayData = dailyData[dataIndex];
                                    return dayData ? dayData.date : '';
                                },
                                label: function(context) {
                                    const dataIndex = context.dataIndex;
                                    const dayData = dailyData[dataIndex];
                                    const value = context.parsed.y;
                                    
                                    if (!dayData) return '';
                                    
                                    let tooltip = [];
                                    
                                    // Status label
                                    let statusLabel = 'Present';
                                    if (dayData.status === 'off') {
                                        statusLabel = 'Off Day';
                                    } else if (dayData.status === 'absent') {
                                        statusLabel = 'Absent';
                                    } else if (dayData.status === 'on_leave') {
                                        statusLabel = 'On Leave';
                                    } else if (dayData.status === 'present_late_early') {
                                        statusLabel = 'Late & Early';
                                    } else if (dayData.status === 'present_late') {
                                        statusLabel = 'Late';
                                    } else if (dayData.status === 'present_early') {
                                        statusLabel = 'Left Early';
                                    }
                                    
                                    tooltip.push(`Status: ${statusLabel}`);
                                    
                                    // Hours info
                                    if (dayData.status === 'off') {
                                        tooltip.push('Weekend/Off Day');
                                    } else if (dayData.status === 'absent') {
                                        tooltip.push('No attendance recorded');
                                    } else if (dayData.status === 'on_leave') {
                                        tooltip.push('On approved leave');
                                    } else {
                                        tooltip.push(`Check In: ${dayData.check_in || '--'}`);
                                        tooltip.push(`Check Out: ${dayData.check_out || '--'}`);
                                        tooltip.push(`Worked Hours: ${dayData.total_hours || 'N/A'}`);
                                        
                                        // Show warnings for late/early
                                        if (dayData.status === 'present_late_early') {
                                            tooltip.push('⚠️ Late arrival & Left early');
                                        } else if (dayData.status === 'present_late') {
                                            tooltip.push('⚠️ Late arrival');
                                        } else if (dayData.status === 'present_early') {
                                            tooltip.push('⚠️ Left early');
                                        }
                                    }
                                    
                                    return tooltip;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            stacked: false, // Not stacked
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
                            stacked: false, // Not stacked - each bar shows independently
                            beginAtZero: true,
                            max: yAxisMax,
                            ticks: {
                                stepSize: 2, // Show ticks every 2 hours
                                callback: function(value) {
                                    if (value === 0) return '0h';
                                    return value + 'h';
                                },
                                font: {
                                    size: 11
                                }
                            },
                            grid: {
                                color: document.documentElement.classList.contains('dark') ? '#27272a' : '#f4f4f5'
                            },
                            title: {
                                display: true,
                                text: 'Hours',
                                font: {
                                    size: 12
                                }
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
            if (typeof Chart === 'undefined') {
                setTimeout(initMonthlyAttendanceChart, 200);
                return false;
            }

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
        
        // Schedule initial rendering
        function scheduleMonthlyAttendanceInit(delay = 120) {
            setTimeout(() => {
                initMonthlyAttendanceChart();
            }, delay);
        }

        scheduleMonthlyAttendanceInit(150);
        
        if (!window.__monthlyAttendanceListenersBound) {
            window.__monthlyAttendanceListenersBound = true;

            if (typeof Livewire !== 'undefined') {
                document.addEventListener('livewire:init', () => {
                    setTimeout(() => {
                        initMonthlyAttendanceChart();
                    }, 400);
                });
                
                // Listen for Livewire component updates (when data is ready)
                Livewire.hook('morph.updated', ({ el }) => {
                    const ourComponent = el.querySelector && el.querySelector('#monthly-attendance-data');
                    if (ourComponent) {
                        setTimeout(() => {
                            initMonthlyAttendanceChart();
                        }, 100);
                    }
                });
            }
            
            // Listen for Livewire updates (when month changes)
            document.addEventListener('livewire:init', () => {
                if (typeof Livewire !== 'undefined') {
                    Livewire.on('monthly-attendance-updated', () => {
                        setTimeout(() => {
                            updateMonthlyAttendanceChart();
                        }, 100);
                    });
                }
            });
            
            // Fallback for Livewire DOM updates
            document.addEventListener('livewire:update', () => {
                setTimeout(() => {
                    updateMonthlyAttendanceChart();
                }, 100);
            });
        } else {
            // If listeners already bound, ensure chart refreshes with new data
            setTimeout(() => {
                updateMonthlyAttendanceChart();
            }, 150);
        }
        
        // Watch for data attribute changes
        var dataElement = document.getElementById('monthly-attendance-data');
        if (dataElement) {
            if (dataElement.__monthlyAttendanceObserver) {
                dataElement.__monthlyAttendanceObserver.disconnect();
            }

            var observer = new MutationObserver((mutations) => {
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

            dataElement.__monthlyAttendanceObserver = observer;
        }
    </script>
</div>
