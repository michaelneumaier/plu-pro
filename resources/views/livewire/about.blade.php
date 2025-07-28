<div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white">
        <div class="max-w-7xl mx-auto px-4 py-16 sm:py-24">
            <div class="text-center">
                <h1 class="text-4xl sm:text-5xl font-bold mb-6">
                    About PLU Pro
                </h1>
                <p class="text-xl sm:text-2xl text-blue-100 max-w-4xl mx-auto leading-relaxed">
                    The comprehensive platform for managing, organizing, and sharing PLU codes across the produce industry.
                    Built by professionals, for professionals.
                </p>
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 py-12">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                <div>
                    <div class="text-3xl font-bold text-blue-600">{{ number_format($stats['total_plus']) }}+</div>
                    <div class="text-gray-600 mt-1">PLU Codes</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-green-600">{{ number_format($stats['commodities']) }}+</div>
                    <div class="text-gray-600 mt-1">Commodities</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-purple-600">{{ number_format($stats['total_lists']) }}+</div>
                    <div class="text-gray-600 mt-1">Lists Created</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-orange-600">{{ number_format($stats['total_users']) }}+</div>
                    <div class="text-gray-600 mt-1">Active Users</div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-16">
        
        <!-- Mission Statement -->
        <div class="text-center mb-16">
            <h2 class="text-3xl font-bold text-gray-900 mb-8">Our Mission</h2>
            <div class="max-w-4xl mx-auto">
                <p class="text-lg text-gray-700 leading-relaxed mb-6">
                    PLU Pro was created to solve the everyday challenges faced by produce professionals. From grocery store managers 
                    organizing seasonal displays to restaurant chains standardizing ingredient codes, we provide the tools needed 
                    to streamline produce management operations.
                </p>
                <p class="text-lg text-gray-700 leading-relaxed">
                    Our platform combines comprehensive PLU code data with powerful list management, real-time inventory tracking, 
                    and seamless collaboration features—all designed to save time, reduce errors, and improve efficiency across 
                    the produce supply chain.
                </p>
            </div>
        </div>

        <!-- The Problem We Solve -->
        <div class="mb-16">
            <h2 class="text-3xl font-bold text-gray-900 text-center mb-12">The Problem We Solve</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">PLU Code Confusion</h3>
                    <p class="text-gray-600">
                        Teams struggle to find accurate PLU codes, leading to checkout errors, inventory mistakes, 
                        and frustrated customers. Scattered information across multiple sources creates inefficiency.
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Manual Inventory Tracking</h3>
                    <p class="text-gray-600">
                        Paper-based or spreadsheet inventory systems are prone to errors, difficult to share, 
                        and impossible to update in real-time across multiple locations or team members.
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Poor Team Collaboration</h3>
                    <p class="text-gray-600">
                        Teams lack effective ways to share PLU lists, coordinate inventory updates, 
                        or maintain consistent produce management practices across departments and locations.
                    </p>
                </div>
            </div>
        </div>

        <!-- Our Solution -->
        <div class="mb-16">
            <h2 class="text-3xl font-bold text-gray-900 text-center mb-12">Our Solution</h2>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="space-y-8">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Comprehensive PLU Database</h3>
                            <p class="text-gray-600">
                                Access over {{ number_format($stats['total_plus']) }} PLU codes covering {{ $stats['commodities'] }}+ commodity categories. 
                                From common items like bananas (4011) to specialty organic varieties, find accurate codes instantly.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Smart List Management</h3>
                            <p class="text-gray-600">
                                Create custom PLU lists for any purpose—seasonal displays, vendor catalogs, inventory counts, 
                                or department-specific items. Organize by commodity, supplier, or any system that works for you.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Real-Time Inventory Tracking</h3>
                            <p class="text-gray-600">
                                Track stock levels in real-time with mobile-optimized controls. Half-unit increments, 
                                instant updates, and offline capability ensure accurate counts anywhere in your facility.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Seamless Collaboration</h3>
                            <p class="text-gray-600">
                                Share lists with team members, publish to the marketplace, or create public resources. 
                                Perfect for multi-location operations or vendor coordination.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-blue-50 to-indigo-100 rounded-lg p-8">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Why Choose PLU Pro?</h3>
                    <ul class="space-y-3">
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700">Industry's most comprehensive PLU database</span>
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700">Mobile-first design for on-the-go management</span>
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700">Offline-capable for warehouse environments</span>
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700">Automatic organic PLU code generation</span>
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700">Barcode generation for scanning workflows</span>
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700">Built-in marketplace for sharing best practices</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Use Cases -->
        <div class="mb-16">
            <h2 class="text-3xl font-bold text-gray-900 text-center mb-12">Who Uses PLU Pro</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Grocery Retailers</h3>
                    <p class="text-gray-600 text-sm">
                        Store managers, produce buyers, and department heads organizing seasonal displays, 
                        training new staff, and maintaining accurate inventory counts.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Restaurant Chains</h3>
                    <p class="text-gray-600 text-sm">
                        Kitchen managers and procurement teams standardizing ingredient codes across 
                        multiple locations for consistent ordering and inventory management.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Distributors</h3>
                    <p class="text-gray-600 text-sm">
                        Wholesale distributors coordinating with suppliers and retailers, managing 
                        seasonal catalogs, and ensuring accurate product identification.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 4v12l-4-2-4 2V4M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Farmers Markets</h3>
                    <p class="text-gray-600 text-sm">
                        Vendors tracking seasonal inventory, educating customers about organic options, 
                        and maintaining consistent pricing across different market locations.
                    </p>
                </div>
            </div>
        </div>

        <!-- Features Deep Dive -->
        <div class="mb-16">
            <h2 class="text-3xl font-bold text-gray-900 text-center mb-12">Platform Features</h2>
            <div class="space-y-12">
                
                <!-- Feature 1: List Management -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="grid grid-cols-1 lg:grid-cols-2">
                        <div class="p-8">
                            <div class="flex items-center mb-4">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-900">Smart List Management</h3>
                            </div>
                            <p class="text-gray-600 mb-4">
                                Create unlimited custom lists tailored to your specific needs. Whether organizing by season, 
                                supplier, department, or any custom category, PLU Pro makes it simple.
                            </p>
                            <ul class="space-y-2 text-sm">
                                <li class="flex items-center">
                                    <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Drag-and-drop organization
                                </li>
                                <li class="flex items-center">
                                    <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Bulk import/export capabilities
                                </li>
                                <li class="flex items-center">
                                    <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Automatic organic variant detection
                                </li>
                                <li class="flex items-center">
                                    <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Advanced filtering and search
                                </li>
                            </ul>
                        </div>
                        <div class="bg-gray-50 p-8 flex items-center">
                            <div class="w-full">
                                <div class="bg-white rounded-lg p-4 shadow-sm mb-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="font-medium text-gray-900">Fall Seasonal Display</span>
                                        <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">Public</span>
                                    </div>
                                    <p class="text-xs text-gray-500">24 items • Updated 2 days ago</p>
                                </div>
                                <div class="bg-white rounded-lg p-4 shadow-sm mb-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="font-medium text-gray-900">Organic Vendor A</span>
                                        <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">Marketplace</span>
                                    </div>
                                    <p class="text-xs text-gray-500">18 items • Updated 1 week ago</p>
                                </div>
                                <div class="bg-white rounded-lg p-4 shadow-sm">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="font-medium text-gray-900">Citrus Inventory</span>
                                        <span class="text-xs bg-gray-100 text-gray-800 px-2 py-1 rounded">Private</span>
                                    </div>
                                    <p class="text-xs text-gray-500">12 items • Updated today</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Feature 2: Inventory Tracking -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="grid grid-cols-1 lg:grid-cols-2">
                        <div class="bg-gray-50 p-8 flex items-center order-2 lg:order-1">
                            <div class="w-full text-center">
                                <div class="bg-white rounded-lg p-6 shadow-sm inline-block">
                                    <div class="flex items-center justify-center space-x-4 mb-4">
                                        <button class="w-8 h-8 bg-red-500 text-white rounded-full flex items-center justify-center">-</button>
                                        <span class="text-2xl font-bold text-gray-900">47.5</span>
                                        <button class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center">+</button>
                                    </div>
                                    <div class="text-sm text-gray-600">Cases in stock</div>
                                    <div class="text-xs text-gray-500 mt-1">Bananas - PLU 4011</div>
                                </div>
                            </div>
                        </div>
                        <div class="p-8 order-1 lg:order-2">
                            <div class="flex items-center mb-4">
                                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-900">Real-Time Inventory</h3>
                            </div>
                            <p class="text-gray-600 mb-4">
                                Track stock levels with precision using our mobile-optimized inventory controls. 
                                Support for half-unit increments, instant updates, and offline synchronization.
                            </p>
                            <ul class="space-y-2 text-sm">
                                <li class="flex items-center">
                                    <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Touch-optimized +/- controls
                                </li>
                                <li class="flex items-center">
                                    <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Half-unit increment support (0.5, 1.5, etc.)
                                </li>
                                <li class="flex items-center">
                                    <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Offline mode with auto-sync
                                </li>
                                <li class="flex items-center">
                                    <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Real-time team collaboration
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Feature 3: Marketplace -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="grid grid-cols-1 lg:grid-cols-2">
                        <div class="p-8">
                            <div class="flex items-center mb-4">
                                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-900">Marketplace & Sharing</h3>
                            </div>
                            <p class="text-gray-600 mb-4">
                                Share your expertise with the community. Publish lists to the marketplace, 
                                collaborate with team members, or create public resources for customers.
                            </p>
                            <ul class="space-y-2 text-sm">
                                <li class="flex items-center">
                                    <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Public marketplace for community sharing
                                </li>
                                <li class="flex items-center">
                                    <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Team collaboration tools
                                </li>
                                <li class="flex items-center">
                                    <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    QR code generation for easy sharing
                                </li>
                                <li class="flex items-center">
                                    <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Copy and customize shared lists
                                </li>
                            </ul>
                        </div>
                        <div class="bg-gray-50 p-8 flex items-center">
                            <div class="w-full space-y-4">
                                <div class="bg-white rounded-lg p-4 shadow-sm">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="font-medium text-gray-900">Holiday Fruit Display</span>
                                        <div class="flex space-x-1">
                                            <span class="text-xs">👁️ 234</span>
                                            <span class="text-xs">📥 52</span>
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500">Featured seasonal fruits for holiday displays</p>
                                    <div class="mt-2">
                                        <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">Seasonal</span>
                                    </div>
                                </div>
                                <div class="bg-white rounded-lg p-4 shadow-sm">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="font-medium text-gray-900">Organic Essentials</span>
                                        <div class="flex space-x-1">
                                            <span class="text-xs">👁️ 189</span>
                                            <span class="text-xs">📥 43</span>
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500">Core organic produce items for any store</p>
                                    <div class="mt-2">
                                        <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">Organic Focus</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Technology Stack -->
        <div class="mb-16">
            <h2 class="text-3xl font-bold text-gray-900 text-center mb-12">Built for Performance</h2>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Mobile-First Design</h3>
                        <p class="text-gray-600 text-sm">
                            Optimized for tablets and smartphones with touch-friendly controls, 
                            offline capability, and responsive layouts that work anywhere.
                        </p>
                    </div>

                    <div class="text-center">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Lightning Fast</h3>
                        <p class="text-gray-600 text-sm">
                            Advanced caching, optimized queries, and modern architecture ensure 
                            instant loading times even with large datasets.
                        </p>
                    </div>

                    <div class="text-center">
                        <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Enterprise Security</h3>
                        <p class="text-gray-600 text-sm">
                            Bank-level security with encrypted data transmission, secure authentication, 
                            and regular security audits to protect your business data.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Call to Action -->
        <div class="text-center bg-gradient-to-r from-blue-600 to-indigo-700 rounded-lg p-12 text-white">
            <h2 class="text-3xl font-bold mb-4">Ready to Streamline Your Produce Management?</h2>
            <p class="text-xl text-blue-100 mb-8 max-w-3xl mx-auto">
                Join thousands of produce professionals who trust PLU Pro to manage their PLU codes, 
                track inventory, and collaborate with their teams.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                @auth
                    <a href="{{ route('dashboard') }}" 
                       class="inline-flex items-center px-8 py-4 bg-white text-blue-600 font-semibold rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2"></path>
                        </svg>
                        Go to Dashboard
                    </a>
                    <a href="{{ route('lists.index') }}" 
                       class="inline-flex items-center px-8 py-4 border-2 border-white text-white font-semibold rounded-lg hover:bg-white hover:text-blue-600 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Create Your First List
                    </a>
                @else
                    <a href="{{ route('register') }}" 
                       class="inline-flex items-center px-8 py-4 bg-white text-blue-600 font-semibold rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                        Start Free Today
                    </a>
                    <a href="{{ route('home') }}" 
                       class="inline-flex items-center px-8 py-4 border-2 border-white text-white font-semibold rounded-lg hover:bg-white hover:text-blue-600 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Browse PLU Codes
                    </a>
                @endauth
            </div>
        </div>
    </div>
</div>