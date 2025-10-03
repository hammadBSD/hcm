<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <!-- Welcome Section -->
        <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <flux:heading size="xl" class="text-zinc-900 dark:text-zinc-100">
                        Welcome, {{ auth()->user()->name ?? 'User' }}
                    </flux:heading>
                    <flux:subheading class="text-zinc-600 dark:text-zinc-400">
                        Here's what's happening in your organization today
                    </flux:subheading>
                </div>
                <div class="text-right">
                    <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ now()->format('l, F j, Y') }}</div>
                    <div class="text-lg font-semibold text-zinc-900 dark:text-zinc-100" id="currentTime">{{ now()->format('h:i A') }}</div>
                </div>
            </div>
        </div>

        <!-- Stats Cards Row -->
        <div class="grid gap-6 md:grid-cols-3">
            <!-- Attendance Status Card -->
            <livewire:dashboard.your-status-card />

            <!-- Total Present Today -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <flux:icon name="users" class="w-5 h-5 text-zinc-500 dark:text-zinc-400" />
                            <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Present Today</span>
                        </div>
                        <flux:heading size="lg" class="text-zinc-900 dark:text-zinc-100">
                            24/28
                        </flux:heading>
                        <div class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                            85.7% attendance rate
                        </div>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/20 rounded-full flex items-center justify-center">
                        <flux:icon name="user-group" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                    </div>
                </div>
            </div>

            <!-- Pending Leave Requests -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <flux:icon name="calendar-days" class="w-5 h-5 text-zinc-500 dark:text-zinc-400" />
                            <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Pending Requests</span>
                        </div>
                        <flux:heading size="lg" class="text-zinc-900 dark:text-zinc-100">
                            7
                        </flux:heading>
                        <div class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                            Leave requests awaiting approval
                        </div>
                    </div>
                    <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900/20 rounded-full flex items-center justify-center">
                        <flux:icon name="exclamation-triangle" class="w-6 h-6 text-amber-600 dark:text-amber-400" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid gap-6 grid-cols-1 lg:grid-cols-2">
            <!-- Attendance Trend Chart -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 md:p-6">
                <div class="flex items-center justify-between mb-4 md:mb-6">
                    <flux:heading size="lg" class="text-zinc-900 dark:text-zinc-100">
                        Attendance Trend
                    </flux:heading>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                        <span class="text-sm text-zinc-600 dark:text-zinc-400">This Week</span>
                    </div>
                </div>
                <div class="h-48 md:h-64 relative w-full">
                    <canvas id="attendanceChart"></canvas>
                </div>
            </div>

            <!-- Department Distribution -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 md:p-6">
                <div class="flex items-center justify-between mb-4 md:mb-6">
                    <flux:heading size="lg" class="text-zinc-900 dark:text-zinc-100">
                        Department Distribution
                    </flux:heading>
                    <flux:button variant="outline" size="sm" class="hidden sm:inline-flex">
                        View Details
                    </flux:button>
                </div>
                <div class="h-48 md:h-64 relative w-full max-w-full">
                    <canvas id="departmentChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Leave Types Chart -->
        <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 md:p-6">
            <flux:heading size="lg" class="text-zinc-900 dark:text-zinc-100 mb-4 md:mb-6">
                Leave Types Distribution
            </flux:heading>
            <div class="h-48 md:h-64 relative overflow-hidden">
                <canvas id="leaveTypesChart"></canvas>
            </div>
        </div>

        <!-- Bottom Section - Recent Activity & Quick Actions -->
        <div class="grid gap-6 lg:grid-cols-2">
            <!-- Recent Activity -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <flux:heading size="lg" class="text-zinc-900 dark:text-zinc-100">
                        Recent Activity
                    </flux:heading>
                    <flux:button variant="outline" size="sm">
                        View All
                    </flux:button>
                </div>
                <div class="space-y-3">
                    <flux:callout variant="success" icon="check-circle" inline>
                        <flux:callout.heading>John Doe checked in</flux:callout.heading>
                        <flux:callout.text>2 minutes ago</flux:callout.text>
                    </flux:callout>
                    
                    <flux:callout variant="secondary" icon="document-plus" inline>
                        <flux:callout.heading>Sarah Johnson requested leave</flux:callout.heading>
                        <flux:callout.text>15 minutes ago</flux:callout.text>
                    </flux:callout>
                    
                    <flux:callout variant="secondary" icon="user-plus" inline>
                        <flux:callout.heading>New employee onboarded</flux:callout.heading>
                        <flux:callout.text>1 hour ago</flux:callout.text>
                    </flux:callout>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                <flux:heading size="lg" class="text-zinc-900 dark:text-zinc-100 mb-4">
                    Quick Actions
                </flux:heading>
                <div class="grid grid-cols-2 gap-3">
                    <flux:button variant="outline" class="h-auto p-4 flex flex-col items-center gap-2">
                        <flux:icon name="clock" class="w-5 h-5" />
                        <span class="text-sm">Mark Attendance</span>
                    </flux:button>
                    <flux:button variant="outline" class="h-auto p-4 flex flex-col items-center gap-2">
                        <flux:icon name="calendar-days" class="w-5 h-5" />
                        <span class="text-sm">Request Leave</span>
                    </flux:button>
                    <flux:button variant="outline" class="h-auto p-4 flex flex-col items-center gap-2">
                        <flux:icon name="user-plus" class="w-5 h-5" />
                        <span class="text-sm">Add Employee</span>
                    </flux:button>
                    <flux:button variant="outline" class="h-auto p-4 flex flex-col items-center gap-2">
                        <flux:icon name="document-text" class="w-5 h-5" />
                        <span class="text-sm">Generate Report</span>
                    </flux:button>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Chart instances storage
        let charts = {
            attendance: null,
            department: null,
            leaveTypes: null
        };

        // Function to destroy existing charts
        function destroyCharts() {
            Object.values(charts).forEach(chart => {
                if (chart) {
                    chart.destroy();
                }
            });
            charts = {
                attendance: null,
                department: null,
                leaveTypes: null
            };
        }

        // Function to initialize all charts
        function initializeCharts() {
            // Check if Chart.js is loaded
            if (typeof Chart === 'undefined') {
                console.error('Chart.js not loaded');
                return;
            }

            // Chart.js configuration for dark mode support
            Chart.defaults.color = document.documentElement.classList.contains('dark') ? '#a1a1aa' : '#71717a';
            Chart.defaults.borderColor = document.documentElement.classList.contains('dark') ? '#27272a' : '#e4e4e7';

            // Attendance Trend Chart (Line Chart)
            const attendanceCanvas = document.getElementById('attendanceChart');
            if (!attendanceCanvas) {
                console.error('Attendance chart canvas not found');
                return;
            }
            const attendanceCtx = attendanceCanvas.getContext('2d');
            charts.attendance = new Chart(attendanceCtx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Attendance Rate (%)',
                    data: [85, 92, 78, 96, 88, 45, 52],
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#3b82f6',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        },
                        grid: {
                            color: document.documentElement.classList.contains('dark') ? '#27272a' : '#f4f4f5'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                elements: {
                    point: {
                        hoverRadius: 8
                    }
                }
            }
        });

            // Department Distribution Chart (Doughnut Chart)
            const departmentCanvas = document.getElementById('departmentChart');
            if (!departmentCanvas) {
                console.error('Department chart canvas not found');
                return;
            }
            const departmentCtx = departmentCanvas.getContext('2d');
            charts.department = new Chart(departmentCtx, {
            type: 'doughnut',
            data: {
                labels: ['HR', 'Engineering', 'Marketing', 'Sales', 'Finance', 'Operations'],
                datasets: [{
                    data: [15, 25, 12, 18, 10, 20],
                    backgroundColor: [
                        '#3b82f6',
                        '#10b981',
                        '#f59e0b',
                        '#ef4444',
                        '#8b5cf6',
                        '#06b6d4'
                    ],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        left: 10,
                        right: 10,
                        top: 10,
                        bottom: 10
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            boxWidth: 12,
                            boxHeight: 12,
                            font: {
                                size: 12
                            }
                        }
                    }
                },
                cutout: '60%'
            }
        });

            // Leave Types Distribution Chart (Bar Chart)
            const leaveTypesCanvas = document.getElementById('leaveTypesChart');
            if (!leaveTypesCanvas) {
                console.error('Leave types chart canvas not found');
                return;
            }
            const leaveTypesCtx = leaveTypesCanvas.getContext('2d');
            charts.leaveTypes = new Chart(leaveTypesCtx, {
            type: 'bar',
            data: {
                labels: ['Sick', 'Vacation', 'Personal', 'Emergency'],
                datasets: [{
                    label: 'Requests',
                    data: [8, 15, 5, 3],
                    backgroundColor: [
                        '#ef4444',
                        '#10b981',
                        '#f59e0b',
                        '#8b5cf6'
                    ],
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: document.documentElement.classList.contains('dark') ? '#27272a' : '#f4f4f5'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
        }

        // Initialize charts when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Wait a bit to ensure all elements are rendered
            setTimeout(() => {
                initializeCharts();
            }, 200);
        });

        // Re-initialize charts when returning to page (for SPA navigation)
        document.addEventListener('livewire:navigated', function() {
            setTimeout(() => {
                destroyCharts();
                initializeCharts();
            }, 300);
        });

        // Additional fallback for page load issues
        window.addEventListener('load', function() {
            setTimeout(() => {
                // Check if charts are missing and re-initialize
                const attendanceCanvas = document.getElementById('attendanceChart');
                const departmentCanvas = document.getElementById('departmentChart');
                const leaveTypesCanvas = document.getElementById('leaveTypesChart');
                
                if (attendanceCanvas && departmentCanvas && leaveTypesCanvas) {
                    const attendanceCtx = attendanceCanvas.getContext('2d');
                    const departmentCtx = departmentCanvas.getContext('2d');
                    const leaveTypesCtx = leaveTypesCanvas.getContext('2d');
                    
                    // Check if any canvas is empty (no chart data)
                    const attendanceEmpty = attendanceCtx.getImageData(0, 0, attendanceCanvas.width, attendanceCanvas.height).data.every(pixel => pixel === 0);
                    const departmentEmpty = departmentCtx.getImageData(0, 0, departmentCanvas.width, departmentCanvas.height).data.every(pixel => pixel === 0);
                    const leaveTypesEmpty = leaveTypesCtx.getImageData(0, 0, leaveTypesCanvas.width, leaveTypesCanvas.height).data.every(pixel => pixel === 0);
                    
                    if (attendanceEmpty || departmentEmpty || leaveTypesEmpty) {
                        console.log('Charts not rendered properly, re-initializing...');
                        destroyCharts();
                        initializeCharts();
                    }
                }
            }, 500);
        });

        // Fallback for page visibility changes
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                setTimeout(() => {
                    // Check if charts are blank and re-initialize if needed
                    const attendanceCanvas = document.getElementById('attendanceChart');
                    const departmentCanvas = document.getElementById('departmentChart');
                    const leaveTypesCanvas = document.getElementById('leaveTypesChart');
                    
                    if (attendanceCanvas && departmentCanvas && leaveTypesCanvas) {
                        const attendanceCtx = attendanceCanvas.getContext('2d');
                        const departmentCtx = departmentCanvas.getContext('2d');
                        const leaveTypesCtx = leaveTypesCanvas.getContext('2d');
                        
                        // Check if canvas is empty (no chart data)
                        if (attendanceCtx.getImageData(0, 0, attendanceCanvas.width, attendanceCanvas.height).data.every(pixel => pixel === 0)) {
                            destroyCharts();
                            initializeCharts();
                        }
                    }
                }, 100);
            }
        });

        // Real-time clock functionality
        function updateClock() {
            const now = new Date();
            const timeElement = document.getElementById('currentTime');
            if (timeElement) {
                timeElement.textContent = now.toLocaleTimeString('en-US', {
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                });
            }
        }

        // Update clock every second
        setInterval(updateClock, 1000);
        
        // Initialize clock on page load
        updateClock();

        // Final fallback - check charts after 2 seconds
        setTimeout(() => {
            const attendanceCanvas = document.getElementById('attendanceChart');
            const departmentCanvas = document.getElementById('departmentChart');
            const leaveTypesCanvas = document.getElementById('leaveTypesChart');
            
            if (attendanceCanvas && departmentCanvas && leaveTypesCanvas) {
                const attendanceCtx = attendanceCanvas.getContext('2d');
                const departmentCtx = departmentCanvas.getContext('2d');
                const leaveTypesCtx = leaveTypesCanvas.getContext('2d');
                
                // Check if any canvas is empty (no chart data)
                const attendanceEmpty = attendanceCtx.getImageData(0, 0, attendanceCanvas.width, attendanceCanvas.height).data.every(pixel => pixel === 0);
                const departmentEmpty = departmentCtx.getImageData(0, 0, departmentCanvas.width, departmentCanvas.height).data.every(pixel => pixel === 0);
                const leaveTypesEmpty = leaveTypesCtx.getImageData(0, 0, leaveTypesCanvas.width, leaveTypesCanvas.height).data.every(pixel => pixel === 0);
                
                if (attendanceEmpty || departmentEmpty || leaveTypesEmpty) {
                    console.log('Final fallback: Charts not rendered, re-initializing...');
                    destroyCharts();
                    initializeCharts();
                }
            }
        }, 2000);
    </script>
</x-layouts.app>

