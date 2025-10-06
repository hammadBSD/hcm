<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:header container class="bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <a href="{{ route('dashboard') }}" class="ms-2 me-5 flex items-center space-x-2 rtl:space-x-reverse lg:ms-0" wire:navigate>
                <x-app-logo />
            </a>
            
            <!-- <flux:brand href="{{ route('dashboard') }}" class="max-lg:hidden dark:hidden" wire:navigate /> -->
            <!-- <flux:brand href="{{ route('dashboard') }}" class="max-lg:hidden! hidden dark:flex" wire:navigate /> -->
            
            <flux:navbar class="-mb-px max-lg:hidden overflow-x-auto">
                <div class="flex items-center space-x-1 min-w-0">
                    <flux:navbar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:navbar.item>
                    <flux:navbar.item icon="users" :href="route('employees.index')" :current="request()->routeIs('employees.*')" wire:navigate>
                        {{ __('Employees') }}
                    </flux:navbar.item>
                    <flux:navbar.item icon="clock" :href="route('attendance.index')" :current="request()->routeIs('attendance.*')" wire:navigate>
                        {{ __('Attendance') }}
                    </flux:navbar.item>
                    <flux:navbar.item icon="currency-dollar" :href="route('payroll.index')" :current="request()->routeIs('payroll.*')" wire:navigate>
                        {{ __('Payroll') }}
                    </flux:navbar.item>
                    <flux:navbar.item icon="calendar" :href="route('leaves.index')" :current="request()->routeIs('leaves.*')" wire:navigate>
                        {{ __('Leaves') }}
                    </flux:navbar.item>
                    <flux:separator vertical variant="subtle" class="my-2"/>
                    <flux:dropdown>
                        <flux:navbar.item icon:trailing="chevron-down">{{ __('More') }}</flux:navbar.item>
                        <flux:navmenu>
                            <flux:navmenu.item href="#" :current="request()->routeIs('performance.*')">
                                <flux:icon name="chart-bar" class="w-4 h-4" />
                                {{ __('Performance') }}
                            </flux:navmenu.item>
                            <flux:navmenu.item href="#" :current="request()->routeIs('reports.*')">
                                <flux:icon name="document-text" class="w-4 h-4" />
                                {{ __('Reports') }}
                            </flux:navmenu.item>
                            <flux:navmenu.separator />
                            <flux:navmenu.item href="#">Departments</flux:navmenu.item>
                            <flux:navmenu.item href="#">Positions</flux:navmenu.item>
                            <flux:navmenu.item href="#">Benefits</flux:navmenu.item>
                        </flux:navmenu>
                    </flux:dropdown>
                </div>
            </flux:navbar>
            
            <flux:spacer />
            
            <flux:navbar class="me-4">
                <flux:navbar.item icon="magnifying-glass" href="#" label="Search" />
                <flux:navbar.item class="max-lg:hidden" icon="bell" href="#" label="Notifications" />
                <flux:navbar.item class="max-lg:hidden" icon="cog-6-tooth" :href="route('system-management.index')" label="Settings" wire:navigate />
            </flux:navbar>

            <!-- Desktop User Menu -->
            <flux:dropdown position="top" align="start">
                <flux:profile avatar="" :initials="auth()->user()->initials()" />
                <flux:menu>
                    <flux:menu.radio.group>
                        <flux:menu.radio checked>{{ auth()->user()->name }}</flux:menu.radio>
                        <flux:menu.radio>{{ auth()->user()->roles->first()->name ?? 'No Role' }}</flux:menu.radio>
                    </flux:menu.radio.group>
                    <flux:menu.separator />
                    <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    <flux:menu.item href="#" icon="user">{{ __('Profile') }}</flux:menu.item>
                    <flux:menu.separator />
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle">{{ __('Log Out') }}</flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        <!-- Mobile Menu -->
        <flux:sidebar sticky collapsible="mobile" class="lg:hidden bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700">
            <flux:sidebar.header>
                <!-- <flux:sidebar.brand
                    href="{{ route('dashboard') }}"
                    name="HCM System"
                    wire:navigate
                /> -->
                <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
            </flux:sidebar.header>
            <flux:sidebar.nav>
                <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                    {{ __('Dashboard') }}
                </flux:sidebar.item>
                <flux:sidebar.item icon="users" :href="route('employees.index')" :current="request()->routeIs('employees.*')" wire:navigate>
                    {{ __('Employees') }}
                </flux:sidebar.item>
                <flux:sidebar.item icon="clock" :href="route('attendance.index')" :current="request()->routeIs('attendance.*')" wire:navigate>
                    {{ __('Attendance') }}
                </flux:sidebar.item>
                <flux:sidebar.item icon="currency-dollar" :href="route('payroll.index')" :current="request()->routeIs('payroll.*')" wire:navigate>
                    {{ __('Payroll') }}
                </flux:sidebar.item>
                <flux:sidebar.item icon="calendar" :href="route('leaves.index')" :current="request()->routeIs('leaves.*')" wire:navigate>
                    {{ __('Leaves') }}
                </flux:sidebar.item>
                <flux:sidebar.item icon="chart-bar" href="#" :current="request()->routeIs('performance.*')">
                    {{ __('Performance') }}
                </flux:sidebar.item>
                <flux:sidebar.item icon="document-text" href="#" :current="request()->routeIs('reports.*')">
                    {{ __('Reports') }}
                </flux:sidebar.item>
                <flux:sidebar.group expandable heading="Management" class="grid">
                    <flux:sidebar.item href="#">Departments</flux:sidebar.item>
                    <flux:sidebar.item href="#">Positions</flux:sidebar.item>
                    <flux:sidebar.item href="#">Benefits</flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>
            <flux:sidebar.spacer />
            <flux:sidebar.nav>
                <flux:sidebar.item icon="cog-6-tooth" :href="route('settings.profile')" wire:navigate>{{ __('Settings') }}</flux:sidebar.item>
                <flux:sidebar.item icon="bell" href="#">{{ __('Notifications') }}</flux:sidebar.item>
            </flux:sidebar.nav>
        </flux:sidebar>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
