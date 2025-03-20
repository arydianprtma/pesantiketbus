<div class="min-h-screen bg-gray-100">
    <!-- Main container with improved margin for sidebar -->
    <div class="ml-16 md:ml-20 transition-margin duration-300 ease-in-out" id="main-content">
        <!-- Your existing main content here -->
    </div>

    <!-- Fixed Sidebar - Improved styling -->
    <div class="fixed left-0 top-0 h-full z-50">
        <div class="sidebar min-h-screen w-16 md:w-20 hover:w-64 bg-gray-900 text-white transition-all duration-300 ease-in-out shadow-lg overflow-hidden group">
            <div class="flex h-screen flex-col">
                <!-- Logo Section - Fixed path -->
                <div class="w-full p-3 flex justify-center md:justify-start items-center h-16 border-b border-gray-800">
                    <img src="/Pesan-Tiket-Bus/assets/img/logo.png" class="w-8 h-8 object-contain" alt="Logo">
                    <span class="ml-3 font-medium text-lg hidden group-hover:block">Dashboard</span>
                </div>

                <!-- Navigation Section with Scroll - Improved styling -->
                <div class="flex-1 overflow-y-auto overflow-x-hidden py-6">
                    <ul class="space-y-1 px-2">
                        <li class="min-w-max">
                            <a href="/Pesan-Tiket-Bus/admin/dashboard.php" class="flex items-center space-x-4 px-4 py-3 text-gray-300 hover:bg-indigo-600 hover:text-white rounded-md transition-colors duration-200">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                                </svg>
                                <span class="hidden group-hover:block">Home</span>
                            </a>
                        </li>
                        
                        <!-- Unit Management - Improved dropdown -->
                        <li class="min-w-max">
                            <div>
                                <div class="dropdown-btn flex items-center justify-between space-x-4 px-4 py-3 text-gray-300 hover:bg-indigo-600 hover:text-white rounded-md cursor-pointer transition-colors duration-200">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l3-3m-3 3L9 8m-5 5h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293h3.172a1 1 0 00.707-.293l2.414-2.414a1 1 0 01.707-.293H20"/>
                                        </svg>
                                        <span class="ml-4 hidden group-hover:block">Unit</span>
                                    </div>
                                    <svg class="w-4 h-4 transition-transform dropdown-arrow hidden group-hover:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                                <div class="dropdown-content pl-6 py-0 max-h-0 opacity-0 invisible">
                                    <a href="/Pesan-Tiket-Bus/admin/unit/tambah.php" class="submenu-item flex items-center py-2 px-4 text-sm text-gray-300 hover:bg-indigo-600 hover:text-white rounded-md group transition-colors duration-200">
                                        <span class="mr-2 text-gray-500 group-hover:text-indigo-200">•</span>
                                        <span class="group-hover:text-white">Tambah Unit</span>
                                    </a>
                                    <a href="/Pesan-Tiket-Bus/admin/unit/list.php" class="submenu-item flex items-center py-2 px-4 text-sm text-gray-300 hover:bg-indigo-600 hover:text-white rounded-md group transition-colors duration-200">
                                        <span class="mr-2 text-gray-500 group-hover:text-indigo-200">•</span>
                                        <span class="group-hover:text-white">List Unit</span>
                                    </a>
                                </div>
                            </div>
                        </li>

                        <!-- Karyawan Management - Improved dropdown -->
                        <li class="min-w-max">
                            <div>
                                <div class="dropdown-btn flex items-center justify-between space-x-4 px-4 py-3 text-gray-300 hover:bg-indigo-600 hover:text-white rounded-md cursor-pointer transition-colors duration-200">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                        </svg>
                                        <span class="ml-4 hidden group-hover:block">Karyawan</span>
                                    </div>
                                    <svg class="w-4 h-4 transition-transform dropdown-arrow hidden group-hover:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                                <div class="dropdown-content pl-6 py-0 max-h-0 opacity-0 invisible">
                                    <a href="/Pesan-Tiket-Bus/admin/karyawan/tambah.php" class="submenu-item flex items-center py-2 px-4 text-sm text-gray-300 hover:bg-indigo-600 hover:text-white rounded-md group transition-colors duration-200">
                                        <span class="mr-2 text-gray-500 group-hover:text-indigo-200">•</span>
                                        <span class="group-hover:text-white">Tambah Karyawan</span>
                                    </a>
                                    <a href="/Pesan-Tiket-Bus/admin/karyawan/list.php" class="submenu-item flex items-center py-2 px-4 text-sm text-gray-300 hover:bg-indigo-600 hover:text-white rounded-md group transition-colors duration-200">
                                        <span class="mr-2 text-gray-500 group-hover:text-indigo-200">•</span>
                                        <span class="group-hover:text-white">List Karyawan</span>
                                    </a>
                                    <a href="/Pesan-Tiket-Bus/admin/karyawan/gaji.php" class="submenu-item flex items-center py-2 px-4 text-sm text-gray-300 hover:bg-indigo-600 hover:text-white rounded-md group transition-colors duration-200">
                                        <span class="mr-2 text-gray-500 group-hover:text-indigo-200">•</span>
                                        <span class="group-hover:text-white">Gaji Karyawan</span>
                                    </a>
                                </div>
                            </div>
                        </li>

                        <!-- Sopir Management -->
                        <li class="min-w-max">
                            <div>
                                <div class="dropdown-btn flex items-center justify-between space-x-4 px-4 py-3 text-gray-300 hover:bg-indigo-600 hover:text-white rounded-md cursor-pointer transition-colors duration-200">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path>
                                        </svg>
                                        <span class="ml-4 hidden group-hover:block">Sopir</span>
                                    </div>
                                    <svg class="w-4 h-4 transition-transform dropdown-arrow hidden group-hover:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                                <div class="dropdown-content pl-6 py-0 max-h-0 opacity-0 invisible">
                                    <a href="/Pesan-Tiket-Bus/admin/sopir/tambah.php" class="submenu-item flex items-center py-2 px-4 text-sm text-gray-300 hover:bg-indigo-600 hover:text-white rounded-md group transition-colors duration-200">
                                        <span class="mr-2 text-gray-500 group-hover:text-indigo-200">•</span>
                                        <span class="group-hover:text-white">Tambah Sopir</span>
                                    </a>
                                    <a href="/Pesan-Tiket-Bus/admin/sopir/list.php" class="submenu-item flex items-center py-2 px-4 text-sm text-gray-300 hover:bg-indigo-600 hover:text-white rounded-md group transition-colors duration-200">
                                        <span class="mr-2 text-gray-500 group-hover:text-indigo-200">•</span>
                                        <span class="group-hover:text-white">List Sopir</span>
                                    </a>
                                    <a href="/Pesan-Tiket-Bus/admin/sopir/gaji.php" class="submenu-item flex items-center py-2 px-4 text-sm text-gray-300 hover:bg-indigo-600 hover:text-white rounded-md group transition-colors duration-200">
                                        <span class="mr-2 text-gray-500 group-hover:text-indigo-200">•</span>
                                        <span class="group-hover:text-white">Gaji Sopir</span>
                                    </a>
                                </div>
                            </div>
                        </li>

                        <!-- Tiket Management -->
                        <li class="min-w-max">
                            <div>
                                <div class="dropdown-btn flex items-center justify-between space-x-4 px-4 py-3 text-gray-300 hover:bg-indigo-600 hover:text-white rounded-md cursor-pointer transition-colors duration-200">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                                        </svg>
                                        <span class="ml-4 hidden group-hover:block">Tiket</span>
                                    </div>
                                    <svg class="w-4 h-4 transition-transform dropdown-arrow hidden group-hover:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                                <div class="dropdown-content pl-6 py-0 max-h-0 opacity-0 invisible">
                                    <a href="/Pesan-Tiket-Bus/admin/tiket/online.php" class="submenu-item flex items-center py-2 px-4 text-sm text-gray-300 hover:bg-indigo-600 hover:text-white rounded-md group">
                                        <span class="mr-2 text-gray-500 group-hover:text-indigo-200">•</span>
                                        <span class="group-hover:text-white">Tiket Online</span>
                                    </a>
                                    <a href="/Pesan-Tiket-Bus/admin/route/tambah.php" class="submenu-item flex items-center py-2 px-4 text-sm text-gray-300 hover:bg-indigo-600 hover:text-white rounded-md group">
                                        <span class="mr-2 text-gray-500 group-hover:text-indigo-200">•</span>
                                        <span class="group-hover:text-white">Tambah Route</span>
                                    </a>
                                    <a href="/Pesan-Tiket-Bus/admin/tiket/route.php" class="submenu-item flex items-center py-2 px-4 text-sm text-gray-300 hover:bg-indigo-600 hover:text-white rounded-md group">
                                        <span class="mr-2 text-gray-500 group-hover:text-indigo-200">•</span>
                                        <span class="group-hover:text-white">Jadwal Route</span>
                                    </a>
                                    <a href="/Pesan-Tiket-Bus/admin/route/list.php" class="submenu-item flex items-center py-2 px-4 text-sm text-gray-300 hover:bg-indigo-600 hover:text-white rounded-md group">
                                        <span class="mr-2 text-gray-500 group-hover:text-indigo-200">•</span>
                                        <span class="group-hover:text-white">List Route</span>
                                    </a>
                                    <a href="/Pesan-Tiket-Bus/admin/tiket/list.php" class="submenu-item flex items-center py-2 px-4 text-sm text-gray-300 hover:bg-indigo-600 hover:text-white rounded-md group">
                                        <span class="mr-2 text-gray-500 group-hover:text-indigo-200">•</span>
                                        <span class="group-hover:text-white">List Tiket</span>
                                    </a>
                                </div>
                            </div>
                        </li>

                        <!-- Keuangan Management -->
                        <li class="min-w-max">
                            <div>
                                <div class="dropdown-btn flex items-center justify-between space-x-4 px-4 py-3 text-gray-300 hover:bg-indigo-600 hover:text-white rounded-md cursor-pointer transition-colors duration-200">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                        <span class="ml-4 hidden group-hover:block">Keuangan</span>
                                    </div>
                                    <svg class="w-4 h-4 transition-transform dropdown-arrow hidden group-hover:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                                <div class="dropdown-content pl-6 py-0 max-h-0 opacity-0 invisible">
                                    <a href="/Pesan-Tiket-Bus/admin/keuangan/pendapatan-bulanan.php" class="submenu-item flex items-center py-2 px-4 text-sm text-gray-300 hover:bg-indigo-600 hover:text-white rounded-md group">
                                        <span class="mr-2 text-gray-500 group-hover:text-indigo-200">•</span>
                                        <span class="group-hover:text-white">Pendapatan Bulanan</span>
                                    </a>
                                    <a href="/Pesan-Tiket-Bus/admin/keuangan/pendapatan-tahunan.php" class="submenu-item flex items-center py-2 px-4 text-sm text-gray-300 hover:bg-indigo-600 hover:text-white rounded-md group">
                                        <span class="mr-2 text-gray-500 group-hover:text-indigo-200">•</span>
                                        <span class="group-hover:text-white">Pendapatan Tahunan</span>
                                    </a>
                                    <a href="/Pesan-Tiket-Bus/admin/keuangan/laba-kotor.php" class="submenu-item flex items-center py-2 px-4 text-sm text-gray-300 hover:bg-indigo-600 hover:text-white rounded-md group">
                                        <span class="mr-2 text-gray-500 group-hover:text-indigo-200">•</span>
                                        <span class="group-hover:text-white">Laba Kotor</span>
                                    </a>
                                    <a href="/Pesan-Tiket-Bus/admin/keuangan/laba-bersih.php" class="submenu-item flex items-center py-2 px-4 text-sm text-gray-300 hover:bg-indigo-600 hover:text-white rounded-md group">
                                        <span class="mr-2 text-gray-500 group-hover:text-indigo-200">•</span>
                                        <span class="group-hover:text-white">Laba Bersih</span>
                                    </a>
                                    <a href="/Pesan-Tiket-Bus/admin/keuangan/pengeluaran.php" class="submenu-item flex items-center py-2 px-4 text-sm text-gray-300 hover:bg-indigo-600 hover:text-white rounded-md group">
                                        <span class="mr-2 text-gray-500 group-hover:text-indigo-200">•</span>
                                        <span class="group-hover:text-white">Pengeluaran</span>
                                    </a>
                                    <a href="/Pesan-Tiket-Bus/admin/keuangan/laporan.php" class="submenu-item flex items-center py-2 px-4 text-sm text-gray-300 hover:bg-indigo-600 hover:text-white rounded-md group">
                                        <span class="mr-2 text-gray-500 group-hover:text-indigo-200">•</span>
                                        <span class="group-hover:text-white">Laporan Keuangan</span>
                                    </a>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Logout Section - Improved styling -->
                <div class="px-3 py-3 border-t border-gray-800 mt-auto">
                    <a href="/Pesan-Tiket-Bus/admin/logout.php" class="flex items-center space-x-3 px-4 py-3 text-gray-300 bg-red-600 hover:bg-red-700 hover:text-white rounded-md transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span class="hidden group-hover:block">Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Base Styles - Improved */
        .sidebar {
            overflow: hidden;
            will-change: width;
        }

        /* Remove scrollbar styling */
        .sidebar::-webkit-scrollbar {
            width: 4px;
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }
        
        .sidebar::-webkit-scrollbar-track {
            background-color: transparent;
        }

        /* Improved dropdown menu */
        .dropdown-content {
            max-height: 0;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            overflow: hidden;
            margin-left: 0.5rem;
        }

        .dropdown-content.active {
            max-height: 500px;
            opacity: 1;
            visibility: visible;
            padding: 0.5rem 0;
            border-left: 2px solid rgb(79, 70, 229);
            margin-top: 0.25rem;
            margin-bottom: 0.25rem;
        }

        /* Active menu item indication */
        .sidebar a.active,
        .dropdown-btn.active {
            background-color: rgb(79, 70, 229);
            color: white;
        }
        
        /* Improved hover effects */
        .sidebar a:hover,
        .dropdown-btn:hover {
            background-color: rgba(79, 70, 229, 0.8);
        }
        
        /* Fix for mobile responsiveness */
        @media (max-width: 640px) {
            .sidebar.hover:w-64 {
                width: 16rem;
            }
            
            #main-content {
                margin-left: 1rem;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dropdownBtns = document.querySelectorAll('.dropdown-btn');
            const sidebar = document.querySelector('.sidebar');
            
            // Get current page path
            const currentPath = window.location.pathname;
            
            // Helper function to get parent section from path
            function getSection(path) {
                const sections = ['unit', 'karyawan', 'tiket', 'keuangan', 'sopir'];
                return sections.find(section => path.includes('/' + section + '/'));
            }

            // Set active state only for parent menu without opening submenus
            const currentSection = getSection(currentPath);
            if (currentSection) {
                dropdownBtns.forEach(btn => {
                    const btnText = btn.querySelector('span').textContent.toLowerCase();
                    if (btnText === currentSection) {
                        btn.classList.add('active');
                    }
                });
            }
            
            function toggleDropdown(btn) {
                const content = btn.nextElementSibling;
                const isOpen = content.classList.contains('active');
                
                // Close all dropdowns first
                dropdownBtns.forEach(otherBtn => {
                    const otherContent = otherBtn.nextElementSibling;
                    otherContent.classList.remove('active');
                    otherBtn.querySelector('.dropdown-arrow').style.transform = 'rotate(0)';
                });

                // Toggle clicked dropdown
                content.classList.toggle('active');
                btn.querySelector('.dropdown-arrow').style.transform = isOpen ? 'rotate(0)' : 'rotate(180deg)';
            }

            // Click handlers for dropdowns
            dropdownBtns.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    toggleDropdown(btn);
                });
            });
            
            // Sidebar hover behavior for mobile/desktop
            sidebar.addEventListener('mouseenter', () => {
                sidebar.classList.add('hovered');
                document.querySelector('#main-content').classList.add('ml-64');
            });
            
            sidebar.addEventListener('mouseleave', () => {
                sidebar.classList.remove('hovered');
                document.querySelector('#main-content').classList.remove('ml-64');
                
                // Keep dropdowns open if they have active items
                dropdownBtns.forEach(btn => {
                    const content = btn.nextElementSibling;
                    const hasActiveItem = content.querySelector('.active');
                    if (!hasActiveItem) {
                        content.classList.remove('active');
                        btn.classList.remove('active');
                        btn.querySelector('.dropdown-arrow').style.transform = 'rotate(0)';
                    }
                });
            });
            
            // Handle clicks outside sidebar to close dropdowns on mobile
            document.addEventListener('click', (e) => {
                if (!e.target.closest('.sidebar') && window.innerWidth < 768) {
                    dropdownBtns.forEach(btn => {
                        const content = btn.nextElementSibling;
                        const hasActiveItem = content.querySelector('.active');
                        if (!hasActiveItem) {
                            content.classList.remove('active');
                            btn.classList.remove('active');
                            btn.querySelector('.dropdown-arrow').style.transform = 'rotate(0)';
                        }
                    });
                }
            });
        });
    </script>
</div>