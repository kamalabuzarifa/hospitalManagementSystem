<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex space-x-reverse">
                    {{-- Admin Navigation --}}
                    @if(auth()->user()->role && auth()->user()->role->name === 'admin')
                        @permission('view_dashboard')
                            <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                                {{ __('لوحة التحكم') }}
                            </x-nav-link>
                        @endpermission
                        @anyPermission('patients.view', 'manage_patients')
                            <x-nav-link :href="route('admin.patients.index')" :active="request()->routeIs('admin.patients.*')">
                                {{ __('المرضى') }}
                            </x-nav-link>
                        @endanyPermission
                        @anyPermission('doctors.view', 'manage_doctors')
                            <x-nav-link :href="route('admin.doctors.index')" :active="request()->routeIs('admin.doctors.*')">
                                {{ __('الأطباء') }}
                            </x-nav-link>
                        @endanyPermission
                        @anyPermission('appointments.view', 'manage_appointments')
                            <x-nav-link :href="route('admin.appointments.index')" :active="request()->routeIs('admin.appointments.*')">
                                {{ __('المواعيد') }}
                            </x-nav-link>
                        @endanyPermission
                        @anyPermission('pharmacy.view', 'manage_pharmacy')
                            <x-nav-link :href="route('admin.pharmacy.index')" :active="request()->routeIs('admin.pharmacy.*')">
                                {{ __('الصيدلية') }}
                            </x-nav-link>
                        @endanyPermission
                    @endif

                    {{-- Doctor Navigation --}}
                    @if(auth()->user()->role && auth()->user()->role->name === 'doctor')
                        @permission('view_dashboard')
                            <x-nav-link :href="route('doctor.dashboard')" :active="request()->routeIs('doctor.dashboard')">
                                {{ __('لوحة التحكم') }}
                            </x-nav-link>
                        @endpermission
                        @anyPermission('appointments.view', 'manage_appointments')
                            <x-nav-link :href="route('doctor.appointments.index')" :active="request()->routeIs('doctor.appointments.*')">
                                {{ __('مواعيدي') }}
                            </x-nav-link>
                        @endanyPermission
                    @endif

                    {{-- Receptionist Navigation --}}
                    @if(auth()->user()->role && auth()->user()->role->name === 'receptionist')
                        @permission('view_dashboard')
                            <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                                {{ __('لوحة التحكم') }}
                            </x-nav-link>
                        @endpermission
                        @anyPermission('patients.view', 'manage_patients')
                            <x-nav-link :href="route('reception.patients.index')" :active="request()->routeIs('reception.patients.*')">
                                {{ __('المرضى') }}
                            </x-nav-link>
                        @endanyPermission
                        @anyPermission('appointments.view', 'manage_appointments')
                            <x-nav-link :href="route('reception.appointments.index')" :active="request()->routeIs('reception.appointments.*')">
                                {{ __('المواعيد') }}
                            </x-nav-link>
                        @endanyPermission
                    @endif

                    {{-- Pharmacy Navigation --}}
                    @if(auth()->user()->role && auth()->user()->role->name === 'pharmacy')
                        @permission('view_dashboard')
                            <x-nav-link :href="route('pharmacy.dashboard')" :active="request()->routeIs('pharmacy.dashboard')">
                                {{ __('لوحة التحكم') }}
                            </x-nav-link>
                        @endpermission
                        @anyPermission('inventory.view', 'manage_inventory')
                            <x-nav-link :href="route('pharmacy.inventory.index')" :active="request()->routeIs('pharmacy.inventory.*')">
                                {{ __('المخزون') }}
                            </x-nav-link>
                        @endanyPermission
                        @anyPermission('prescriptions.view', 'manage_prescriptions')
                            <x-nav-link :href="route('pharmacy.prescriptions.index')" :active="request()->routeIs('pharmacy.prescriptions.*')">
                                {{ __('الوصفات') }}
                            </x-nav-link>
                        @endanyPermission
                    @endif

                    {{-- Patient Navigation --}}
                    @if(auth()->user()->role && auth()->user()->role->name === 'patient')
                        <x-nav-link :href="route('patient.dashboard')" :active="request()->routeIs('patient.dashboard')">
                            {{ __('لوحة التحكم') }}
                        </x-nav-link>
                        <x-nav-link :href="route('patient.appointments.index')" :active="request()->routeIs('patient.appointments.*')">
                            {{ __('مواعيدي') }}
                        </x-nav-link>
                        <x-nav-link :href="route('patient.prescriptions.index')" :active="request()->routeIs('patient.prescriptions.*')">
                            {{ __('وصفاتي الطبية') }}
                        </x-nav-link>
                    @endif

                    {{-- Default Dashboard for users without roles --}}
                    @if(!auth()->user()->role)
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            {{-- Admin Responsive Navigation --}}
            @if(auth()->user()->role && auth()->user()->role->name === 'admin')
                @permission('view_dashboard')
                    <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                        {{ __('لوحة التحكم') }}
                    </x-responsive-nav-link>
                @endpermission
                @anyPermission('patients.view', 'manage_patients')
                    <x-responsive-nav-link :href="route('admin.patients.index')" :active="request()->routeIs('admin.patients.*')">
                        {{ __('المرضى') }}
                    </x-responsive-nav-link>
                @endanyPermission
                @anyPermission('doctors.view', 'manage_doctors')
                    <x-responsive-nav-link :href="route('admin.doctors.index')" :active="request()->routeIs('admin.doctors.*')">
                        {{ __('الأطباء') }}
                    </x-responsive-nav-link>
                @endanyPermission
                @anyPermission('appointments.view', 'manage_appointments')
                    <x-responsive-nav-link :href="route('admin.appointments.index')" :active="request()->routeIs('admin.appointments.*')">
                        {{ __('المواعيد') }}
                    </x-responsive-nav-link>
                @endanyPermission
                @anyPermission('pharmacy.view', 'manage_pharmacy')
                    <x-responsive-nav-link :href="route('admin.pharmacy.index')" :active="request()->routeIs('admin.pharmacy.*')">
                        {{ __('الصيدلية') }}
                    </x-responsive-nav-link>
                @endanyPermission
            @endif

            {{-- Doctor Responsive Navigation --}}
            @if(auth()->user()->role && auth()->user()->role->name === 'doctor')
                @permission('view_dashboard')
                    <x-responsive-nav-link :href="route('doctor.dashboard')" :active="request()->routeIs('doctor.dashboard')">
                        {{ __('لوحة التحكم') }}
                    </x-responsive-nav-link>
                @endpermission
                @anyPermission('appointments.view', 'manage_appointments')
                    <x-responsive-nav-link :href="route('doctor.appointments.index')" :active="request()->routeIs('doctor.appointments.*')">
                        {{ __('مواعيدي') }}
                    </x-responsive-nav-link>
                @endanyPermission
            @endif

            {{-- Receptionist Responsive Navigation --}}
            @if(auth()->user()->role && auth()->user()->role->name === 'receptionist')
                @permission('view_dashboard')
                    <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('لوحة التحكم') }}
                    </x-responsive-nav-link>
                @endpermission
                @anyPermission('patients.view', 'manage_patients')
                    <x-responsive-nav-link :href="route('reception.patients.index')" :active="request()->routeIs('reception.patients.*')">
                        {{ __('المرضى') }}
                    </x-responsive-nav-link>
                @endanyPermission
                @anyPermission('appointments.view', 'manage_appointments')
                    <x-responsive-nav-link :href="route('reception.appointments.index')" :active="request()->routeIs('reception.appointments.*')">
                        {{ __('المواعيد') }}
                    </x-responsive-nav-link>
                @endanyPermission
            @endif

            {{-- Pharmacy Responsive Navigation --}}
            @if(auth()->user()->role && auth()->user()->role->name === 'pharmacy')
                @permission('view_dashboard')
                    <x-responsive-nav-link :href="route('pharmacy.dashboard')" :active="request()->routeIs('pharmacy.dashboard')">
                        {{ __('لوحة التحكم') }}
                    </x-responsive-nav-link>
                @endpermission
                @anyPermission('inventory.view', 'manage_inventory')
                    <x-responsive-nav-link :href="route('pharmacy.inventory.index')" :active="request()->routeIs('pharmacy.inventory.*')">
                        {{ __('المخزون') }}
                    </x-responsive-nav-link>
                @endanyPermission
                @anyPermission('prescriptions.view', 'manage_prescriptions')
                    <x-responsive-nav-link :href="route('pharmacy.prescriptions.index')" :active="request()->routeIs('pharmacy.prescriptions.*')">
                        {{ __('الوصفات') }}
                    </x-responsive-nav-link>
                @endanyPermission
            @endif

            {{-- Patient Responsive Navigation --}}
            @if(auth()->user()->role && auth()->user()->role->name === 'patient')
                <x-responsive-nav-link :href="route('patient.dashboard')" :active="request()->routeIs('patient.dashboard')">
                    {{ __('لوحة التحكم') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('patient.appointments.index')" :active="request()->routeIs('patient.appointments.*')">
                    {{ __('مواعيدي') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('patient.prescriptions.index')" :active="request()->routeIs('patient.prescriptions.*')">
                    {{ __('وصفاتي الطبية') }}
                </x-responsive-nav-link>
            @endif

            {{-- Default for users without roles --}}
            @if(!auth()->user()->role)
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
